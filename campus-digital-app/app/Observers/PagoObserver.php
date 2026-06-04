<?php

namespace App\Observers;

use App\Models\Pago;
use App\Models\Saldo;
use App\Models\Movimiento;

class PagoObserver
{
    public function created(Pago $pago): void
    {
        if ($pago->estado !== 'completado') {
            return;
        }

        $saldo = Saldo::firstOrCreate(
            ['user_id' => $pago->user_id],
            ['saldo' => 0]
        );

        if ($saldo->saldo < $pago->monto) {
            throw new \Exception('Saldo insuficiente');
        }

        $saldo->decrement('saldo', $pago->monto);

        Movimiento::create([
            'usuario_id' => $pago->user_id,
            'tipo' => 'pago',
            'monto' => -$pago->monto,
            'estado' => 'completado',
            'referencia_id' => $pago->id,
            'referencia_type' => Pago::class,
        ]);

        $pago->comprobante()->create([
            'usuario_id' => $pago->user_id,
            'total' => $pago->monto,
        ]);
    }
}