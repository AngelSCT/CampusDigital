<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CrearCarritoRequest;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Exceptions\CartOwnershipException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Services\CarritoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function __construct(private readonly CarritoService $carritoService) {}

    public function store(CrearCarritoRequest $request): JsonResponse
    {
        $modulo  = $this->moduloFromRequest($request);
        $carrito = $this->carritoService->crear($modulo, $request->validated());

        return response()->json($this->carritoService->toArray($carrito), 201);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $modulo = $this->moduloFromRequest($request);

        try {
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
        } catch (ModelNotFoundException) {
            return response()->json(['mensaje' => 'Carrito no encontrado.'], 404);
        } catch (CartOwnershipException) {
            return response()->json(['error' => 'OWNERSHIP_DENIED', 'mensaje' => 'El carrito no pertenece a este módulo.'], 403);
        }

        return response()->json($this->carritoService->toArray($carrito));
    }

    public function cancelar(Request $request, string $uuid): JsonResponse
    {
        $modulo = $this->moduloFromRequest($request);

        try {
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $carrito = $this->carritoService->cancelar($carrito);
        } catch (ModelNotFoundException) {
            return response()->json(['mensaje' => 'Carrito no encontrado.'], 404);
        } catch (CartOwnershipException) {
            return response()->json(['error' => 'OWNERSHIP_DENIED', 'mensaje' => 'El carrito no pertenece a este módulo.'], 403);
        } catch (CartStateException $e) {
            return response()->json(['error' => 'CART_STATE_ERROR', 'mensaje' => $e->getMessage()], 409);
        }

        return response()->json(['estado' => $carrito->estado]);
    }

    public function historico(Request $request): JsonResponse
    {
        $modulo  = $this->moduloFromRequest($request);
        $filters = $request->only(['estado', 'usuario_ref', 'desde', 'hasta', 'per_page']);
        $result  = $this->carritoService->historial($modulo, $filters);

        return response()->json($result);
    }

    private function moduloFromRequest(Request $request): ModuloCliente
    {
        $payload = $request->attributes->get('module_payload');
        return ModuloCliente::findOrFail((int) $payload['sub']);
    }
}
