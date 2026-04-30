<?php

namespace App\Modules\Cart\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SaldoClient
{
    private string $baseUrl;
    private int    $timeout;
    private string $internalToken;

    public function __construct()
    {
        $this->baseUrl       = rtrim((string) config('cart.saldo.base_url', 'http://localhost'), '/');
        $this->timeout       = (int) config('cart.saldo.timeout', 3);
        $this->internalToken = (string) config('cart.saldo.internal_token', '');
    }

    /**
     * Solicita una reserva (hold) de fondos.
     *
     * POST /api/internal/saldo/reservar
     * 200 → { ok, reserva_id, saldo_disponible_post_reserva, expira_at }
     * 402 → fondos insuficientes
     * 503 → servicio no disponible
     */
    public function reservar(
        string $usuarioRef,
        float  $monto,
        string $carritoUuid,
        string $moduloSlug,
        string $concepto,
    ): SaldoResult {
        $endpoint = (string) config('cart.saldo.endpoint_reservar', '/api/internal/saldo/reservar');

        try {
            $response = $this->http()
                ->retry(2, 200, fn($e) => $e instanceof ConnectionException)
                ->post($this->baseUrl . $endpoint, [
                    'usuario_ref'  => $usuarioRef,
                    'monto'        => $monto,
                    'carrito_uuid' => $carritoUuid,
                    'modulo_slug'  => $moduloSlug,
                    'concepto'     => $concepto,
                ]);

            if ($response->status() === 402) {
                return new SaldoInsufficientFunds();
            }

            if ($response->successful()) {
                return new SaldoConfirmed(
                    reservaId: $response->json('reserva_id'),
                    expiraAt:  $response->json('expira_at'),
                );
            }

            return new SaldoUnavailable();
        } catch (\Exception) {
            return new SaldoUnavailable();
        }
    }

    /**
     * Confirma (ejecuta) una reserva existente.
     *
     * POST /api/internal/saldo/confirmar/{reserva_id}
     * 200 → ok
     * 409 → reserva_expirada | reserva_ya_consumida → false
     */
    public function confirmar(string $reservaId, string $carritoUuid): bool
    {
        $template = (string) config(
            'cart.saldo.endpoint_confirmar',
            '/api/internal/saldo/confirmar/{reserva_id}'
        );
        $endpoint = str_replace('{reserva_id}', rawurlencode($reservaId), $template);

        try {
            $response = $this->http()
                ->post($this->baseUrl . $endpoint, [
                    'carrito_uuid' => $carritoUuid,
                ]);

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Libera (cancela) una reserva para devolver los fondos retenidos.
     *
     * POST /api/internal/saldo/liberar/{reserva_id}
     * 200 → ok
     */
    public function liberar(string $reservaId): bool
    {
        $template = (string) config(
            'cart.saldo.endpoint_liberar',
            '/api/internal/saldo/liberar/{reserva_id}'
        );
        $endpoint = str_replace('{reserva_id}', rawurlencode($reservaId), $template);

        try {
            return $this->http()
                ->post($this->baseUrl . $endpoint)
                ->successful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Registra un cargo forzoso (puede dejar saldo negativo).
     * Usado por el job de conciliación diferida.
     *
     * POST /api/internal/saldo/cargo-forzoso
     * 200 → ok
     */
    public function cargoForzoso(
        string  $usuarioRef,
        float   $monto,
        string  $carritoUuid,
        string  $concepto,
        ?string $carritoEstado = null,
    ): bool {
        $endpoint = (string) config(
            'cart.saldo.endpoint_cargo_forzoso',
            '/api/internal/saldo/cargo-forzoso'
        );

        try {
            return $this->http()
                ->post($this->baseUrl . $endpoint, array_filter([
                    'usuario_ref'    => $usuarioRef,
                    'monto'          => $monto,
                    'carrito_uuid'   => $carritoUuid,
                    'concepto'       => $concepto,
                    'carrito_estado' => $carritoEstado,
                ]))
                ->successful();
        } catch (\Exception) {
            return false;
        }
    }

    private function http()
    {
        return Http::timeout($this->timeout)
            ->withHeaders(['X-Internal-Token' => $this->internalToken]);
    }
}
