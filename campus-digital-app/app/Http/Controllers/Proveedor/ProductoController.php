<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

/**
 * MODIFICADO — integración módulo 4.3 → 4.9
 *
 * El método index() ahora consulta el endpoint del módulo 4.3 para obtener
 * el catálogo oficial (con precio, disponibilidad y reglas ya calculados)
 * y lo pasa a la vista como prop adicional `catalogo_43`.
 *
 * Esto permite que Inventario.vue muestre dos secciones:
 *   1. Productos propios de la tienda (tabla local `producto`)
 *   2. Ítems del catálogo oficial 4.3 que corresponden a este vendedor
 *
 * NOTA: El campo `vendedor_catalogo_id` en la tabla `tienda` es el que
 * vincula cada tienda del 4.9 con su id_vendedor en el catálogo del 4.3.
 * Ver migración: 2026_add_vendedor_catalogo_id_to_tienda_table.php
 */
class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->tienda_id) abort(403);

        $tienda = Tienda::findOrFail($user->tienda_id);

        // Productos propios registrados en esta tienda (tabla local)
        $productos = Producto::where('tienda_id', $user->tienda_id)
            ->orderBy('nombre')
            ->get();

        // ── Catálogo del módulo 4.3 ───────────────────────────────────────────
        // Se consulta sólo si la tienda tiene un id de vendedor asignado.
        // Si la llamada falla (red, 404, etc.) se devuelve arreglo vacío
        // para no romper la vista del proveedor.
        $catalogo43 = [];

        if ($tienda->vendedor_catalogo_id) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['X-API-KEY' => env('API_KEYS')])
                    ->get(
                        rtrim(config('app.url'), '/') .
                        '/api/catalogo-integracion/vendedor/' .
                        $tienda->vendedor_catalogo_id,
                        ['activo' => 1]
                    );

                if ($response->successful() && $response->json('ok')) {
                    $catalogo43 = $response->json('items');
                }
            } catch (\Throwable $e) {
                // Si el módulo 4.3 no responde, la vista sigue funcionando
                // con catálogo vacío. No se rompe el panel del proveedor.
                \Log::warning('Módulo 4.3 no disponible: ' . $e->getMessage());
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        return Inertia::render('Proveedor/Inventario', [
            'productos'   => $productos,
            'catalogo_43' => $catalogo43,   // ← prop nuevo
            'tienda'      => $tienda,
            'tipos'       => Tienda::TIPOS,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->tienda_id) abort(403);

        $tienda = Tienda::findOrFail($user->tienda_id);

        $validated = $request->validate([
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'activo'      => 'required|boolean',
            'imagen_url'  => 'nullable|string',
        ]);

        $validated['tienda_id'] = $user->tienda_id;
        $validated['modulo']    = $tienda->tipo;

        Producto::create($validated);

        return back()->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        $user = $request->user();
        if ($producto->tienda_id !== $user->tienda_id) {
            abort(403);
        }

        $validated = $request->validate([
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'activo'      => 'required|boolean',
            'imagen_url'  => 'nullable|string',
        ]);

        $producto->update($validated);

        return back()->with('success', 'Producto actualizado correctamente');
    }

    public function destroy(Producto $producto)
    {
        $user = request()->user();
        if ($producto->tienda_id !== $user->tienda_id) {
            abort(403);
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado correctamente');
    }
}
