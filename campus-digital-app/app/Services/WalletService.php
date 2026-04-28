<?php

namespace App\Services;

use App\Models\Recarga;
use App\Models\Saldo;
use App\Models\Movimiento;
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
     * ➕ Recargar saldo (simulado — listo para integración con API externa)
     * TODO: Cuando se integre la API del equipo de pagos, reemplazar la lógica
     *       de simulación por: Http::post(config('api.pagos_url').'/recargas', $data)
     */
    public function recargar($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $data['metodo_pago'] = strtolower($data['metodo_pago']);

            // Crear registro en estado pendiente
            $recarga = Recarga::create([
                'usuario_id' => $user->id,
                'monto' => $data['monto'],
                'metodo_pago' => $data['metodo_pago'],
                'estado' => 'pendiente',
                'referencia' => 'WEB-' . strtoupper(uniqid()),
            ]);

            // Simular procesamiento de pago (80% éxito)
            $pagoExitoso = random_int(1, 100) <= 80;

            if ($pagoExitoso) {
                $this->procesarAbonoExitoso($recarga, $user);
                $recarga->update(['estado' => 'exitosa']);
            } else {
                $recarga->update([
                    'estado' => 'fallida',
                    'razon_fallo' => 'Pago rechazado por la entidad financiera',
                ]);
            }

            return $recarga;
        });
    }

    /**
     * 💳 Realizar pago (simulado — listo para integración con API de módulo de pagos)
     * TODO: Integrar con API de otro módulo cuando esté disponible
     */
    public function pagar($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {

            $saldo = Saldo::firstOrCreate(
                ['usuario_id' => $user->id],
                ['saldo' => 0]
            );

            if ($saldo->saldo < $data['monto']) {
                throw new Exception("Saldo insuficiente");
            }

            // Descontar saldo
            $saldo->saldo -= $data['monto'];
            $saldo->save();

            // Registrar movimiento de pago
            $movimiento = Movimiento::create([
                'usuario_id' => $user->id,
                'tipo' => 'pago',
                'monto' => $data['monto'],
                'estado' => 'exitosa',
            ]);

            return $movimiento;
        });
    }

    /**
     * 🧾 Historial de movimientos
     */
    public function movimientos($user)
    {
        return Movimiento::where('usuario_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * 📄 Comprobantes
     */
    public function comprobantes($user)
    {
        return \App\Models\Comprobante::where('usuario_id', $user->id)
            ->latest('fecha')
            ->get();
    }

    /**
     * Procesar abono exitoso en el saldo
     */
    private function procesarAbonoExitoso($recarga, $usuario)
    {
        $saldo = Saldo::where('usuario_id', $usuario->id)->first();

        if (!$saldo) {
            $saldo = Saldo::create([
                'usuario_id' => $usuario->id,
                'saldo' => 0,
            ]);
        }

        $saldo->saldo += $recarga->monto;
        $saldo->save();

        $movimiento = Movimiento::create([
            'usuario_id' => $usuario->id,
            'tipo' => 'recarga',
            'monto' => $recarga->monto,
            'estado' => 'exitosa',
            'referencia_type' => Recarga::class,
            'referencia_id' => $recarga->id,
        ]);

        $recarga->update(['saldo_movimiento_id' => $movimiento->id]);
    }
}
