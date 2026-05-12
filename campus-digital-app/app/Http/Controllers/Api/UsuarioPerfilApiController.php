<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioPerfil;
use Illuminate\Http\Request;

class UsuarioPerfilApiController extends Controller
{
    // GET /api/usuario-perfiles
    public function index()
    {
        return response()->json(
            UsuarioPerfil::with('usuario')->whereNull('deleted_at')->get()
        );
    }

    // GET /api/usuario-perfiles/{id}
    public function show($id)
    {
        return response()->json(
            UsuarioPerfil::with('usuario')->whereNull('deleted_at')->findOrFail($id)
        );
    }

    // PUT /api/usuario-perfiles/{id}
    public function update(Request $request, $id)
    {
        $perfil = UsuarioPerfil::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'fecha_nacimiento'   => 'nullable|date',
            'genero'             => 'nullable|string|max:30',
            'direccion'          => 'nullable|string',
            'preferencias_json'  => 'nullable|array',
        ]);

        $perfil->update($request->only(['fecha_nacimiento', 'genero', 'direccion', 'preferencias_json']));

        return response()->json($perfil->fresh());
    }
}