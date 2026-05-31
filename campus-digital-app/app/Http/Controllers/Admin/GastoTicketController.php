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

        // TODO: return redirect()->route('admin.gastos-ticket.index')->with('success', ...)
        return response()->json($gasto->load(['ticket', 'insumo']), 201);
    }

    public function show(GastoTicket $gastoTicket)
    {
        // TODO: return Inertia::render('Admin/GastosTicket/Show', [...])
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

        // TODO: return redirect()->route('admin.gastos-ticket.index')->with('success', ...)
        return response()->json($gastoTicket->fresh()->load(['ticket', 'insumo']));
    }

    public function destroy(GastoTicket $gastoTicket)
    {
        $gastoTicket->delete();

        // TODO: return redirect()->route('admin.gastos-ticket.index')->with('success', ...)
        return response()->json(['message' => 'Gasto de ticket eliminado correctamente.']);
    }
}
