<?php

namespace App\Http\Controllers;

use App\Models\Recarga;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecargaController extends Controller
{
    // Muestra la página de recargas con el historial del usuario
    public function index()
    {
        $usuario  = Auth::user();
        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();

        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return inertia('Monedero/Recargas', [
            'monedero' => $monedero,
            'recargas' => $recargas,
        ]);
    }

    // Procesa la recarga desde el formulario web
    public function store(Request $request)
    {
        $request->validate([
            'monto'          => 'required|numeric|min:1|max:5000',
            'metodo_pago'    => 'required|in:tarjeta,transferencia,efectivo',
            'resultado_pago' => 'required|in:exitoso,fallido',
        ]);

        $usuario = Auth::user();

        DB::transaction(function () use ($request, $usuario) {

            $recarga = Recarga::create([
                'usuario_id'      => $usuario->id,
                'monto'           => $request->monto,
                'metodo_pago'     => $request->metodo_pago,
                'estado'          => 'pendiente',
                'referencia_pago' => 'WEB-' . strtoupper(uniqid()),
            ]);

            if ($request->resultado_pago === 'fallido') {
                $recarga->update([
                    'estado'      => 'fallido',
                    'razon_fallo' => 'Pago no autorizado por el banco.',
                ]);
                return;
            }

            $monedero = SaldoMonedero::firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['saldo_disponible' => 0.00, 'saldo_retenido' => 0.00]
            );

            $anterior = $monedero->saldo_disponible;
            $monedero->saldo_disponible += $request->monto;
            $monedero->save();

            $movimiento = SaldoMovimiento::create([
                'usuario_id'        => $usuario->id,
                'saldo_monedero_id' => $monedero->id,
                'tipo'              => 'abono',
                'monto'             => $request->monto,
                'saldo_anterior'    => $anterior,
                'saldo_nuevo'       => $monedero->saldo_disponible,
                'modulo'            => 'recarga',
                'concepto'          => 'Recarga vía ' . $request->metodo_pago,
                'referencia_tabla'  => 'recarga',
                'referencia_id'     => $recarga->id,
            ]);

            $recarga->update([
                'estado'              => 'exitoso',
                'saldo_movimiento_id' => $movimiento->id,
            ]);
        });

        $estado = $request->resultado_pago === 'exitoso' ? 'exitoso' : 'fallido';

        return redirect()->route('monedero.recargas')
            ->with($estado === 'exitoso' ? 'success' : 'error',
                   $estado === 'exitoso'
                       ? "Recarga de $$request->monto realizada exitosamente."
                       : 'El pago no fue autorizado. No se acreditó saldo.');
    }
}
