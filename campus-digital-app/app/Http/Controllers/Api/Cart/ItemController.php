<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AgregarItemRequest;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartOwnershipException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\ScopeDeniedException;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\ItemService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(
        private readonly CarritoService $carritoService,
        private readonly ItemService $itemService,
    ) {}

    public function store(AgregarItemRequest $request, string $uuid): JsonResponse
    {
        $modulo     = $this->moduloFromRequest($request);
        $jwtPayload = $request->attributes->get('module_payload');

        try {
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $result  = $this->itemService->agregar($carrito, $modulo, $jwtPayload, $request->validated());
        } catch (ModelNotFoundException) {
            return response()->json(['mensaje' => 'Carrito no encontrado.'], 404);
        } catch (CartOwnershipException) {
            return response()->json(['error' => 'OWNERSHIP_DENIED', 'mensaje' => 'El carrito no pertenece a este módulo.'], 403);
        } catch (ScopeDeniedException) {
            return response()->json(['error' => 'SCOPE_DENIED', 'mensaje' => 'El módulo no está autorizado para esta categoría.'], 403);
        } catch (CartStateException $e) {
            return response()->json(['error' => 'CART_STATE_ERROR', 'mensaje' => $e->getMessage()], 409);
        } catch (CartBusinessException $e) {
            return response()->json(['error' => 'BUSINESS_RULE_VIOLATION', 'mensaje' => $e->getMessage()], 422);
        }

        $status = $result['accion'] === 'creado' ? 201 : 200;
        return response()->json($result, $status);
    }

    public function destroy(Request $request, string $uuid, int $itemId): JsonResponse
    {
        $modulo = $this->moduloFromRequest($request);

        try {
            $carrito       = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $totalActual   = $this->itemService->remover($carrito, $itemId);
        } catch (ModelNotFoundException) {
            return response()->json(['mensaje' => 'Recurso no encontrado.'], 404);
        } catch (CartOwnershipException) {
            return response()->json(['error' => 'OWNERSHIP_DENIED'], 403);
        } catch (CartStateException $e) {
            return response()->json(['error' => 'CART_STATE_ERROR', 'mensaje' => $e->getMessage()], 409);
        }

        return response()->json(['mensaje' => 'Ítem removido.', 'total_actualizado' => $totalActual]);
    }

    public function devolver(Request $request, string $uuid, int $itemId): JsonResponse
    {
        $modulo = $this->moduloFromRequest($request);

        try {
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $item    = $this->itemService->devolver($carrito, $itemId);
        } catch (ModelNotFoundException) {
            return response()->json(['mensaje' => 'Recurso no encontrado.'], 404);
        } catch (CartOwnershipException) {
            return response()->json(['error' => 'OWNERSHIP_DENIED'], 403);
        } catch (CartBusinessException $e) {
            return response()->json(['error' => 'BUSINESS_RULE_VIOLATION', 'mensaje' => $e->getMessage()], 422);
        }

        return response()->json(['estado_item' => $item->estado_item]);
    }

    private function moduloFromRequest(Request $request): ModuloCliente
    {
        $payload = $request->attributes->get('module_payload');
        return ModuloCliente::findOrFail((int) $payload['sub']);
    }
}
