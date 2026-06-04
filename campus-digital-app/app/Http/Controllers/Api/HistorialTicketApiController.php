<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HistorialTicketResource;
use App\Models\HistorialTicket;
use Illuminate\Http\Request;

class HistorialTicketApiController extends Controller
{
    // GET /api/historial-tickets
    public function index()
    {
        return HistorialTicketResource::collection(
            HistorialTicket::with(['ticket', 'usuario'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // GET /api/historial-tickets/{id}
    public function show($id)
    {
        return new HistorialTicketResource(
            HistorialTicket::with(['ticket', 'usuario'])->whereNull('deleted_at')->findOrFail($id)
        );
    }

    // POST /api/historial-tickets
    public function store(Request $request)
    {
        $request->validate([
            'id_ticket'    => 'required|integer|exists:tickets,id_ticket',
            'id_usuario'   => 'required|integer|exists:usuario,id',
            'estado_nuevo' => 'required|string|max:255',
        ]);

        $registro = HistorialTicket::create(
            $request->only(['id_ticket', 'id_usuario', 'estado_nuevo'])
        );

        return (new HistorialTicketResource($registro->load(['ticket', 'usuario'])))->response()->setStatusCode(201);
    }

    // PUT /api/historial-tickets/{id}
    public function update(Request $request, $id)
    {
        $registro = HistorialTicket::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_ticket'    => 'required|integer|exists:tickets,id_ticket',
            'id_usuario'   => 'required|integer|exists:usuario,id',
            'estado_nuevo' => 'required|string|max:255',
        ]);

        $registro->update(
            $request->only(['id_ticket', 'id_usuario', 'estado_nuevo'])
        );

        return new HistorialTicketResource($registro->fresh()->load(['ticket', 'usuario']));
    }

    // DELETE /api/historial-tickets/{id}
    public function destroy($id)
    {
        $registro = HistorialTicket::whereNull('deleted_at')->findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Registro de historial eliminado correctamente.']);
    }
}
