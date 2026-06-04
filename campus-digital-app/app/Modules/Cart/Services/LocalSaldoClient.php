<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Carrito;
use App\Models\SaldoMonedero;

/**
 * Implementación local de SaldoClient que usa SaldoMonedero directamente.
 *
 * Activa cuando CART_SALDO_LOCAL_MODE=true (entorno de desarrollo/demo integrado).
 * El contrato HTTP hacia M4.2 se reemplaza por operaciones directas en la BD.
 *
 * Mapa de operaciones:
 *   reservar()     → SaldoMonedero::retener()  (disponible → retenido)
 *   confirmar()    → decrement(saldo_retenido)  (consumir hold)
 *   liberar()      → retenido → disponible      (cancelar hold)
 *   cargoForzoso() → SaldoMonedero::cargar()    (cargo directo desde disponible)
 */
class LocalSaldoClient extends SaldoClient
{
    public function reservar(
        string $usuarioRef,
        float  $monto,
        string $carritoUuid,
        string $moduloSlug,
        string $concepto,
    ): SaldoResult {
        try {
            $usuarioId = $this->resolverUsuarioId($usuarioRef);
            if (!$usuarioId) {
                return new SaldoInsufficientFunds();
            }

            $monedero = SaldoMonedero::obtenerOCrear($usuarioId);

            if (!$monedero->tieneSaldo($monto)) {
                return new SaldoInsufficientFunds();
            }

            // disponible → retenido (hold)
            $monedero->retener(
                $monto,
                "Reserva carrito {$carritoUuid}",
                $moduloSlug ?: 'otro'
            );

            return new SaldoConfirmed(
                reservaId: $carritoUuid,  // carritoUuid actúa como reservaId en modo local
                expiraAt:  now()->addMinutes(5)->toISOString()
            );
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'insuficiente') || str_contains($e->getMessage(), 'Saldo')) {
                return new SaldoInsufficientFunds();
            }
            return new SaldoUnavailable();
        }
    }

    public function confirmar(string $reservaId, string $carritoUuid): bool
    {
        // En modo local, reservaId = carritoUuid
        try {
            $carrito = Carrito::where('uuid', $reservaId)->first();
            if (!$carrito) return false;

            $usuarioId = $this->resolverUsuarioId($carrito->usuario_ref);
            if (!$usuarioId) return false;

            $monedero    = SaldoMonedero::obtenerOCrear($usuarioId);
            $monto       = (float) $carrito->total;
            $liberarMonto = min($monto, (float) $monedero->saldo_retenido);

            if ($liberarMonto > 0) {
                $monedero->decrement('saldo_retenido', $liberarMonto);
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function liberar(string $reservaId): bool
    {
        try {
            $carrito = Carrito::where('uuid', $reservaId)->first();
            if (!$carrito) return true;

            $usuarioId = $this->resolverUsuarioId($carrito->usuario_ref);
            if (!$usuarioId) return true;

            $monedero     = SaldoMonedero::obtenerOCrear($usuarioId);
            $monto        = (float) $carrito->total;
            $liberarMonto = min($monto, (float) $monedero->saldo_retenido);

            if ($liberarMonto > 0) {
                // retenido → disponible (cancelar hold)
                $monedero->decrement('saldo_retenido', $liberarMonto);
                $monedero->increment('saldo_disponible', $liberarMonto);
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cargoForzoso(
        string  $usuarioRef,
        float   $monto,
        string  $carritoUuid,
        string  $concepto,
        ?string $carritoEstado = null,
    ): SaldoResult {
        try {
            $usuarioId = $this->resolverUsuarioId($usuarioRef);
            if (!$usuarioId) {
                return new CargoForzosoRechazado();
            }

            $monedero = SaldoMonedero::obtenerOCrear($usuarioId);
            $monedero->cargar($monto, $concepto ?: 'Cargo forzoso desde carrito', 'carrito');

            return new CargoForzosoCobrado();
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'insuficiente') || str_contains($e->getMessage(), 'Saldo')) {
                return new CargoForzosoRechazado();
            }
            return new CargoForzosoDesconocido();
        }
    }

    private function resolverUsuarioId(string $usuarioRef): ?int
    {
        if (is_numeric($usuarioRef)) {
            return (int) $usuarioRef;
        }
        try {
            $usuario = \App\Models\Usuario::where('matricula', $usuarioRef)->first();
            return $usuario?->id;
        } catch (\Throwable) {
            return null;
        }
    }
}
