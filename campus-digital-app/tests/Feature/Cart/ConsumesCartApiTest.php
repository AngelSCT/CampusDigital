<?php

namespace Tests\Feature\Cart;

use App\Modules\Cart\Exceptions\Client\CartApiUnavailableException;
use App\Modules\Cart\Exceptions\Client\CartScopeException;
use App\Modules\Cart\Exceptions\Client\CartValidationException;
use App\Modules\Cart\Exceptions\Client\InsufficientFundsException;
use App\Modules\Cart\Exceptions\Client\ModuleTokenExpiredException;
use App\Modules\Cart\Exceptions\Client\SaldoUnavailableForClientException;
use App\Modules\Cart\Support\ConsumesCartApi;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** Minimal stub controller that uses the trait. */
class _CartApiStub
{
    use ConsumesCartApi;
}

class ConsumesCartApiTest extends TestCase
{
    private _CartApiStub $stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset static token cache between tests
        _CartApiStub::resetCartTokenCache();

        config([
            'cart.client.module_token'  => 'test-access-token',
            'cart.client.refresh_token' => 'test-refresh-token',
            'services.cart.url'         => 'http://cart.test',
        ]);

        $this->stub = new _CartApiStub();
    }

    protected function tearDown(): void
    {
        _CartApiStub::resetCartTokenCache();
        parent::tearDown();
    }

    // ─── createCart ───────────────────────────────────────────────────────────

    #[Test]
    public function create_cart_con_respuesta_201_retorna_array_con_uuid(): void
    {
        Http::fake([
            'cart.test/*' => Http::response(['uuid' => 'abc-123', 'estado' => 'abierto', 'total' => '0.00'], 201),
        ]);

        $result = $this->stub->createCart('MAT-001');

        $this->assertEquals('abc-123', $result['uuid']);
        $this->assertEquals('abierto', $result['estado']);

        Http::assertSent(fn($req) =>
            str_contains($req->url(), '/carritos') &&
            $req->hasHeader('Authorization', 'Bearer test-access-token')
        );
    }

    // ─── getCart ──────────────────────────────────────────────────────────────

    #[Test]
    public function get_cart_con_respuesta_200_retorna_array_con_items(): void
    {
        Http::fake([
            'cart.test/*/carritos/uuid-1' => Http::response([
                'uuid'  => 'uuid-1',
                'items' => [['id' => 1, 'nombre' => 'Libro A']],
                'total' => '0.00',
            ], 200),
        ]);

        $result = $this->stub->getCart('uuid-1');

        $this->assertEquals('uuid-1', $result['uuid']);
        $this->assertCount(1, $result['items']);
    }

    // ─── addItem — 422 ────────────────────────────────────────────────────────

    #[Test]
    public function add_item_con_422_lanza_cart_validation_exception(): void
    {
        Http::fake([
            'cart.test/*' => Http::response([
                'message' => 'El campo nombre es requerido.',
                'errors'  => ['nombre' => ['El campo nombre es requerido.']],
            ], 422),
        ]);

        $this->expectException(CartValidationException::class);
        $this->stub->addItem('uuid-1', []);
    }

    // ─── addItem — 403 ────────────────────────────────────────────────────────

    #[Test]
    public function add_item_con_403_lanza_cart_scope_exception(): void
    {
        Http::fake([
            'cart.test/*' => Http::response(['message' => 'Categoría no autorizada.'], 403),
        ]);

        $this->expectException(CartScopeException::class);
        $this->stub->addItem('uuid-1', ['categoria_slug' => 'no-autorizada']);
    }

    // ─── checkout — 402 ───────────────────────────────────────────────────────

    #[Test]
    public function checkout_con_402_lanza_insufficient_funds_exception(): void
    {
        Http::fake([
            'cart.test/*' => Http::response(['error' => 'SALDO_INSUFICIENTE', 'message' => 'Saldo insuficiente.'], 402),
        ]);

        $this->expectException(InsufficientFundsException::class);
        $this->stub->checkout('uuid-1');
    }

    // ─── checkout — 503 ───────────────────────────────────────────────────────

    #[Test]
    public function checkout_con_503_lanza_saldo_unavailable_for_client_exception(): void
    {
        Http::fake([
            'cart.test/*' => Http::response(['error' => 'SALDO_NO_DISPONIBLE', 'message' => 'Saldo caído.'], 503),
        ]);

        $this->expectException(SaldoUnavailableForClientException::class);
        $this->stub->checkout('uuid-1');
    }

    // ─── 401 TOKEN_EXPIRED — retry automático ─────────────────────────────────

    #[Test]
    public function operacion_con_401_token_expired_llama_refresh_y_reintenta(): void
    {
        Http::fake([
            // Primera llamada a getCart → 401 TOKEN_EXPIRED
            'cart.test/*/carritos/uuid-r' => Http::sequence()
                ->push(['code' => 'TOKEN_EXPIRED', 'message' => 'Expirado.'], 401)
                ->push(['uuid' => 'uuid-r', 'items' => [], 'total' => '0.00'], 200),

            // Llamada al refresh
            'cart.test/*/tokens/refresh' => Http::response([
                'access_token'  => 'refreshed-access-token',
                'refresh_token' => 'refreshed-refresh-token',
            ], 200),
        ]);

        $result = $this->stub->getCart('uuid-r');

        $this->assertEquals('uuid-r', $result['uuid']);

        // Verificar que se hizo exactamente 1 llamada a refresh
        Http::assertSentCount(3); // 1 original + 1 refresh + 1 retry

        // Verificar que el segundo intento usó el nuevo token
        $requests = Http::recorded();
        $retryRequest = collect($requests)->last()[0];
        $this->assertStringContainsString('refreshed-access-token', $retryRequest->header('Authorization')[0]);
    }

    // ─── Refresh también falla → ModuleTokenExpiredException ─────────────────

    #[Test]
    public function refresh_tambien_falla_lanza_module_token_expired_exception(): void
    {
        Http::fake([
            'cart.test/*/carritos/*' => Http::response(
                ['code' => 'TOKEN_EXPIRED', 'message' => 'Expirado.'], 401
            ),
            'cart.test/*/tokens/refresh' => Http::response(
                ['code' => 'TOKEN_INVALIDO', 'message' => 'Refresh inválido.'], 401
            ),
        ]);

        $this->expectException(ModuleTokenExpiredException::class);
        $this->expectExceptionMessage('El token del módulo expiró. Solicita uno nuevo al admin.');
        $this->stub->getCart('uuid-x');
    }

    // ─── 5xx → CartApiUnavailableException ───────────────────────────────────

    #[Test]
    public function cualquier_5xx_lanza_cart_api_unavailable_exception(): void
    {
        Http::fake([
            'cart.test/*' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $this->expectException(CartApiUnavailableException::class);
        $this->stub->createCart('MAT-001');
    }
}
