<?php

namespace App\Http\Controllers\Recargas;

use App\Services\SimulacionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

/**
 * WalletController
 *
 * Controlador principal del módulo 8. Gestiona la vista del monedero
 * universitario mostrando saldo, historial de movimientos y módulos disponibles.
 */
class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly SimulacionService $simulacionService
    ) {}

    /**
     * Vista principal del monedero.
     * Incluye saldo actual, últimos movimientos y módulos disponibles para simular.
     */
    public function index(Request $request)
    {
        $usuario     = auth()->user();
        $saldo       = $this->walletService->obtenerSaldo($usuario);
        $movimientos = $this->walletService->movimientos($usuario);
        $modulos     = $this->simulacionService->obtenerModulos();

        return Inertia::render('Recargas/Recargas', [
            'saldo'       => $saldo,
            'movimientos' => $movimientos,
            'modulos'     => $modulos,
        ]);
    }

    /**
     * Retorna el saldo actual en JSON.
     * GET /modulo_8/saldo
     */
    public function saldo(Request $request)
    {
        return response()->json(
            $this->walletService->obtenerSaldo($request->user())
        );
    }

    /**
     * Procesa un pago (endpoint legado, mantenido por compatibilidad).
     * Para simulaciones, usar POST /modulo_8/simular.
     */
    public function pagar(Request $request)
    {
        $request->validate([
            'monto'   => 'required|numeric|min:0.01',
            'concepto' => 'required|string|max:100',
        ]);

        try {
            $pago = $this->walletService->pagar($request->user(), $request->all());

            return response()->json([
                'message' => 'Pago realizado',
                'data'    => $pago,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Historial de movimientos del usuario en JSON.
     * GET /modulo_8/movimientos?tipo=pago&modulo=cafeteria&per_page=15
     */
    public function movimientos(Request $request)
    {
        return response()->json(
            $this->walletService->movimientos($request->user(), $request->only([
                'tipo', 'modulo', 'per_page',
            ]))
        );
    }

    /**
     * Comprobantes del usuario en JSON.
     */
    public function comprobantes(Request $request)
    {
        return response()->json(
            $this->walletService->comprobantes($request->user())
        );
    }
}