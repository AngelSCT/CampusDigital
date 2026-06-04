<?php

namespace App\Http\Controllers;

use App\Models\Vendedor;
use App\Models\CatalogoVendedor;
use App\Models\PrecioVendedor;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CatalogoUsuarioDemoController extends Controller
{
    public function index(Request $request)
    {
        $vendedores = Vendedor::select('id_vendedor', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $selectedVendedorId = $request->integer('vendedor_id');

        if (!$selectedVendedorId || !$vendedores->contains('id_vendedor', $selectedVendedorId)) {
            $selectedVendedorId = $vendedores->first()?->id_vendedor;
        }

        if (!$selectedVendedorId) {
            return Inertia::render('CatalogoUsuario/Dashboard', [
                'vendedores' => [],
                'vendedorActual' => null,
                'stats' => [
                    'total_catalogo' => 0,
                    'total_activos' => 0,
                    'total_inactivos' => 0,
                    'sin_precio_actual' => 0,
                    'sin_disponibilidad' => 0,
                    'sin_regla' => 0,
                    'promociones_activas' => 0,
                ],
                'catalogo' => [],
                'pendientes' => [],
            ]);
        }

        $precioActualSubquery = PrecioVendedor::query()
            ->select('id_cv', DB::raw('MAX(id_precio_v) as id_precio_v'))
            ->whereNull('fecha_fin')
            ->groupBy('id_cv');

        $catalogo = CatalogoVendedor::query()
            ->where('catalogo_vendedor.id_vendedor', $selectedVendedorId)
            ->leftJoinSub($precioActualSubquery, 'precio_v_actual_rel', function ($join) {
                $join->on('catalogo_vendedor.id_cv', '=', 'precio_v_actual_rel.id_cv');
            })
            ->leftJoin('precios_vendedor as precio_v_actual', 'precio_v_actual.id_precio_v', '=', 'precio_v_actual_rel.id_precio_v')
            ->select('catalogo_vendedor.*', 'precio_v_actual.precio as precio_actual')
            ->orderByDesc('catalogo_vendedor.id_cv')
            ->get();

        $sinPrecioActual = DB::table('catalogo_vendedor as cv')
            ->leftJoin('precios_vendedor as pv', function ($join) {
                $join->on('cv.id_cv', '=', 'pv.id_cv')
                    ->whereNull('pv.fecha_fin');
            })
            ->where('cv.id_vendedor', $selectedVendedorId)
            ->whereNull('pv.id_precio_v')
            ->count();

        $sinDisponibilidad = DB::table('catalogo_vendedor as cv')
            ->leftJoin('disponibilidad_vendedor as dv', 'cv.id_cv', '=', 'dv.id_cv')
            ->where('cv.id_vendedor', $selectedVendedorId)
            ->whereNull('dv.id_disp_v')
            ->count();

        $sinRegla = DB::table('catalogo_vendedor as cv')
            ->leftJoin('reglas_vendedor as rv', 'cv.id_cv', '=', 'rv.id_cv')
            ->where('cv.id_vendedor', $selectedVendedorId)
            ->whereNull('rv.id_regla_v')
            ->count();

        $promocionesActivas = Promocion::query()
            ->join('promociones_vendedor as pv', 'promociones.id_promocion', '=', 'pv.id_promocion')
            ->join('catalogo_vendedor as cv', 'pv.id_cv', '=', 'cv.id_cv')
            ->where('cv.id_vendedor', $selectedVendedorId)
            ->where('promociones.activa', true)
            ->distinct('promociones.id_promocion')
            ->count('promociones.id_promocion');

        $pendientes = DB::table('catalogo_vendedor as cv')
            ->leftJoin('precios_vendedor as pv', function ($join) {
                $join->on('cv.id_cv', '=', 'pv.id_cv')
                    ->whereNull('pv.fecha_fin');
            })
            ->leftJoin('disponibilidad_vendedor as dv', 'cv.id_cv', '=', 'dv.id_cv')
            ->leftJoin('reglas_vendedor as rv', 'cv.id_cv', '=', 'rv.id_cv')
            ->select(
                'cv.id_cv',
                'cv.nombre_personalizado',
                'cv.activo',
                DB::raw('CASE WHEN pv.id_precio_v IS NULL THEN 1 ELSE 0 END as falta_precio'),
                DB::raw('CASE WHEN dv.id_disp_v IS NULL THEN 1 ELSE 0 END as falta_disponibilidad'),
                DB::raw('CASE WHEN rv.id_regla_v IS NULL THEN 1 ELSE 0 END as falta_regla')
            )
            ->where('cv.id_vendedor', $selectedVendedorId)
            ->where(function ($query) {
                $query->whereNull('pv.id_precio_v')
                    ->orWhereNull('dv.id_disp_v')
                    ->orWhereNull('rv.id_regla_v')
                    ->orWhere('cv.activo', false);
            })
            ->orderByDesc('cv.id_cv')
            ->limit(8)
            ->get();

        $stats = [
            'total_catalogo' => $catalogo->count(),
            'total_activos' => $catalogo->where('activo', true)->count(),
            'total_inactivos' => $catalogo->where('activo', false)->count(),
            'sin_precio_actual' => $sinPrecioActual,
            'sin_disponibilidad' => $sinDisponibilidad,
            'sin_regla' => $sinRegla,
            'promociones_activas' => $promocionesActivas,
        ];

        return Inertia::render('CatalogoUsuario/Dashboard', [
            'vendedores' => $vendedores,
            'vendedorActual' => $selectedVendedorId,
            'stats' => $stats,
            'catalogo' => $catalogo,
            'pendientes' => $pendientes,
        ]);
    }
}
