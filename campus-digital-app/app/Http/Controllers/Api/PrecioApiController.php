<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Precio;

/**
 * PrecioApiController — Módulo 4.3 (Catálogo de Servicios y Productos)
 *
 * GET /api/precios
 *   → todos los precios (sin filtros)
 *
 * GET /api/precios?id_catalogo=X
 *   → todos los precios del ítem X (historial completo)
 *
 * GET /api/precios?id_catalogo=X&vigente=true
 *   → precio vigente hoy para el ítem X
 *     (fecha_inicio <= hoy <= fecha_fin, o fecha_fin IS NULL)
 *     Devuelve el registro más reciente si hubiera solapamiento.
 *
 * Esta lógica es la "fuente de verdad" de precio vigente para todos los
 * módulos que consuman el catálogo (p. ej. Módulo 4.5 — Pedidos).
 */
class PrecioApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Precio::query();

        // Filtro por producto/servicio
        if ($request->filled('id_catalogo')) {
            $query->where('id_catalogo', $request->integer('id_catalogo'));
        }

        // Filtro de vigencia: fecha_inicio <= hoy AND (fecha_fin IS NULL OR fecha_fin >= hoy)
        if ($request->boolean('vigente')) {
            $hoy = now()->toDateString();

            $query->where('fecha_inicio', '<=', $hoy)
                  ->where(function ($q) use ($hoy) {
                      $q->whereNull('fecha_fin')
                        ->orWhere('fecha_fin', '>=', $hoy);
                  })
                  ->orderByDesc('fecha_inicio') // el más reciente primero
                  ->limit(1);                   // sólo el precio vigente activo
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        return response()->json(Precio::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (!empty($data['id'])) {
            $item = Precio::findOrFail($data['id']);
            $item->update($data);
            return response()->json($item);
        }
        $item = Precio::create($data);
        return response()->json($item, 201);
    }

    public function destroy($id)
    {
        $item = Precio::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }
}
