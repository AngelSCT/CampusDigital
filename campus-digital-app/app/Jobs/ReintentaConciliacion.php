<?php

namespace App\Jobs;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Services\CargoForzosoCobrado;
use App\Modules\Cart\Services\CargoForzosoDesconocido;
use App\Modules\Cart\Services\CargoForzosoRechazado;
use App\Modules\Cart\Services\SaldoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ReintentaConciliacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly ConciliacionPendiente $conciliacion) {}

    /**
     * Flujo de dos TX cortas (sin HTTP dentro de transacción):
     *
     * TX1 (corta): lockForUpdate → verificar PENDIENTE → marcar PROCESANDO → commit
     *   Si estado ≠ PENDIENTE → return silencioso (otro worker ya lo tomó).
     *
     * HTTP (fuera de TX): cargoForzoso()
     *   CargoForzosoCobrado   → onConfirmado(): crear Pedido + marcar EXITOSA
     *   CargoForzosoRechazado → onRechazado(): reagendar como PENDIENTE (backoff)
     *   CargoForzosoDesconocido → onDesconocido(): REQUIERE_REVISION, no reintentar
     *
     * Si el worker muere entre TX1 y TX2: conciliación queda en PROCESANDO.
     * ReconciliarSaldoCommand limpia PROCESANDO > TTL → REQUIERE_REVISION (nunca → PENDIENTE).
     */
    public function handle(SaldoClient $saldo, PedidoCreatorInterface $pedidoCreator): void
    {
        // ── TX1: lock + verificar PENDIENTE + marcar PROCESANDO ──────────────
        $conciliacion = DB::transaction(function () {
            $locked = ConciliacionPendiente::where('id', $this->conciliacion->id)
                ->lockForUpdate()
                ->first();

            if (!$locked || $locked->estado_conciliacion !== ConciliacionPendiente::ESTADO_PENDIENTE) {
                return null; // otro worker ya procesó esto
            }

            $locked->update(['estado_conciliacion' => ConciliacionPendiente::ESTADO_PROCESANDO]);
            return $locked->fresh();
        });

        if ($conciliacion === null) {
            return;
        }

        // ── HTTP: cargoForzoso (FUERA de TX) ─────────────────────────────────
        $result = $saldo->cargoForzoso(
            $conciliacion->usuario_ref,
            (float) $conciliacion->monto,
            $conciliacion->carrito_uuid,
            'conciliacion_diferida',
            Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
        );

        // ── TX2: guardar resultado ────────────────────────────────────────────
        if ($result instanceof CargoForzosoCobrado) {
            $this->onConfirmado($conciliacion, $pedidoCreator);
        } elseif ($result instanceof CargoForzosoRechazado) {
            $this->onRechazado($conciliacion);
        } else {
            $this->onDesconocido($conciliacion);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * El cargo fue confirmado. Actualiza la conciliación, el Carrito y crea el Pedido
     * en una única DB::transaction (atómica).
     *
     * Si crearDesdeCarrito() falla: cargo cobrado pero Pedido no creado → REQUIERE_REVISION.
     * El job NO se reintenta para evitar doble cobro.
     */
    private function onConfirmado(ConciliacionPendiente $conciliacion, PedidoCreatorInterface $pedidoCreator): void
    {
        $carrito = Carrito::where('uuid', $conciliacion->carrito_uuid)
            ->with('modulo')
            ->first();

        if (!$carrito) {
            $conciliacion->update([
                'estado_conciliacion' => ConciliacionPendiente::ESTADO_REQUIERE_REVISION,
                'ultimo_intento_at'   => now(),
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($conciliacion, $carrito, $pedidoCreator) {
                $conciliacion->update([
                    'estado_conciliacion' => ConciliacionPendiente::ESTADO_EXITOSA,
                    'ultimo_intento_at'   => now(),
                ]);

                $carrito->update([
                    'estado'       => Carrito::ESTADO_CONFIRMADO,
                    'confirmed_at' => now(),
                ]);

                $pedidoCreator->crearDesdeCarrito($carrito);

                Bitacora::create([
                    'accion'       => Bitacora::ACCION_PEDIDO_CREADO,
                    'modulo_id'    => $conciliacion->modulo_id,
                    'carrito_uuid' => $conciliacion->carrito_uuid,
                    'payload'      => ['monto' => $conciliacion->monto, 'tipo' => 'conciliacion_diferida'],
                ]);
            });
        } catch (\Throwable $e) {
            // Cargo cobrado pero Pedido no creado: no reintentar → REQUIERE_REVISION
            Bitacora::create([
                'accion'       => 'conciliacion.pedido_creation_failed',
                'modulo_id'    => $conciliacion->modulo_id,
                'carrito_uuid' => $conciliacion->carrito_uuid,
                'payload'      => ['error' => $e->getMessage()],
            ]);

            $conciliacion->update([
                'estado_conciliacion' => ConciliacionPendiente::ESTADO_REQUIERE_REVISION,
                'ultimo_intento_at'   => now(),
            ]);
            return;
        }

        Bitacora::create([
            'accion'       => 'conciliacion.exitosa',
            'modulo_id'    => $conciliacion->modulo_id,
            'carrito_uuid' => $conciliacion->carrito_uuid,
            'payload'      => ['monto' => $conciliacion->monto],
        ]);
    }

    /**
     * Saldo rechazó explícitamente (HTTP 400/402/422): cobro no ejecutado → seguro reintentar.
     * Reagenda con backoff exponencial. Al agotar reintentos → REQUIERE_REVISION.
     */
    private function onRechazado(ConciliacionPendiente $conciliacion): void
    {
        $intentosNuevos = $conciliacion->intentos + 1;
        $reintentosMax  = (int) config('cart.saldo.reintentos_max', 5);

        if ($intentosNuevos >= $reintentosMax) {
            $conciliacion->update([
                'intentos'            => $intentosNuevos,
                'ultimo_intento_at'   => now(),
                'estado_conciliacion' => ConciliacionPendiente::ESTADO_REQUIERE_REVISION,
            ]);

            Bitacora::create([
                'accion'       => 'conciliacion.revision_manual',
                'modulo_id'    => $conciliacion->modulo_id,
                'carrito_uuid' => $conciliacion->carrito_uuid,
                'payload'      => ['intentos' => $intentosNuevos],
            ]);
            return;
        }

        $backoffMinutos = ConciliacionPendiente::BACKOFF_MINUTOS[$intentosNuevos - 1] ?? 360;
        $proximoIntento = now()->addMinutes($backoffMinutos);

        $conciliacion->update([
            'intentos'            => $intentosNuevos,
            'ultimo_intento_at'   => now(),
            'proximo_intento_at'  => $proximoIntento,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE, // reset desde PROCESANDO
        ]);

        static::dispatch($conciliacion->fresh())->delay($proximoIntento);
    }

    /**
     * Resultado desconocido (timeout, 409, 5xx, red caída):
     * el cargo pudo haberse ejecutado → NO reintentar → REQUIERE_REVISION.
     */
    private function onDesconocido(ConciliacionPendiente $conciliacion): void
    {
        $conciliacion->update([
            'ultimo_intento_at'   => now(),
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_REQUIERE_REVISION,
        ]);

        Bitacora::create([
            'accion'       => 'conciliacion.resultado_desconocido',
            'modulo_id'    => $conciliacion->modulo_id,
            'carrito_uuid' => $conciliacion->carrito_uuid,
            'payload'      => ['motivo' => 'cargo_forzoso_desconocido'],
        ]);
    }
}
