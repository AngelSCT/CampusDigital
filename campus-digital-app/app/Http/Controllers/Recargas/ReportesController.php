<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\SaldoMonedero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportesController extends Controller
{
    /**
     * Dashboard principal del Módulo 8 (Recargas)
     */
    public function dashboard(Request $request)
    {
        $usuario = Auth::user();
        $periodo = $request->get('periodo', '30');
        $dias    = is_numeric($periodo) && intval($periodo) > 0 ? intval($periodo) : 30;

        // Saldo actual
        $saldo       = SaldoMonedero::where('usuario_id', $usuario->id)->first();
        $saldo_actual = $saldo ? floatval($saldo->saldo_disponible) : 0;

        // Recargas del período
        $recargas_periodo = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', now()->subDays($dias))
            ->get();

        $stats = [
            'saldo_actual'        => $saldo_actual,
            'recargas_periodo'    => $recargas_periodo->count(),
            'exitosas_periodo'    => $recargas_periodo->where('estado', Recarga::ESTADO_EXITOSO)->count(),
            'fallidas_periodo'    => $recargas_periodo->where('estado', Recarga::ESTADO_FALLIDO)->count(),
            'monto_total_periodo' => floatval($recargas_periodo->where('estado', Recarga::ESTADO_EXITOSO)->sum('monto')),
            'ratio_exito'         => $recargas_periodo->count() > 0
                ? round(
                    ($recargas_periodo->where('estado', Recarga::ESTADO_EXITOSO)->count()
                        / $recargas_periodo->count()) * 100,
                    2
                )
                : 0,
        ];

        // Métodos de pago más usados en el período
        $metodos = $recargas_periodo
            ->groupBy('metodo_pago')
            ->map(fn($grupo) => $grupo->count())
            ->toArray();

        // Últimas 5 recargas (sin filtro de período)
        $ultimas = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'monto'      => floatval($r->monto),
                'metodo_pago'=> $r->metodo_pago,
                'estado'     => $r->estado,
                'created_at' => $r->created_at?->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Recargas/Dashboard', [
            'stats'   => $stats,
            'metodos' => $metodos,
            'ultimas' => $ultimas,
            'periodo' => $periodo,
        ]);
    }

    /**
     * Historial completo de recargas con filtros
     */
    public function historialRecargas(Request $request)
    {
        $usuario       = Auth::user();
        $filtro_estado = $request->get('estado', 'todos');
        $filtro_periodo = $request->get('periodo', '30');

        $query = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($filtro_estado !== 'todos') {
            $query->where('estado', $filtro_estado);
        }

        if ($filtro_periodo !== 'todos' && is_numeric($filtro_periodo)) {
            $query->whereDate('created_at', '>=', now()->subDays(intval($filtro_periodo)));
        }

        $recargas = $query->paginate(15)->through(fn($r) => [
            'id'             => $r->id,
            'monto'          => floatval($r->monto),
            'metodo_pago'    => $r->metodo_pago,
            'estado'         => $r->estado,
            'referencia'     => $r->referencia_pago,
            'razon_fallo'    => $r->razon_fallo,
            'created_at'     => $r->created_at?->format('Y-m-d H:i:s'),
        ]);

        $all = Recarga::where('usuario_id', $usuario->id)->whereNull('deleted_at');
        $stats = [
            'total_recargas'    => $all->count(),
            'recargas_exitosas' => (clone $all)->where('estado', Recarga::ESTADO_EXITOSO)->count(),
            'recargas_fallidas' => (clone $all)->where('estado', Recarga::ESTADO_FALLIDO)->count(),
            'monto_total'       => floatval((clone $all)->where('estado', Recarga::ESTADO_EXITOSO)->sum('monto')),
        ];

        return Inertia::render('Recargas/HistorialRecargas', [
            'recargas'       => $recargas,
            'stats'          => $stats,
            'filtro_estado'  => $filtro_estado,
            'filtro_periodo' => $filtro_periodo,
        ]);
    }

    /**
     * Reporte de pagos fallidos con opción de reintento
     */
    public function pagosFallidos(Request $request)
    {
        $usuario        = Auth::user();
        $filtro_periodo = $request->get('periodo', '30');

        $query = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->where('estado', Recarga::ESTADO_FALLIDO)
            ->orderByDesc('created_at');

        if ($filtro_periodo !== 'todos' && is_numeric($filtro_periodo)) {
            $query->whereDate('created_at', '>=', now()->subDays(intval($filtro_periodo)));
        }

        $recargas = $query->get()->map(fn($r) => [
            'id'          => $r->id,
            'monto'       => floatval($r->monto),
            'metodo_pago' => $r->metodo_pago,
            'estado'      => $r->estado,
            'referencia'  => $r->referencia_pago,
            'razon_fallo' => $r->razon_fallo,
            'created_at'  => $r->created_at?->format('Y-m-d H:i:s'),
        ]);

        $allFallidas = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->where('estado', Recarga::ESTADO_FALLIDO);

        $stats = [
            'total_fallidas'   => $allFallidas->count(),
            'monto_intentado'  => floatval($allFallidas->sum('monto')),
        ];

        return Inertia::render('Recargas/PagosFallidos', [
            'recargas'       => $recargas,
            'stats'          => $stats,
            'filtro_periodo' => $filtro_periodo,
        ]);
    }

    /**
     * Conciliación de recargas por período de fechas
     */
    public function conciliacionPeriodo(Request $request)
    {
        $usuario     = Auth::user();
        $fecha_inicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fecha_fin   = $request->get('fecha_fin', now()->format('Y-m-d'));

        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $fecha_inicio)
            ->whereDate('created_at', '<=', $fecha_fin)
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'periodo_inicio'  => $fecha_inicio,
            'periodo_fin'     => $fecha_fin,
            'total_recargas'  => $recargas->count(),
            'exitosas'        => $recargas->where('estado', Recarga::ESTADO_EXITOSO)->count(),
            'fallidas'        => $recargas->where('estado', Recarga::ESTADO_FALLIDO)->count(),
            'pendientes'      => $recargas->where('estado', Recarga::ESTADO_PENDIENTE)->count(),
            'monto_exitoso'   => floatval($recargas->where('estado', Recarga::ESTADO_EXITOSO)->sum('monto')),
            'monto_fallido'   => floatval($recargas->where('estado', Recarga::ESTADO_FALLIDO)->sum('monto')),
        ];

        $resumen_metodos = $recargas->groupBy('metodo_pago')->map(fn($grupo) => [
            'total'    => $grupo->count(),
            'exitosas' => $grupo->where('estado', Recarga::ESTADO_EXITOSO)->count(),
            'fallidas' => $grupo->where('estado', Recarga::ESTADO_FALLIDO)->count(),
            'monto'    => floatval($grupo->sum('monto')),
        ]);

        $recargas_mapped = $recargas->map(fn($r) => [
            'id'          => $r->id,
            'monto'       => floatval($r->monto),
            'metodo_pago' => $r->metodo_pago,
            'estado'      => $r->estado,
            'referencia'  => $r->referencia_pago,
            'razon_fallo' => $r->razon_fallo,
            'created_at'  => $r->created_at?->format('Y-m-d H:i:s'),
        ]);

        return Inertia::render('Recargas/ConciliacionPeriodo', [
            'recargas'        => $recargas_mapped,
            'stats'           => $stats,
            'resumen_metodos' => $resumen_metodos,
            'fecha_inicio'    => $fecha_inicio,
            'fecha_fin'       => $fecha_fin,
        ]);
    }
}
