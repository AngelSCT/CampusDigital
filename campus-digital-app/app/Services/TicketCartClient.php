<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP para los endpoints del Cart API (carritos de cobro).
 *
 * Autenticación mediante JWT Bearer token.
 * Configuración:
 *   - config('tickets.api.base_url')  → URL base compartida con TicketApiClient
 *   - config('tickets.cart.*')        → tokens JWT y timeout
 */
class TicketCartClient
{
    private string $baseUrl;
    private int    $timeout;
    private string $moduleToken;
    private string $refreshTokenStr;

    public function __construct()
    {
        $this->baseUrl         = rtrim((string) config('tickets.api.base_url', 'http://localhost'), '/');
        $this->timeout         = (int) config('tickets.cart.timeout', 5);
        $this->moduleToken     = (string) config('tickets.cart.module_token', '');
        $this->refreshTokenStr = (string) config('tickets.cart.refresh_token', '');
    }

    // ──────────────────────────────────────────────────────────────
    //  Carritos
    // ──────────────────────────────────────────────────────────────

    /**
     * POST /api/cart/carritos — Crea un carrito para el cobro de un ticket.
     *
     * Maneja 401 automáticamente: refresca el token y reintenta una vez.
     *
     * @param  string  $usuarioRef  Referencia del usuario (ej: su ID como string).
     * @param  int     $ticketId    ID del ticket para metadata.
     * @return array|null {uuid, estado, ...} o null si falla.
     */
    public function crearCarrito(string $usuarioRef, int $ticketId): ?array
    {
        $payload = [
            'usuario_ref'    => $usuarioRef,
            'requiere_saldo' => true,
            'metadata'       => [
                'origen'    => 'modulo_tickets',
                'ticket_id' => $ticketId,
            ],
        ];

        try {
            $response = $this->http()
                ->post($this->baseUrl . '/api/cart/carritos', $payload);

            // Manejo de 401 — refresh y reintento
            if ($response->status() === 401 && $this->refreshToken()) {
                $response = $this->http()
                    ->post($this->baseUrl . '/api/cart/carritos', $payload);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('TicketCartClient::crearCarrito — respuesta no exitosa', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('TicketCartClient::crearCarrito — excepción', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Ítems
    // ──────────────────────────────────────────────────────────────

    /**
     * POST /api/cart/carritos/{uuid}/items — Agrega un ítem de servicio de mantenimiento.
     *
     * Maneja 401 automáticamente: refresca el token y reintenta una vez.
     *
     * @param  string  $carritoUuid  UUID del carrito.
     * @param  int     $ticketId     ID del ticket.
     * @param  string  $nombre       Descripción del servicio (ej: 'Mantenimiento Ticket #123').
     * @param  float   $precio       Costo total del servicio.
     * @return array|null Resultado o null si falla.
     */
    public function agregarItemMantenimiento(
        string $carritoUuid,
        int    $ticketId,
        string $nombre,
        float  $precio,
    ): ?array {
        $endpoint = $this->baseUrl . '/api/cart/carritos/' . rawurlencode($carritoUuid) . '/items';

        $payload = [
            'categoria_slug'     => 'servicio',
            'referencia_externa' => 'TICKET-' . $ticketId,
            'nombre'             => $nombre,
            'precio_unitario'    => $precio,
            'cantidad'           => 1,
            'metadata'           => [
                'ticket_id' => $ticketId,
                'tipo'      => 'servicio_mantenimiento',
            ],
        ];

        try {
            $response = $this->http()
                ->post($endpoint, $payload);

            // Manejo de 401 — refresh y reintento
            if ($response->status() === 401 && $this->refreshToken()) {
                $response = $this->http()
                    ->post($endpoint, $payload);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('TicketCartClient::agregarItemMantenimiento — respuesta no exitosa', [
                'carrito_uuid' => $carritoUuid,
                'status'       => $response->status(),
                'body'         => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('TicketCartClient::agregarItemMantenimiento — excepción', [
                'carrito_uuid' => $carritoUuid,
                'message'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Token refresh
    // ──────────────────────────────────────────────────────────────

    /**
     * POST /api/cart/tokens/refresh — Refresca el token JWT usando el refresh token.
     *
     * Actualiza las propiedades en runtime ($moduleToken, $refreshTokenStr)
     * con los nuevos valores devueltos por el servidor.
     *
     * @return bool true si el refresh fue exitoso.
     */
    public function refreshToken(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/api/cart/tokens/refresh', [
                    'refresh_token' => $this->refreshTokenStr,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->moduleToken     = $data['access_token']  ?? $this->moduleToken;
                $this->refreshTokenStr = $data['refresh_token'] ?? $this->refreshTokenStr;

                // Actualiza la config en runtime para otros consumidores
                config([
                    'tickets.cart.module_token'  => $this->moduleToken,
                    'tickets.cart.refresh_token' => $this->refreshTokenStr,
                ]);

                Log::info('TicketCartClient::refreshToken — token refrescado exitosamente', [
                    'expires_in' => $data['expires_in'] ?? null,
                ]);

                return true;
            }

            Log::warning('TicketCartClient::refreshToken — respuesta no exitosa', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('TicketCartClient::refreshToken — excepción', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  HTTP base
    // ──────────────────────────────────────────────────────────────

    /**
     * Retorna un PendingRequest configurado con timeout, Bearer JWT y retry.
     */
    private function http()
    {
        return Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->moduleToken,
            ])
            ->retry(2, 200, fn ($e) => $e instanceof ConnectionException);
    }
}
