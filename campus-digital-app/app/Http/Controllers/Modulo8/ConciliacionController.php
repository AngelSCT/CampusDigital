<?php

namespace App\Http\Controllers\Modulo8;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\Saldo;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConciliacionController extends Controller
{
    /**
     * GET /modulo_8/reportes/conciliacion
     * Conciliación de recargas por período.
     * Devuelve JSON cuando el cliente lo solicita, Inertia en caso contrario.
     */
    public function conciliacion(Request $request)
    {
        $usuario      = Auth::user();
        $fecha_inicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fecha_fin    = $request->get('fecha_fin', now()->format('Y-m-d'));

        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->whereDate('created_at', '>=', $fecha_inicio)
            ->whereDate('created_at', '<=', $fecha_fin)
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'periodo_inicio' => $fecha_inicio,
            'periodo_fin'    => $fecha_fin,
            'total_recargas' => $recargas->count(),
            'exitosas'       => $recargas->where('estado', 'exitosa')->count(),
            'fallidas'       => $recargas->where('estado', 'fallida')->count(),
            'pendientes'     => $recargas->where('estado', 'pendiente')->count(),
            'monto_exitoso'  => floatval($recargas->where('estado', 'exitosa')->sum('monto')),
            'monto_fallido'  => floatval($recargas->where('estado', 'fallida')->sum('monto')),
        ];

        $resumen_metodos = $recargas->groupBy('metodo_pago')->map(function ($grupo) {
            return [
                'total'    => $grupo->count(),
                'exitosas' => $grupo->where('estado', 'exitosa')->count(),
                'fallidas' => $grupo->where('estado', 'fallida')->count(),
                'monto'    => floatval($grupo->sum('monto')),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'recargas'        => $recargas,
                    'stats'           => $stats,
                    'resumen_metodos' => $resumen_metodos,
                    'fecha_inicio'    => $fecha_inicio,
                    'fecha_fin'       => $fecha_fin,
                ],
                'message' => 'Conciliación obtenida correctamente',
            ]);
        }

        return Inertia::render('Recargas/ConciliacionPeriodo', [
            'recargas'        => $recargas,
            'stats'           => $stats,
            'resumen_metodos' => $resumen_metodos,
            'fecha_inicio'    => $fecha_inicio,
            'fecha_fin'       => $fecha_fin,
        ]);
    }

    /**
     * GET /modulo_8/recargas/historial
     * Historial de recargas con filtros y paginación.
     */
    public function historial(Request $request)
    {
        $usuario = Auth::user();

        $query = Recarga::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at');

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        $recargas = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $recargas,
            'message' => 'Historial obtenido correctamente',
        ]);
    }

    /**
     * GET /modulo_8/estadisticas/periodo
     * Estadísticas por período: tasa de éxito, desglose por método y desglose diario.
     */
    public function estadisticasPeriodo(Request $request)
    {
        $usuario      = Auth::user();
        $fecha_inicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fecha_fin    = $request->get('fecha_fin', now()->format('Y-m-d'));

        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->whereDate('created_at', '>=', $fecha_inicio)
            ->whereDate('created_at', '<=', $fecha_fin)
            ->get();

        $total   = $recargas->count();
        $exitosas = $recargas->where('estado', 'exitosa')->count();

        $por_metodo = $recargas->groupBy('metodo_pago')->map(function ($grupo) use ($total) {
            $count = $grupo->count();
            return [
                'total'       => $count,
                'exitosas'    => $grupo->where('estado', 'exitosa')->count(),
                'fallidas'    => $grupo->where('estado', 'fallida')->count(),
                'monto_total' => floatval($grupo->where('estado', 'exitosa')->sum('monto')),
                'porcentaje'  => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        });

        $desglose_diario = $recargas->groupBy(function ($r) {
            return $r->created_at->format('Y-m-d');
        })->map(function ($grupo, $fecha) {
            return [
                'fecha'    => $fecha,
                'total'    => $grupo->count(),
                'exitosas' => $grupo->where('estado', 'exitosa')->count(),
                'fallidas' => $grupo->where('estado', 'fallida')->count(),
                'monto'    => floatval($grupo->where('estado', 'exitosa')->sum('monto')),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'periodo_inicio'  => $fecha_inicio,
                'periodo_fin'     => $fecha_fin,
                'total_recargas'  => $total,
                'exitosas'        => $exitosas,
                'fallidas'        => $recargas->where('estado', 'fallida')->count(),
                'tasa_exito'      => $total > 0 ? round(($exitosas / $total) * 100, 2) : 0,
                'monto_exitoso'   => floatval($recargas->where('estado', 'exitosa')->sum('monto')),
                'por_metodo'      => $por_metodo,
                'desglose_diario' => $desglose_diario,
            ],
            'message' => 'Estadísticas obtenidas correctamente',
        ]);
    }

    /**
     * GET /modulo_8/recargas/{id}
     * Detalle de una recarga específica del usuario autenticado.
     */
    public function detalleRecarga($id)
    {
        $usuario = Auth::user();

        $recarga = Recarga::where('usuario_id', $usuario->id)->find($id);

        if (!$recarga) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Recarga no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $recarga,
            'message' => 'Detalle de recarga obtenido correctamente',
        ]);
    }

    /**
     * POST /modulo_8/recargar/{id}/reintentar
     * Reintentar un pago fallido.
     * Devuelve JSON cuando el cliente lo solicita, redirige en caso contrario.
     */
    public function reintentar(Request $request, $id)
    {
        $usuario = Auth::user();

        $recarga = Recarga::where('usuario_id', $usuario->id)->find($id);

        if (!$recarga) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Recarga no encontrada',
                ], 404);
            }
            abort(404, 'Recarga no encontrada');
        }

        if ($recarga->estado !== 'fallida') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Solo se pueden reintentar recargas fallidas',
                ], 400);
            }
            return back()->withErrors(['recarga' => 'Solo se pueden reintentar recargas fallidas']);
        }

        try {
            DB::transaction(function () use ($recarga, $usuario) {
                $recarga->update(['estado' => 'pendiente', 'razon_fallo' => null]);

                // TODO: Replace with actual payment gateway integration
                $pagoExitoso = rand(1, 100) <= 80;

                if ($pagoExitoso) {
                    $saldo = Saldo::where('usuario_id', $usuario->id)->first();

                    if (!$saldo) {
                        $saldo = Saldo::create([
                            'usuario_id' => $usuario->id,
                            'saldo'      => 0,
                        ]);
                    }

                    $saldo->saldo += $recarga->monto;
                    $saldo->save();

                    Movimiento::create([
                        'usuario_id'       => $usuario->id,
                        'tipo'             => 'recarga',
                        'monto'            => $recarga->monto,
                        'estado'           => 'exitosa',
                        'referencia_type'  => Recarga::class,
                        'referencia_id'    => $recarga->id,
                    ]);

                    $recarga->update(['estado' => 'exitosa']);
                } else {
                    $recarga->update([
                        'estado'      => 'fallida',
                        'razon_fallo' => 'Reintento fallido. Pago rechazado nuevamente.',
                    ]);
                }
            });

            $recarga->refresh();
            $exitosa = $recarga->estado === 'exitosa';
            $mensaje = $exitosa
                ? "Reintento exitoso. Se acreditó \${$recarga->monto}"
                : 'El reintento falló nuevamente. Intenta más tarde.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => $exitosa,
                    'data'    => [
                        'id'         => $recarga->id,
                        'estado'     => $recarga->estado,
                        'referencia' => $recarga->referencia,
                        'monto'      => floatval($recarga->monto),
                    ],
                    'message' => $mensaje,
                ]);
            }

            return redirect()->route('modulo_8.recargar.form')
                ->with($exitosa ? 'success' : 'error', $mensaje);

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Error al procesar el reintento: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['recarga' => $e->getMessage()]);
        }
    }
}
