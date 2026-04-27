<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\Movimiento;
use App\Models\Saldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportesController extends Controller
{
    /**
     * Reporte de Historial de Recargas
     */
    public function historialRecargas(Request $request)
    {
        $usuario = Auth::user();
        $filtro_estado = $request->get('estado', 'todos');
        $filtro_periodo = $request->get('periodo', '30');

        $query = Recarga::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at');

        // Filtrar por estado
        if ($filtro_estado !== 'todos') {
            $query->where('estado', $filtro_estado);
        }

        // Filtrar por período
        if ($filtro_periodo !== 'todos') {
            $dias = intval($filtro_periodo);
            $query->whereDate('created_at', '>=', now()->subDays($dias));
        }

        $recargas = $query->paginate(15);

        $stats = [
            'total_recargas' => Recarga::where('usuario_id', $usuario->id)->count(),
            'recargas_exitosas' => Recarga::where('usuario_id', $usuario->id)->where('estado', 'exitosa')->count(),
            'recargas_fallidas' => Recarga::where('usuario_id', $usuario->id)->where('estado', 'fallida')->count(),
            'monto_total' => Recarga::where('usuario_id', $usuario->id)->where('estado', 'exitosa')->sum('monto'),
        ];

        return Inertia::render('Recargas/HistorialRecargas', [
            'recargas' => $recargas,
            'stats' => $stats,
            'filtro_estado' => $filtro_estado,
            'filtro_periodo' => $filtro_periodo,
        ]);
    }

    /**
     * Reporte de Pagos Fallidos
     */
    public function pagosFallidos(Request $request)
    {
        $usuario = Auth::user();
        $filtro_periodo = $request->get('periodo', '30');

        $query = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'fallida')
            ->orderByDesc('created_at');

        if ($filtro_periodo !== 'todos') {
            $dias = intval($filtro_periodo);
            $query->whereDate('created_at', '>=', now()->subDays($dias));
        }

        $recargas = $query->paginate(15);

        $stats = [
            'total_fallidas' => Recarga::where('usuario_id', $usuario->id)->where('estado', 'fallida')->count(),
            'monto_intentado' => Recarga::where('usuario_id', $usuario->id)->where('estado', 'fallida')->sum('monto'),
        ];

        return Inertia::render('Recargas/PagosFallidos', [
            'recargas' => $recargas,
            'stats' => $stats,
            'filtro_periodo' => $filtro_periodo,
        ]);
    }

    /**
     * Reporte de Conciliación por Período
     */
    public function conciliacionPeriodo(Request $request)
    {
        $usuario = Auth::user();
        $fecha_inicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fecha_fin = $request->get('fecha_fin', now()->format('Y-m-d'));

        // Recargas en el período
        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->whereDate('created_at', '>=', $fecha_inicio)
            ->whereDate('created_at', '<=', $fecha_fin)
            ->orderByDesc('created_at')
            ->get();

        // Estadísticas del período
        $stats = [
            'periodo_inicio' => $fecha_inicio,
            'periodo_fin' => $fecha_fin,
            'total_recargas' => $recargas->count(),
            'exitosas' => $recargas->where('estado', 'exitosa')->count(),
            'fallidas' => $recargas->where('estado', 'fallida')->count(),
            'pendientes' => $recargas->where('estado', 'pendiente')->count(),
            'monto_exitoso' => $recargas->where('estado', 'exitosa')->sum('monto'),
            'monto_fallido' => $recargas->where('estado', 'fallida')->sum('monto'),
            'métodos_usados' => $recargas->groupBy('metodo_pago')->map(function ($grupo) {
                return $grupo->count();
            }),
        ];

        // Resumen por método
        $resumen_metodos = $recargas->groupBy('metodo_pago')->map(function ($grupo) {
            return [
                'total' => $grupo->count(),
                'exitosas' => $grupo->where('estado', 'exitosa')->count(),
                'fallidas' => $grupo->where('estado', 'fallida')->count(),
                'monto' => $grupo->sum('monto'),
            ];
        });

        return Inertia::render('Recargas/ConciliacionPeriodo', [
            'recargas' => $recargas,
            'stats' => $stats,
            'resumen_metodos' => $resumen_metodos,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);
    }

    /**
     * Dashboard del Módulo 8
     */
    public function dashboard(Request $request)
    {
        $usuario = Auth::user();
        $periodo = $request->get('periodo', '30');
        $dias = intval($periodo) > 0 ? intval($periodo) : 30;

        // Saldo actual
        $saldo = Saldo::where('usuario_id', $usuario->id)->first();
        $saldo_actual = $saldo ? floatval($saldo->saldo) : 0;

        // Recargas del período
        $recargas_periodo = Recarga::where('usuario_id', $usuario->id)
            ->whereDate('created_at', '>=', now()->subDays($dias))
            ->get();

        $stats = [
            'saldo_actual' => $saldo_actual,
            'recargas_periodo' => $recargas_periodo->count(),
            'exitosas_periodo' => $recargas_periodo->where('estado', 'exitosa')->count(),
            'fallidas_periodo' => $recargas_periodo->where('estado', 'fallida')->count(),
            'monto_total_periodo' => floatval($recargas_periodo->where('estado', 'exitosa')->sum('monto')),
            'ratio_exito' => $recargas_periodo->count() > 0 
                ? round(($recargas_periodo->where('estado', 'exitosa')->count() / $recargas_periodo->count()) * 100, 2)
                : 0,
        ];

        // Métodos más usados
        $metodos = $recargas_periodo->groupBy('metodo_pago')->map(function ($grupo) {
            return $grupo->count();
        })->toArray();

        // Últimas 5 recargas
        $ultimas = Recarga::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Recargas/Dashboard', [
            'stats' => $stats,
            'metodos' => $metodos,
            'ultimas' => $ultimas,
            'periodo' => $periodo,
        ]);
    }
}
