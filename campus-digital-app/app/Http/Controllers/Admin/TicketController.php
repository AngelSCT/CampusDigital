<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\CategoriaTicket;
use App\Models\EquipoActivo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        $categorias = CategoriaTicket::with('area')->orderBy('nombre_categoria')->get(['id_categoria', 'nombre_categoria', 'id_area']);
        $equipos    = EquipoActivo::orderBy('nombre_equipo')->get(['id_equipo', 'nombre_equipo']);
        $usuarios   = Usuario::orderBy('nombre')->get(['id', 'nombre', 'apellido']);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets'    => $tickets,
            'categorias' => $categorias,
            'equipos'    => $equipos,
            'usuarios'   => $usuarios,
            'filters'    => $request->only(['search', 'estado', 'prioridad', 'categoria', 'desde', 'hasta']),
        ]);
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

        Ticket::create($validated);

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket creado correctamente.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['usuarioSolicitante', 'categoria.area', 'equipo']);

        $categorias = CategoriaTicket::orderBy('nombre_categoria')->get(['id_categoria', 'nombre_categoria']);
        $equipos    = EquipoActivo::orderBy('nombre_equipo')->get(['id_equipo', 'nombre_equipo']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket'     => $ticket,
            'categorias' => $categorias,
            'equipos'    => $equipos,
        ]);
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

        return redirect()->route('admin.tickets.show', $ticket->id_ticket)->with('success', 'Ticket actualizado correctamente.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket eliminado correctamente.');
    }
}
