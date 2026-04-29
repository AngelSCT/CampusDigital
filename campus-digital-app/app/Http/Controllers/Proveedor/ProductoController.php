<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->tienda_id) abort(403);

        $tienda = \App\Models\Tienda::findOrFail($user->tienda_id);

        $productos = Producto::where('tienda_id', $user->tienda_id)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Proveedor/Inventario', [
            'productos' => $productos,
            'tienda' => $tienda,
            'tipos' => \App\Models\Tienda::TIPOS
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->tienda_id) abort(403);
        
        $tienda = \App\Models\Tienda::findOrFail($user->tienda_id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'activo' => 'required|boolean',
            'imagen_url' => 'nullable|string',
        ]);

        $validated['tienda_id'] = $user->tienda_id;
        $validated['modulo'] = $tienda->tipo; // Keep for compatibility

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
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'activo' => 'required|boolean',
            'imagen_url' => 'nullable|string',
        ]);

        $producto->update($validated);

        return back()->with('success', 'Producto actualizado correctamente');
    }

    public function destroy(Producto $producto)
    {
        $user = $request->user();
        if ($producto->tienda_id !== $user->tienda_id) {
            abort(403);
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado correctamente');
    }
}
