<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index(Request $request)
    {
        $query = Rol::withCount('usuarios');

        if ($request->filled('search')) {
            $query->where('nombre', 'ilike', "%{$request->search}%");
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create', [
            'permisos' => Permiso::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:rol,nombre'],
            'descripcion' => ['nullable', 'string'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['exists:permiso,id'],
        ]);

        $rol = Rol::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? '',
            'activo' => true,
        ]);

        if (!empty($validated['permisos'])) {
            $rol->permisos()->attach($validated['permisos']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Rol $rol)
    {
        $rol->load('permisos');
        
        return Inertia::render('Admin/Roles/Edit', [
            'rol' => $rol,
            'permisos' => Permiso::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:rol,nombre,' . $rol->id],
            'descripcion' => ['nullable', 'string'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['exists:permiso,id'],
        ]);

        $rol->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? '',
        ]);

        $rol->permisos()->sync($validated['permisos'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Rol $rol)
    {
        if ($rol->usuarios()->count() > 0) {
            return back()->withErrors(['error' => 'No puedes eliminar un rol que tiene usuarios asignados.']);
        }

        $rol->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}