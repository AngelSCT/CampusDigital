<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipoActivo;
use Illuminate\Http\Request;

class EquipoActivoApiController extends Controller
{
    // GET /api/equipos-activos
    public function index()
    {
        return response()->json(
            EquipoActivo::with(['categoria', 'ubicacion'])->whereNull('deleted_at')->get()
        );
    }

    // GET /api/equipos-activos/{id}
    public function show($id)
    {
        $equipo = EquipoActivo::with(['categoria', 'ubicacion'])->whereNull('deleted_at')->findOrFail($id);

        return response()->json($equipo);
    }

    // POST /api/equipos-activos
    public function store(Request $request)
    {
        $request->validate([
            'id_categoria'  => 'required|integer|exists:categorias_ticket,id_categoria',
            'id_ubicacion'  => 'required|integer|exists:ubicaciones,id_ubicacion',
            'nombre_equipo' => 'required|string|max:120',
            'estado_actual' => 'required|string|max:50',
        ]);

        $equipo = EquipoActivo::create(
            $request->only(['id_categoria', 'id_ubicacion', 'nombre_equipo', 'estado_actual'])
        );

        return response()->json($equipo->load(['categoria', 'ubicacion']), 201);
    }

    // PUT /api/equipos-activos/{id}
    public function update(Request $request, $id)
    {
        $equipo = EquipoActivo::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_categoria'  => 'required|integer|exists:categorias_ticket,id_categoria',
            'id_ubicacion'  => 'required|integer|exists:ubicaciones,id_ubicacion',
            'nombre_equipo' => 'required|string|max:120',
            'estado_actual' => 'required|string|max:50',
        ]);

        $equipo->update(
            $request->only(['id_categoria', 'id_ubicacion', 'nombre_equipo', 'estado_actual'])
        );

        return response()->json($equipo->fresh()->load(['categoria', 'ubicacion']));
    }

    // DELETE /api/equipos-activos/{id}
    public function destroy($id)
    {
        $equipo = EquipoActivo::whereNull('deleted_at')->findOrFail($id);
        $equipo->delete();

        return response()->json(['message' => 'Equipo eliminado correctamente.']);
    }
}
