<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReservaReporteController extends Controller
{
    public function porRecurso(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->subDays(30);
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::now();

        $datos = DB::table('reservas')
            ->join('recursos', 'reservas.id_recurso', '=', 'recursos.id_recurso')
            ->whereBetween('reservas.fecha_inicio', [$desde, $hasta->endOfDay()])
            ->select(
                'recursos.id_recurso',
                'recursos.nombre',
                'recursos.tipo',
                DB::raw('COUNT(reservas.id_reserva) as total_reservas'),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'confirmada' THEN 1 END) as confirmadas"),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'cancelada' THEN 1 END) as canceladas"),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'no_show' THEN 1 END) as no_shows"),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'completada' THEN 1 END) as completadas"),
                DB::raw('COALESCE(SUM(EXTRACT(EPOCH FROM (reservas.fecha_fin - reservas.fecha_inicio)) / 3600), 0) as horas_totales'),
                DB::raw('COALESCE(SUM(reservas.monto_cobrado), 0) as ingresos')
            )
            ->groupBy('recursos.id_recurso', 'recursos.nombre', 'recursos.tipo')
            ->orderByDesc('total_reservas')
            ->get();

        return Inertia::render('Admin/Reservas/Reportes/PorRecurso', [
            'datos'  => $datos,
            'desde'  => $desde->toDateString(),
            'hasta'  => $hasta->toDateString(),
        ]);
    }

    public function porUsuario(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->subDays(30);
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::now();

        $datos = DB::table('reservas')
            ->join('usuario', 'reservas.id_usuario', '=', 'usuario.id')
            ->whereBetween('reservas.fecha_inicio', [$desde, $hasta->endOfDay()])
            ->select(
                'usuario.id',
                'usuario.nombre',
                'usuario.apellido',
                'usuario.email',
                DB::raw('COUNT(reservas.id_reserva) as total_reservas'),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'completada' THEN 1 END) as completadas"),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'cancelada' THEN 1 END) as canceladas"),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'no_show' THEN 1 END) as no_shows"),
                DB::raw('COALESCE(SUM(reservas.monto_cobrado), 0) as total_gastado')
            )
            ->groupBy('usuario.id', 'usuario.nombre', 'usuario.apellido', 'usuario.email')
            ->orderByDesc('total_reservas')
            ->limit(50)
            ->get();

        return Inertia::render('Admin/Reservas/Reportes/PorUsuario', [
            'datos'  => $datos,
            'desde'  => $desde->toDateString(),
            'hasta'  => $hasta->toDateString(),
        ]);
    }

    public function infrautilizados(Request $request)
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde) : Carbon::now()->subDays(30);
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta) : Carbon::now();

        $totalHoras = $desde->diffInDays($hasta) * 12;

        $datos = Recurso::query()
            ->leftJoin('reservas', function ($join) use ($desde, $hasta) {
                $join->on('recursos.id_recurso', '=', 'reservas.id_recurso')
                     ->whereIn('reservas.estado', [Reserva::ESTADO_CONFIRMADA, Reserva::ESTADO_COMPLETADA])
                     ->whereBetween('reservas.fecha_inicio', [$desde, $hasta->endOfDay()]);
            })
            ->select(
                'recursos.id_recurso',
                'recursos.nombre',
                'recursos.tipo',
                'recursos.estado',
                DB::raw('COUNT(reservas.id_reserva) as total_reservas'),
                DB::raw('COALESCE(SUM(EXTRACT(EPOCH FROM (reservas.fecha_fin - reservas.fecha_inicio)) / 3600), 0) as horas_uso')
            )
            ->groupBy('recursos.id_recurso', 'recursos.nombre', 'recursos.tipo', 'recursos.estado')
            ->having('total_reservas', '<', 3)
            ->orderBy('total_reservas')
            ->orderBy('horas_uso')
            ->get();

        return Inertia::render('Admin/Reservas/Reportes/Infrautilizados', [
            'datos'  => $datos,
            'desde'  => $desde->toDateString(),
            'hasta'  => $hasta->toDateString(),
        ]);
    }
}
