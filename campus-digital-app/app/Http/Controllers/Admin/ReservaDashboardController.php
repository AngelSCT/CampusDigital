<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReservaDashboardController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today();
        $hace7Dias = $hoy->copy()->subDays(7);
        $hace30Dias = $hoy->copy()->subDays(30);

        // ── KPIs principales ────────────────────────────────────────────────
        $reservasHoy    = Reserva::whereDate('fecha_inicio', $hoy)->count();
        $ocupadasAhora  = Reserva::where('estado', Reserva::ESTADO_CONFIRMADA)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->count();
        $canceladasHoy  = Reserva::where('estado', Reserva::ESTADO_CANCELADA)
            ->whereDate('updated_at', $hoy)->count();
        $noShow7Dias    = Reserva::where('estado', Reserva::ESTADO_NO_SHOW)
            ->where('fecha_inicio', '>=', $hace7Dias)
            ->count();

        // ── Ocupación por recurso (7 días) ───────────────────────────────────
        $ocupacionPorRecurso = Recurso::query()
            ->leftJoin('reservas', function ($join) use ($hace7Dias) {
                $join->on('recursos.id_recurso', '=', 'reservas.id_recurso')
                     ->whereIn('reservas.estado', [Reserva::ESTADO_CONFIRMADA, Reserva::ESTADO_COMPLETADA])
                     ->where('reservas.fecha_inicio', '>=', $hace7Dias);
            })
            ->select(
                'recursos.id_recurso',
                'recursos.nombre',
                'recursos.tipo',
                DB::raw('COUNT(reservas.id_reserva) as total_reservas'),
                DB::raw('COALESCE(SUM(EXTRACT(EPOCH FROM (reservas.fecha_fin - reservas.fecha_inicio)) / 3600), 0) as total_horas')
            )
            ->groupBy('recursos.id_recurso', 'recursos.nombre', 'recursos.tipo')
            ->orderByDesc('total_reservas')
            ->limit(10)
            ->get();

        // ── Horarios pico (7 días) - reservas agrupadas por hora ─────────────
        $horariosPico = DB::table('reservas')
            ->select(
                DB::raw('EXTRACT(HOUR FROM fecha_inicio)::int as hora'),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_inicio', '>=', $hace7Dias)
            ->whereIn('estado', [Reserva::ESTADO_CONFIRMADA, Reserva::ESTADO_COMPLETADA])
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->pluck('total', 'hora')
            ->toArray();

        // Rellenar todas las horas de 0 a 23
        $horariosPicoCompleto = [];
        for ($h = 0; $h < 24; $h++) {
            $horariosPicoCompleto[$h] = $horariosPico[$h] ?? 0;
        }

        // ── Tendencia 30 días ───────────────────────────────────────────────
        $tendencia = DB::table('reservas')
            ->select(
                DB::raw('DATE(fecha_inicio) as fecha'),
                DB::raw("COUNT(CASE WHEN estado = 'confirmada' THEN 1 END) as confirmadas"),
                DB::raw("COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as canceladas"),
                DB::raw("COUNT(CASE WHEN estado = 'no_show' THEN 1 END) as no_show"),
                DB::raw("COUNT(CASE WHEN estado = 'completada' THEN 1 END) as completadas")
            )
            ->where('fecha_inicio', '>=', $hace30Dias)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // ── Cancelaciones y no-shows por recurso (7 días) ────────────────────
        $problemasPorRecurso = DB::table('reservas')
            ->join('recursos', 'reservas.id_recurso', '=', 'recursos.id_recurso')
            ->select(
                'recursos.nombre',
                DB::raw("COUNT(CASE WHEN reservas.estado = 'cancelada' THEN 1 END) as cancelaciones"),
                DB::raw("COUNT(CASE WHEN reservas.estado = 'no_show' THEN 1 END) as no_shows")
            )
            ->where('reservas.fecha_inicio', '>=', $hace7Dias)
            ->groupBy('recursos.nombre')
            ->orderByDesc('cancelaciones')
            ->get();

        return Inertia::render('Admin/Reservas/Dashboard', [
            'kpis' => [
                'reservas_hoy'   => $reservasHoy,
                'ocupadas_ahora' => $ocupadasAhora,
                'canceladas_hoy' => $canceladasHoy,
                'no_show_7d'     => $noShow7Dias,
            ],
            'ocupacionPorRecurso' => $ocupacionPorRecurso,
            'horariosPico'        => $horariosPicoCompleto,
            'tendencia'           => $tendencia,
            'problemasPorRecurso' => $problemasPorRecurso,
        ]);
    }
}
