<?php

namespace App\Http\Controllers;

use App\Models\Recarga;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RecargaController extends Controller
{
    // Muestra la página de saldo / estado de cuenta del estudiante
    public function miSaldo()
    {
        $usuario  = Auth::user();
        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();

        $movimientos = SaldoMovimiento::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $resumen = [
            'saldo_actual'  => $monedero?->saldo_disponible ?? 0,
            'total_abonos'  => $movimientos->where('tipo', 'abono')->sum('monto'),
            'total_cargos'  => $movimientos->where('tipo', 'cargo')->sum('monto'),
            'cantidad'      => $movimientos->count(),
        ];

        return inertia('Monedero/MiSaldo', [
            'monedero'   => $monedero,
            'movimientos'=> $movimientos,
            'resumen'    => $resumen,
        ]);
    }

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

    public function store(Request $request)
{
    $request->validate([
        'monto'       => 'required|numeric|min:1|max:5000',
        'metodo_pago' => 'required|in:tarjeta,transferencia,efectivo',
    ]);

    $usuario = Auth::user();

    try {
        DB::transaction(function () use ($request, $usuario) {
            // 1. Crear recarga en estado "creado"
            $recarga = Recarga::create([
                'usuario_id'      => $usuario->id,
                'monto'           => $request->monto,
                'metodo_pago'     => $request->metodo_pago,
                'estado'          => 'creado',
                'referencia_pago' => 'REC-' . strtoupper(uniqid()),
            ]);

            // 2. Llamar a POST /pedidos para procesar el pago
            $response = $this->crearPedidoPago($recarga, $request->metodo_pago);

            // 3. Manejar respuesta
            if ($response && isset($response['id'])) {
                // Pago procesado exitosamente
                $recarga->update([
                    'estado'           => 'aceptado',
                    'referencia_pago'  => $response['id'] ?? $recarga->referencia_pago,
                ]);

                // Acreditar saldo
                $this->acreditarSaldo($usuario, $recarga);
            } else {
                // Pago fallido
                $recarga->update([
                    'estado'      => 'cancelado',
                    'razon_fallo' => 'Error en el procesamiento del pago',
                ]);
            }
        });

        $recarga = Recarga::where('usuario_id', $usuario->id)
            ->latest()
            ->first();

        if ($recarga->estado === 'aceptado') {
            return redirect()->route('monedero.recargas')
                ->with('success', "Recarga de \${$request->monto} realizada exitosamente.");
        } else {
            return redirect()->route('monedero.recargas')
                ->with('error', "No se pudo procesar la recarga. {$recarga->razon_fallo}");
        }
    } catch (\Exception $e) {
        return redirect()->route('monedero.recargas')
            ->with($estado === 'exitoso' ? 'success' : 'error',
                   $estado === 'exitoso'
                       ? 'Recarga de $' . $request->monto . ' realizada exitosamente.'
                       : 'El pago no fue autorizado. No se acreditó saldo.');
    }
}

// Llamar API de pedidos
private function crearPedidoPago($recarga, $metodo_pago)
{
    try {
        $response = \Illuminate\Support\Facades\Http::post(
            config('app.api_base_url') . '/pedidos',
            [
                'usuario_id'   => $recarga->usuario_id,
                'monto'        => $recarga->monto,
                'metodo_pago'  => $metodo_pago,
                'tipo'         => 'recarga',
                'referencia'   => $recarga->referencia_pago,
            ]
        );

        if ($response->successful()) {
            return $response->json();
        }
        return null;
    } catch (\Exception $e) {
        \Log::error('Error en POST /pedidos: ' . $e->getMessage());
        return null;
    }
}

// Acreditar saldo al usuario
private function acreditarSaldo($usuario, $recarga)
{
    $monedero = SaldoMonedero::firstOrCreate(
        ['usuario_id' => $usuario->id],
        ['saldo_disponible' => 0.00, 'saldo_retenido' => 0.00]
    );

    $anterior = $monedero->saldo_disponible;
    $monedero->saldo_disponible += $recarga->monto;
    $monedero->save();

    $movimiento = SaldoMovimiento::create([
        'usuario_id'        => $usuario->id,
        'saldo_monedero_id' => $monedero->id,
        'tipo'              => 'abono',
        'monto'             => $recarga->monto,
        'saldo_anterior'    => $anterior,
        'saldo_nuevo'       => $monedero->saldo_disponible,
        'modulo'            => 'recarga',
        'concepto'          => 'Recarga vía ' . $recarga->metodo_pago,
        'referencia_tabla'  => 'recarga',
        'referencia_id'     => $recarga->id,
    ]);

    $recarga->update([
        'saldo_movimiento_id' => $movimiento->id,
    ]);
}
}
