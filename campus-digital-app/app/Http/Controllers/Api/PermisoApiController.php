<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermisoResource;
use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoApiController extends Controller
{
    // GET /api/permisos
    public function index()
    {
        return PermisoResource::collection(Permiso::whereNull('deleted_at')->get());
    }

    // GET /api/permisos/{id}
    public function show($id)
    {
        return new PermisoResource(Permiso::whereNull('deleted_at')->findOrFail($id));
    }

    // POST /api/permisos
    public function store(Request $request)
    {
        $request->validate([
            'clave'       => 'required|string|max:100|unique:permiso,clave',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        $permiso = Permiso::create($request->only(['clave', 'descripcion', 'activo']));

        return (new PermisoResource($permiso))->response()->setStatusCode(201);
    }

    // PUT /api/permisos/{id}
    public function update(Request $request, $id)
    {
        $permiso = Permiso::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'clave'       => "sometimes|string|max:100|unique:permiso,clave,{$id}",
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        $permiso->update($request->only(['clave', 'descripcion', 'activo']));

        return new PermisoResource($permiso->fresh());
    }

    // DELETE /api/permisos/{id}
    public function destroy($id)
    {
        $permiso = Permiso::whereNull('deleted_at')->findOrFail($id);
        $permiso->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Permiso eliminado.']);
    }
}