<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use App\Models\CatalogoVendedor;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * INTEGRACIÓN MÓDULO 4.3 → 4.9
 *
 * Expone el catálogo de cada vendedor con precio, disponibilidad y regla
 * ya resueltos (precedencia vendedor > global), listo para que el panel
 * operativo del módulo 4.9 lo consuma directamente.
 *
 * Rutas registradas en routes/api.php:
 *   GET /api/catalogo-integracion/vendedor/{id_vendedor}
 *   GET /api/catalogo-integracion/vendedores
 *
 * Autenticación: Header X-API-KEY (middleware api.key — igual que el resto)
 */
class CatalogoIntegracionApiController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /api/catalogo-integracion/vendedor/{id_vendedor}
    //
    // Devuelve el catálogo completo de un vendedor con precio, disponibilidad,
    // disponible_ahora y regla calculados. El módulo 4.9 no necesita saber nada
    // sobre la lógica interna de precedencia.
    //
    // Query params opcionales:
    //   ?tipo=producto|servicio
    //   ?id_categoria=N
    //   ?activo=1|0
    //   ?disponible_ahora=1|0
    // -------------------------------------------------------------------------
    public function porVendedor(Request $request, $id_vendedor)
    {
        $vendedor = Vendedor::find($id_vendedor);

        if (!$vendedor) {
            return response()->json([
                'ok'      => false,
                'message' => 'Vendedor no encontrado',
            ], 404);
        }

        $query = CatalogoVendedor::with(['vendedor', 'categoria', 'catalogoBase'])
            ->where('id_vendedor', $id_vendedor);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', (int) $request->id_categoria);
        }

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        $items = $query->get()->map(function ($cv) use ($request) {

            $precio = $cv->getPrecioAplicable();
            $disp   = $cv->getDisponibilidadAplicable();
            $regla  = $cv->getReglaAplicable();

            $dispAhora = $this->calcularDisponibleAhora($disp);

            // Filtro por disponible_ahora (después de calcular)
            if ($request->filled('disponible_ahora')) {
                $filtro = filter_var($request->disponible_ahora, FILTER_VALIDATE_BOOLEAN);
                if ($filtro !== $dispAhora) {
                    return null;
                }
            }

            // Inventario: buscar por id_catalogo_base si existe
            $inventario = null;
            if ($cv->id_catalogo_base) {
                $inventario = Inventario::where('id_catalogo', $cv->id_catalogo_base)->first();
            }

            return [
                'id_cv'            => $cv->id_cv,
                'nombre'           => $cv->nombre_personalizado,
                'descripcion'      => $cv->descripcion_personalizada,
                'tipo'             => $cv->tipo,
                'activo'           => (bool) $cv->activo,
                'id_catalogo_base' => $cv->id_catalogo_base,

                'categoria' => $cv->categoria ? [
                    'id_categoria' => $cv->categoria->id_categoria,
                    'nombre'       => $cv->categoria->nombre,
                ] : null,

                'precio' => $precio ? [
                    'valor'        => (float) $precio->precio,
                    'fuente'       => isset($precio->id_precio_v) ? 'vendedor' : 'global',
                    'fecha_inicio' => $precio->fecha_inicio,
                ] : null,

                'disponibilidad' => $disp ? [
                    'dia_semana'  => $disp->dia_semana,
                    'hora_inicio' => $disp->hora_inicio,
                    'hora_fin'    => $disp->hora_fin,
                    'disponible'  => (bool) $disp->disponible,
                    'fuente'      => isset($disp->id_disp_v) ? 'vendedor' : 'global',
                ] : null,

                'disponible_ahora' => $dispAhora,

                'regla' => $regla ? [
                    'tipo_regla'  => $regla->tipo_regla,
                    'descripcion' => $regla->descripcion,
                    'fuente'      => isset($regla->id_regla_v) ? 'vendedor' : 'global',
                ] : null,

                'stock_actual' => $inventario ? (int) $inventario->stock_actual : null,
                'stock_minimo' => $inventario ? (int) $inventario->stock_minimo : null,
            ];

        })->filter()->values();

        return response()->json([
            'ok'      => true,
            'vendedor' => [
                'id_vendedor' => $vendedor->id_vendedor,
                'nombre'      => $vendedor->nombre,
                'descripcion' => $vendedor->descripcion,
                'activo'      => (bool) $vendedor->activo,
            ],
            'total' => $items->count(),
            'items' => $items,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/catalogo-integracion/vendedores
    //
    // Lista todos los vendedores con resumen de catálogo. Útil para la vista
    // de administración del panel de proveedores en el módulo 4.9.
    //
    // Query params opcionales:
    //   ?activo=1|0
    //   ?tipo=producto|servicio
    // -------------------------------------------------------------------------
    public function vendedores(Request $request)
    {
        $query = Vendedor::withCount('catalogoItems');

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        $vendedores = $query->get()->map(function ($v) use ($request) {
            $itemsActivos = CatalogoVendedor::where('id_vendedor', $v->id_vendedor)
                ->where('activo', true)
                ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
                ->count();

            return [
                'id_vendedor'   => $v->id_vendedor,
                'nombre'        => $v->nombre,
                'descripcion'   => $v->descripcion,
                'activo'        => (bool) $v->activo,
                'total_items'   => $v->catalogo_items_count,
                'items_activos' => $itemsActivos,
            ];
        });

        return response()->json([
            'ok'        => true,
            'vendedores' => $vendedores,
        ]);
    }

    // -------------------------------------------------------------------------
    // Calcula si un ítem está disponible en este momento exacto.
    //
    // Reglas en orden:
    //   1. Sin disponibilidad registrada   → siempre disponible (true)
    //   2. disponibilidad.disponible=false → no disponible
    //   3. día de hoy no coincide          → no disponible
    //   4. hora fuera de [inicio, fin]     → no disponible
    //   5. Todo coincide                   → disponible
    //
    // dia_semana se guarda en español minúsculas (lunes, martes…).
    // Carbon::now()->locale('es')->dayName devuelve el nombre en español.
    // -------------------------------------------------------------------------
    private function calcularDisponibleAhora($disp): bool
    {
        if (!$disp) {
            return true;
        }

        if (!(bool) $disp->disponible) {
            return false;
        }

        $ahora     = Carbon::now()->locale('es');
        $diaHoy    = strtolower($ahora->dayName);
        $horaAhora = $ahora->format('H:i');

        if ($diaHoy !== strtolower($disp->dia_semana)) {
            return false;
        }

        if ($horaAhora < $disp->hora_inicio || $horaAhora > $disp->hora_fin) {
            return false;
        }

        return true;
    }
}
