<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Models\PedidoHistorial;
use App\Models\ActividadBitacora;
use App\Support\MaquinaEstadosPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        return PedidoResource::collection($query->orderByDesc('created_at')->paginate($request->get('per_page', 15)));
    }

    // GET /api/pedidos/{id}
    public function show($id)
    {
        return new PedidoResource(
            Pedido::with(['usuario', 'operador', 'tarjetaLectura', 'saldoMovimiento'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // POST /api/pedidos  ← crear pedido nuevo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id'  => 'required|exists:usuario,id',
            'modulo'      => ['required', 'string', 'in:' . implode(',', Pedido::MODULOS)],
            'total'       => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'notas'       => 'nullable|string',
            'meta_json'   => 'nullable|array',
        ]);

        $pedido = DB::transaction(function () use ($validated, $request) {
            $pedido = Pedido::create([
                'usuario_id'   => $validated['usuario_id'],
                'numero_folio' => Pedido::generarFolio(),  // ✅ Folio unificado y seguro
                'estado'       => 'creado',
                'modulo'       => $validated['modulo'],
                'total'        => $validated['total'],
                'descripcion'  => $validated['descripcion'] ?? '',
                'notas'        => $validated['notas'] ?? '',
                'meta_json'    => $validated['meta_json'] ?? [],
            ]);

            // ✅ Registrar en historial que se creó el pedido
            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => null,
                'estado_nuevo'    => 'creado',
                'usuario_id'      => Auth::id() ?? $validated['usuario_id'],
                'notas'           => 'Pedido creado vía API',
            ]);

            // ✅ Registrar en bitácora
            ActividadBitacora::create([
                'usuario_id'    => Auth::id() ?? $validated['usuario_id'],
                'accion'        => 'pedido.creado_api',
                'descripcion'   => "Pedido {$pedido->numero_folio} creado vía API",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);

            return $pedido;
        });

        return (new PedidoResource($pedido->load('usuario')))->response()->setStatusCode(201);
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

        return new PedidoResource($pedido->fresh());
    }

    // POST /api/pedidos/{id}/estado  ← cambiar estado del pedido
    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado'              => ['required', 'in:' . implode(',', Pedido::ESTADOS)],
            'notas'               => ['nullable', 'string', 'max:500'],
            'operador_usuario_id' => ['nullable', 'integer', 'exists:usuario,id'],
        ]);

        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);

        // ✅ Validación de transición centralizada
        if (!MaquinaEstadosPedido::puedeTransicionar($pedido->estado, $validated['estado'])) {
            return response()->json([
                'error'           => 'Transición de estado no permitida.',
                'estado_actual'   => $pedido->estado,
                'estado_recibido' => $validated['estado'],
                'siguientes_permitidos' => MaquinaEstadosPedido::siguientesEstados($pedido->estado),
            ], 422);
        }

        $estadoAnterior = $pedido->estado;

        // Identifica quién hace el cambio vía API
        // Si hay usuario autenticado (por api.key) lo usa; si no, usa el operador_usuario_id del request
        $usuarioId = Auth::id() ?? $validated['operador_usuario_id'] ?? $pedido->operador_usuario_id;

        // ✅ Todo envuelto en transacción
        DB::transaction(function () use ($pedido, $validated, $estadoAnterior, $usuarioId, $request) {
            $pedido->update([
                'estado'              => $validated['estado'],
                'operador_usuario_id' => $validated['operador_usuario_id'] ?? $pedido->operador_usuario_id,
                'notas'               => $validated['notas'] ?? $pedido->notas,
            ]);

            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $validated['estado'],
                'usuario_id'      => $usuarioId,
                'notas'           => $validated['notas'] ?? '',
            ]);

            ActividadBitacora::create([
                'usuario_id'    => $usuarioId,
                'accion'        => 'pedido.estado_cambio_api',
                'descripcion'   => "Pedido {$pedido->numero_folio}: $estadoAnterior → {$validated['estado']} (vía API)",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);
        });

        return response()->json([
            'message' => "Pedido actualizado a: {$validated['estado']}.",
            'pedido'  => $pedido->fresh()->load(['usuario', 'operador']),
        ]);
    }

    // POST /api/pedidos/{id}/confirmar-tarjeta  ← confirmar entrega con tarjeta RFID
    public function confirmarConTarjeta(Request $request, $id)
    {
        $validated = $request->validate([
            'tarjeta_lectura_id' => 'required|exists:tarjeta_lectura,id',
        ]);

        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);

        // ✅ Solo se puede confirmar con tarjeta si el pedido está listo para entregarse
        if (!MaquinaEstadosPedido::puedeTransicionar($pedido->estado, 'entregado')) {
            return response()->json([
                'error'         => 'El pedido no está en un estado que permita ser entregado.',
                'estado_actual' => $pedido->estado,
            ], 422);
        }

        $estadoAnterior = $pedido->estado;
        $usuarioId = Auth::id() ?? $pedido->operador_usuario_id;

        DB::transaction(function () use ($pedido, $validated, $estadoAnterior, $usuarioId, $request) {
            $pedido->update([
                'confirmado_con_tarjeta' => true,
                'confirmado_at'          => now(),
                'tarjeta_lectura_id'     => $validated['tarjeta_lectura_id'],
                'estado'                 => 'entregado',
            ]);

            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'entregado',
                'usuario_id'      => $usuarioId,
                'notas'           => 'Entrega confirmada con tarjeta RFID (lectura #' . $validated['tarjeta_lectura_id'] . ')',
            ]);

            ActividadBitacora::create([
                'usuario_id'    => $usuarioId,
                'accion'        => 'pedido.confirmado_tarjeta',
                'descripcion'   => "Pedido {$pedido->numero_folio} confirmado con tarjeta RFID",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);
        });

        return response()->json([
            'message' => 'Entrega confirmada con tarjeta.',
            'pedido'  => $pedido->fresh()->load(['usuario', 'operador', 'tarjetaLectura']),
        ]);
    }

    // DELETE /api/pedidos/{id}
    public function destroy(Request $request, $id)
    {
        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);

        // ✅ Validar que el estado permita eliminación
        if (!MaquinaEstadosPedido::puedeEliminarse($pedido->estado)) {
            return response()->json([
                'error'                => 'No se puede eliminar un pedido en este estado.',
                'estado_actual'        => $pedido->estado,
                'estados_eliminables'  => MaquinaEstadosPedido::ESTADOS_ELIMINABLES,
                'hint'                 => 'Solo se pueden eliminar pedidos en estado "creado" o "cancelado". Si quieres anular un pedido en otro estado, cámbialo primero a "cancelado".',
            ], 409); // 409 Conflict — el estado del recurso no permite la operación
        }

        $usuarioId = Auth::id() ?? $pedido->operador_usuario_id;

        // ✅ Todo en transacción
        DB::transaction(function () use ($pedido, $usuarioId, $request) {
            // Soft delete usando la capa de Eloquent (aprovecha SoftDeletes del modelo)
            $pedido->delete();

            // Registrar en historial
            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $pedido->estado,
                'estado_nuevo'    => $pedido->estado, // no cambia el estado, solo se marca como eliminado
                'usuario_id'      => $usuarioId,
                'notas'           => 'Pedido eliminado (soft delete) vía API',
            ]);

            // Registrar en bitácora
            ActividadBitacora::create([
                'usuario_id'    => $usuarioId,
                'accion'        => 'pedido.eliminado_api',
                'descripcion'   => "Pedido {$pedido->numero_folio} eliminado (estado: {$pedido->estado})",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);
        });

        return response()->json([
            'message' => 'Pedido eliminado correctamente.',
            'pedido'  => [
                'id'           => $pedido->id,
                'numero_folio' => $pedido->numero_folio,
                'estado'       => $pedido->estado,
            ],
        ]);
    }
    // ──────────────────────────────────────────────────────────────
    // NUEVOS ENDPOINTS (Paso 1.6)
    // ──────────────────────────────────────────────────────────────

    /**
     * POST /api/pedidos/{id}/cancelar
     * Cancela un pedido con motivo obligatorio.
     * A diferencia de cambiarEstado, aquí el motivo es REQUERIDO.
     */
    public function cancelar(Request $request, $id)
    {
        $validated = $request->validate([
            'motivo'              => ['required', 'string', 'min:5', 'max:300'],
            'operador_usuario_id' => ['nullable', 'integer', 'exists:usuario,id'],
        ]);

        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);

        // Validar que sea una transición permitida
        if (!MaquinaEstadosPedido::puedeTransicionar($pedido->estado, 'cancelado')) {
            return response()->json([
                'error'         => 'Este pedido no puede ser cancelado.',
                'estado_actual' => $pedido->estado,
                'hint'          => 'Solo se pueden cancelar pedidos en estados creado, aceptado, en_proceso o listo.',
            ], 422);
        }

        $estadoAnterior = $pedido->estado;
        $usuarioId = Auth::id() 
            ?? $validated['operador_usuario_id'] 
            ?? $pedido->operador_usuario_id;

        DB::transaction(function () use ($pedido, $validated, $estadoAnterior, $usuarioId, $request) {
            $pedido->update([
                'estado' => 'cancelado',
                'notas'  => $validated['motivo'],
            ]);

            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'cancelado',
                'usuario_id'      => $usuarioId,
                'notas'           => 'Cancelado vía API. Motivo: ' . $validated['motivo'],
            ]);

            ActividadBitacora::create([
                'usuario_id'    => $usuarioId,
                'accion'        => 'pedido.cancelado_api',
                'descripcion'   => "Pedido {$pedido->numero_folio} cancelado (vía API). Motivo: {$validated['motivo']}",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);
        });

        return response()->json([
            'message' => 'Pedido cancelado correctamente.',
            'pedido'  => $pedido->fresh()->load(['usuario', 'operador']),
        ]);
    }

    /**
     * GET /api/pedidos/{id}/historial
     * Devuelve el historial completo de transiciones del pedido,
     * ordenado cronológicamente (más antiguo → más reciente).
     */
    public function historial($id)
    {
        $pedido = Pedido::whereNull('deleted_at')->findOrFail($id);

        $historial = $pedido->historial()
            ->with('usuario:id,nombre,apellido,email')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($h) {
                return [
                    'id'              => $h->id,
                    'estado_anterior' => $h->estado_anterior,
                    'estado_nuevo'    => $h->estado_nuevo,
                    'notas'           => $h->notas,
                    'usuario'         => $h->usuario ? [
                        'id'     => $h->usuario->id,
                        'nombre' => trim($h->usuario->nombre . ' ' . $h->usuario->apellido),
                        'email'  => $h->usuario->email,
                    ] : null,
                    'created_at'      => $h->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'pedido' => [
                'id'             => $pedido->id,
                'numero_folio'   => $pedido->numero_folio,
                'estado_actual'  => $pedido->estado,
            ],
            'historial' => $historial,
            'total'     => $historial->count(),
        ]);
    }

    /**
     * GET /api/pedidos/operador/cola
     * Devuelve la cola de pedidos activos ordenada por prioridad:
     * listo → en_proceso → aceptado → creado (más antiguos primero dentro de cada grupo).
     * 
     * Query params opcionales:
     * - modulo: filtra por módulo específico (cafeteria, copias, etc.)
     * - operador_id: filtra por operador asignado
     */
    public function colaOperador(Request $request)
    {
        $query = Pedido::with(['usuario:id,nombre,apellido,email', 'operador:id,nombre,apellido'])
            ->whereNull('deleted_at')
            ->whereNotIn('estado', ['entregado', 'cancelado']);

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('operador_id')) {
            $query->where('operador_usuario_id', $request->operador_id);
        }

        $pedidos = $query
            ->orderByRaw("CASE estado
                WHEN 'listo'      THEN 1
                WHEN 'en_proceso' THEN 2
                WHEN 'aceptado'   THEN 3
                WHEN 'creado'     THEN 4
                ELSE 5 END")
            ->orderBy('created_at', 'asc')
            ->get();

        // Resumen para el operador
        $resumen = [
            'listo'      => $pedidos->where('estado', 'listo')->count(),
            'en_proceso' => $pedidos->where('estado', 'en_proceso')->count(),
            'aceptado'   => $pedidos->where('estado', 'aceptado')->count(),
            'creado'     => $pedidos->where('estado', 'creado')->count(),
            'total'      => $pedidos->count(),
        ];

        return response()->json([
            'resumen' => $resumen,
            'cola'    => $pedidos,
            'filtros' => [
                'modulo'      => $request->modulo,
                'operador_id' => $request->operador_id,
            ],
        ]);
    }
}