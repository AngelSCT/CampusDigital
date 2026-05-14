<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderApiController extends Controller
{
    public function getMetrics(Request $request)
    {
        $user = $request->user();
        $modulo = $user->modulo ?? 'otro';
        $hoy = now()->startOfDay();

        $avgPrepTime = Pedido::where('modulo', $modulo)
            ->where('estado', 'entregado')
            ->whereNotNull('confirmado_at')
            ->select(DB::raw('AVG(EXTRACT(EPOCH FROM (confirmado_at - created_at))/60) as avg_time'))
            ->first()->avg_time ?? 0;

        $stats = [
            'pedidos_pendientes' => Pedido::where('modulo', $modulo)->where('estado', 'creado')->count(),
            'pedidos_en_proceso' => Pedido::where('modulo', $modulo)->whereIn('estado', ['aceptado', 'en_proceso'])->count(),
            'pedidos_listos'     => Pedido::where('modulo', $modulo)->where('estado', 'listo')->count(),
            'entregados_hoy'     => Pedido::where('modulo', $modulo)->where('estado', 'entregado')->whereDate('confirmado_at', $hoy)->count(),
            'ventas_hoy'         => Pedido::where('modulo', $modulo)->where('estado', 'entregado')->whereDate('confirmado_at', $hoy)->sum('total'),
            'tiempo_avg'         => round($avgPrepTime, 1),
        ];

        return response()->json($stats);
    }

    public function getReports(Request $request)
    {
        $user = $request->user();
        $modulo = $user->modulo ?? 'otro';
        $periodo = $request->query('periodo', 'hoy');

        $query = Pedido::where('modulo', $modulo)
            ->where('estado', 'entregado');

        if ($periodo === 'hoy') {
            $query->whereDate('confirmado_at', now()->today());
        } elseif ($periodo === 'semana') {
            $query->where('confirmado_at', '>=', now()->startOfWeek());
        } elseif ($periodo === 'mes') {
            $query->where('confirmado_at', '>=', now()->startOfMonth());
        }

        $reportes = [
            'total_ventas' => (float)$query->sum('total'),
            'total_pedidos' => $query->count(),
            'ventas_por_dia' => $this->getVentasPorDia($modulo, $periodo),
            'rendimiento_turno' => $this->getRendimientoTurno($modulo, $periodo),
            'ventas_por_producto' => $this->getVentasPorProducto($modulo, $periodo),
            'historial_atencion' => $this->getHistorialAtencion($modulo, $periodo),
        ];

        return response()->json($reportes);
    }

    private function getHistorialAtencion($modulo, $periodo)
    {
        $query = Pedido::with('usuario')
            ->where('modulo', $modulo)
            ->where('estado', 'entregado');

        if ($periodo === 'hoy') {
            $query->whereDate('confirmado_at', now()->today());
        } elseif ($periodo === 'semana') {
            $query->where('confirmado_at', '>=', now()->startOfWeek());
        } elseif ($periodo === 'mes') {
            $query->where('confirmado_at', '>=', now()->startOfMonth());
        }

        return $query->orderByDesc('confirmado_at')->take(10)->get();
    }

    private function getVentasPorProducto($modulo, $periodo)
    {
        // For now, based on descriptions or static until PedidoDetalle is implemented
        return [
            ['producto' => 'Producto A', 'cantidad' => 15, 'total' => 450],
            ['producto' => 'Producto B', 'cantidad' => 10, 'total' => 300],
            ['producto' => 'Producto C', 'cantidad' => 5, 'total' => 150],
        ];
    }

    private function getVentasPorDia($modulo, $periodo)
    {
        $days = $periodo === 'mes' ? 30 : ($periodo === 'semana' ? 7 : 1);
        
        return DB::table('pedido')
            ->select(DB::raw('DATE(confirmado_at) as fecha'), DB::raw('SUM(total) as total'))
            ->where('modulo', $modulo)
            ->where('estado', 'entregado')
            ->where('confirmado_at', '>=', now()->subDays($days))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    private function getRendimientoTurno($modulo, $periodo)
    {
        // Simplificado: Mañana (6-14), Tarde (14-22), Noche (22-6)
        return DB::table('pedido')
            ->select(
                DB::raw("CASE 
                    WHEN EXTRACT(HOUR FROM confirmado_at) BETWEEN 6 AND 13 THEN 'Mañana'
                    WHEN EXTRACT(HOUR FROM confirmado_at) BETWEEN 14 AND 21 THEN 'Tarde'
                    ELSE 'Noche'
                END as turno"),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('AVG(EXTRACT(EPOCH FROM (confirmado_at - created_at))/60) as tiempo_promedio')
            )
            ->where('modulo', $modulo)
            ->where('estado', 'entregado')
            ->whereNotNull('confirmado_at')
            ->groupBy('turno')
            ->get();
    }
}
