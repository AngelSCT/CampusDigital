<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarritoItem;
use App\Models\PedidoDetalle;
use App\Models\PedidoTienda;
use Illuminate\Http\JsonResponse;

class CartApiController extends Controller
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Construye la estructura formal de ticket para un pedido.
     * Reutilizado en getUserStats y getOrderHistory.
     */
    private function formatTicket(PedidoTienda $pedido): array
    {
        $total    = (float) $pedido->total;
        $subtotal = round($total / 1.16, 2);
        $iva      = round($total - $subtotal, 2);
        $folio    = 'CD-2026-' . str_pad($pedido->id, 5, '0', STR_PAD_LEFT);
        $hash     = hash('sha256', $pedido->id . '|' . $pedido->usuario_id . '|' . $pedido->created_at->toDateTimeString());

        return [
            'id'                  => $pedido->id,
            'folio'               => $folio,
            'ticket_hash'         => $hash,
            'url_verificacion_qr' => url('/v1/validar-ticket/' . $hash),
            'estado'      => $pedido->estado,
            'metodo_pago' => $pedido->metodo_pago,
            'fecha'       => $pedido->created_at->toDateTimeString(),
            'descuento'   => 0.00,
            'subtotal'    => $subtotal,
            'iva'         => $iva,
            'total'       => $total,
            'detalles'    => $pedido->detalles->map(fn ($d) => [
                'producto_id'     => $d->producto_id,
                'nombre_producto' => $d->nombre_producto,
                'cantidad'        => (int) $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario,
                'subtotal'        => (float) $d->subtotal,
            ])->values(),
        ];
    }

    // ─── Endpoints ───────────────────────────────────────────────────────────

    /**
     * GET /api/v1/carrito/usuarios/{id}/stats
     * Total gastado, último pedido (con ticket formal) y carrito activo.
     */
    public function getUserStats(int $id): JsonResponse
    {
        $totalGastado = PedidoTienda::where('usuario_id', $id)
            ->where('estado', 'pagado')
            ->sum('total');

        $totalPedidos = PedidoTienda::where('usuario_id', $id)->count();

        $ultimoPedido = PedidoTienda::where('usuario_id', $id)
            ->orderByDesc('created_at')
            ->with('detalles')
            ->first();

        $itemsCarrito = CarritoItem::where('usuario_id', $id)
            ->where('en_wishlist', false)
            ->where('guardado_para_despues', false)
            ->with('producto:id,nombre,precio,imagen')
            ->get()
            ->map(fn ($item) => [
                'id'       => $item->id,
                'producto' => $item->producto,
                'cantidad' => (int) $item->cantidad,
            ])->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'resumen' => [
                    'total_gastado' => (float) $totalGastado,
                    'total_pedidos' => $totalPedidos,
                ],
                'ultimo_pedido'  => $ultimoPedido ? $this->formatTicket($ultimoPedido) : null,
                'carrito_activo' => $itemsCarrito,
            ],
        ]);
    }

    /**
     * GET /api/v1/carrito/stats/global
     * Top 5 productos más vendidos (Dashboard Admin).
     */
    public function getGlobalStats(): JsonResponse
    {
        $top5 = PedidoDetalle::selectRaw(
                'producto_id, nombre_producto,
                 SUM(cantidad) as total_vendido,
                 SUM(subtotal) as ingresos_total'
            )
            ->groupBy('producto_id', 'nombre_producto')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get()
            ->map(fn ($row, $i) => [
                'posicion'       => $i + 1,
                'producto_id'    => $row->producto_id,
                'nombre'         => $row->nombre_producto,
                'total_vendido'  => (int) $row->total_vendido,
                'ingresos_total' => (float) $row->ingresos_total,
            ])->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'generado_en'   => now()->toDateTimeString(),
                'top_productos' => $top5,
            ],
        ]);
    }

    /**
     * GET /v1/validar-ticket/{hash}
     * Verificación pública de autenticidad de un ticket (sin API key).
     */
    public function validarHash(string $hash): JsonResponse
    {
        $pedidos = PedidoTienda::with('detalles')->get();

        foreach ($pedidos as $pedido) {
            $hashEsperado = hash(
                'sha256',
                $pedido->id . '|' . $pedido->usuario_id . '|' . $pedido->created_at->toDateTimeString()
            );

            if (hash_equals($hashEsperado, $hash)) {
                return response()->json([
                    'status'  => 'success',
                    'mensaje' => 'Ticket auténtico y verificado.',
                    'ticket'  => [
                        'folio'       => 'CD-2026-' . str_pad($pedido->id, 5, '0', STR_PAD_LEFT),
                        'estado'      => $pedido->estado,
                        'metodo_pago' => $pedido->metodo_pago,
                        'total'       => (float) $pedido->total,
                        'fecha'       => $pedido->created_at->toDateTimeString(),
                    ],
                ]);
            }
        }

        return response()->json([
            'status'  => 'error',
            'mensaje' => 'Hash inválido. Este ticket no pudo ser verificado.',
        ], 404);
    }

    /**
     * GET /api/v1/carrito/usuarios/{id}/historial
     * Lista completa de pedidos con estructura de ticket formal.
     */
    public function getOrderHistory(int $id): JsonResponse
    {
        $pedidos = PedidoTienda::where('usuario_id', $id)
            ->orderByDesc('created_at')
            ->with('detalles')
            ->get()
            ->map(fn ($pedido) => $this->formatTicket($pedido))
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'usuario_id'    => $id,
                'total_pedidos' => $pedidos->count(),
                'pedidos'       => $pedidos,
            ],
        ]);
    }
}
