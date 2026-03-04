<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolApiController extends Controller
{
    // GET /api/roles
    public function index()
    {
        return response()->json(
            Rol::with(['permisos' => fn($q) => $q->whereNull('rol_permiso.deleted_at')])
                ->whereNull('deleted_at')
                ->get()
        );
    }

    // GET /api/roles/{id}
    public function show($id)
    {
        $rol = Rol::with(['permisos' => fn($q) => $q->whereNull('rol_permiso.deleted_at')])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return response()->json($rol);
    }

    // POST /api/roles
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:50|unique:rol,nombre',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        $rol = Rol::create($request->only(['nombre', 'descripcion', 'activo']));

        return response()->json($rol, 201);
    }

    // PUT /api/roles/{id}
    public function update(Request $request, $id)
    {
        $rol = Rol::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'nombre'      => "sometimes|string|max:50|unique:rol,nombre,{$id}",
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        $rol->update($request->only(['nombre', 'descripcion', 'activo']));

        return response()->json($rol->fresh());
    }

    // DELETE /api/roles/{id}
    public function destroy($id)
    {
        $rol = Rol::whereNull('deleted_at')->findOrFail($id);
        $rol->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Rol eliminado.']);
    }
}