<?php

namespace App\Jobs;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Modules\Cart\Services\SaldoClient;
use App\Modules\Cart\Services\SaldoConfirmed;
use App\Modules\Cart\Services\SaldoInsufficientFunds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReintentaConciliacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly ConciliacionPendiente $conciliacion) {}

    public function handle(SaldoClient $saldo): void
    {
        $conciliacion = $this->conciliacion->fresh();

        if (!$conciliacion || $conciliacion->estado_conciliacion !== ConciliacionPendiente::ESTADO_PENDIENTE) {
            return;
        }

        $result = $saldo->reservar(
            $conciliacion->usuario_ref,
            (float) $conciliacion->monto,
            $conciliacion->carrito_uuid
        );

        if ($result instanceof SaldoConfirmed) {
            $this->onConfirmado($conciliacion);
            return;
        }

        if ($result instanceof SaldoInsufficientFunds) {
            $this->onDeuda($conciliacion, $saldo);
            return;
        }

        // SaldoUnavailable: incrementar intentos y reagendar o escalar.
        $this->onNoDisponible($conciliacion);
    }

    private function onConfirmado(ConciliacionPendiente $conciliacion): void
    {
        $conciliacion->update([
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_EXITOSA,
            'ultimo_intento_at'   => now(),
        ]);

        Carrito::where('uuid', $conciliacion->carrito_uuid)->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO,
            'confirmed_at' => now(),
        ]);

        Bitacora::create([
            'accion'       => 'conciliacion.exitosa',
            'modulo_id'    => $conciliacion->modulo_id,
            'carrito_uuid' => $conciliacion->carrito_uuid,
            'payload'      => ['monto' => $conciliacion->monto],
        ]);
    }

    private function onDeuda(ConciliacionPendiente $conciliacion, SaldoClient $saldo): void
    {
        $conciliacion->update([
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_DEUDA,
            'ultimo_intento_at'   => now(),
        ]);

        Carrito::where('uuid', $conciliacion->carrito_uuid)->update([
            'estado' => Carrito::ESTADO_REVERTIDO,
        ]);

        // Intentar registrar cargo forzoso (saldo negativo) en el módulo Saldo.
        $saldo->cargoForzoso($conciliacion->usuario_ref, (float) $conciliacion->monto, $conciliacion->carrito_uuid);

        Bitacora::create([
            'accion'       => 'conciliacion.deuda',
            'modulo_id'    => $conciliacion->modulo_id,
            'carrito_uuid' => $conciliacion->carrito_uuid,
            'payload'      => ['monto' => $conciliacion->monto, 'usuario_ref' => $conciliacion->usuario_ref],
        ]);
    }

    private function onNoDisponible(ConciliacionPendiente $conciliacion): void
    {
        $intentosNuevos  = $conciliacion->intentos + 1;
        $reintentosMax   = (int) config('cart.saldo.reintentos_max', 5);

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
        ]);

        static::dispatch($conciliacion->fresh())->delay($proximoIntento);
    }
}
