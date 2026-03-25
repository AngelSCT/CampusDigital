<?php

namespace App\Services;

use App\Models\Recarga;
use App\Models\Pago;
use App\Models\Saldo;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * 💰 Obtener saldo
     */
    public function obtenerSaldo($user)
    {
        return Saldo::firstOrCreate(
            ['usuario_id' => $user->id],
            ['saldo' => 0]
        );
    }

    /**
     * ➕ Recargar saldo
     */
    public function recargar($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $data['metodo_pago'] = strtolower($data['metodo_pago']);
            $recarga = Recarga::create([
                'usuario_id' => $user->id,
                'monto' => $data['monto'],
                'metodo_pago' => $data['metodo_pago'],
                'estado' => 'exitosa'
            ]);

            // 🔥 Aquí NO hacemos nada más
            // El observer se encarga de todo

            return $recarga;
        });
    }

    /**
     * 💳 Realizar pago
     */
    public function pagar($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {

            $saldo = Saldo::firstOrCreate(['user_id' => $user->id]);

            if ($saldo->saldo < $data['monto']) {
                throw new Exception("Saldo insuficiente");
            }

            $pago = Pago::create([
                'user_id' => $user->id,
                'monto' => $data['monto'],
                'concepto' => $data['concepto'],
                'estado' => 'completado'
            ]);

            // 🔥 Observer hace lo demás

            return $pago;
        });
    }

    /**
     * 🧾 Historial de movimientos
     */
    public function movimientos($user)
    {
        return $user->movimientos()
            ->latest()
            ->get();
    }

    /**
     * 📄 Comprobantes
     */
    public function comprobantes($user)
    {
        return $user->comprobantes()
            ->latest('fecha')
            ->get();
    }
}