<?php

namespace App\Jobs;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
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
     * Usa cargoForzoso() directamente porque el servicio ya fue entregado
     * y la deuda necesita cobrarse sin importar el saldo disponible.
     *
     * Si el cargo es exitoso, crea el Pedido dentro de una DB::transaction
     * junto con el cambio de estado del Carrito (atómico).
     * Si la creación del Pedido falla, el cargoForzoso ya fue cobrado y NO
     * se puede revertir: se escala a revisión manual sin reintentar el job.
     */
    public function handle(SaldoClient $saldo, PedidoCreatorInterface $pedidoCreator): void
    {
        $conciliacion = $this->conciliacion->fresh();

        if (!$conciliacion || $conciliacion->estado_conciliacion !== ConciliacionPendiente::ESTADO_PENDIENTE) {
            return;
        }

        $ok = $saldo->cargoForzoso(
            $conciliacion->usuario_ref,
            (float) $conciliacion->monto,
            $conciliacion->carrito_uuid,
            'conciliacion_diferida',
            Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
        );

        if ($ok) {
            $this->onConfirmado($conciliacion, $pedidoCreator);
            return;
        }

        $this->onNoDisponible($conciliacion);
    }

    /**
     * El cargo fue confirmado. Actualiza la conciliación, el Carrito y crea el Pedido
     * en una única DB::transaction (atómica).
     *
     * Si crearDesdeCarrito() falla: la transacción hace rollback (carrito y conciliación
     * no se actualizan), se registra el incidente y se escala a REQUIERE_REVISION.
     * El job NO se reintenta porque el cargo de saldo YA fue cobrado.
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
            // El cargo YA fue cobrado pero el Pedido no pudo crearse.
            // NO reintentar (evitar doble cobro). Escalar a revisión manual.
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

    private function onNoDisponible(ConciliacionPendiente $conciliacion): void
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
            'intentos'           => $intentosNuevos,
            'ultimo_intento_at'  => now(),
            'proximo_intento_at' => $proximoIntento,
        ]);

        static::dispatch($conciliacion->fresh())->delay($proximoIntento);
    }
}
