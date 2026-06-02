<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MantenimientoPreventivoResource;
use App\Models\MantenimientoPreventivo;
use Illuminate\Http\Request;

class MantenimientoPreventivoApiController extends Controller
{
    // GET /api/mantenimientos-preventivos
    public function index()
    {
        return MantenimientoPreventivoResource::collection(
            MantenimientoPreventivo::with(['equipo.categoria', 'equipo.ubicacion'])
                ->whereNull('deleted_at')
                ->orderBy('proxima_fecha_programada', 'asc')
                ->get()
        );
    }

    // GET /api/mantenimientos-preventivos/{id}
    public function show($id)
    {
        return new MantenimientoPreventivoResource(
            MantenimientoPreventivo::with(['equipo.categoria', 'equipo.ubicacion'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
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

        return (new MantenimientoPreventivoResource($preventivo->load(['equipo.categoria', 'equipo.ubicacion'])))->response()->setStatusCode(201);
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

        return new MantenimientoPreventivoResource($preventivo->fresh()->load(['equipo.categoria', 'equipo.ubicacion']));
    }

    // DELETE /api/mantenimientos-preventivos/{id}
    public function destroy($id)
    {
        $preventivo = MantenimientoPreventivo::whereNull('deleted_at')->findOrFail($id);
        $preventivo->delete();

        return response()->json(['message' => 'Mantenimiento preventivo eliminado correctamente.']);
    }
}
