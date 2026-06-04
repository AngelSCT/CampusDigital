<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RepartidorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Pedidos asignados al repartidor que no han sido entregados o cancelados
        $pedidosActivos = Pedido::where('repartidor_id', $user->id)
            ->whereIn('estado', ['aceptado', 'en_proceso', 'listo'])
            ->with(['usuario', 'tienda'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Historial de entregas de hoy
        $entregasHoy = Pedido::where('repartidor_id', $user->id)
            ->where('estado', 'entregado')
            ->whereDate('updated_at', today())
            ->count();

        // Estadísticas generales para el repartidor
        $stats = [
            'pendientes' => $pedidosActivos->count(),
            'entregados_hoy' => $entregasHoy,
            'total_historico' => Pedido::where('repartidor_id', $user->id)->where('estado', 'entregado')->count()
        ];

        return Inertia::render('Dashboard/Repartidor', [
            'pedidosActivos' => $pedidosActivos,
            'stats' => $stats
        ]);
    }

    public function actualizarEstado(Request $request, Pedido $pedido)
    {
        // Validar que el pedido pertenezca al repartidor
        if ($pedido->repartidor_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para gestionar este pedido.');
        }

        $validated = $request->validate([
            'estado' => 'required|in:en_proceso,entregado,cancelado'
        ]);

        $pedido->update([
            'estado' => $validated['estado']
        ]);

        $mensaje = $validated['estado'] === 'entregado' ? '¡Pedido entregado con éxito!' : 'Estado del pedido actualizado.';

        return back()->with('success', $mensaje);
    }
}
