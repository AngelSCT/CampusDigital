<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['usuarioSolicitante', 'categoria.area', 'equipo']);

        if ($request->filled('search')) {
            $query->whereHas('usuarioSolicitante', function ($q) use ($request) {
                $q->where('nombre', 'ilike', "%{$request->search}%")
                  ->orWhere('apellido', 'ilike', "%{$request->search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_creacion', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_creacion', '<=', $request->hasta);
        }

        $tickets = $query->orderBy('fecha_creacion', 'desc')->paginate(10)->withQueryString();

        // TODO: return Inertia::render('Admin/Tickets/Index', [...])
        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario_solicitante' => ['required', 'integer', 'exists:usuario,id'],
            'id_categoria'           => ['required', 'integer', 'exists:categorias_ticket,id_categoria'],
            'id_equipo'              => ['nullable', 'integer', 'exists:equipos_activos,id_equipo'],
            'estado'                 => ['required', 'string', 'max:50'],
            'prioridad'              => ['required', 'string', 'max:30'],
            'fecha_creacion'         => ['nullable', 'date'],
        ]);

        $ticket = Ticket::create($validated);

        // TODO: return redirect()->route('admin.tickets.index')->with('success', ...)
        return response()->json($ticket->load(['usuarioSolicitante', 'categoria.area', 'equipo']), 201);
    }

    public function show(Ticket $ticket)
    {
        // TODO: return Inertia::render('Admin/Tickets/Show', [...])
        return response()->json($ticket->load(['usuarioSolicitante', 'categoria.area', 'equipo']));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'id_usuario_solicitante' => ['required', 'integer', 'exists:usuario,id'],
            'id_categoria'           => ['required', 'integer', 'exists:categorias_ticket,id_categoria'],
            'id_equipo'              => ['nullable', 'integer', 'exists:equipos_activos,id_equipo'],
            'estado'                 => ['required', 'string', 'max:50'],
            'prioridad'              => ['required', 'string', 'max:30'],
            'fecha_creacion'         => ['nullable', 'date'],
        ]);

        $ticket->update($validated);

        // TODO: return redirect()->route('admin.tickets.index')->with('success', ...)
        return response()->json($ticket->fresh()->load(['usuarioSolicitante', 'categoria.area', 'equipo']));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        // TODO: return redirect()->route('admin.tickets.index')->with('success', ...)
        return response()->json(['message' => 'Ticket eliminado correctamente.']);
    }
}
