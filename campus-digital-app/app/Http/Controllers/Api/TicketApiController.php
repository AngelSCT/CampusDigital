<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Services\TicketCartClient;
use App\Services\TicketApiClient;
use App\Models\HistorialTicket;

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

    // POST /api/tickets/{id}/generar-cobro
    public function generarCobro($id, TicketCartClient $cartClient)
    {
        $ticket = Ticket::with(['usuarioSolicitante', 'gastos.insumo'])->whereNull('deleted_at')->findOrFail($id);

        if (!$ticket->usuarioSolicitante) {
            return response()->json(['message' => 'El ticket no tiene un solicitante asignado.'], 422);
        }

        $costoTotal = $ticket->calcularCostoTotal();
        if ($costoTotal <= 0) {
            return response()->json(['message' => 'El ticket no tiene gastos o insumos con costo.'], 422);
        }

        $cartResult = $cartClient->crearCarrito((string) $ticket->usuarioSolicitante->id, $ticket->id_ticket);
        if (!$cartResult || !isset($cartResult['carrito_uuid'])) {
            return response()->json(['message' => 'Error al comunicarse con el módulo de Carrito.'], 502);
        }

        $uuid = $cartResult['carrito_uuid'];
        $itemResult = $cartClient->agregarItemMantenimiento(
            $uuid,
            $ticket->id_ticket,
            "Mantenimiento - Ticket #{$ticket->id_ticket}",
            $costoTotal
        );

        if (!$itemResult) {
            return response()->json(['message' => 'Error al agregar el ítem al carrito.'], 502);
        }

        $ticket->update([
            'carrito_uuid' => $uuid,
            'estado_pago'  => Ticket::PAGO_PENDIENTE,
        ]);

        return response()->json([
            'message' => 'Cobro generado',
            'carrito_uuid' => $uuid,
        ]);
    }

    // POST /api/tickets/{id}/confirmar-pago
    public function confirmarPago($id, Request $request, TicketApiClient $apiClient)
    {
        $ticket = Ticket::whereNull('deleted_at')->findOrFail($id);

        if ($ticket->estado_pago === Ticket::PAGO_PAGADO) {
            return response()->json(['message' => 'El ticket ya estaba pagado.']);
        }

        $ticket->update([
            'estado_pago' => Ticket::PAGO_PAGADO,
            'fecha_pago'  => now(),
        ]);

        // Si se requiere interactuar con el api client aquí para asentar un movimiento adicional
        // pero el carrito debería descontar el saldo por su cuenta.

        return response()->json(['message' => 'Pago confirmado.']);
    }
}
