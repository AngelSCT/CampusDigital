<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DTOs\CheckoutPedidoDTO;
use App\Services\Pedidos\CrearPedidoDesdeCheckout;
use App\Services\Pedidos\CancelarPedidoDesdeCheckout;
use App\Exceptions\Pedidos\ProductoNoDisponibleException;
use App\Exceptions\Pedidos\SaldoInsuficienteException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CheckoutApiController — Endpoint público para crear pedidos vía HTTP.
 *
 * Cualquier módulo (M4.4 Carrito, M4.9 Proveedores, o consumidor externo)
 * puede crear pedidos enviando un JSON con usuario, módulo e items.
 *
 * POST /api/pedidos/checkout   → crear pedido
 * POST /api/pedidos/checkout/cancelar → cancelar por carrito_uuid
 */
class CheckoutApiController extends Controller
{
    public function __construct(
        private readonly CrearPedidoDesdeCheckout $crearService,
        private readonly CancelarPedidoDesdeCheckout $cancelarService,
    ) {}

    /**
     * POST /api/pedidos/checkout
     *
     * Body JSON:
     * {
     *   "usuario_id": 1,
     *   "modulo": "cafeteria",
     *   "items": [{"producto_id": 1, "cantidad": 2}],
     *   "descripcion": "Pedido opcional",
     *   "carrito_uuid": "uuid-opcional-para-idempotencia"
     * }
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'usuario_id'       => 'required|integer|exists:usuario,id',
            'modulo'           => 'required|string|in:cafeteria,copias,souvenirs,biblioteca,otro',
            'items'            => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.cantidad'    => 'required|integer|min:1',
            'descripcion'      => 'nullable|string|max:500',
            'carrito_uuid'     => 'nullable|string|max:100',
        ]);

        try {
            $dto = CheckoutPedidoDTO::fromArray($request->all());
            $pedido = $this->crearService->ejecutar($dto);

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente.',
                'pedido'  => [
                    'id'            => $pedido->id,
                    'numero_folio'  => $pedido->numero_folio,
                    'estado'        => $pedido->estado,
                    'modulo'        => $pedido->modulo,
                    'total'         => $pedido->total,
                    'carrito_uuid'  => $pedido->carrito_uuid,
                    'items_count'   => $pedido->items->count(),
                    'items'         => $pedido->items->map(fn($i) => [
                        'producto_id'     => $i->producto_id,
                        'nombre_producto' => $i->nombre_producto,
                        'cantidad'        => $i->cantidad,
                        'precio_unitario' => $i->precio_unitario,
                        'total_linea'     => $i->total_linea,
                    ]),
                    'created_at'    => $pedido->created_at->toISOString(),
                ],
            ], 201);

        } catch (ProductoNoDisponibleException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'PRODUCTO_NO_DISPONIBLE',
                'message' => $e->getMessage(),
            ], 422);

        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'SALDO_INSUFICIENTE',
                'message' => $e->getMessage(),
                'saldo_actual'    => $e->saldoActual,
                'total_requerido' => $e->totalRequerido,
            ], 402);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'DATOS_INVALIDOS',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/pedidos/checkout/cancelar
     *
     * Body JSON:
     * {
     *   "carrito_uuid": "uuid-del-carrito",
     *   "motivo": "Razón de la cancelación"
     * }
     */
    public function cancelar(Request $request): JsonResponse
    {
        $request->validate([
            'carrito_uuid' => 'required|string|max:100',
            'motivo'       => 'nullable|string|max:500',
        ]);

        $pedido = $this->cancelarService->ejecutar(
            $request->input('carrito_uuid'),
            $request->input('motivo', 'Cancelado desde checkout')
        );

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'error'   => 'PEDIDO_NO_ENCONTRADO',
                'message' => 'No existe un pedido asociado a ese carrito_uuid.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado exitosamente.',
            'pedido'  => [
                'id'            => $pedido->id,
                'numero_folio'  => $pedido->numero_folio,
                'estado'        => $pedido->estado,
                'carrito_uuid'  => $pedido->carrito_uuid,
            ],
        ]);
    }
}