<?php

namespace App\Observers;

use App\Models\Recarga;
use App\Models\Saldo;
use App\Models\Movimiento;

class RecargaObserver
{
    public function created(Recarga $recarga)
    {
        // Solo si fue exitosa
        if ($recarga->estado !== 'exitosa')
            return;

        // 💰 Actualizar saldo
        $saldo = Saldo::firstOrCreate([
            'usuario_id' => $recarga->usuario_id
        ]);

        $saldo->increment('saldo', $recarga->monto);

        // 🧾 Crear movimiento
        $recarga->movimiento()->create([
            'usuario_id' => $recarga->usuario_id,
            'tipo' => 'recarga',
            'monto' => $recarga->monto,
            'estado' => $recarga->estado
        ]);

        // 📄 Crear comprobante
        $recarga->comprobante()->create([
    'usuario_id' => $recarga->usuario_id,
    'total' => $recarga->monto,
]);
    }
}