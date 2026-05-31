<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GastoTicket;
use Illuminate\Http\Request;

class GastoTicketApiController extends Controller
{
    // GET /api/gastos-ticket
    public function index()
    {
        return response()->json(
            GastoTicket::with(['ticket', 'insumo'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // GET /api/gastos-ticket/{id}
    public function show($id)
    {
        $gasto = GastoTicket::with(['ticket', 'insumo'])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return response()->json($gasto);
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

        return response()->json($gasto->load(['ticket', 'insumo']), 201);
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

        return response()->json($gasto->fresh()->load(['ticket', 'insumo']));
    }

    // DELETE /api/gastos-ticket/{id}
    public function destroy($id)
    {
        $gasto = GastoTicket::whereNull('deleted_at')->findOrFail($id);
        $gasto->delete();

        return response()->json(['message' => 'Gasto de ticket eliminado correctamente.']);
    }
}
