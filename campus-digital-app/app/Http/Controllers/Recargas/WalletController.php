<?php

namespace App\Http\Controllers\Recargas;

use App\Services\WalletService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index(Request $request, WalletService $service)
    {
        $saldo = $service->obtenerSaldo(auth()->user());

        $movimientos = $service->movimientos(auth()->user());

        return Inertia::render('Recargas/Recargas', [
            'saldo' => $saldo,
            'movimientos' => $movimientos,
        ]);
    }

    public function saldo(Request $request, WalletService $service)
    {
        return response()->json(
            $service->obtenerSaldo($request->user())
        );
    }

    public function recargar(Request $request, WalletService $service)
{
    $request->validate([
        'monto' => 'required|numeric|min:1',
        'metodo_pago' => 'required|string'
    ]);

    $service->recargar(
        auth()->user(),
        $request->only(['monto', 'metodo_pago'])
    );

    return redirect()->back()->with('success', 'Recarga exitosa');
}

    public function pagar(Request $request, WalletService $service)
    {
        $request->validate([
            'monto' => 'required|numeric|min:1',
            'concepto' => 'required|string|max:100'
        ]);

        try {
            $pago = $service->pagar($request->user(), $request->all());

            return response()->json([
                'message' => 'Pago realizado',
                'data' => $pago
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function movimientos(Request $request, WalletService $service)
    {
        return response()->json(
            $service->movimientos($request->user())
        );
    }

    public function comprobantes(Request $request, WalletService $service)
    {
        return response()->json(
            $service->comprobantes($request->user())
        );
    }
}