<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Carrito;
use App\Models\Cart\ItemCarrito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CartReportService
{
    /**
     * Consumos por período — KPIs + detalle de carritos confirmados/pendientes.
     */
    public function consumosPorPeriodo(Carbon $desde, Carbon $hasta, ?string $moduloSlug = null): array
    {
        $query = Carrito::with('modulo')
            ->whereBetween('created_at', [$desde->startOfDay(), $hasta->endOfDay()]);

        if ($moduloSlug) {
            $query->whereHas('modulo', fn($q) => $q->where('slug', $moduloSlug));
        }

        $todos = $query->get();

        $confirmados = $todos->where('estado', Carrito::ESTADO_CONFIRMADO);
        $pendientes  = $todos->where('estado', Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION);

        $totalConfirmado = $confirmados->sum(fn($c) => (float) $c->total);
        $totalPendiente  = $pendientes->sum(fn($c) => (float) $c->total);
        $numeroCheckouts = $confirmados->count() + $pendientes->count();
        $promedioConsumo = $confirmados->count() > 0
            ? round($confirmados->avg(fn($c) => (float) $c->total), 2)
            : 0.00;

        $detalle = $confirmados->merge($pendientes)
            ->sortByDesc('confirmed_at')
            ->values()
            ->map(fn($c) => [
                'carrito_uuid' => (string) $c->uuid,
                'estado'       => $c->estado,
                'total'        => (float) $c->total,
                'usuario_ref'  => $c->usuario_ref,
                'modulo'       => $c->modulo?->slug ?? '—',
                'confirmed_at' => $c->confirmed_at?->toISOString(),
            ])->toArray();

        return [
            'total_confirmado' => round($totalConfirmado, 2),
            'total_pendiente'  => round($totalPendiente, 2),
            'numero_checkouts' => $numeroCheckouts,
            'promedio_consumo' => $promedioConsumo,
            'detalle'          => $detalle,
        ];
    }

    /**
     * Carritos abandonados (cancelados/expirados) + abiertos vencidos en el período.
     */
    public function carritosAbandonados(Carbon $desde, Carbon $hasta, ?string $moduloSlug = null): array
    {
        $baseQuery = Carrito::with('modulo')
            ->whereBetween('created_at', [$desde->startOfDay(), $hasta->endOfDay()]);

        if ($moduloSlug) {
            $baseQuery->whereHas('modulo', fn($q) => $q->where('slug', $moduloSlug));
        }

        $abandonados = (clone $baseQuery)
            ->whereIn('estado', [Carrito::ESTADO_CANCELADO, Carrito::ESTADO_EXPIRADO])
            ->get();

        $abiertosVencidos = Carrito::with('modulo')
            ->where('estado', Carrito::ESTADO_ABIERTO)
            ->whereNotNull('expira_at')
            ->where('expira_at', '<', now())
            ->get();

        $mapear = fn($c) => [
            'carrito_uuid' => (string) $c->uuid,
            'usuario_ref'  => $c->usuario_ref,
            'modulo'       => $c->modulo?->slug ?? '—',
            'estado'       => $c->estado,
            'total'        => (float) $c->total,
            'created_at'   => $c->created_at?->toISOString(),
            'expira_at'    => $c->expira_at?->toISOString(),
        ];

        return [
            'total'             => $abandonados->count(),
            'lista'             => $abandonados->map($mapear)->values()->toArray(),
            'abiertos_vencidos' => $abiertosVencidos->map($mapear)->values()->toArray(),
        ];
    }

    /**
     * Consumo agrupado por categoría en el período (solo ítems activos de carritos confirmados).
     */
    public function consumoPorCategoria(Carbon $desde, Carbon $hasta, ?string $categoriaSlug = null): array
    {
        $carritoIds = Carrito::where('estado', Carrito::ESTADO_CONFIRMADO)
            ->whereBetween('confirmed_at', [$desde->startOfDay(), $hasta->endOfDay()])
            ->pluck('id');

        $itemsQuery = ItemCarrito::with('categoria')
            ->whereIn('carrito_id', $carritoIds)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO);

        if ($categoriaSlug) {
            $itemsQuery->whereHas('categoria', fn($q) => $q->where('slug', $categoriaSlug));
        }

        $items = $itemsQuery->get();

        return $items
            ->groupBy(fn($i) => $i->categoria?->slug ?? 'sin_categoria')
            ->map(function ($grupo, $slug) {
                $cat = $grupo->first()?->categoria;
                return [
                    'categoria_slug'   => $slug,
                    'categoria_nombre' => $cat?->nombre ?? $slug,
                    'cantidad_items'   => $grupo->count(),
                    'total_unidades'   => $grupo->sum(fn($i) => $i->cantidad),
                    'total_consumido'  => round($grupo->sum(fn($i) => (float) $i->precio_unitario * $i->cantidad), 2),
                ];
            })
            ->sortByDesc('total_consumido')
            ->values()
            ->toArray();
    }
}
