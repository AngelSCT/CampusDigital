<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoDashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['administrador'])) {
            return redirect()->route('sin-permiso');
        }

        // Pedidos por estado
        $porEstado = Pedido::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Pedidos por módulo
        $porModulo = Pedido::select('modulo', DB::raw('count(*) as total'))
            ->groupBy('modulo')
            ->pluck('total', 'modulo');

        // KPIs
        $hoy          = Pedido::whereDate('created_at', today())->count();
        $cancelados   = Pedido::where('estado', 'cancelado')->count();
        $total        = Pedido::count();
        $tasaCancelacion = $total > 0 ? round(($cancelados / $total) * 100, 1) : 0;

        // Pedidos activos (no terminales)
        $activos = Pedido::whereNotIn('estado', ['entregado', 'cancelado'])->count();

        // Últimos 7 días — pedidos creados por día
        $ultimos7dias = Pedido::select(
                DB::raw("DATE(created_at) as dia"),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // Tiempo promedio entre creado → entregado (en minutos)
        $tiempoPromedio = DB::select("
            SELECT ROUND(AVG(EXTRACT(EPOCH FROM (p.updated_at - p.created_at))/60)::numeric, 1) as minutos
            FROM pedido p
            WHERE p.estado = 'entregado'
              AND p.deleted_at IS NULL
        ");
        $tiempoPromedioMin = $tiempoPromedio[0]->minutos ?? 0;

        // Top 5 usuarios con más pedidos
        $topUsuarios = Pedido::select('usuario_id', DB::raw('count(*) as total'))
            ->with('usuario:id,nombre,apellido')
            ->groupBy('usuario_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return Inertia::render('Pedidos/Dashboard', [
            'porEstado'       => $porEstado,
            'porModulo'       => $porModulo,
            'kpis'            => [
                'hoy'              => $hoy,
                'activos'          => $activos,
                'total'            => $total,
                'cancelados'       => $cancelados,
                'tasaCancelacion'  => $tasaCancelacion,
                'tiempoPromedioMin'=> (float) $tiempoPromedioMin,
            ],
            'ultimos7dias'    => $ultimos7dias,
            'topUsuarios'     => $topUsuarios,
            'estados'         => Pedido::ESTADOS,
            'modulos'         => Pedido::MODULOS,
        ]);
    }
}