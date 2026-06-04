<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    /**
     * Saldo actual del usuario autenticado (JSON — para llamadas AJAX).
     */
    public function saldo(Request $request)
    {
        $saldo = $this->walletService->obtenerSaldo($request->user());

        return response()->json($saldo);
    }

    /**
     * Realiza un pago/cargo desde el monedero.
     */
    public function pagar(Request $request)
    {
        $request->validate([
            'monto'    => 'required|numeric|min:0.01',
            'concepto' => 'required|string|max:100',
        ]);

        try {
            $resultado = $this->walletService->pagar($request->user(), $request->all());

            return response()->json([
                'message' => 'Pago realizado exitosamente.',
                'data'    => $resultado,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Historial de movimientos del monedero.
     */
    public function movimientos(Request $request)
    {
        $movimientos = $this->walletService->movimientos($request->user());

        return response()->json($movimientos);
    }

    /**
     * Lista de comprobantes de recargas exitosas.
     */
    public function comprobantes(Request $request)
    {
        $comprobantes = $this->walletService->comprobantes($request->user());

        return response()->json($comprobantes);
    }
}
