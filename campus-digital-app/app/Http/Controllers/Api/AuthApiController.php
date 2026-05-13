<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    // AUTORIZACION DENTRO DEL API
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->email)
            ->whereNull('deleted_at')
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }

        if ($usuario->bloqueado) {
            return response()->json(['message' => 'Usuario bloqueado.'], 403);
        }

        // REVOCAR LO QUE SON LOS TOKENS ANTERIORES
        $usuario->tokens()->delete();

        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'email'  => $usuario->email,
                'roles'  => $usuario->roles()->whereNull('usuario_rol.deleted_at')->pluck('nombre'),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['perfil', 'roles' => function ($q) {
            $q->whereNull('usuario_rol.deleted_at');
        }]);

        return response()->json($user);
    }
}