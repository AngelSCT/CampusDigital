<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Catalogo;
use App\Models\Categoria;
use App\Models\Area;
use App\Models\Movimiento;
use App\Models\Vendedor;
use App\Models\Promocion;

class CatalogoDashboardController extends Controller
{
    public function index()
    {
        $sinPrecioActual = DB::table('catalogo as c')
            ->leftJoin('precios as p', function ($join) {
                $join->on('c.id_catalogo', '=', 'p.id_catalogo')
                    ->whereNull('p.fecha_fin');
            })
            ->whereNull('p.id_precio')
            ->count();

        $sinDisponibilidad = DB::table('catalogo as c')
            ->leftJoin('disponibilidad as d', 'c.id_catalogo', '=', 'd.id_catalogo')
            ->whereNull('d.id_disponibilidad')
            ->count();

        $sinRegla = DB::table('catalogo as c')
            ->leftJoin('reglas as r', 'c.id_catalogo', '=', 'r.id_catalogo')
            ->whereNull('r.id_regla')
            ->count();

        // 📊 MÉTRICAS GENERALES
        $stats = [
            'total_catalogo' => Catalogo::count(),
            'total_categorias' => Categoria::count(),
            'total_areas' => Area::count(),
            'total_vendedores' => Vendedor::count(),
            'total_promociones' => Promocion::count(),
            'total_movimientos' => Movimiento::count(),
            'total_activos' => Catalogo::where('activo', true)->count(),
            'total_inactivos' => Catalogo::where('activo', false)->count(),
            'sin_precio_actual' => $sinPrecioActual,
            'sin_disponibilidad' => $sinDisponibilidad,
            'sin_regla' => $sinRegla,
        ];

        // 🔥 TOP PRODUCTOS / SERVICIOS
        $top = DB::table('carrito_items')
            ->join('productos', 'carrito_items.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                DB::raw('SUM(carrito_items.cantidad) as total')
            )
            ->where('carrito_items.guardado_para_despues', false)
            ->where('carrito_items.en_wishlist', false)
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 📊 CONSUMO POR CATEGORÍA
        $categorias = DB::table('carrito_items')
            ->join('productos', 'carrito_items.producto_id', '=', 'productos.id')
            ->select(
                'productos.categoria as nombre',
                DB::raw('SUM(carrito_items.cantidad) as total')
            )
            ->where('carrito_items.guardado_para_despues', false)
            ->where('carrito_items.en_wishlist', false)
            ->whereNotNull('productos.categoria')
            ->groupBy('productos.categoria')
            ->orderByDesc('total')
            ->get();

        $pendientes = DB::table('catalogo as c')
            ->leftJoin('precios as p', function ($join) {
                $join->on('c.id_catalogo', '=', 'p.id_catalogo')
                    ->whereNull('p.fecha_fin');
            })
            ->leftJoin('disponibilidad as d', 'c.id_catalogo', '=', 'd.id_catalogo')
            ->leftJoin('reglas as r', 'c.id_catalogo', '=', 'r.id_catalogo')
            ->select(
                'c.id_catalogo',
                'c.nombre',
                'c.activo',
                DB::raw('CASE WHEN p.id_precio IS NULL THEN 1 ELSE 0 END as falta_precio'),
                DB::raw('CASE WHEN d.id_disponibilidad IS NULL THEN 1 ELSE 0 END as falta_disponibilidad'),
                DB::raw('CASE WHEN r.id_regla IS NULL THEN 1 ELSE 0 END as falta_regla')
            )
            ->where(function ($query) {
                $query->whereNull('p.id_precio')
                    ->orWhereNull('d.id_disponibilidad')
                    ->orWhereNull('r.id_regla')
                    ->orWhere('c.activo', false);
            })
            ->orderByDesc('c.id_catalogo')
            ->limit(8)
            ->get();

        $crudSummary = [
            [
                'title' => 'Catalogo',
                'count' => $stats['total_catalogo'],
                'href' => '/catalogo',
                'cta' => 'Gestionar'
            ],
            [
                'title' => 'Vendedores',
                'count' => $stats['total_vendedores'],
                'href' => '/vendedores',
                'cta' => 'Gestionar'
            ],
            [
                'title' => 'Categorias',
                'count' => $stats['total_categorias'],
                'href' => '/categorias',
                'cta' => 'Administrar'
            ],
            [
                'title' => 'Areas',
                'count' => $stats['total_areas'],
                'href' => '/areas',
                'cta' => 'Administrar'
            ],
            [
                'title' => 'Promociones',
                'count' => $stats['total_promociones'],
                'href' => '/promociones',
                'cta' => 'Administrar'
            ],
            [
                'title' => 'Precios activos',
                'count' => DB::table('precios')->whereNull('fecha_fin')->count(),
                'href' => '/precios',
                'cta' => 'Revisar'
            ],
            [
                'title' => 'Inventario',
                'count' => DB::table('inventario')->count(),
                'href' => '/inventario',
                'cta' => 'Gestionar'
            ],
            [
                'title' => 'Disponibilidad',
                'count' => DB::table('disponibilidad')->count(),
                'href' => '/disponibilidad',
                'cta' => 'Revisar'
            ],
            [
                'title' => 'Reglas',
                'count' => DB::table('reglas')->count(),
                'href' => '/reglas',
                'cta' => 'Revisar'
            ],
            [
                'title' => 'Movimientos',
                'count' => $stats['total_movimientos'],
                'href' => '/movimientos',
                'cta' => 'Registrar'
            ],
        ];

        return Inertia::render('Catalogo/Dashboard', [
            'stats' => $stats,
            'top' => $top,
            'categorias' => $categorias,
            'pendientes' => $pendientes,
            'crudSummary' => $crudSummary,
        ]);
    }
}