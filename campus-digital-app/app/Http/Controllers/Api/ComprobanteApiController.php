<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\CartComprobanteService;
use Illuminate\Http\Request;

/**
 * ComprobanteApiController — Módulo 8
 *
 * Endpoints que el Módulo 4 (Carrito) consume para:
 * 1. Solicitar la generación del ticket tras checkout exitoso
 * 2. Consultar el status de una venta
 * 3. Confirmar recepción del ticket (webhook de Módulo 4)
 */
class ComprobanteApiController extends Controller
{
    public function __construct(protected CartComprobanteService $comprobanteService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT 1 — GENERAR TICKET
    // POST /api/modulo8/comprobantes/generar
    //
    // El Módulo 4 llama esto tras un checkout exitoso.
    // Nosotros consultamos su API, generamos el ticket y lo devolvemos.
    //
    // Body: { carrito_uuid: "7fe28afc-..." }
    // Auth: X-API-KEY
    // ─────────────────────────────────────────────────────────────────────────

    public function generarTicket(Request $request)
    {
        $request->validate([
            'carrito_uuid' => 'required|string|max:100',
        ]);

        $resultado = $this->comprobanteService->generarTicket($request->carrito_uuid);

        if (!$resultado['ok']) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $resultado['error'] ?? 'Error al generar el ticket.',
            ], $resultado['code'] ?? 500);
        }

        return response()->json([
            'ok'     => true,
            'ticket' => $resultado['ticket'],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT 2 — STATUS DE VENTA
    // GET /api/modulo8/ventas/{carrito_uuid}/status
    //
    // Devuelve el estado actual de una venta/comprobante.
    // Para uso interno y para otros módulos externos.
    // Auth: X-API-KEY
    // ─────────────────────────────────────────────────────────────────────────

    public function statusVenta(string $carritoUuid)
    {
        $comprobante = Comprobante::where('carrito_uuid', $carritoUuid)->first();

        if (!$comprobante) {
            // Intentar obtenerlo del Módulo 4 en tiempo real
            $resultado = $this->comprobanteService->obtenerYGuardar($carritoUuid);

            if (!$resultado['ok']) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'Venta no encontrada.',
                ], 404);
            }

            $comprobante = $resultado['comprobante'];
        }

        return response()->json([
            'ok'           => true,
            'carrito_uuid' => $comprobante->carrito_uuid,
            'ticket_id'    => $comprobante->ticket_id,
            'folio'        => $comprobante->folio,
            'estado'       => $comprobante->estado,
            'confirmado'   => $comprobante->estaConfirmado(),
            'total'        => floatval($comprobante->total),
            'modulo'       => $comprobante->modulo_origen,
            'fecha'        => $comprobante->fecha_confirmacion?->toIso8601String(),
            'actualizado'  => $comprobante->updated_at?->toIso8601String(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT 3 — DETALLE COMPLETO DE VENTA
    // GET /api/modulo8/ventas/{carrito_uuid}/detalle
    //
    // Devuelve el comprobante completo con items, totales, usuario.
    // Para el personal del módulo y módulos externos que necesiten detalles.
    // Auth: X-API-KEY
    // ─────────────────────────────────────────────────────────────────────────

    public function detalleVenta(string $carritoUuid)
    {
        $comprobante = Comprobante::where('carrito_uuid', $carritoUuid)->first();

        if (!$comprobante) {
            $resultado = $this->comprobanteService->obtenerYGuardar($carritoUuid);

            if (!$resultado['ok']) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'Venta no encontrada.',
                ], 404);
            }

            $comprobante = $resultado['comprobante'];
        }

        return response()->json([
            'ok'   => true,
            'venta' => [
                'carrito_uuid'  => $comprobante->carrito_uuid,
                'ticket_id'     => $comprobante->ticket_id,
                'folio'         => $comprobante->folio,
                'estado'        => $comprobante->estado,
                'confirmado'    => $comprobante->estaConfirmado(),
                'modulo_origen' => $comprobante->modulo_origen,
                'usuario_ref'   => $comprobante->usuario_ref,
                'items'         => $comprobante->items,
                'total'         => floatval($comprobante->total),
                'total_formato' => $comprobante->totalFormateado(),
                'fecha'         => $comprobante->fecha_confirmacion?->toIso8601String(),
                'creado'        => $comprobante->created_at?->toIso8601String(),
                'actualizado'   => $comprobante->updated_at?->toIso8601String(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT 4 — CONFIRMACIÓN WEBHOOK (Módulo 4 nos avisa que mostró el ticket)
    // POST /api/modulo8/comprobantes/{carrito_uuid}/confirmar
    //
    // El Módulo 4 llama esto cuando el usuario ya vio el ticket en pantalla.
    // Actualizamos el estado del comprobante.
    // Auth: X-API-KEY
    // ─────────────────────────────────────────────────────────────────────────

    public function confirmarMostrado(string $carritoUuid)
    {
        $comprobante = Comprobante::where('carrito_uuid', $carritoUuid)->first();

        if (!$comprobante) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Comprobante no encontrado.',
            ], 404);
        }

        $comprobante->update([
            'estado'   => 'confirmado',
            'data_raw' => array_merge($comprobante->data_raw ?? [], [
                'confirmado_mostrado_at' => now()->toIso8601String(),
            ]),
        ]);

        return response()->json([
            'ok'        => true,
            'mensaje'   => 'Comprobante confirmado correctamente.',
            'ticket_id' => $comprobante->ticket_id,
            'folio'     => $comprobante->folio,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENDPOINT 5 — LISTADO DE VENTAS POR USUARIO
    // GET /api/modulo8/ventas/usuario/{usuario_ref}
    //
    // Para consultar el historial de compras de un usuario.
    // Auth: X-API-KEY
    // ─────────────────────────────────────────────────────────────────────────

    public function ventasPorUsuario(string $usuarioRef)
    {
        $comprobantes = Comprobante::where('usuario_ref', $usuarioRef)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'carrito_uuid' => $c->carrito_uuid,
                'ticket_id'    => $c->ticket_id,
                'folio'        => $c->folio,
                'estado'       => $c->estado,
                'total'        => floatval($c->total),
                'modulo'       => $c->modulo_origen,
                'fecha'        => $c->fecha_confirmacion?->toIso8601String(),
            ]);

        return response()->json([
            'ok'          => true,
            'usuario_ref' => $usuarioRef,
            'total_ventas'=> $comprobantes->count(),
            'ventas'      => $comprobantes,
        ]);
    }
}
