<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialTicket;
use Illuminate\Http\Request;

class HistorialTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = HistorialTicket::with(['ticket', 'usuario']);

        if ($request->filled('ticket')) {
            $query->where('id_ticket', $request->ticket);
        }

        if ($request->filled('usuario')) {
            $query->where('id_usuario', $request->usuario);
        }

        $historial = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/HistorialTickets/Index', [...])
        return response()->json($historial);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_ticket'   => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_usuario'  => ['required', 'integer', 'exists:usuario,id'],
            'estado_nuevo'=> ['required', 'string', 'max:255'],
        ]);

        $registro = HistorialTicket::create($validated);

        // TODO: return redirect()->route('admin.historial-tickets.index')->with('success', ...)
        return response()->json($registro->load(['ticket', 'usuario']), 201);
    }

    public function show(HistorialTicket $historialTicket)
    {
        // TODO: return Inertia::render('Admin/HistorialTickets/Show', [...])
        return response()->json($historialTicket->load(['ticket', 'usuario']));
    }

    public function update(Request $request, HistorialTicket $historialTicket)
    {
        $validated = $request->validate([
            'id_ticket'   => ['required', 'integer', 'exists:tickets,id_ticket'],
            'id_usuario'  => ['required', 'integer', 'exists:usuario,id'],
            'estado_nuevo'=> ['required', 'string', 'max:255'],
        ]);

        $historialTicket->update($validated);

        // TODO: return redirect()->route('admin.historial-tickets.index')->with('success', ...)
        return response()->json($historialTicket->fresh()->load(['ticket', 'usuario']));
    }

    public function destroy(HistorialTicket $historialTicket)
    {
        $historialTicket->delete();

        // TODO: return redirect()->route('admin.historial-tickets.index')->with('success', ...)
        return response()->json(['message' => 'Registro de historial eliminado correctamente.']);
    }
}
