<?php

namespace Tests\Feature\Catalogo;

use App\Models\Catalogo\Catalogo;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Inventario;
use App\Models\Catalogo\Precio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Tests\TestCase;

class CatalogoCartProxyTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl;
    protected string $testUuid = 'test-uuid-1234';

    protected function setUp(): void
    {
        parent::setUp();

        // Autenticar un usuario real
        $user = Usuario::factory()->create();
        $this->actingAs($user);

        // Fijar sesión por defecto
        session(['cart_uuid' => $this->testUuid]);

        // URL base de la api del carrito
        $this->baseUrl = rtrim(config('cart.api.base_url', 'http://127.0.0.1:8000/api/v1/internal'), '/');

        // Comportamiento base genérico de Http::fake
        Http::fake([
            "{$this->baseUrl}/tokens/refresh" => Http::response(['access_token' => 'new-token'], 200),
        ]);
    }

    private function crearProducto(string $nombreCategoria, float $precio, int $stock = 10): Catalogo
    {
        $categoria = Categoria::factory()->create(['nombre' => $nombreCategoria]);
        
        $producto = Catalogo::factory()->create([
            'id_categoria' => $categoria->id_categoria,
            'tipo' => 'producto',
            'activo' => true,
        ]);

        Precio::factory()->create([
            'id_catalogo' => $producto->id_catalogo,
            'precio' => $precio,
        ]);

        Inventario::factory()->create([
            'id_catalogo' => $producto->id_catalogo,
            'stock_actual' => $stock,
        ]);

        return $producto;
    }

    public function test_1_agregar_item_ignora_precio_del_request()
    {
        $producto = $this->crearProducto('Cafetería', 18.00);

        Http::fake([
            "{$this->baseUrl}/carritos/{$this->testUuid}/items" => Http::response(['id' => 1], 201),
        ]);

        $response = $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/items", [
            'referencia_externa' => 'CAT-' . $producto->id_catalogo,
            'cantidad' => 1,
            'precio_unitario' => 0.01 // Precio malicioso
        ]);

        $response->assertStatus(201);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return $request->url() === "{$this->baseUrl}/carritos/{$this->testUuid}/items"
                && $request['precio_unitario'] === '18.00' // Real de BD
                && $request['cantidad'] === 1;
        });
    }

    public function test_2_agregar_item_con_cantidad_mayor_a_stock_lanza_out_of_stock()
    {
        $producto = $this->crearProducto('Cafetería', 18.00, 10); // Stock = 10

        $response = $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/items", [
            'referencia_externa' => 'CAT-' . $producto->id_catalogo,
            'cantidad' => 999
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['error' => 'OUT_OF_STOCK']);

        // No se debió llamar al cart API (salvo el refresh de token que está fakeado globalmente,
        // pero aseguramos que a /items no fue)
        Http::assertNotSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), '/items');
        });
    }

    public function test_3_agregar_item_con_categoria_sin_mapa_retorna_category_unavailable()
    {
        // 'Electrónica' no está en SLUG_MAP del resolver
        $producto = $this->crearProducto('Electrónica', 1500.00);

        $response = $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/items", [
            'referencia_externa' => 'CAT-' . $producto->id_catalogo,
            'cantidad' => 1
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['error' => 'CATEGORY_UNAVAILABLE']);

        Http::assertNotSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), '/items');
        });
    }

    public function test_4_checkout_detecta_price_changed()
    {
        $producto = $this->crearProducto('Cafetería', 18.00);

        // Fake el carrito que tiene un precio diferente
        Http::fake([
            "{$this->baseUrl}/carritos/{$this->testUuid}" => Http::response([
                'items' => [
                    [
                        'referencia_externa' => 'CAT-' . $producto->id_catalogo,
                        'precio_unitario' => 99.99, // Diferente al 18.00 en BD
                        'cantidad' => 1
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/checkout");

        $response->assertStatus(409)
                 ->assertJsonFragment(['error' => 'PRICE_CHANGED']);
                 
        // Aseguramos que NO se llamó al endpoint de checkout real
        Http::assertNotSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), '/checkout');
        });
    }

    public function test_5_cualquier_operacion_con_uuid_ajeno_retorna_cart_ownership_denied()
    {
        // El setup dejó session cart_uuid = test-uuid-1234
        $response = $this->getJson("/catalogo/cart-proxy/carritos/uuid-diferente");

        $response->assertStatus(403)
                 ->assertJsonFragment(['error' => 'CART_OWNERSHIP_DENIED']);
    }

    public function test_6_historico_fuerza_usuario_ref_desde_auth_e_ignora_query_del_browser()
    {
        Http::fake([
            "{$this->baseUrl}/historico*" => Http::response(['data' => []], 200),
        ]);

        $response = $this->getJson("/catalogo/cart-proxy/historico?usuario_ref=hacker");

        $response->assertStatus(200);

        $expectedUsuarioRef = auth()->user()->matricula ?? strval(auth()->id());

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($expectedUsuarioRef) {
            return str_contains($request->url(), '/historico')
                && str_contains($request->url(), 'usuario_ref=' . urlencode($expectedUsuarioRef));
        });
    }

    public function test_7_referencia_externa_con_formato_invalido_retorna_invalid_reference()
    {
        $response = $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/items", [
            'referencia_externa' => 'PRODUCTO-11', // Inválido, debe ser CAT-11
            'cantidad' => 1
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['error' => 'INVALID_REFERENCE']);

        Http::assertNotSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), '/items');
        });
    }

    public function test_8_cart_api_devuelve_401_proxy_refresca_y_reintenta()
    {
        $producto = $this->crearProducto('Cafetería', 18.00);

        // Sequence: primero 401, luego el refresh (genérico), luego 201 en el reintento
        Http::fake([
            "{$this->baseUrl}/carritos/{$this->testUuid}/items" => Http::sequence()
                ->push(['error' => 'Unauthorized'], 401)
                ->push(['id' => 1], 201),
        ]);

        $response = $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/items", [
            'referencia_externa' => 'CAT-' . $producto->id_catalogo,
            'cantidad' => 1
        ]);

        $response->assertStatus(201);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), '/tokens/refresh');
        });
    }

    public function test_9_checkout_exitoso_limpia_session_cart_uuid_402_no_la_limpia()
    {
        $producto = $this->crearProducto('Cafetería', 18.00);

        // Sub-test A: Éxito (200)
        Http::fake([
            "{$this->baseUrl}/carritos/{$this->testUuid}" => Http::response([
                'items' => [
                    ['referencia_externa' => 'CAT-' . $producto->id_catalogo, 'precio_unitario' => 18.00, 'cantidad' => 1]
                ]
            ], 200),
            "{$this->baseUrl}/carritos/{$this->testUuid}/checkout" => Http::response(['estado' => 'confirmado'], 200),
        ]);

        $this->postJson("/catalogo/cart-proxy/carritos/{$this->testUuid}/checkout")
             ->assertStatus(200);

        $this->assertNull(session('cart_uuid'));

        // Restaurar para Sub-test B usando otro UUID para no mezclar mocks
        $testUuidB = 'test-uuid-402';
        session(['cart_uuid' => $testUuidB]);

        // Sub-test B: Error 402
        Http::fake([
            "{$this->baseUrl}/carritos/{$testUuidB}" => Http::response([
                'items' => [
                    ['referencia_externa' => 'CAT-' . $producto->id_catalogo, 'precio_unitario' => 18.00, 'cantidad' => 1]
                ]
            ], 200),
            "{$this->baseUrl}/carritos/{$testUuidB}/checkout" => Http::response(['error' => 'SALDO_INSUFICIENTE'], 402),
        ]);

        $this->postJson("/catalogo/cart-proxy/carritos/{$testUuidB}/checkout")
             ->assertStatus(402);

        $this->assertEquals($testUuidB, session('cart_uuid'));
    }

    public function test_10_connection_exception_retorna_503_cart_api_unavailable()
    {
        // Forzamos un ConnectionException usando un callable en Http::fake
        Http::fake([
            "{$this->baseUrl}/carritos/{$this->testUuid}" => function () {
                throw new ConnectionException('Connection refused');
            }
        ]);

        $response = $this->getJson("/catalogo/cart-proxy/carritos/{$this->testUuid}");

        $response->assertStatus(503)
                 ->assertJsonFragment(['error' => 'CART_API_UNAVAILABLE']);
    }
}
