<?php

namespace App\Modules\Cart\Support;

use App\Modules\Cart\Exceptions\Client\CartApiUnavailableException;
use App\Modules\Cart\Exceptions\Client\CartScopeException;
use App\Modules\Cart\Exceptions\Client\CartValidationException;
use App\Modules\Cart\Exceptions\Client\InsufficientFundsException;
use App\Modules\Cart\Exceptions\Client\ModuleTokenExpiredException;
use App\Modules\Cart\Exceptions\Client\SaldoUnavailableForClientException;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Trait para controladores de módulos clientes que necesitan hablar con
 * la API privada del Módulo Carrito.
 *
 * Gestión automática del refresh token: si la API devuelve 401 TOKEN_EXPIRED,
 * el trait intenta refrescar el token UNA vez, actualiza la memoria estática
 * y reintenta la llamada original. El caller nunca necesita manejar este ciclo.
 *
 * Ver app/Modules/Cart/Support/README.md para guía de integración completa.
 */
trait ConsumesCartApi
{
    // Static so the refreshed token persists across multiple calls in the same request.
    private static ?string $_cartAccessToken  = null;
    private static ?string $_cartRefreshToken = null;

    // ─── Public API ──────────────────────────────────────────────────────────

    public function createCart(
        string $usuarioRef,
        bool   $requiereSaldo   = false,
        int    $expiraEnMinutos = 30,
        array  $metadata        = [],
    ): array {
        return $this->cartCall(fn(PendingRequest $h) => $h->post('/carritos', [
            'usuario_ref'       => $usuarioRef,
            'requiere_saldo'    => $requiereSaldo,
            'expira_en_minutos' => $expiraEnMinutos,
            'metadata'          => $metadata,
        ]));
    }

    public function getCart(string $uuid): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->get("/carritos/{$uuid}"));
    }

    public function addItem(string $uuid, array $item): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->post("/carritos/{$uuid}/items", $item));
    }

    public function removeItem(string $uuid, int $itemId): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->delete("/carritos/{$uuid}/items/{$itemId}"));
    }

    public function checkout(string $uuid, array $metadataCheckout = []): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->post("/carritos/{$uuid}/checkout", [
            'metadata_checkout' => $metadataCheckout,
        ]));
    }

    public function cancel(string $uuid): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->post("/carritos/{$uuid}/cancelar"));
    }

    public function returnItem(string $uuid, int $itemId): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->post("/carritos/{$uuid}/items/{$itemId}/devolver"));
    }

    public function history(array $filters = []): array
    {
        return $this->cartCall(fn(PendingRequest $h) => $h->get('/carritos/historial', $filters));
    }

    /** Fuerza el refresh del token de módulo. Útil para warm-up al iniciar la aplicación. */
    public function refreshToken(): array
    {
        return $this->doCartRefresh();
    }

    // ─── Internal ────────────────────────────────────────────────────────────

    /**
     * Executes an HTTP call against the Cart API.
     * On 401 TOKEN_EXPIRED: refreshes once and retries.
     */
    private function cartCall(Closure $build): array
    {
        try {
            $response = $build($this->cartHttp());
        } catch (ConnectionException) {
            throw new CartApiUnavailableException(
                'El servicio de Carrito no está disponible en este momento.'
            );
        }

        if ($response->status() === 401) {
            if ($response->json('code') === 'TOKEN_EXPIRED') {
                $this->doCartRefresh();
                try {
                    $response = $build($this->cartHttp());
                } catch (ConnectionException) {
                    throw new CartApiUnavailableException(
                        'El servicio de Carrito no está disponible en este momento.'
                    );
                }

                if ($response->status() === 401) {
                    throw new ModuleTokenExpiredException(
                        'El token del módulo expiró. Solicita uno nuevo al admin.'
                    );
                }
            } else {
                // TOKEN_MISSING, TOKEN_INVALID, MODULE_INACTIVE, etc.
                throw new ModuleTokenExpiredException(
                    $response->json('message')
                    ?? 'Token de módulo inválido o no configurado. Ejecuta: php artisan carrito:demo-biblioteca-setup'
                );
            }
        }

        return $this->parseCartResponse($response);
    }

    private function doCartRefresh(): array
    {
        try {
            $response = Http::withToken($this->cartRefreshTokenValue())
                ->baseUrl($this->cartApiBaseUrl())
                ->acceptJson()
                ->post('/tokens/refresh');
        } catch (ConnectionException) {
            throw new CartApiUnavailableException(
                'El servicio de Carrito no está disponible en este momento.'
            );
        }

        if ($response->status() === 401) {
            throw new ModuleTokenExpiredException(
                'El token del módulo expiró. Solicita uno nuevo al admin.'
            );
        }

        if ($response->serverError()) {
            throw new CartApiUnavailableException(
                'El servicio de Carrito no está disponible en este momento.'
            );
        }

        $data = $response->json() ?? [];
        static::$_cartAccessToken  = $data['access_token']  ?? null;
        static::$_cartRefreshToken = $data['refresh_token'] ?? null;

        return $data;
    }

    private function parseCartResponse(Response $response): array
    {
        // Domain errors first (before the generic 5xx guard)
        match ($response->status()) {
            422 => throw new CartValidationException(
                $response->json('message', 'Datos inválidos.'),
                $response->json('errors', [])
            ),
            403 => throw new CartScopeException(
                $response->json('message', 'Acción no permitida para este módulo.')
            ),
            402 => throw new InsufficientFundsException(
                $response->json('message', 'Saldo insuficiente.')
            ),
            503 => throw new SaldoUnavailableForClientException(
                $response->json('message', 'Servicio de saldo no disponible.')
            ),
            default => null,
        };

        // Generic 5xx (500, 502, 504, …)
        if ($response->serverError()) {
            throw new CartApiUnavailableException(
                'El servicio de Carrito no está disponible en este momento.'
            );
        }

        return $response->json() ?? [];
    }

    private function cartHttp(): PendingRequest
    {
        return Http::withToken($this->cartAccessTokenValue())
            ->baseUrl($this->cartApiBaseUrl())
            ->acceptJson();
    }

    private function cartApiBaseUrl(): string
    {
        return rtrim((string) config('services.cart.url', config('app.url', 'http://localhost')), '/') . '/api/cart';
    }

    protected function cartAccessTokenValue(): string
    {
        return static::$_cartAccessToken
            ?? (string) config('cart.client.module_token', '');
    }

    protected function cartRefreshTokenValue(): string
    {
        return static::$_cartRefreshToken
            ?? (string) config('cart.client.refresh_token', '');
    }

    /** Resets static token cache. Call in tests tearDown to prevent pollution. */
    public static function resetCartTokenCache(): void
    {
        static::$_cartAccessToken  = null;
        static::$_cartRefreshToken = null;
    }
}
