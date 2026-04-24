<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Recarga;
use App\Models\Saldo;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * WalletService
 *
 * Servicio de monedero universitario. Gestiona el saldo del usuario,
 * recargas y consulta de historial de movimientos.
 */
class WalletService
{
    /**
     * Obtiene o crea el saldo del usuario.
     */
    public function obtenerSaldo($user): Saldo
    {
        return Saldo::firstOrCreate(
            ['usuario_id' => $user->id],
            ['saldo' => 0]
        );
    }

    /**
     * Registra una recarga directa (uso interno / legado).
     * Para recargas desde el formulario, usar RecargaController.
     */
    public function recargar($user, array $data): Recarga
    {
        return DB::transaction(function () use ($user, $data) {
            $data['metodo_pago'] = strtolower($data['metodo_pago']);

            $recarga = Recarga::create([
                'usuario_id'  => $user->id,
                'monto'       => $data['monto'],
                'metodo_pago' => $data['metodo_pago'],
                'estado'      => 'exitosa',
            ]);

            return $recarga;
        });
    }

    /**
     * Realiza un pago descontando del saldo del usuario.
     * Usa lockForUpdate() para evitar condiciones de carrera.
     *
     * @throws Exception si el saldo es insuficiente
     */
    public function pagar($user, array $data): Pago
    {
        return DB::transaction(function () use ($user, $data) {

            // Corrección: usar usuario_id (no user_id) según el esquema de la BD
            $saldo = Saldo::where('usuario_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['usuario_id' => $user->id],
                    ['saldo' => 0]
                );

            if ($saldo->saldo < $data['monto']) {
                throw new Exception('Saldo insuficiente');
            }

            $saldo->saldo -= $data['monto'];
            $saldo->save();

            $pago = Pago::create([
                'usuario_id' => $user->id,
                'monto'      => $data['monto'],
                'concepto'   => $data['concepto'],
                'estado'     => 'completado',
            ]);

            return $pago;
        });
    }

    /**
     * Historial de movimientos del usuario con filtros opcionales.
     *
     * @param  mixed $user
     * @param  array $filtros  ['tipo' => 'pago|recarga', 'modulo' => 'cafeteria|...', 'per_page' => 15]
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function movimientos($user, array $filtros = [])
    {
        $query = Movimiento::where('usuario_id', $user->id)
            ->orderByDesc('created_at');

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['modulo'])) {
            $query->where('modulo', $filtros['modulo']);
        }

        if (!empty($filtros['per_page'])) {
            return $query->paginate((int) $filtros['per_page']);
        }

        return $query->limit(50)->get();
    }

    /**
     * Comprobantes del usuario.
     */
    public function comprobantes($user)
    {
        return $user->comprobantes()
            ->latest('fecha')
            ->get();
    }
}