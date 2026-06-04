<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\CategoriaTicket;
use App\Models\EquipoActivo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Area;
use App\Services\TicketCartClient;
use App\Services\TicketApiClient;
use App\Models\HistorialTicket;

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
        $areas = Area::orderBy('name_area')->get(['id_area', 'name_area']);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets'    => $tickets,
            'categorias' => $categorias,
            'equipos'    => $equipos,
            'usuarios'   => $usuarios,
            'areas'      => $areas,
            'filters'    => $request->only(['search', 'estado', 'prioridad', 'categoria', 'desde', 'hasta']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario_solicitante' => ['nullable', 'integer', 'exists:usuario,id'],
            'id_categoria'           => ['nullable', 'integer', 'exists:categorias_ticket,id_categoria'],
            'id_equipo'              => ['nullable', 'integer', 'exists:equipos_activos,id_equipo'],
            'estado'                 => ['nullable', 'string', 'max:50'],
            'prioridad'              => ['nullable', 'string', 'max:30'],
            'fecha_creacion'         => ['nullable', 'date'],
        ]);

        $validated['id_usuario_solicitante'] = $validated['id_usuario_solicitante'] ?? Usuario::orderBy('id')->value('id');
        $validated['id_categoria'] = $validated['id_categoria'] ?? CategoriaTicket::orderBy('id_categoria')->value('id_categoria');
        $validated['estado'] = $validated['estado'] ?? 'Abierto';
        $validated['prioridad'] = $validated['prioridad'] ?? 'Media';

        if (empty($validated['fecha_creacion'])) {
            unset($validated['fecha_creacion']);
        }

        Ticket::create($validated);

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket creado correctamente.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['usuarioSolicitante', 'categoria.area', 'equipo', 'gastos.insumo']);

        $categorias = CategoriaTicket::orderBy('nombre_categoria')->get(['id_categoria', 'nombre_categoria']);
        $equipos    = EquipoActivo::orderBy('nombre_equipo')->get(['id_equipo', 'nombre_equipo']);
        $insumos    = \App\Models\Insumo::orderBy('nombre_insumo')->get(['id_insumo', 'nombre_insumo', 'precio_unitario']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket'     => $ticket,
            'categorias' => $categorias,
            'equipos'    => $equipos,
            'insumos'    => $insumos,
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

    public function pdf(Ticket $ticket)
    {
        $ticket->load(['usuarioSolicitante', 'categoria.area', 'equipo']);

        $user = auth()->user();
        $generadoPor = $user ? "{$user->nombre} {$user->apellido}" : 'Sistema';

        $pdf = Pdf::loadView('pdf.ticket', [
            'ticket'      => $ticket,
            'fecha'       => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm'),
            'generadoPor' => $generadoPor,
        ]);

        return $pdf->download("ticket-{$ticket->id_ticket}.pdf");
    }

    public function generarCobro(Ticket $ticket, TicketCartClient $cartClient, TicketApiClient $apiClient)
    {
        $ticket->load(['usuarioSolicitante', 'gastos.insumo']);

        if (!$ticket->usuarioSolicitante) {
            return redirect()->back()->withErrors('El ticket no tiene un solicitante asignado.');
        }

        $costoTotal = $ticket->calcularCostoTotal();

        if ($costoTotal <= 0) {
            return redirect()->back()->withErrors('El ticket no tiene gastos o insumos con costo.');
        }

        // 1. Crear carrito
        $cartResult = $cartClient->crearCarrito((string) $ticket->usuarioSolicitante->id, $ticket->id_ticket);

        if (!$cartResult || !isset($cartResult['carrito_uuid'])) {
            return redirect()->back()->withErrors('Error al comunicarse con el módulo de Carrito (Crear).');
        }

        $uuid = $cartResult['carrito_uuid'];

        // 2. Agregar el ítem
        $itemResult = $cartClient->agregarItemMantenimiento(
            $uuid,
            $ticket->id_ticket,
            "Mantenimiento - Ticket #{$ticket->id_ticket}",
            $costoTotal
        );

        if (!$itemResult) {
            return redirect()->back()->withErrors('Error al comunicarse con el módulo de Carrito (Agregar Ítem).');
        }

        // 3. Actualizar el ticket
        $ticket->update([
            'carrito_uuid' => $uuid,
            'estado_pago'  => Ticket::PAGO_PENDIENTE,
        ]);

        HistorialTicket::create([
            'id_ticket'    => $ticket->id_ticket,
            'id_usuario'   => auth()->id() ?? $ticket->id_usuario_solicitante,
            'estado_nuevo' => 'Generó cobro en carrito: ' . $uuid,
        ]);

        return redirect()->route('admin.tickets.show', $ticket->id_ticket)->with('success', 'Cobro generado en el carrito exitosamente.');
    }

    public function confirmarPago(Ticket $ticket, Request $request)
    {
        // Esto normalmente se llamaría vía API, pero si el admin lo fuerza manualmente:
        $ticket->update([
            'estado_pago' => Ticket::PAGO_PAGADO,
            'fecha_pago'  => now(),
        ]);

        HistorialTicket::create([
            'id_ticket'    => $ticket->id_ticket,
            'id_usuario'   => auth()->id(),
            'estado_nuevo' => 'Pago confirmado manualmente',
        ]);

        return redirect()->back()->with('success', 'Pago confirmado.');
    }
}
