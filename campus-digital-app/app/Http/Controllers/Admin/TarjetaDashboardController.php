<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarjetaLectura;
use App\Models\TarjetaUniversitaria;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TarjetaDashboardController extends Controller
{
    public function index()
    {
        // ── Estadísticas generales ──────────────────────────
        $stats = [
            'total_tarjetas'   => TarjetaUniversitaria::count(),
            'activas'          => TarjetaUniversitaria::where('estado', 'activa')->count(),
            'bloqueadas'       => TarjetaUniversitaria::whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])->count(),
            'lecturas_hoy'     => TarjetaLectura::whereDate('created_at', today())->count(),
            'lecturas_semana'  => TarjetaLectura::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        // ── Lecturas por día (últimos 14 días) ───────────────
        $lecturasPorDia = TarjetaLectura::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN exito = true THEN 1 ELSE 0 END) as exitosas'),
                DB::raw('SUM(CASE WHEN exito = false THEN 1 ELSE 0 END) as fallidas')
            )
            ->where('created_at', '>=', now()->subDays(13))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Rellenar días vacíos
        $diasCompletos = [];
        for ($i = 13; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->toDateString();
            $dia = $lecturasPorDia->firstWhere('fecha', $fecha);
            $diasCompletos[] = [
                'fecha'    => $fecha,
                'total'    => $dia ? (int)$dia->total : 0,
                'exitosas' => $dia ? (int)$dia->exitosas : 0,
                'fallidas' => $dia ? (int)$dia->fallidas : 0,
            ];
        }

        // ── Lecturas por módulo ──────────────────────────────
        $lecturasPorModulo = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        // ── Usuarios más activos (últimos 30 días) ───────────
        $usuariosActivos = TarjetaUniversitaria::select(
                'tarjeta_universitaria.id',
                'tarjeta_universitaria.uid',
                'tarjeta_universitaria.estado',
                DB::raw('COUNT(tarjeta_lectura.id) as total_lecturas')
            )
            ->leftJoin('tarjeta_lectura', function ($join) {
                $join->on('tarjeta_lectura.tarjeta_id', '=', 'tarjeta_universitaria.id')
                    ->where('tarjeta_lectura.created_at', '>=', now()->subDays(30))
                    ->whereNull('tarjeta_lectura.deleted_at');
            })
            ->with('usuario:id,nombre,apellido,email')
            ->groupBy('tarjeta_universitaria.id', 'tarjeta_universitaria.uid', 'tarjeta_universitaria.estado')
            ->orderByDesc('total_lecturas')
            ->take(10)
            ->get();

        // ── Tarjetas bloqueadas recientes ────────────────────
        $tarjetasBloqueadas = TarjetaUniversitaria::with(['usuario:id,nombre,apellido,email', 'bloqueadoPor:id,nombre,apellido'])
            ->whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])
            ->latest('bloqueado_at')
            ->take(5)
            ->get();

        // ── Lecturas recientes ───────────────────────────────
        $lecturasRecientes = TarjetaLectura::with([
                'tarjeta.usuario:id,nombre,apellido',
                'operador:id,nombre,apellido',
            ])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Admin/Tarjetas/Dashboard', [
            'stats'               => $stats,
            'lecturasPorDia'      => $diasCompletos,
            'lecturasPorModulo'   => $lecturasPorModulo,
            'usuariosActivos'     => $usuariosActivos,
            'tarjetasBloqueadas'  => $tarjetasBloqueadas,
            'lecturasRecientes'   => $lecturasRecientes,
        ]);
    }
}