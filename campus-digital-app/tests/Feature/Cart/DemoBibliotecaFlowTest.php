<?php

namespace Tests\Feature\Cart;

use App\Modules\Cart\Support\ConsumesCartApi;
use App\Http\Controllers\Demo\Biblioteca\PrestamoController;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests end-to-end del demo Biblioteca.
 *
 * Las rutas proxy llaman al trait ConsumesCartApi que a su vez llama
 * a la Cart API. Aquí se mockea la Cart API con Http::fake() para
 * aislar el proxy del servicio real.
 *
 * Punto clave: el JWT de módulo NUNCA debe aparecer en los responses
 * del proxy — viaja solo como Authorization header interno.
 */
class DemoBibliotecaFlowTest extends \Tests\TestCase
{
    private string $moduleToken  = 'demo-module-jwt-secret-token';
    private string $refreshToken = 'demo-refresh-jwt-secret-token';

    protected function setUp(): void
    {
        parent::setUp();

        PrestamoController::resetCartTokenCache();

        config([
            'services.cart.url'                      => 'http://cart-internal.test',
            'services.cart.biblioteca_token'         => $this->moduleToken,
            'services.cart.biblioteca_refresh_token' => $this->refreshToken,
        ]);
    }

    protected function tearDown(): void
    {
        PrestamoController::resetCartTokenCache();
        parent::tearDown();
    }

    // ─── Flujo completo ───────────────────────────────────────────────────────

    #[Test]
    public function flujo_completo_crear_carrito_agregar_item_checkout(): void
    {
        $carritoUuid = 'demo-uuid-' . uniqid();

        Http::fake([
            // 1. Crear carrito
            'cart-internal.test/*/carritos' => Http::response([
                'uuid'    => $carritoUuid,
                'estado'  => 'abierto',
                'total'   => '0.00',
                'items'   => [],
            ], 201),

            // 2. Agregar ítem
            "cart-internal.test/*/carritos/{$carritoUuid}/items" => Http::response([
                'uuid'    => $carritoUuid,
                'estado'  => 'abierto',
                'total'   => '0.00',
                'items'   => [
                    ['id' => 1, 'nombre' => 'Clean Code', 'referencia_externa' => '978-0-13-468599-1'],
                ],
            ], 201),

            // 3. Consultar carrito
            "cart-internal.test/*/carritos/{$carritoUuid}" => Http::response([
                'uuid'  => $carritoUuid,
                'items' => [['id' => 1, 'nombre' => 'Clean Code']],
                'total' => '0.00',
            ], 200),

            // 4. Checkout
            "cart-internal.test/*/carritos/{$carritoUuid}/checkout" => Http::response([
                'uuid'         => $carritoUuid,
                'estado'       => 'confirmado',
                'total'        => '0.00',
                'confirmed_at' => now()->toISOString(),
            ], 200),
        ]);

        // 1. Crear carrito vía proxy
        $r1 = $this->postJson('/demo/biblioteca/cart-proxy/carritos', [
            'usuario_ref' => 'MAT-TEST-001',
        ]);
        $r1->assertStatus(201);
        $this->assertEquals($carritoUuid, $r1->json('uuid'));

        // 2. Agregar libro vía proxy
        $r2 = $this->postJson("/demo/biblioteca/cart-proxy/carritos/{$carritoUuid}/items", [
            'categoria_slug'     => 'prestamo',
            'referencia_externa' => '978-0-13-468599-1',
            'nombre'             => 'Clean Code',
            'precio_unitario'    => 0.00,
            'cantidad'           => 1,
        ]);
        $r2->assertStatus(201);
        $this->assertCount(1, $r2->json('items'));

        // 3. Consultar carrito vía proxy
        $r3 = $this->getJson("/demo/biblioteca/cart-proxy/carritos/{$carritoUuid}");
        $r3->assertOk();
        $this->assertEquals($carritoUuid, $r3->json('uuid'));

        // 4. Checkout vía proxy
        $r4 = $this->postJson("/demo/biblioteca/cart-proxy/carritos/{$carritoUuid}/checkout");
        $r4->assertOk();
        $this->assertEquals('confirmado', $r4->json('estado'));
    }

    // ─── JWT nunca en responses del proxy ─────────────────────────────────────

    #[Test]
    public function jwt_de_modulo_nunca_aparece_en_response_del_proxy(): void
    {
        Http::fake([
            'cart-internal.test/*' => Http::response([
                'uuid'   => 'uuid-jwt-check',
                'estado' => 'abierto',
                'items'  => [],
                'total'  => '0.00',
            ], 201),
        ]);

        $response = $this->postJson('/demo/biblioteca/cart-proxy/carritos', [
            'usuario_ref' => 'MAT-JWT-CHECK',
        ]);

        $response->assertStatus(201);
        $body = json_encode($response->json());

        // El JWT del módulo no debe aparecer en el response body
        $this->assertStringNotContainsString(
            $this->moduleToken,
            $body,
            'El access token del módulo NO debe ser expuesto en el response del proxy.'
        );
        $this->assertStringNotContainsString(
            $this->refreshToken,
            $body,
            'El refresh token del módulo NO debe ser expuesto en el response del proxy.'
        );

        // Pero sí debe haber viajado como Authorization header interno
        Http::assertSent(fn($req) =>
            $req->hasHeader('Authorization', "Bearer {$this->moduleToken}")
        );
    }

    // ─── Proxy propaga errores del Cart API ───────────────────────────────────

    #[Test]
    public function proxy_propaga_402_de_saldo_insuficiente(): void
    {
        Http::fake([
            'cart-internal.test/*' => Http::response(
                ['error' => 'SALDO_INSUFICIENTE', 'message' => 'Saldo insuficiente.'], 402
            ),
        ]);

        $this->postJson('/demo/biblioteca/cart-proxy/carritos/uuid-x/checkout')
            ->assertStatus(402)
            ->assertJsonPath('error', 'SALDO_INSUFICIENTE');
    }

    #[Test]
    public function proxy_propaga_503_cuando_cart_no_disponible(): void
    {
        Http::fake([
            'cart-internal.test/*' => Http::response([], 500),
        ]);

        $this->postJson('/demo/biblioteca/cart-proxy/carritos', ['usuario_ref' => 'MAT-X'])
            ->assertStatus(503)
            ->assertJsonPath('error', 'CART_NO_DISPONIBLE');
    }
}
