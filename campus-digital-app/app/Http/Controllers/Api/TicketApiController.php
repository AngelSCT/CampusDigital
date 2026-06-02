<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketApiController extends Controller
{
    // GET /api/tickets
    public function index()
    {
        return TicketResource::collection(
            Ticket::with(['usuarioSolicitante', 'categoria.area', 'equipo'])
                ->whereNull('deleted_at')
                ->orderBy('fecha_creacion', 'desc')
                ->get()
        );
    }

    // GET /api/tickets/{id}
    public function show($id)
    {
        return new TicketResource(
            Ticket::with(['usuarioSolicitante', 'categoria.area', 'equipo'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // POST /api/tickets
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario_solicitante' => 'required|integer|exists:usuario,id',
            'id_categoria'           => 'required|integer|exists:categorias_ticket,id_categoria',
            'id_equipo'              => 'nullable|integer|exists:equipos_activos,id_equipo',
            'estado'                 => 'required|string|max:50',
            'prioridad'              => 'required|string|max:30',
            'fecha_creacion'         => 'nullable|date',
        ]);

        $ticket = Ticket::create(
            $request->only(['id_usuario_solicitante', 'id_categoria', 'id_equipo', 'estado', 'prioridad', 'fecha_creacion'])
        );

        return (new TicketResource($ticket->load(['usuarioSolicitante', 'categoria.area', 'equipo'])))->response()->setStatusCode(201);
    }

    // PUT /api/tickets/{id}
    public function update(Request $request, $id)
    {
        $ticket = Ticket::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_usuario_solicitante' => 'required|integer|exists:usuario,id',
            'id_categoria'           => 'required|integer|exists:categorias_ticket,id_categoria',
            'id_equipo'              => 'nullable|integer|exists:equipos_activos,id_equipo',
            'estado'                 => 'required|string|max:50',
            'prioridad'              => 'required|string|max:30',
            'fecha_creacion'         => 'nullable|date',
        ]);

        $ticket->update(
            $request->only(['id_usuario_solicitante', 'id_categoria', 'id_equipo', 'estado', 'prioridad', 'fecha_creacion'])
        );

        return new TicketResource($ticket->fresh()->load(['usuarioSolicitante', 'categoria.area', 'equipo']));
    }

    // DELETE /api/tickets/{id}
    public function destroy($id)
    {
        $ticket = Ticket::whereNull('deleted_at')->findOrFail($id);
        $ticket->delete();

        return response()->json(['message' => 'Ticket eliminado correctamente.']);
    }
}
