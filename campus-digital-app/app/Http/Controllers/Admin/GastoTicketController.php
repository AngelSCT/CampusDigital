<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GastoTicket;
use Illuminate\Http\Request;

class GastoTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = GastoTicket::with(['ticket', 'insumo']);

        if ($request->filled('ticket')) {
            $query->where('id_ticket', $request->ticket);
        }

        if ($request->filled('insumo')) {
            $query->where('id_insumo', $request->insumo);
        }

        $gastos = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/GastosTicket/Index', [...])
        return response()->json($gastos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_ticket' => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_insumo' => ['required', 'integer', 'exists:insumos,id_insumo'],
            'cantidad'  => ['required', 'integer', 'min:1'],
        ]);

        $gasto = GastoTicket::create($validated);

        // Recalcular costo total del ticket
        $ticket = $gasto->ticket;
        if ($ticket) {
            $ticket->calcularCostoTotal();
        }

        return redirect()->back()->with('success', 'Insumo agregado correctamente al ticket.');
    }

    public function show(GastoTicket $gastoTicket)
    {
        return response()->json($gastoTicket->load(['ticket', 'insumo']));
    }

    public function update(Request $request, GastoTicket $gastoTicket)
    {
        $validated = $request->validate([
            'id_ticket' => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_insumo' => ['required', 'integer', 'exists:insumos,id_insumo'],
            'cantidad'  => ['required', 'integer', 'min:1'],
        ]);

        $gastoTicket->update($validated);

        // Recalcular costo total del ticket
        $ticket = $gastoTicket->ticket;
        if ($ticket) {
            $ticket->calcularCostoTotal();
        }

        return redirect()->back()->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(GastoTicket $gastoTicket)
    {
        $ticket = $gastoTicket->ticket;
        $gastoTicket->delete();

        if ($ticket) {
            $ticket->calcularCostoTotal();
        }

        return redirect()->back()->with('success', 'Insumo eliminado del ticket.');
    }
}
