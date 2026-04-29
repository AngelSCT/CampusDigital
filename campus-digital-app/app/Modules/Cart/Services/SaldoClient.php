<?php

namespace App\Modules\Cart\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP hacia el módulo Saldo Digital Universitario.
 *
 * Todos los endpoints y la URL base son configurables en config('cart.saldo.*')
 * para poder adaptarse al contrato final del equipo de Saldo sin tocar código.
 *
 * Mockeable en tests con Http::fake() — no requiere dobles adicionales.
 */
class SaldoClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('cart.saldo.base_url', 'http://localhost'), '/');
        $this->timeout = (int) config('cart.saldo.timeout', 3);
    }

    /**
     * Solicita una reserva (hold) de fondos al módulo Saldo.
     *
     * Contrato esperado:
     *   POST {base_url}/api/internal/saldo/reservar
     *   Response 200: { ok: bool, reserva_id: string|null, saldo_disponible: decimal, motivo: string|null }
     *   Response 402: fondos insuficientes
     *   5xx / timeout: servicio no disponible
     */
    public function reservar(string $usuarioRef, float $monto, string $carritoUuid): SaldoResult
    {
        $endpoint = (string) config('cart.saldo.endpoint_reservar', '/api/internal/saldo/reservar');

        try {
            $response = Http::timeout($this->timeout)
                ->retry(2, 200, fn($e) => $e instanceof ConnectionException)
                ->post($this->baseUrl . $endpoint, [
                    'usuario_ref'  => $usuarioRef,
                    'monto'        => $monto,
                    'carrito_uuid' => $carritoUuid,
                ]);

            if ($response->status() === 402) {
                return new SaldoInsufficientFunds();
            }

            if ($response->successful()) {
                return new SaldoConfirmed($response->json('reserva_id'));
            }

            return new SaldoUnavailable();
        } catch (\Exception) {
            return new SaldoUnavailable();
        }
    }

    /**
     * Registra un cargo forzoso (saldo negativo) cuando la conciliación detecta
     * que el usuario no tenía fondos al momento de cobrar.
     *
     * Contrato esperado:
     *   POST {base_url}/api/internal/saldo/cargo-forzoso
     *   Response 200: éxito
     *
     * @return bool true si el módulo Saldo confirmó el registro.
     */
    public function cargoForzoso(string $usuarioRef, float $monto, string $carritoUuid): bool
    {
        $endpoint = (string) config('cart.saldo.endpoint_cargo_forzoso', '/api/internal/saldo/cargo-forzoso');

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . $endpoint, [
                    'usuario_ref'  => $usuarioRef,
                    'monto'        => $monto,
                    'carrito_uuid' => $carritoUuid,
                    'motivo'       => 'conciliacion_deuda',
                ]);

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
