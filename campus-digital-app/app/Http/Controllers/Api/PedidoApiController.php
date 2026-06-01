<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PedidoApiController extends Controller
{
    // GET /api/pedidos
    public function index(Request $request)
    {
        $query = Pedido::with(['usuario', 'operador', 'tarjetaLectura', 'saldoMovimiento'])
            ->whereNull('deleted_at');

        if ($request->filled('usuario_id'))  $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('estado')) {
            $estados = explode(',', $request->estado);
            $query->whereIn('estado', $estados);
        }
        if ($request->filled('modulo'))      $query->where('modulo', $request->modulo);
        if ($request->filled('operador_id')) $query->where('operador_usuario_id', $request->operador_id);
        if ($request->filled('folio'))       $query->where('numero_folio', 'ilike', '%'.$request->folio.'%');
        if ($request->filled('desde'))       $query->where('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))       $query->where('created_at', '<=', $request->hasta);

        return response()->json($query->orderByDesc('created_at')->paginate($request->get('per_page', 15)));
    }

    // GET /api/pedidos/{id}
    public function show($id)
    {
        return response()->json(
            Pedido::with(['usuario', 'operador', 'tarjetaLectura', 'saldoMovimiento'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // POST /api/pedidos  ← crear pedido nuevo
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'  => 'required|exists:usuario,id',
            'modulo'      => 'required|string|max:50',
            'total'       => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'notas'       => 'nullable|string',
        ]);

        $pedido = Pedido::create([
            'usuario_id'   => $request->usuario_id,
            'numero_folio' => 'F-' . strtoupper(Str::random(8)),
            'estado'       => 'creado',
            'modulo'       => $request->modulo,
            'total'        => $request->total,
            'descripcion'  => $request->descripcion ?? '',
            'notas'        => $request->notas ?? '',
            'meta_json'    => $request->meta_json ?? [],
        ]);

        return response()->json($pedido->load('usuario'), 201);
    }

    // PUT /api/pedidos/{id}  ← actualizar datos generales
    public function update(Request $request, $id)
    {
        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'notas'       => 'nullable|string',
            'descripcion' => 'nullable|string',
            'total'       => 'sometimes|numeric|min:0',
        ]);

        $pedido->update($request->only(['notas', 'descripcion', 'total', 'meta_json']));

        return response()->json($pedido->fresh());
    }

    // POST /api/pedidos/{id}/estado  ← cambiar estado del pedido
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:creado,aceptado,en_proceso,listo,entregado,cancelado',
        ]);

        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);
        $pedido->update([
            'estado'              => $request->estado,
            'operador_usuario_id' => $request->operador_usuario_id ?? $pedido->operador_usuario_id,
        ]);

        return response()->json(['message' => "Pedido actualizado a: {$request->estado}.", 'pedido' => $pedido->fresh()]);
    }

    // POST /api/pedidos/{id}/confirmar-tarjeta  ← confirmar entrega con tarjeta RFID
    public function confirmarConTarjeta(Request $request, $id)
    {
        $request->validate([
            'tarjeta_lectura_id' => 'required|exists:tarjeta_lectura,id',
        ]);

        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);
        $pedido->update([
            'confirmado_con_tarjeta' => true,
            'confirmado_at'          => now(),
            'tarjeta_lectura_id'     => $request->tarjeta_lectura_id,
            'estado'                 => 'entregado',
        ]);

        return response()->json(['message' => 'Entrega confirmada con tarjeta.', 'pedido' => $pedido->fresh()]);
    }

    // DELETE /api/pedidos/{id}
    public function destroy($id)
    {
        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);
        $pedido->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Pedido eliminado.']);
    }
}