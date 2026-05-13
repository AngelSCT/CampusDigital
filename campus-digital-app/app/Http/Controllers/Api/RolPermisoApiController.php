<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RolPermiso;
use Illuminate\Http\Request;

class RolPermisoApiController extends Controller
{
    // GET /api/rol-permisos
    public function index(Request $request)
    {
        $query = RolPermiso::with(['rol', 'permiso'])->whereNull('deleted_at');

        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        return response()->json($query->get());
    }

    // POST /api/rol-permisos
    public function store(Request $request)
    {
        $request->validate([
            'rol_id'     => 'required|exists:rol,id',
            'permiso_id' => 'required|exists:permiso,id',
        ]);

        $existe = RolPermiso::where('rol_id', $request->rol_id)
            ->where('permiso_id', $request->permiso_id)
            ->whereNull('deleted_at')
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'El permiso ya está asignado a ese rol.'], 409);
        }

        $rp = RolPermiso::create($request->only(['rol_id', 'permiso_id']));

        return response()->json($rp->load(['rol', 'permiso']), 201);
    }

    // DELETE /api/rol-permisos/{id}
    public function destroy($id)
    {
        $rp = RolPermiso::whereNull('deleted_at')->findOrFail($id);
        $rp->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Permiso revocado del rol.']);
    }
}