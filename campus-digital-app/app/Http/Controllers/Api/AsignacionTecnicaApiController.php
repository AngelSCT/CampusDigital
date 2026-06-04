<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AsignacionTecnicaResource;
use App\Models\AsignacionTecnica;
use Illuminate\Http\Request;

class AsignacionTecnicaApiController extends Controller
{
    // GET /api/asignaciones-tecnicas
    public function index()
    {
        return AsignacionTecnicaResource::collection(
            AsignacionTecnica::with(['ticket', 'tecnico'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // GET /api/asignaciones-tecnicas/{id}
    public function show($id)
    {
        return new AsignacionTecnicaResource(
            AsignacionTecnica::with(['ticket', 'tecnico'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // POST /api/asignaciones-tecnicas
    public function store(Request $request)
    {
        $request->validate([
            'id_ticket'          => 'required|integer|exists:tickets,id_ticket',
            'id_usuario_tecnico' => 'required|integer|exists:usuario,id',
        ]);

        $asignacion = AsignacionTecnica::create(
            $request->only(['id_ticket', 'id_usuario_tecnico'])
        );

        return (new AsignacionTecnicaResource($asignacion->load(['ticket', 'tecnico'])))->response()->setStatusCode(201);
    }

    // PUT /api/asignaciones-tecnicas/{id}
    public function update(Request $request, $id)
    {
        $asignacion = AsignacionTecnica::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_ticket'          => 'required|integer|exists:tickets,id_ticket',
            'id_usuario_tecnico' => 'required|integer|exists:usuario,id',
        ]);

        $asignacion->update(
            $request->only(['id_ticket', 'id_usuario_tecnico'])
        );

        return new AsignacionTecnicaResource($asignacion->fresh()->load(['ticket', 'tecnico']));
    }

    // DELETE /api/asignaciones-tecnicas/{id}
    public function destroy($id)
    {
        $asignacion = AsignacionTecnica::whereNull('deleted_at')->findOrFail($id);
        $asignacion->delete();

        return response()->json(['message' => 'Asignación técnica eliminada correctamente.']);
    }
}
