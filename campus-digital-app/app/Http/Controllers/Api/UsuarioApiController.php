<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioApiController extends Controller
{
    // GET /api/usuarios
    public function index(Request $request)
    {
        $query = Usuario::with(['perfil', 'roles' => fn($q) => $q->whereNull('usuario_rol.deleted_at')])
            ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('nombre', 'ilike', "%$s%")
                ->orWhere('apellido', 'ilike', "%$s%")
                ->orWhere('email', 'ilike', "%$s%"));
        }

        if ($request->filled('bloqueado')) {
            $query->where('bloqueado', filter_var($request->bloqueado, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    // GET /api/usuarios/{id}
    public function show($id)
    {
        $usuario = Usuario::with(['perfil', 'roles' => fn($q) => $q->whereNull('usuario_rol.deleted_at')])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return response()->json($usuario);
    }

    // POST /api/usuarios
    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'email'    => 'required|email|unique:usuario,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:30',
        ]);

        $usuario = Usuario::create([
            'nombre'        => $request->nombre,
            'apellido'      => $request->apellido,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'telefono'      => $request->telefono ?? '',
            'email_verificado' => true,
        ]);

        $usuario->perfil()->create([]);

        return response()->json($usuario, 201);
    }

    // PUT /api/usuarios/{id}
    public function update(Request $request, $id)
    {
        $usuario = Usuario::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'nombre'   => 'sometimes|string|max:120',
            'apellido' => 'sometimes|string|max:120',
            'email'    => "sometimes|email|unique:usuario,email,{$id}",
            'telefono' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8',
        ]);

        $data = $request->only(['nombre', 'apellido', 'email', 'telefono']);

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return response()->json($usuario->fresh());
    }

    // DELETE /api/usuarios/{id}
    public function destroy($id)
    {
        $usuario = Usuario::whereNull('deleted_at')->findOrFail($id);
        $usuario->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Usuario eliminado.']);
    }

    // POST /api/usuarios/{id}/toggle-block
    public function toggleBlock($id)
    {
        $usuario = Usuario::whereNull('deleted_at')->findOrFail($id);
        $usuario->update(['bloqueado' => !$usuario->bloqueado]);

        $estado = $usuario->bloqueado ? 'bloqueado' : 'desbloqueado';
        return response()->json(['message' => "Usuario $estado.", 'bloqueado' => $usuario->bloqueado]);
    }
}