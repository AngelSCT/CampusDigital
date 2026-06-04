<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TiendaController extends Controller
{
    public function dashboard()
    {
        $tiendas = Tienda::withCount(['productos', 'pedidos'])->get();
        
        $stats = [
            'total' => $tiendas->count(),
            'activas' => $tiendas->where('activo', true)->count(),
            'por_tipo' => $tiendas->groupBy('tipo')->map->count(),
        ];

        return Inertia::render('Admin/TiendaPanel', [
            'tiendas' => $tiendas,
            'stats' => $stats,
            'tipos' => Tienda::TIPOS
        ]);
    }

    public function index()
    {
        return Inertia::render('Admin/Tiendas', [
            'tiendas'      => Tienda::withCount(['productos', 'pedidos'])->get(),
            'proveedores'  => \App\Models\Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'proveedor_area'))->with(['roles', 'tienda'])->get(),
            'repartidores' => \App\Models\Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'repartidor'))->with(['roles', 'tienda'])->get(),
            'tipos'        => Tienda::TIPOS,
            'tabActive'    => 'tiendas'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo'        => 'required|string|in:' . implode(',', array_keys(Tienda::TIPOS)),
            'ubicacion'   => 'nullable|string',
            'activo'      => 'boolean',
            'imagen'      => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('tiendas', 'public');
            $validated['imagen_url'] = $path;
        }

        Tienda::create($validated);

        return redirect()->back()->with('success', 'Tienda creada correctamente.');
    }

    public function update(Request $request, Tienda $tienda)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo'        => 'required|string|in:' . implode(',', array_keys(Tienda::TIPOS)),
            'ubicacion'   => 'nullable|string',
            'activo'      => 'boolean',
            'imagen'      => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            if ($tienda->imagen_url) {
                Storage::disk('public')->delete($tienda->imagen_url);
            }
            $path = $request->file('imagen')->store('tiendas', 'public');
            $validated['imagen_url'] = $path;
        }

        $tienda->update($validated);

        return redirect()->back()->with('success', 'Tienda actualizada correctamente.');
    }

    public function destroy(Tienda $tienda)
    {
        if ($tienda->imagen_url) {
            Storage::disk('public')->delete($tienda->imagen_url);
        }
        $tienda->delete();
        return redirect()->back()->with('success', 'Tienda eliminada correctamente.');
    }
}
