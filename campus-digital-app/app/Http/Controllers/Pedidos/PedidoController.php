<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoHistorial;
use App\Models\ActividadBitacora;
use App\Support\MaquinaEstadosPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoController extends Controller
{
    // ── Vista: mis pedidos (usuario) ────────────────────────────
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->hasAnyRole(['administrador']);
        $esOperador = $usuario->hasAnyRole(['operador', 'administrador']);

        $query = Pedido::with(['usuario', 'operador', 'historial.usuario'])
            ->when(!$esAdmin, fn($q) => $q->where('usuario_id', $usuario->id))
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->when($request->modulo, fn($q, $v) => $q->where('modulo', $v))
            ->when($request->fecha_desde, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->buscar, fn($q, $v) => $q->where(function($sq) use ($v) {
                $sq->where('numero_folio', 'ilike', "%$v%")
                   ->orWhere('descripcion', 'ilike', "%$v%");
            }))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Pedidos/Index', [
            'pedidos'   => $query,
            'estados'   => Pedido::ESTADOS,
            'modulos'   => Pedido::MODULOS,
            'esAdmin'   => $esAdmin,
            'esOperador'=> $esOperador,
            'filtros'   => $request->only(['estado', 'modulo', 'fecha_desde', 'fecha_hasta', 'buscar']),
        ]);
    }

    // ── Vista: detalle de un pedido ─────────────────────────────
    public function show(Pedido $pedido)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->hasAnyRole(['administrador']);

        // Solo el dueño o admin puede ver
        if (!$esAdmin && $pedido->usuario_id !== $usuario->id) {
            return redirect()->route('sin-permiso');
        }

        $pedido->load(['usuario', 'operador', 'historial.usuario']);

        return Inertia::render('Pedidos/Show', [
            'pedido'  => $pedido,
            'estados' => Pedido::ESTADOS,
            'puedeCancelar' => in_array($pedido->estado, ['creado', 'aceptado']) &&
                               ($pedido->usuario_id === $usuario->id || $esAdmin),
        ]);
    }

    // ── Vista: panel del operador ────────────────────────────────
    public function operador(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['operador', 'administrador'])) {
            return redirect()->route('sin-permiso');
        }

        $pedidos = Pedido::with(['usuario', 'historial'])
            ->when($request->modulo, fn($q, $v) => $q->where('modulo', $v))
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->orderByRaw("CASE estado
                WHEN 'listo'      THEN 1
                WHEN 'en_proceso' THEN 2
                WHEN 'aceptado'   THEN 3
                WHEN 'creado'     THEN 4
                ELSE 5 END")
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Pedidos/Operador', [
            'pedidos' => $pedidos,
            'estados' => Pedido::ESTADOS,
            'modulos' => Pedido::MODULOS,
            'moduloActual' => $request->modulo ?? 'todos',
        ]);
    }

    // ── Acción: cambiar estado ───────────────────────────────────
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['operador', 'administrador'])) {
            return back()->withErrors(['error' => 'Sin permiso.']);
        }

        $request->validate([
            'estado' => ['required', 'in:' . implode(',', Pedido::ESTADOS)],
            'notas'  => ['nullable', 'string', 'max:500'],
        ]);

        // ✅ Validación de transición centralizada
        if (!MaquinaEstadosPedido::puedeTransicionar($pedido->estado, $request->estado)) {
            return back()->withErrors([
                'error' => "No se puede pasar de '{$pedido->estado}' a '{$request->estado}'."
            ]);
        }

        $estadoAnterior = $pedido->estado;

        // ✅ Todo envuelto en transacción: si algo falla, se hace rollback de todo
        DB::transaction(function () use ($pedido, $request, $usuario, $estadoAnterior) {
            $pedido->update([
                'estado'              => $request->estado,
                'operador_usuario_id' => $usuario->id,
                'notas'               => $request->notas ?? $pedido->notas,
            ]);

            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $request->estado,
                'usuario_id'      => $usuario->id,
                'notas'           => $request->notas ?? '',
            ]);

            ActividadBitacora::create([
                'usuario_id'    => $usuario->id,
                'accion'        => 'pedido.estado_cambio',
                'descripcion'   => "Pedido {$pedido->numero_folio}: $estadoAnterior → {$request->estado}",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);
        });

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    // ── Acción: cancelar (usuario dueño) ─────────────────────────
    public function cancelar(Request $request, Pedido $pedido)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->hasAnyRole(['administrador']);

        if (!$esAdmin && $pedido->usuario_id !== $usuario->id) {
            return back()->withErrors(['error' => 'Sin permiso.']);
        }

        // ✅ Usa la máquina de estados (cancelar es una transición válida desde creado/aceptado)
        if (!MaquinaEstadosPedido::puedeTransicionar($pedido->estado, 'cancelado')) {
            return back()->withErrors(['error' => 'Este pedido ya no puede cancelarse.']);
        }

        $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:300'],
        ]);

        $estadoAnterior = $pedido->estado;

        // ✅ Todo en transacción
        DB::transaction(function () use ($pedido, $request, $usuario, $estadoAnterior) {
            $pedido->update([
                'estado' => 'cancelado',
                'notas'  => $request->motivo,
            ]);

            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'cancelado',
                'usuario_id'      => $usuario->id,
                'notas'           => 'Cancelado por usuario: ' . $request->motivo,
            ]);

            ActividadBitacora::create([
                'usuario_id'    => $usuario->id,
                'accion'        => 'pedido.cancelado',
                'descripcion'   => "Pedido {$pedido->numero_folio} cancelado. Motivo: {$request->motivo}",
                'modulo'        => 'pedidos',
                'referencia_id' => $pedido->id,
                'ip'            => $request->ip(),
            ]);
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido cancelado.');
    }

    // ── API: polling de estado ────────────────────────────────────
    public function estadoJson(Pedido $pedido)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->hasAnyRole(['administrador']);

        if (!$esAdmin && $pedido->usuario_id !== $usuario->id) {
            return response()->json(['error' => 'Sin permiso'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'estado'     => $pedido->estado,
                'updated_at' => $pedido->updated_at?->toIso8601String(),
            ],
        ]);
    }
}