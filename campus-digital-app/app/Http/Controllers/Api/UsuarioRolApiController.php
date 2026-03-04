<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioRol;
use Illuminate\Http\Request;

class UsuarioRolApiController extends Controller
{
    // GET /api/usuario-roles
    public function index(Request $request)
    {
        $query = UsuarioRol::with(['usuario', 'rol'])->whereNull('deleted_at');

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        return response()->json($query->get());
    }

    // POST /api/usuario-roles
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuario,id',
            'rol_id'     => 'required|exists:rol,id',
        ]);

        $existe = UsuarioRol::where('usuario_id', $request->usuario_id)
            ->where('rol_id', $request->rol_id)
            ->whereNull('deleted_at')
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'El usuario ya tiene ese rol.'], 409);
        }

        $ur = UsuarioRol::create([
            'usuario_id'              => $request->usuario_id,
            'rol_id'                  => $request->rol_id,
            'asignado_por_usuario_id' => $request->user()->id,
            'asignado_at'             => now(),
        ]);

        return response()->json($ur->load(['usuario', 'rol']), 201);
    }

    // DELETE /api/usuario-roles/{id}
    public function destroy($id)
    {
        $ur = UsuarioRol::whereNull('deleted_at')->findOrFail($id);
        $ur->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Rol revocado del usuario.']);
    }
}