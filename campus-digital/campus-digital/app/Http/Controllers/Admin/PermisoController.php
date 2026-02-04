<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $query = Permiso::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('clave', 'ilike', "%{$request->search}%")
                  ->orWhere('descripcion', 'ilike', "%{$request->search}%");
            });
        }

        $permisos = $query->orderBy('clave')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Permisos/Index', [
            'permisos' => $permisos,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Permisos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => ['required', 'string', 'max:100', 'unique:permiso,clave'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Permiso::create([
            'clave' => $validated['clave'],
            'descripcion' => $validated['descripcion'] ?? '',
            'activo' => true,
        ]);

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso creado exitosamente.');
    }

    public function edit(Permiso $permiso)
    {
        return Inertia::render('Admin/Permisos/Edit', [
            'permiso' => $permiso,
        ]);
    }

    public function update(Request $request, Permiso $permiso)
    {
        $validated = $request->validate([
            'clave' => ['required', 'string', 'max:100', 'unique:permiso,clave,' . $permiso->id],
            'descripcion' => ['nullable', 'string'],
        ]);

        $permiso->update([
            'clave' => $validated['clave'],
            'descripcion' => $validated['descripcion'] ?? '',
        ]);

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso actualizado exitosamente.');
    }

    public function destroy(Permiso $permiso)
    {
        $permiso->delete();

        return redirect()->route('admin.permisos.index')->with('success', 'Permiso eliminado exitosamente.');
    }
}