<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MantenimientoPreventivo;
use Illuminate\Http\Request;

class MantenimientoPreventivoApiController extends Controller
{
    // GET /api/mantenimientos-preventivos
    public function index()
    {
        return response()->json(
            MantenimientoPreventivo::with(['equipo.categoria', 'equipo.ubicacion'])
                ->whereNull('deleted_at')
                ->orderBy('proxima_fecha_programada', 'asc')
                ->get()
        );
    }

    // GET /api/mantenimientos-preventivos/{id}
    public function show($id)
    {
        $preventivo = MantenimientoPreventivo::with(['equipo.categoria', 'equipo.ubicacion'])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return response()->json($preventivo);
    }

    // POST /api/mantenimientos-preventivos
    public function store(Request $request)
    {
        $request->validate([
            'id_equipo'               => 'required|integer|exists:equipos_activos,id_equipo',
            'proxima_fecha_programada' => 'required|date',
        ]);

        $preventivo = MantenimientoPreventivo::create(
            $request->only(['id_equipo', 'proxima_fecha_programada'])
        );

        return response()->json($preventivo->load(['equipo.categoria', 'equipo.ubicacion']), 201);
    }

    // PUT /api/mantenimientos-preventivos/{id}
    public function update(Request $request, $id)
    {
        $preventivo = MantenimientoPreventivo::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_equipo'               => 'required|integer|exists:equipos_activos,id_equipo',
            'proxima_fecha_programada' => 'required|date',
        ]);

        $preventivo->update(
            $request->only(['id_equipo', 'proxima_fecha_programada'])
        );

        return response()->json($preventivo->fresh()->load(['equipo.categoria', 'equipo.ubicacion']));
    }

    // DELETE /api/mantenimientos-preventivos/{id}
    public function destroy($id)
    {
        $preventivo = MantenimientoPreventivo::whereNull('deleted_at')->findOrFail($id);
        $preventivo->delete();

        return response()->json(['message' => 'Mantenimiento preventivo eliminado correctamente.']);
    }
}
