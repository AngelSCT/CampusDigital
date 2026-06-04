<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GastoTicketResource;
use App\Models\GastoTicket;
use Illuminate\Http\Request;

class GastoTicketApiController extends Controller
{
    // GET /api/gastos-ticket
    public function index()
    {
        return GastoTicketResource::collection(
            GastoTicket::with(['ticket', 'insumo'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // GET /api/gastos-ticket/{id}
    public function show($id)
    {
        return new GastoTicketResource(
            GastoTicket::with(['ticket', 'insumo'])->whereNull('deleted_at')->findOrFail($id)
        );
    }

    // POST /api/gastos-ticket
    public function store(Request $request)
    {
        $request->validate([
            'id_ticket' => 'required|integer|exists:tickets,id_ticket',
            'id_insumo' => 'required|integer|exists:insumos,id_insumo',
            'cantidad'  => 'required|integer|min:1',
        ]);

        $gasto = GastoTicket::create(
            $request->only(['id_ticket', 'id_insumo', 'cantidad'])
        );

        // Recalcular el costo total del ticket
        if ($gasto->ticket) {
            $gasto->ticket->calcularCostoTotal();
        }

        return (new GastoTicketResource($gasto->load(['ticket', 'insumo'])))->response()->setStatusCode(201);
    }

    // PUT /api/gastos-ticket/{id}
    public function update(Request $request, $id)
    {
        $gasto = GastoTicket::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'id_ticket' => 'required|integer|exists:tickets,id_ticket',
            'id_insumo' => 'required|integer|exists:insumos,id_insumo',
            'cantidad'  => 'required|integer|min:1',
        ]);

        $gasto->update(
            $request->only(['id_ticket', 'id_insumo', 'cantidad'])
        );

        // Recalcular el costo total del ticket
        if ($gasto->ticket) {
            $gasto->ticket->calcularCostoTotal();
        }

        return new GastoTicketResource($gasto->fresh()->load(['ticket', 'insumo']));
    }

    // DELETE /api/gastos-ticket/{id}
    public function destroy($id)
    {
        $gasto = GastoTicket::whereNull('deleted_at')->findOrFail($id);
        $ticket = $gasto->ticket;
        $gasto->delete();

        // Recalcular el costo total del ticket
        if ($ticket) {
            $ticket->calcularCostoTotal();
        }

        return response()->json(['message' => 'Gasto de ticket eliminado correctamente.']);
    }
}
