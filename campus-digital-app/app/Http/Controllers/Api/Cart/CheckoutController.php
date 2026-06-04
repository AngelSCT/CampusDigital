<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CheckoutRequest;
use App\Models\Cart\Carrito;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartOwnershipException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\CheckoutRevertidoException;
use App\Modules\Cart\Exceptions\SaldoInsufficientFundsException;
use App\Modules\Cart\Exceptions\SaldoUnavailableException;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\CheckoutService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CarritoService $carritoService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function checkout(CheckoutRequest $request, string $uuid): JsonResponse
    {
        $modulo = $this->moduloFromRequest($request);

        try {
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $carrito = $this->checkoutService->confirmar($carrito, $request->validated());
        } catch (CheckoutRevertidoException $e) {
            return response()->json([
                'error'   => 'CHECKOUT_REVERTIDO',
                'estado'  => Carrito::ESTADO_REVERTIDO,
                'mensaje' => $e->getMessage(),
            ], 409);
        } catch (ModelNotFoundException) {
            return response()->json(['mensaje' => 'Carrito no encontrado.'], 404);
        } catch (CartOwnershipException) {
            return response()->json(['error' => 'OWNERSHIP_DENIED'], 403);
        } catch (CartStateException $e) {
            return response()->json(['error' => 'CART_STATE_ERROR', 'mensaje' => $e->getMessage()], 409);
        } catch (CartBusinessException $e) {
            return response()->json(['error' => 'CHECKOUT_ERROR', 'mensaje' => $e->getMessage()], 422);
        } catch (SaldoInsufficientFundsException $e) {
            return response()->json(['error' => 'SALDO_INSUFICIENTE', 'mensaje' => $e->getMessage()], 402);
        } catch (SaldoUnavailableException $e) {
            return response()->json(['error' => 'SALDO_NO_DISPONIBLE', 'mensaje' => $e->getMessage()], 503);
        }

        $data = [
            'carrito_uuid' => $carrito->uuid,
            'estado'       => $carrito->estado,
            'total'        => $carrito->total,
            'confirmed_at' => $carrito->confirmed_at?->toISOString(),
        ];

        if ($carrito->estado === Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION) {
            $data['aviso'] = 'Cargo pendiente de procesamiento por el módulo de Saldo';
        }

        return response()->json($data);
    }

    private function moduloFromRequest(Request $request): ModuloCliente
    {
        $payload = $request->attributes->get('module_payload');
        return ModuloCliente::findOrFail((int) $payload['sub']);
    }
}
