<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CheckoutRevertidoException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\SaldoInsufficientFundsException;
use App\Modules\Cart\Exceptions\SaldoUnavailableException;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private readonly CarritoService       $carritoService,
        private readonly SaldoClient          $saldoClient,
        private readonly PedidoCreatorInterface $pedidoCreator,
    ) {}

    /**
     * Confirma el carrito siguiendo el contrato v1.2 + integración Pedidos.
     *
     * Flujo según requiere_saldo:
     *
     *  false → confirmarSinSaldo():
     *      DB::transaction { confirmarDirecto() + crearDesdeCarrito() }
     *
     *  true  →
     *      1. [HTTP] reservar()
     *      2. SaldoConfirmed  → confirmarConReserva():
     *             DB::transaction { confirmarDirecto() + crearDesdeCarrito() }
     *             [HTTP] confirmar()  ← después del commit
     *             Si confirmar() falla → compensarPostCommit()
     *         SaldoInsufficientFunds → SaldoInsufficientFundsException (402)
     *         SaldoUnavailable + sin diferido → SaldoUnavailableException (503)
     *         SaldoUnavailable + diferido + topes → confirmarPendienteConciliacion()
     *             (el Pedido se crea cuando ReintentaConciliacion confirme el cobro)
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
            return $this->confirmarSinSaldo($carrito, $data);
        }

        // ── Integración con Saldo ──────────────────────────────────────────────
        // Paso 1: reservar [HTTP, ANTES de cualquier transacción de BD]
        $total      = (float) $carrito->total;
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
     * Path sin Saldo.
     *
     * La transacción garantiza atomicidad: si crearDesdeCarrito() lanza,
     * la confirmación del Carrito también se revierte. El Carrito pasa a
     * 'revertido' para señalizar que este intento de checkout falló de
     * forma irrecuperable (el usuario debe crear uno nuevo).
     */
    private function confirmarSinSaldo(Carrito $carrito, array $data): Carrito
    {
        try {
            DB::transaction(function () use ($carrito, $data) {
                $this->confirmarDirecto($carrito, $data);
                $this->pedidoCreator->crearDesdeCarrito($carrito);
                Bitacora::create([
                    'accion'       => Bitacora::ACCION_PEDIDO_CREADO,
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['usuario_ref' => $carrito->usuario_ref, 'tipo' => 'directo'],
                ]);
            });
        } catch (\Throwable $e) {
            // Rollback automático: carrito NO está confirmado en BD.
            // Lo marcamos 'revertido' para que no quede en estado ambiguo.
            try {
                $carrito->update(['estado' => Carrito::ESTADO_REVERTIDO]);
                Bitacora::create([
                    'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['motivo' => 'pedido_creation_failed'],
                ]);
            } catch (\Throwable) {}
            throw $e;
        }

        return $carrito->fresh();
    }

    /**
     * Path con reserva de Saldo confirmada.
     *
     * Orden garantizado (ninguna llamada HTTP dentro de la transacción):
     *   1. [HTTP] reservar()  ← ya ejecutado por confirmar() antes de llamar aquí
     *   2. DB::transaction { confirmarDirecto() + crearDesdeCarrito() }
     *   3. [HTTP] confirmar(reservaId)  ← después del commit
     *
     * Compensación:
     *   - Si la transacción falla (pre-commit) → liberar reserva + carrito='revertido'
     *   - Si confirmar() retorna false/409 post-commit → cancelar Pedido + liberar + carrito='revertido'
     *   - Si confirmar() lanza post-commit → idem
     */
    private function confirmarConReserva(Carrito $carrito, array $data, ?string $reservaId): Carrito
    {
        $transaccionCompletada = false;

        try {
            // Paso 2: transacción de BD [sin llamadas HTTP]
            DB::transaction(function () use ($carrito, $data) {
                $this->confirmarDirecto($carrito, $data);
                $this->pedidoCreator->crearDesdeCarrito($carrito);
                Bitacora::create([
                    'accion'       => Bitacora::ACCION_PEDIDO_CREADO,
                    'modulo_id'    => $carrito->modulo_id,
                    'carrito_uuid' => $carrito->uuid,
                    'payload'      => ['usuario_ref' => $carrito->usuario_ref, 'tipo' => 'con_saldo'],
                ]);
            });
            $transaccionCompletada = true;

            // Paso 3: confirmar Saldo [HTTP, DESPUÉS del commit de BD]
            $ok = $this->saldoClient->confirmar($reservaId, $carrito->uuid);

            if (!$ok) {
                // 409 — reserva expirada: Pedido creado pero cobro rechazado → compensar
                $this->compensarPostCommit($carrito, $reservaId, 'confirmar_409');
                // Ambos modos de fallo post-commit lanzan la misma excepción → 409 en el controller
                throw new CheckoutRevertidoException('La confirmación del cobro fue rechazada; el checkout fue revertido.');
            }

            return $carrito->fresh();

        } catch (CheckoutRevertidoException $e) {
            throw $e; // Ya compensado arriba, propagar sin envolver de nuevo
        } catch (\Throwable $e) {
            if (!$transaccionCompletada) {
                // La transacción falló (rollback automático) → liberar fondos retenidos + revertir
                try { $this->saldoClient->liberar($reservaId); } catch (\Throwable) {}
                try {
                    $carrito->update(['estado' => Carrito::ESTADO_REVERTIDO]);
                    Bitacora::create([
                        'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                        'modulo_id'    => $carrito->modulo_id,
                        'carrito_uuid' => $carrito->uuid,
                        'payload'      => ['reserva_id' => $reservaId, 'motivo' => 'pedido_creation_failed'],
                    ]);
                } catch (\Throwable) {}
                throw $e; // Pre-commit: re-throw original (CartBusinessException, RuntimeException…)
            } else {
                // confirmar() lanzó post-commit → compensar → unificar respuesta con el path false
                try { $this->compensarPostCommit($carrito, $reservaId, 'confirmar_excepcion'); } catch (\Throwable) {}
                throw new CheckoutRevertidoException('Error al confirmar el cobro; el checkout fue revertido.');
            }
        }
    }

    /**
     * Compensación post-commit: el Carrito y el Pedido ya están en BD,
     * pero el cobro de Saldo falló. Cancela el Pedido, libera la reserva
     * y deja el Carrito en 'revertido'.
     */
    /**
     * Compensación post-commit: Carrito y Pedido ya están en BD, cobro falló.
     *
     * Orden defensivo: cada paso tiene su propio try/catch para que el carrito
     * llegue a 'revertido' incluso si pasos intermedios fallan.
     * Los fallos de liberar() se registran en bitácora (auditable/reconciliable).
     */
    private function compensarPostCommit(Carrito $carrito, ?string $reservaId, string $motivo): void
    {
        // 1. Cancelar el Pedido (idempotente — si falla, continuar)
        try {
            $this->pedidoCreator->cancelarPedidoDeCarrito((string) $carrito->uuid);
            Bitacora::create([
                'accion'       => Bitacora::ACCION_PEDIDO_CANCELADO,
                'modulo_id'    => $carrito->modulo_id,
                'carrito_uuid' => $carrito->uuid,
                'payload'      => ['motivo' => $motivo],
            ]);
        } catch (\Throwable) {}

        // 2. Liberar la reserva de Saldo — registrar fallo para auditoría
        if ($reservaId) {
            try {
                $this->saldoClient->liberar($reservaId);
            } catch (\Throwable $e) {
                try {
                    Bitacora::create([
                        'accion'       => Bitacora::ACCION_SALDO_LIBERAR_FALLIDO,
                        'modulo_id'    => $carrito->modulo_id,
                        'carrito_uuid' => $carrito->uuid,
                        'payload'      => ['reserva_id' => $reservaId, 'motivo' => $motivo, 'error' => $e->getMessage()],
                    ]);
                } catch (\Throwable) {}
            }
        }

        // 3. Carrito → revertido — siempre, incluso si pasos anteriores fallaron
        try {
            $carrito->update(['estado' => Carrito::ESTADO_REVERTIDO]);
            Bitacora::create([
                'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
                'modulo_id'    => $carrito->modulo_id,
                'carrito_uuid' => $carrito->uuid,
                'payload'      => ['reserva_id' => $reservaId, 'motivo' => $motivo],
            ]);
        } catch (\Throwable) {}
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

    /**
     * Path de pago diferido (SaldoUnavailable + permite_pago_diferido).
     *
     * El Pedido NO se crea aquí: el cobro aún no está confirmado.
     * ReintentaConciliacion debe crear el Pedido cuando confirme el cargo.
     */
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
