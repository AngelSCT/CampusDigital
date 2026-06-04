<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Carrito;
use Illuminate\Http\JsonResponse;

class ComprobanteController extends Controller
{
    /**
     * GET /api/cart/comprobantes/{carritoUuid}
     *
     * Devuelve los detalles del pago de un carrito confirmado.
     * Usado por M4.8 (Saldo) para generar comprobantes de pago.
     */
    public function show(string $carritoUuid): JsonResponse
    {
        $carrito = Carrito::with(['items.categoria', 'modulo'])
            ->where('uuid', $carritoUuid)
            ->where('estado', Carrito::ESTADO_CONFIRMADO)
            ->first();

        if (!$carrito) {
            return response()->json([
                'error'   => 'COMPROBANTE_NO_ENCONTRADO',
                'mensaje' => 'No existe comprobante para este carrito o aún no está confirmado.',
            ], 404);
        }

        $items = $carrito->items->map(fn($i) => [
            'referencia_externa' => $i->referencia_externa,
            'nombre'             => $i->nombre,
            'precio_unitario'    => (float) $i->precio_unitario,
            'cantidad'           => $i->cantidad,
            'subtotal'           => round((float) $i->precio_unitario * $i->cantidad, 2),
            'categoria_slug'     => $i->categoria?->slug,
        ]);

        return response()->json([
            'carrito_uuid'       => (string) $carrito->uuid,
            'fecha_confirmacion' => $carrito->confirmed_at?->toIso8601String(),
            'usuario_ref'        => $carrito->usuario_ref,
            'modulo'             => $carrito->modulo?->slug ?? 'catalogo',
            'items'              => $items,
            'total'              => (float) $carrito->total,
            'requiere_saldo'     => (bool) $carrito->requiere_saldo,
            'estado'             => $carrito->estado,
        ]);
    }
}
