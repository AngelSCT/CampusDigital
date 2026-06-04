<?php

namespace App\Http\Controllers;

use App\Models\Recarga;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecargaController extends Controller
{
    public function miSaldo()
    {
        $usuario = Auth::user();

        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();

        $movimientos = SaldoMovimiento::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $resumen = [
            'saldo_actual' => $monedero?->saldo_disponible ?? 0,
            'total_abonos' => $movimientos->where('tipo', 'abono')->sum('monto'),
            'total_cargos' => $movimientos->where('tipo', 'cargo')->sum('monto'),
            'cantidad' => $movimientos->count(),
        ];

        return inertia('Monedero/MiSaldo', [
            'monedero' => $monedero,
            'movimientos' => $movimientos,
            'resumen' => $resumen,
        ]);
    }

    public function index()
    {
        $usuario = Auth::user();

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
            'monto' => 'required|numeric|min:1|max:5000',
            'metodo_pago' => 'required|in:tarjeta,transferencia,efectivo',
        ]);

        $usuario = Auth::user();

        try {
            $recarga = DB::transaction(function () use ($request, $usuario) {
                $recarga = Recarga::create([
                    'usuario_id' => $usuario->id,
                    'monto' => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                    'estado' => 'exitosa',
                ]);

                $this->acreditarSaldo($usuario, $recarga);

                return $recarga;
            });

            return redirect()->route('monedero.recargas')
                ->with('success', 'Recarga de $' . number_format($recarga->monto, 2) . ' realizada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al procesar recarga: ' . $e->getMessage());

            return redirect()->route('monedero.recargas')
                ->with('error', 'No se pudo procesar la recarga. Intenta nuevamente.');
        }
    }

    private function acreditarSaldo($usuario, Recarga $recarga): void
    {
        $monedero = SaldoMonedero::firstOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'saldo_disponible' => 0.00,
                'saldo_retenido' => 0.00,
            ]
        );

        $saldoAnterior = $monedero->saldo_disponible;
        $saldoNuevo = $saldoAnterior + $recarga->monto;

        $monedero->update([
            'saldo_disponible' => $saldoNuevo,
        ]);

        SaldoMovimiento::create([
            'usuario_id' => $usuario->id,
            'saldo_monedero_id' => $monedero->id,
            'tipo' => 'abono',
            'monto' => $recarga->monto,
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => $saldoNuevo,
            'modulo' => 'recarga',
            'concepto' => 'Recarga vía ' . $recarga->metodo_pago,
            'referencia_tabla' => 'recarga',
            'referencia_id' => $recarga->id,
        ]);
    }
}