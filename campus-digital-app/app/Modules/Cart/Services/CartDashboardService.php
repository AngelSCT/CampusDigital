<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Carrito;
use Illuminate\Support\Facades\DB;

class CartDashboardService
{
    /**
     * Métricas generales del módulo Carrito sobre tablas cart_*.
     */
    public function resumen(): array
    {
        $completados = Carrito::where('estado', Carrito::ESTADO_CONFIRMADO)->count();
        $pendientes  = Carrito::where('estado', Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION)->count();
        $abandonados = Carrito::whereIn('estado', [Carrito::ESTADO_CANCELADO, Carrito::ESTADO_EXPIRADO])->count();
        $abiertosVencidos = Carrito::where('estado', Carrito::ESTADO_ABIERTO)
            ->whereNotNull('expira_at')
            ->where('expira_at', '<', now())
            ->count();

        $tasaAbandono = ($completados + $abandonados) > 0
            ? round($abandonados / ($completados + $abandonados) * 100, 2)
            : 0.00;

        $consumoPromedio = Carrito::where('estado', Carrito::ESTADO_CONFIRMADO)
            ->avg('total') ?? 0.00;

        $totalRecaudado = Carrito::where('estado', Carrito::ESTADO_CONFIRMADO)
            ->sum('total') ?? 0.00;

        return [
            'checkouts_completados'    => $completados,
            'confirmados_pendientes'   => $pendientes,
            'abandonados'              => $abandonados,
            'abiertos_vencidos'        => $abiertosVencidos,
            'tasa_abandono'            => $tasaAbandono,
            'consumo_promedio'         => round((float) $consumoPromedio, 2),
            'total_recaudado'          => round((float) $totalRecaudado, 2),
        ];
    }

    /**
     * Agrupación de checkouts confirmados por hora del día (0-23).
     * Usa colecciones PHP en lugar de funciones SQL para compatibilidad SQLite/PostgreSQL.
     *
     * @return array<int, int>  [hora => cantidad]
     */
    public function consumoPorHorario(): array
    {
        $confirmados = Carrito::where('estado', Carrito::ESTADO_CONFIRMADO)
            ->whereNotNull('confirmed_at')
            ->get(['confirmed_at']);

        $porHora = $confirmados
            ->groupBy(fn($c) => (int) $c->confirmed_at->format('G'))
            ->map(fn($grupo) => $grupo->count())
            ->toArray();

        // Ordenar por hora
        ksort($porHora);

        return $porHora;
    }
}
