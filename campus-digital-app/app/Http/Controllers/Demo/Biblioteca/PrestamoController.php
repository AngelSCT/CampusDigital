<?php

namespace App\Http\Controllers\Demo\Biblioteca;

use App\Http\Controllers\Controller;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartOwnershipException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\CheckoutRevertidoException;
use App\Modules\Cart\Exceptions\SaldoInsufficientFundsException;
use App\Modules\Cart\Exceptions\SaldoUnavailableException;
use App\Modules\Cart\Exceptions\ScopeDeniedException;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\CheckoutService;
use App\Modules\Cart\Services\ItemService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Demo: Módulo Biblioteca embebiendo el Módulo Carrito.
 *
 * Las rutas /cart-proxy/* actúan como proxy local sin HTTP —
 * llaman a los servicios del Carrito directamente para evitar
 * el deadlock del servidor de desarrollo (self-HTTP-call).
 */
class PrestamoController extends Controller
{
    public function __construct(
        private readonly CarritoService  $carritoService,
        private readonly ItemService     $itemService,
        private readonly CheckoutService $checkoutService,
    ) {}

    private function getModulo(): ModuloCliente
    {
        return ModuloCliente::where('slug', 'biblioteca-demo')->firstOrFail();
    }

    private function jwtPayload(): array
    {
        $modulo = $this->getModulo();
        return [
            'sub'                    => (string) $modulo->id,
            'categorias_autorizadas' => $modulo->categorias_autorizadas,
        ];
    }

    public function index(): Response
    {
        return Inertia::render('Demo/Biblioteca/Prestamo');
    }

    // ─── Proxy routes ─────────────────────────────────────────────────────────

    public function proxyCreateCart(Request $request): JsonResponse
    {
        try {
            $modulo  = $this->getModulo();
            $carrito = $this->carritoService->crear($modulo, $request->validate([
                'usuario_ref'       => 'required|string|max:120',
                'requiere_saldo'    => 'nullable|boolean',
                'expira_en_minutos' => 'nullable|integer|min:1',
                'metadata'          => 'nullable|array',
            ]));
            return response()->json($this->carritoService->toArray($carrito), 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function proxyGetCart(Request $request, string $uuid): JsonResponse
    {
        try {
            $modulo  = $this->getModulo();
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            return response()->json($this->carritoService->toArray($carrito));
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function proxyAddItem(Request $request, string $uuid): JsonResponse
    {
        try {
            $modulo     = $this->getModulo();
            $jwtPayload = $this->jwtPayload();
            $carrito    = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $result     = $this->itemService->agregar($carrito, $modulo, $jwtPayload, $request->all());
            $status     = $result['accion'] === 'creado' ? 201 : 200;
            return response()->json($result, $status);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function proxyRemoveItem(Request $request, string $uuid, int $itemId): JsonResponse
    {
        try {
            $modulo  = $this->getModulo();
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $total   = $this->itemService->remover($carrito, $itemId);
            return response()->json(['mensaje' => 'Ítem removido.', 'total_actualizado' => $total]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function proxyCheckout(Request $request, string $uuid): JsonResponse
    {
        try {
            $modulo  = $this->getModulo();
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $carrito = $this->checkoutService->confirmar($carrito, [
                'metadata_checkout' => $request->input('metadata_checkout', []),
            ]);

            $data = [
                'carrito_uuid' => $carrito->uuid,
                'estado'       => $carrito->estado,
                'total'        => $carrito->total,
                'confirmed_at' => $carrito->confirmed_at?->toISOString(),
            ];

            if ($carrito->estado === \App\Models\Cart\Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION) {
                $data['aviso'] = 'Cargo pendiente de procesamiento por el módulo de Saldo';
            }

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function proxyCancel(Request $request, string $uuid): JsonResponse
    {
        try {
            $modulo  = $this->getModulo();
            $carrito = $this->carritoService->obtenerPorUuid($uuid, $modulo);
            $carrito = $this->carritoService->cancelar($carrito);
            return response()->json(['estado' => $carrito->estado]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── Error handler ───────────────────────────────────────────────────────

    /**
     * Renderiza una excepción del dominio como respuesta JSON estructurada.
     * Se auto-registra como callable desde el inline handler de cada ruta.
     */
    private function errorResponse(\Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ModelNotFoundException => response()->json(
                ['mensaje' => 'Recurso no encontrado.'], 404
            ),
            $e instanceof ValidationException => response()->json(
                ['error' => 'VALIDATION_ERROR', 'message' => 'Datos inválidos.', 'errors' => $e->errors()], 422
            ),
            $e instanceof CartOwnershipException => response()->json(
                ['error' => 'OWNERSHIP_DENIED', 'mensaje' => $e->getMessage()], 403
            ),
            $e instanceof ScopeDeniedException => response()->json(
                ['error' => 'SCOPE_DENIED', 'mensaje' => 'El módulo no está autorizado para esta categoría.'], 403
            ),
            $e instanceof CartStateException => response()->json(
                ['error' => 'CART_STATE_ERROR', 'mensaje' => $e->getMessage()], 409
            ),
            $e instanceof CartBusinessException => response()->json(
                ['error' => 'BUSINESS_RULE_VIOLATION', 'mensaje' => $e->getMessage()], 422
            ),
            $e instanceof CheckoutRevertidoException => response()->json(
                ['error' => 'CHECKOUT_REVERTIDO', 'mensaje' => $e->getMessage()], 409
            ),
            $e instanceof SaldoInsufficientFundsException => response()->json(
                ['error' => 'SALDO_INSUFICIENTE', 'mensaje' => $e->getMessage()], 402
            ),
            $e instanceof SaldoUnavailableException => response()->json(
                ['error' => 'SALDO_NO_DISPONIBLE', 'mensaje' => $e->getMessage()], 503
            ),
            $e instanceof \RuntimeException => response()->json(
                ['error' => 'ERROR_DEMO', 'mensaje' => $e->getMessage()], 400
            ),
            default => response()->json(
                ['error' => 'ERROR_INTERNO', 'mensaje' => 'Ocurrió un error inesperado.'], 500
            ),
        };
    }
}
