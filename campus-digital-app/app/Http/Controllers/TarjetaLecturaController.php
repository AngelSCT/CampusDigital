<?php

namespace App\Http\Controllers;

use App\Models\TarjetaUniversitaria;
use App\Models\TarjetaLectura;
use App\Models\SaldoMonedero;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TarjetaLecturaController extends Controller
{
    /* ─── Panel del lector (vista proveedor) ─────────────── */

    public function index()
    {
        $lecturasRecientes = TarjetaLectura::with([
                'tarjeta.usuario:id,nombre,apellido,email',
                'operador:id,nombre,apellido',
                'pedido:id,numero_folio',
            ])
            ->latest()
            ->take(30)
            ->get()
            ->map(fn($l) => [
                'id'             => $l->id,
                'uid_leido'      => $l->uid_leido,
                'modulo'         => $l->modulo,
                'tipo_lectura'   => $l->tipo_lectura,
                'exito'          => $l->exito,
                'detalle'        => $l->detalle,
                'created_at'     => $l->created_at,
                'usuario_nombre' => $l->tarjeta?->usuario
                    ? $l->tarjeta->usuario->nombre . ' ' . $l->tarjeta->usuario->apellido
                    : null,
                'folio_pedido'   => $l->pedido?->numero_folio,
            ]);

        return Inertia::render('Lector/Index', [
            'lecturasRecientes' => $lecturasRecientes,
            'modulos'           => TarjetaLectura::MODULOS,
            'tipos'             => TarjetaLectura::TIPOS,
            'scan_result'       => session('scan_result'),
        ]);
    }

    /* ─── Procesar lectura ───────────────────────────────── */

    public function leer(Request $request)
    {
        $request->validate([
            'uid'          => 'required|string|max:64',
            'modulo'       => 'required|string|in:' . implode(',', TarjetaLectura::MODULOS),
            'tipo_lectura' => 'required|string|in:' . implode(',', TarjetaLectura::TIPOS),
        ]);

        $uid         = strtoupper(trim($request->uid));
        $modulo      = $request->modulo;
        $tipo        = $request->tipo_lectura;
        $operadorId  = Auth::id();

        $tarjeta = TarjetaUniversitaria::where('uid', $uid)
            ->whereNull('deleted_at')
            ->with('usuario:id,nombre,apellido,email,foto_url')
            ->first();

        // ── Tarjeta no encontrada ────────────────────────────
        if (!$tarjeta) {
            TarjetaLectura::create([
                'tarjeta_id'          => null,
                'uid_leido'           => $uid,
                'modulo'              => $modulo,
                'tipo_lectura'        => $tipo,
                'exito'               => false,
                'detalle'             => 'Tarjeta no registrada en el sistema.',
                'ip'                  => $request->ip(),
                'user_agent'          => $request->userAgent() ?? '',
                'operador_usuario_id' => $operadorId,
                'meta_json'           => [],
            ]);

            return back()->with('scan_result', [
                'exito'   => false,
                'mensaje' => 'Tarjeta no registrada en el sistema.',
                'uid'     => $uid,
            ]);
        }

        // ── Tarjeta bloqueada ────────────────────────────────
        if ($tarjeta->estaBloqueada()) {
            TarjetaLectura::create([
                'tarjeta_id'          => $tarjeta->id,
                'uid_leido'           => $uid,
                'modulo'              => $modulo,
                'tipo_lectura'        => $tipo,
                'exito'               => false,
                'detalle'             => "Tarjeta {$tarjeta->estado}. Motivo: " . ($tarjeta->motivo_bloqueo ?? 'Sin motivo'),
                'ip'                  => $request->ip(),
                'user_agent'          => $request->userAgent() ?? '',
                'operador_usuario_id' => $operadorId,
                'meta_json'           => [],
            ]);

            return back()->with('scan_result', [
                'exito'   => false,
                'mensaje' => "Tarjeta {$tarjeta->estado}. " . ($tarjeta->motivo_bloqueo ? "Motivo: {$tarjeta->motivo_bloqueo}" : ''),
                'uid'     => $uid,
            ]);
        }

        $usuario = $tarjeta->usuario;

        if (!$usuario || $usuario->deleted_at || $usuario->bloqueado) {
            return back()->with('scan_result', [
                'exito'   => false,
                'mensaje' => 'Usuario no disponible.',
                'uid'     => $uid,
            ]);
        }

        // ── Despachar según tipo ─────────────────────────────
        return match ($tipo) {
            'consulta_saldo'       => $this->consultarSaldo($request, $tarjeta, $uid, $modulo, $operadorId),
            'confirmacion_entrega' => $this->confirmarEntrega($request, $tarjeta, $uid, $modulo, $operadorId),
            default                => $this->registrarAccesoOConsumo($request, $tarjeta, $uid, $modulo, $tipo, $operadorId),
        };
    }

    /* ─── Consulta rápida de saldo ───────────────────────── */

    private function consultarSaldo(Request $request, TarjetaUniversitaria $tarjeta, string $uid, string $modulo, int $operadorId)
    {
        $usuario  = $tarjeta->usuario;
        $monedero = SaldoMonedero::obtenerOCrear($usuario->id);

        TarjetaLectura::create([
            'tarjeta_id'          => $tarjeta->id,
            'uid_leido'           => $uid,
            'modulo'              => $modulo,
            'tipo_lectura'        => 'consulta_saldo',
            'exito'               => true,
            'detalle'             => 'Consulta de saldo. Disponible: $' . number_format($monedero->saldo_disponible, 2),
            'ip'                  => $request->ip(),
            'user_agent'          => $request->userAgent() ?? '',
            'operador_usuario_id' => $operadorId,
            'meta_json'           => [],
        ]);

        return back()->with('scan_result', [
            'exito'          => true,
            'tipo'           => 'consulta_saldo',
            'mensaje'        => 'Saldo consultado correctamente.',
            'uid'            => $uid,
            'usuario'        => [
                'nombre'     => $usuario->nombre . ' ' . $usuario->apellido,
                'email'      => $usuario->email,
                'foto_url'   => $usuario->foto_url,
            ],
            'saldo'          => number_format($monedero->saldo_disponible, 2),
            'saldo_retenido' => number_format($monedero->saldo_retenido, 2),
        ]);
    }

    /* ─── Muestra pedidos pendientes para confirmar ──────── */

    private function confirmarEntrega(Request $request, TarjetaUniversitaria $tarjeta, string $uid, string $modulo, int $operadorId)
    {
        $usuario = $tarjeta->usuario;

        $pedidosPendientes = Pedido::where('usuario_id', $usuario->id)
            ->where('modulo', $modulo)
            ->whereIn('estado', ['listo', 'aceptado', 'en_proceso'])
            ->where('confirmado_con_tarjeta', false)
            ->orderBy('created_at')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'folio'       => $p->numero_folio,
                'estado'      => $p->estado,
                'total'       => $p->total,
                'descripcion' => $p->descripcion,
                'created_at'  => $p->created_at,
            ]);

        TarjetaLectura::create([
            'tarjeta_id'          => $tarjeta->id,
            'uid_leido'           => $uid,
            'modulo'              => $modulo,
            'tipo_lectura'        => 'confirmacion_entrega',
            'exito'               => true,
            'detalle'             => 'Escaneo para confirmación. Pedidos pendientes: ' . count($pedidosPendientes),
            'ip'                  => $request->ip(),
            'user_agent'          => $request->userAgent() ?? '',
            'operador_usuario_id' => $operadorId,
            'meta_json'           => [],
        ]);

        return back()->with('scan_result', [
            'exito'              => true,
            'tipo'               => 'confirmacion_entrega',
            'mensaje'            => count($pedidosPendientes) > 0
                ? 'Tarjeta válida. ' . count($pedidosPendientes) . ' pedido(s) pendiente(s).'
                : 'Tarjeta válida. Sin pedidos pendientes en este módulo.',
            'uid'                => $uid,
            'tarjeta_id'         => $tarjeta->id,
            'usuario'            => [
                'nombre'         => $usuario->nombre . ' ' . $usuario->apellido,
                'email'          => $usuario->email,
                'foto_url'       => $usuario->foto_url,
            ],
            'pedidos_pendientes' => $pedidosPendientes,
        ]);
    }

    /* ─── Confirmar pedido específico con tarjeta ────────── */

    public function confirmarPedido(Request $request)
    {
        $data = $request->validate([
            'pedido_id'  => 'required|integer|exists:pedido,id',
            'tarjeta_id' => 'required|integer|exists:tarjeta_universitaria,id',
            'modulo'     => 'required|string',
            'cobrar'     => 'boolean',
        ]);

        $pedido  = Pedido::findOrFail($data['pedido_id']);
        $tarjeta = TarjetaUniversitaria::with('usuario')->findOrFail($data['tarjeta_id']);
        $usuario = $tarjeta->usuario;

        if ($pedido->usuario_id !== $usuario->id) {
            return back()->with('scan_result', [
                'exito'   => false,
                'mensaje' => 'Este pedido no pertenece al titular de la tarjeta.',
            ]);
        }

        if (!$pedido->puedeEntregarse()) {
            return back()->with('scan_result', [
                'exito'   => false,
                'mensaje' => 'El pedido no puede entregarse en su estado actual: ' . $pedido->estado,
            ]);
        }

        try {
            DB::transaction(function () use ($pedido, $tarjeta, $usuario, $data, $request) {
                $movimientoId = null;

                if (!empty($data['cobrar']) && $pedido->total > 0) {
                    $monedero   = SaldoMonedero::obtenerOCrear($usuario->id);
                    $movimiento = $monedero->cargar(
                        $pedido->total,
                        'Pago pedido ' . $pedido->numero_folio,
                        $pedido->modulo,
                        Auth::id()
                    );
                    $movimientoId = $movimiento->id;
                }

                $lectura = TarjetaLectura::create([
                    'tarjeta_id'          => $tarjeta->id,
                    'uid_leido'           => $tarjeta->uid,
                    'modulo'              => $pedido->modulo,
                    'tipo_lectura'        => 'confirmacion_entrega',
                    'exito'               => true,
                    'detalle'             => 'Entrega confirmada con tarjeta. Pedido: ' . $pedido->numero_folio,
                    'ip'                  => $request->ip(),
                    'user_agent'          => $request->userAgent() ?? '',
                    'operador_usuario_id' => Auth::id(),
                    'pedido_id'           => $pedido->id,
                    'meta_json'           => [],
                ]);

                $pedido->update([
                    'estado'                  => 'entregado',
                    'confirmado_con_tarjeta'  => true,
                    'confirmado_at'           => now(),
                    'tarjeta_lectura_id'      => $lectura->id,
                    'operador_usuario_id'     => Auth::id(),
                    'cobrado_de_saldo'        => !empty($data['cobrar']),
                    'saldo_movimiento_id'     => $movimientoId,
                ]);
            });

            return back()->with('scan_result', [
                'exito'   => true,
                'tipo'    => 'entrega_confirmada',
                'mensaje' => 'Pedido ' . $pedido->numero_folio . ' entregado y confirmado con tarjeta.',
            ]);

        } catch (\Exception $e) {
            return back()->with('scan_result', [
                'exito'   => false,
                'mensaje' => $e->getMessage(),
            ]);
        }
    }

    /* ─── Acceso / consumo simple ────────────────────────── */

    private function registrarAccesoOConsumo(Request $request, TarjetaUniversitaria $tarjeta, string $uid, string $modulo, string $tipo, int $operadorId)
    {
        $usuario = $tarjeta->usuario;

        TarjetaLectura::create([
            'tarjeta_id'          => $tarjeta->id,
            'uid_leido'           => $uid,
            'modulo'              => $modulo,
            'tipo_lectura'        => $tipo,
            'exito'               => true,
            'detalle'             => "Lectura exitosa en módulo: {$modulo}. Tipo: {$tipo}.",
            'ip'                  => $request->ip(),
            'user_agent'          => $request->userAgent() ?? '',
            'operador_usuario_id' => $operadorId,
            'meta_json'           => [],
        ]);

        return back()->with('scan_result', [
            'exito'   => true,
            'tipo'    => $tipo,
            'mensaje' => 'Lectura exitosa.',
            'uid'     => $uid,
            'usuario' => [
                'nombre'   => $usuario->nombre . ' ' . $usuario->apellido,
                'email'    => $usuario->email,
                'foto_url' => $usuario->foto_url,
            ],
            'tarjeta' => [
                'id'     => $tarjeta->id,
                'estado' => $tarjeta->estado,
            ],
            'lectura_id' => null,
        ]);
    }
}