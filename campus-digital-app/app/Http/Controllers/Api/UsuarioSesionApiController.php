<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioSesion;
use Illuminate\Http\Request;

class UsuarioSesionApiController extends Controller
{
    // GET /api/sesiones
    public function index(Request $request)
    {
        $query = UsuarioSesion::with('usuario')->whereNull('deleted_at');

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('activa')) {
            $query->where('activa', filter_var($request->activa, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('desde')) {
            $query->where('inicia_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->where('inicia_at', '<=', $request->hasta);
        }

        return response()->json($query->orderByDesc('inicia_at')->paginate($request->get('per_page', 20)));
    }

    // GET /api/sesiones/{id}
    public function show($id)
    {
        return response()->json(
            UsuarioSesion::with('usuario')->whereNull('deleted_at')->findOrFail($id)
        );
    }
}