<?php

namespace App\Http\Controllers\Demo\Biblioteca;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Exceptions\Client\CartApiUnavailableException;
use App\Modules\Cart\Exceptions\Client\CartScopeException;
use App\Modules\Cart\Exceptions\Client\CartValidationException;
use App\Modules\Cart\Exceptions\Client\InsufficientFundsException;
use App\Modules\Cart\Exceptions\Client\ModuleTokenExpiredException;
use App\Modules\Cart\Exceptions\Client\SaldoUnavailableForClientException;
use App\Modules\Cart\Support\ConsumesCartApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Demo: Módulo Biblioteca embebiendo el Módulo Carrito.
 *
 * Las rutas /cart-proxy/* son el proxy que interpone el JWT de módulo.
 * El navegador nunca ve el token; solo recibe los datos del carrito.
 */
class PrestamoController extends Controller
{
    use ConsumesCartApi;

    // ─── Token override — lee BIBLIOTECA_CART_TOKEN en lugar del genérico ────

    protected function cartAccessTokenValue(): string
    {
        return static::$_cartAccessToken
            ?? (string) config('services.cart.biblioteca_token', '');
    }

    protected function cartRefreshTokenValue(): string
    {
        return static::$_cartRefreshToken
            ?? (string) config('services.cart.biblioteca_refresh_token', '');
    }

    /** Página principal del catálogo de préstamos. */
    public function index(): Response
    {
        return Inertia::render('Demo/Biblioteca/Prestamo');
    }

    // ─── Proxy routes ─────────────────────────────────────────────────────────

    public function proxyCreateCart(Request $request): JsonResponse
    {
        return $this->proxyCall(fn() => $this->createCart(
            usuarioRef:      (string) $request->input('usuario_ref', 'anonimo'),
            requiereSaldo:   (bool)   $request->input('requiere_saldo', false),
            expiraEnMinutos: (int)    $request->input('expira_en_minutos', 30),
            metadata:        (array)  ($request->input('metadata') ?? []),
        ), 201);
    }

    public function proxyGetCart(Request $request, string $uuid): JsonResponse
    {
        return $this->proxyCall(fn() => $this->getCart($uuid));
    }

    public function proxyAddItem(Request $request, string $uuid): JsonResponse
    {
        return $this->proxyCall(fn() => $this->addItem($uuid, $request->all()), 201);
    }

    public function proxyRemoveItem(Request $request, string $uuid, int $itemId): JsonResponse
    {
        return $this->proxyCall(fn() => $this->removeItem($uuid, $itemId));
    }

    public function proxyCheckout(Request $request, string $uuid): JsonResponse
    {
        return $this->proxyCall(fn() => $this->checkout($uuid, $request->input('metadata_checkout', [])));
    }

    public function proxyCancel(Request $request, string $uuid): JsonResponse
    {
        return $this->proxyCall(fn() => $this->cancel($uuid));
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function proxyCall(callable $fn, int $successStatus = 200): JsonResponse
    {
        try {
            return response()->json($fn(), $successStatus);
        } catch (ModuleTokenExpiredException $e) {
            return response()->json(['error' => 'TOKEN_MODULO_EXPIRADO', 'message' => $e->getMessage()], 503);
        } catch (CartApiUnavailableException $e) {
            Log::error('demo.biblioteca.cart_unavailable', [
                'endpoint' => request()->path(),
                'detail'   => $e->getMessage(),
            ]);
            return response()->json([
                'error'   => 'CART_NO_DISPONIBLE',
                'message' => 'No pudimos conectar con el servicio de carrito. Intenta de nuevo en unos segundos.',
            ], 503);
        } catch (CartValidationException $e) {
            return response()->json(['error' => 'VALIDACION', 'message' => $e->getMessage(), 'errors' => $e->errors], 422);
        } catch (CartScopeException $e) {
            return response()->json(['error' => 'SCOPE', 'message' => $e->getMessage()], 403);
        } catch (InsufficientFundsException $e) {
            return response()->json(['error' => 'SALDO_INSUFICIENTE', 'message' => $e->getMessage()], 402);
        } catch (SaldoUnavailableForClientException $e) {
            return response()->json(['error' => 'SALDO_NO_DISPONIBLE', 'message' => $e->getMessage()], 503);
        }
    }
}
