<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\SaldoInsufficientFundsException;
use App\Modules\Cart\Exceptions\SaldoUnavailableException;

class CheckoutService
{
    public function __construct(
        private readonly CarritoService $carritoService,
        private readonly SaldoClient $saldoClient,
    ) {}

    /**
     * Confirma el carrito.
     *
     * Flujo sección C5 / contrato v1.2:
     *  - requiere_saldo=false → confirma directo.
     *  - requiere_saldo=true  →
     *      1. reservar() al módulo Saldo.
     *      2. SaldoConfirmed  → confirmarDirecto() + confirmar(reserva_id).
     *         - confirmar() falla (409) → revierte carrito a 'abierto', registra incidente.
     *         - Excepción inesperada entre reservar y confirmar → liberar(reserva_id).
     *      3. SaldoInsufficientFunds → SaldoInsufficientFundsException (→ 402).
     *      4. SaldoUnavailable + sin diferido → SaldoUnavailableException (→ 503).
     *      5. SaldoUnavailable + diferido + dentro de topes → confirmado_pendiente_conciliacion.
     *
     * @throws CartStateException
     * @throws CartBusinessException
     * @throws SaldoInsufficientFundsException
     * @throws SaldoUnavailableException
     */
    public function confirmar(Carrito $carrito, array $data): Carrito
    {
        $this->carritoService->assertOperable($carrito);

        $items = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->with('categoria.reglas')
            ->get();

        if ($items->isEmpty()) {
            throw new CartBusinessException('No se puede hacer checkout de un carrito vacío.');
        }

        if (!$carrito->requiere_saldo) {
            return $this->confirmarDirecto($carrito, $data);
        }

        // ── Integración con Saldo ──────────────────────────────────────────
        $total     = (float) $carrito->total;
        $moduloSlug = $carrito->modulo?->slug ?? '';

        $result = $this->saldoClient->reservar(
            $carrito->usuario_ref,
            $total,
            $carrito->uuid,
            $moduloSlug,
            'checkout',
        );

        if ($result instanceof SaldoConfirmed) {
            return $this->confirmarConReserva($carrito, $data, $result->reservaId);
        }

        if ($result instanceof SaldoInsufficientFunds) {
            throw new SaldoInsufficientFundsException('Saldo insuficiente para completar el pago.');
        }

        // SaldoUnavailable — determinar si la categoría permite pago diferido.
        $permiteDiferido = $items->every(function ($item) {
            $regla = $item->categoria->reglas->firstWhere('clave', 'permite_pago_diferido');
            return $regla ? $regla->valorCasteado() : false;
        });

        if (!$permiteDiferido) {
            throw new SaldoUnavailableException('Servicio de saldo no disponible, intenta más tarde.');
        }

        // Verificar topes de exposición (sección 5.3 del cambio C5).
        $topeUsuario = (float) config('cart.saldo.tope_pendiente_por_usuario', 200);
        $topeGlobal  = (float) config('cart.saldo.tope_pendiente_global', 50000);

        $pendienteUsuario = (float) ConciliacionPendiente::where('usuario_ref', $carrito->usuario_ref)
            ->where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
            ->sum('monto');

        if ($pendienteUsuario + $total > $topeUsuario) {
            throw new SaldoUnavailableException('Se ha alcanzado el tope de crédito diferido para este usuario.');
        }

        $pendienteGlobal = (float) ConciliacionPendiente::where('estado_conciliacion', ConciliacionPendiente::ESTADO_PENDIENTE)
            ->sum('monto');

        if ($pendienteGlobal + $total > $topeGlobal) {
            throw new SaldoUnavailableException('El sistema no puede procesar más pagos diferidos en este momento.');
        }

        return $this->confirmarPendienteConciliacion($carrito, $data, $total);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Confirma el carrito y ejecuta la reserva en Saldo.
     * Si confirmar() falla (409) revierte el carrito a abierto y registra incidente.
     * Si hay una excepción inesperada ANTES de llamar a confirmar(), libera la reserva.
     */
    private function confirmarConReserva(Carrito $carrito, array $data, ?string $reservaId): Carrito
    {
        // True solo cuando confirmar() retornó normalmente (true o false).
        // Si lanza excepción, permanece false → finally libera la reserva.
        $confirmarCompletado = false;

        try {
            $this->confirmarDirecto($carrito, $data);

            $ok = $this->saldoClient->confirmar($reservaId, $carrito->uuid);
            $confirmarCompletado = true;

            if (!$ok) {
                // 409 — reserva expirada o ya consumida: revertir carrito
                $carrito->update(['estado' => Carrito::ESTADO_ABIERTO, 'confirmed_at' => null]);

                Bitacora::create([
                    'accion'       => 'saldo.confirmar_fallido',
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['reserva_id' => $reservaId, 'motivo' => 'confirmar_409'],
                ]);
            }

            return $carrito->fresh();
        } finally {
            // Excepción inesperada (incluyendo dentro de confirmar()) → liberar fondos retenidos.
            if (!$confirmarCompletado && $reservaId) {
                $this->saldoClient->liberar($reservaId);
            }
        }
    }

    private function confirmarDirecto(Carrito $carrito, array $data): Carrito
    {
        $carrito->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO,
            'confirmed_at' => now(),
            'metadata'     => array_merge(
                $carrito->metadata ?? [],
                ['metadata_checkout' => $data['metadata_checkout'] ?? null]
            ),
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CHECKOUT,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['total' => $carrito->total, 'tipo' => 'directo'],
        ]);

        return $carrito->fresh();
    }

    private function confirmarPendienteConciliacion(Carrito $carrito, array $data, float $total): Carrito
    {
        $carrito->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
            'confirmed_at' => now(),
            'metadata'     => array_merge(
                $carrito->metadata ?? [],
                ['metadata_checkout' => $data['metadata_checkout'] ?? null]
            ),
        ]);

        ConciliacionPendiente::create([
            'carrito_uuid'        => $carrito->uuid,
            'modulo_id'           => $carrito->modulo_id,
            'usuario_ref'         => $carrito->usuario_ref,
            'monto'               => $total,
            'intentos'            => 0,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
            'proximo_intento_at'  => now()->addMinutes(ConciliacionPendiente::BACKOFF_MINUTOS[0]),
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CHECKOUT,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['total' => $total, 'tipo' => 'pendiente_conciliacion'],
        ]);

        return $carrito->fresh();
    }
}
