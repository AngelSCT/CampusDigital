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

        $tiendaId = $request->query('tienda_id');
        if (!$tiendaId || $tiendaId === 'null') {
            $tiendaId = $user->tienda_id;
        }

        $query = Pedido::query();
        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        } else {
            $query->where('modulo', $modulo);
        }

        $avgPrepTime = (clone $query)
            ->where('estado', 'entregado')
            ->whereNotNull('confirmado_at')
            ->select(DB::raw('AVG(EXTRACT(EPOCH FROM (confirmado_at - created_at))/60) as avg_time'))
            ->first()->avg_time ?? 0;

        $stats = [
            'pedidos_pendientes' => (clone $query)->where('estado', 'creado')->count(),
            'pedidos_en_proceso' => (clone $query)->whereIn('estado', ['aceptado', 'en_proceso'])->count(),
            'pedidos_listos'     => (clone $query)->where('estado', 'listo')->count(),
            'entregados_hoy'     => (clone $query)->where('estado', 'entregado')->whereDate('confirmado_at', $hoy)->count(),
            'ventas_hoy'         => (clone $query)->where('estado', 'entregado')->whereDate('confirmado_at', $hoy)->sum('total'),
            'tiempo_avg'         => round($avgPrepTime, 1),
        ];

        return response()->json($stats);
    }

    public function getReports(Request $request)
    {
        $user = $request->user();
        $modulo = $user->modulo ?? 'otro';
        $periodo = $request->query('periodo', 'hoy');
        
        $tiendaId = $request->query('tienda_id');
        if (!$tiendaId || $tiendaId === 'null') {
            $tiendaId = $user->tienda_id;
        }

        $query = Pedido::query();
        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        } else {
            $query->where('modulo', $modulo);
        }

        $query->where('estado', 'entregado');

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
            'ventas_por_dia' => $this->getVentasPorDia($modulo, $tiendaId, $periodo),
            'rendimiento_turno' => $this->getRendimientoTurno($modulo, $tiendaId, $periodo),
            'ventas_por_producto' => $this->getVentasPorProducto($modulo, $tiendaId, $periodo),
            'historial_atencion' => $this->getHistorialAtencion($modulo, $tiendaId, $periodo),
        ];

        return response()->json($reportes);
    }

    private function getHistorialAtencion($modulo, $tiendaId, $periodo)
    {
        $query = Pedido::with('usuario');
        
        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        } else {
            $query->where('modulo', $modulo);
        }

        $query->where('estado', 'entregado');

        if ($periodo === 'hoy') {
            $query->whereDate('confirmado_at', now()->today());
        } elseif ($periodo === 'semana') {
            $query->where('confirmado_at', '>=', now()->startOfWeek());
        } elseif ($periodo === 'mes') {
            $query->where('confirmado_at', '>=', now()->startOfMonth());
        }

        return $query->orderByDesc('confirmado_at')->take(10)->get();
    }

    private function getVentasPorProducto($modulo, $tiendaId, $periodo)
    {
        // Si hay una tienda seleccionada, podemos obtener productos de esa tienda
        if ($tiendaId) {
            $productos = DB::table('producto')
                ->where('tienda_id', $tiendaId)
                ->pluck('nombre')
                ->toArray();
        } else {
            $productos = ['Café Americano', 'Sándwich de Jamón', 'Enchiladas Verdes'];
        }

        // Datos simulados de venta por producto dinámico
        $res = [];
        $totalVentas = 0;
        foreach ($productos as $i => $p) {
            $cant = rand(5, 30);
            $prec = rand(15, 60);
            $total = $cant * $prec;
            $res[] = ['producto' => $p, 'cantidad' => $cant, 'total' => $total];
        }

        return $res;
    }

    private function getVentasPorDia($modulo, $tiendaId, $periodo)
    {
        $days = $periodo === 'mes' ? 30 : ($periodo === 'semana' ? 7 : 1);
        
        $query = DB::table('pedido')
            ->select(DB::raw('DATE(confirmado_at) as fecha'), DB::raw('SUM(total) as total'))
            ->where('estado', 'entregado');

        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        } else {
            $query->where('modulo', $modulo);
        }

        return $query->where('confirmado_at', '>=', now()->subDays($days))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
    }

    private function getRendimientoTurno($modulo, $tiendaId, $periodo)
    {
        $query = DB::table('pedido')
            ->select(
                DB::raw("CASE 
                    WHEN EXTRACT(HOUR FROM confirmado_at) BETWEEN 6 AND 13 THEN 'Mañana'
                    WHEN EXTRACT(HOUR FROM confirmado_at) BETWEEN 14 AND 21 THEN 'Tarde'
                    ELSE 'Noche'
                END as turno"),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('AVG(EXTRACT(EPOCH FROM (confirmado_at - created_at))/60) as tiempo_promedio')
            )
            ->where('estado', 'entregado')
            ->whereNotNull('confirmado_at');

        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        } else {
            $query->where('modulo', $modulo);
        }

        return $query->groupBy('turno')->get();
    }
}
