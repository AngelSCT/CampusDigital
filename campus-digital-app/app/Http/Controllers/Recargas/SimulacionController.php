<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Services\SimulacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SimulacionController
 *
 * Controlador dedicado para simular transacciones de saldo en los módulos del campus.
 * Expone endpoints para simular consumos y consultar el historial de movimientos.
 */
class SimulacionController extends Controller
{
    public function __construct(
        private readonly SimulacionService $simulacionService
    ) {}

    /**
     * Simula una transacción en el módulo indicado.
     *
     * POST /modulo_8/simular
     *
     * Body JSON:
     * {
     *   "modulo": "cafeteria" | "copias" | "souvenirs" | "biblioteca" | "acceso"
     * }
     *
     * Respuestas:
     * - 200: Transacción simulada exitosamente con detalle del movimiento
     * - 400: Saldo insuficiente o módulo inválido
     * - 422: Datos de entrada inválidos
     */
    public function simular(Request $request)
    {
        $request->validate([
            'modulo' => [
                'required',
                'string',
                'in:cafeteria,copias,souvenirs,biblioteca,acceso',
            ],
        ]);

        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        try {
            $resultado = $this->simulacionService->simular($usuario, $request->modulo);

            return back()->with('simulacion_ok', [
                'mensaje'    => "Simulación exitosa en {$resultado['modulo']}",
                'monto'      => $resultado['monto'],
                'concepto'   => $resultado['concepto'],
                'saldo_nuevo'=> $resultado['saldo_nuevo'],
                'modulo'     => $resultado['modulo'],
            ]);

        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['modulo' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['simulacion' => $e->getMessage()]);
        }
    }

    /**
     * Historial de movimientos con filtros y paginación.
     *
     * GET /modulo_8/historial?tipo=pago&modulo=cafeteria&per_page=15
     */
    public function historial(Request $request)
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        $movimientos = $this->simulacionService->historial($usuario, $request->only([
            'tipo',
            'modulo',
            'per_page',
        ]));

        return response()->json($movimientos);
    }
}
