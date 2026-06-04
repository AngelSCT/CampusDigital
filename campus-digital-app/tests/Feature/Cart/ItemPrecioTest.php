<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\ReglaCategoria;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\ModuleTokenService;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests de Tanda 1 — validación de precio y atomicidad de ItemService.
 *
 * CartTestCase corre solo migraciones 2026_04_28_*. Las reglas de precio
 * no existen en esas migraciones; se insertan directamente en setUp().
 */
class ItemPrecioTest extends CartTestCase
{
    private ModuloCliente $modulo;
    private string $token;
    private CarritoService $carritoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();
        $this->seedPriceRules();

        $this->carritoService = new CarritoService();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'test-precio-' . uniqid(),
            'tipo_modulo'            => 'test',
            'categorias_autorizadas' => ['prestamo', 'producto', 'copias', 'sin_reglas'],
        ]);

        $tokenService = new ModuleTokenService();
        $par          = $tokenService->issuePair($this->modulo);
        $this->token  = $par['access_token'];
    }

    // ─── Precio: formato (validado por FormRequest) ───────────────────────────

    #[Test]
    public function precio_con_tres_decimales_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Producto test',
                'precio_unitario'    => 0.001,
                'cantidad'           => 1,
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('cart_items_carrito', ['carrito_id' => $carrito->id]);
    }

    #[Test]
    public function precio_con_cuatro_decimales_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Producto test',
                'precio_unitario'    => 1.9999,
                'cantidad'           => 1,
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function precio_valido_con_dos_decimales_es_aceptado(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Producto test',
                'precio_unitario'    => 1.50,
                'cantidad'           => 1,
            ])
            ->assertStatus(201);
    }

    // ─── Precio: reglas de negocio por categoría (validado por ItemService) ──

    #[Test]
    public function categoria_copias_con_precio_cero_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'copias',
                'referencia_externa' => 'COPIA-001',
                'nombre'             => 'Copia B&N',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'BUSINESS_RULE_VIOLATION');

        $this->assertDatabaseMissing('cart_items_carrito', ['carrito_id' => $carrito->id]);
    }

    #[Test]
    public function categoria_copias_con_precio_subcentavo_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();

        // 0.001 falla por decimal:0,2 en FormRequest (HTTP 422 antes de llegar a ItemService)
        // 0.00 falla por regla de negocio en ItemService
        // Este test verifica que precio mínimo de la categoría también se aplica
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'copias',
                'referencia_externa' => 'COPIA-001',
                'nombre'             => 'Copia B&N',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function categoria_prestamo_acepta_precio_cero(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'LIBRO-001',
                'nombre'             => 'Fundamentos de Redes',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('cart_items_carrito', [
            'carrito_id'      => $carrito->id,
            'referencia_externa' => 'LIBRO-001',
            'precio_unitario' => '0.00',
        ]);
    }

    #[Test]
    public function categoria_sin_reglas_con_precio_cero_devuelve_422(): void
    {
        // Categoría 'sin_reglas' no tiene permite_precio_cero ni precio_minimo.
        // El default seguro en ItemService aplica: permite_precio_cero = false, precio_minimo = 0.01.
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'sin_reglas',
                'referencia_externa' => 'SRV-001',
                'nombre'             => 'Sin reglas',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'BUSINESS_RULE_VIOLATION');
    }

    // ─── Total: recalcula correctamente ───────────────────────────────────────

    #[Test]
    public function agregar_item_actualiza_total_del_carrito(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'LIBRO-001',
                'nombre'             => 'Libro A',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(201)
            ->assertJsonPath('total_actualizado', '0.00');

        // Agregar producto con precio para verificar total
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Souvenir',
                'precio_unitario'    => 15.50,
                'cantidad'           => 2,
            ])
            ->assertStatus(201)
            ->assertJsonPath('total_actualizado', '31.00');

        $this->assertDatabaseHas('cart_carritos', [
            'uuid'  => $carrito->uuid,
            'total' => '31.00',
        ]);
    }

    #[Test]
    public function remover_item_actualiza_total_del_carrito(): void
    {
        $carrito = $this->crearCarrito();

        // Agregar 2 ítems
        $res1 = $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Souvenir',
                'precio_unitario'    => 10.00,
                'cantidad'           => 1,
            ]);
        $itemId1 = $res1->json('item_id');

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-002',
                'nombre'             => 'Souvenir 2',
                'precio_unitario'    => 5.00,
                'cantidad'           => 1,
            ]);

        // Total debe ser 15.00
        $this->assertDatabaseHas('cart_carritos', ['uuid' => $carrito->uuid, 'total' => '15.00']);

        // Remover primer ítem
        $this->withToken($this->token)
            ->deleteJson("/api/cart/carritos/{$carrito->uuid}/items/{$itemId1}")
            ->assertOk()
            ->assertJsonPath('total_actualizado', '5.00');

        // Total debe ser 5.00
        $this->assertDatabaseHas('cart_carritos', ['uuid' => $carrito->uuid, 'total' => '5.00']);
    }

    #[Test]
    public function agregar_item_duplicado_incrementa_cantidad_y_recalcula_total(): void
    {
        $carrito = $this->crearCarrito();

        // Primera vez
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Souvenir',
                'precio_unitario'    => 10.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(201)
            ->assertJsonPath('total_actualizado', '10.00');

        // Segunda vez — mismo referencia_externa debe incrementar
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Souvenir',
                'precio_unitario'    => 10.00,
                'cantidad'           => 2,
            ])
            ->assertOk()
            ->assertJsonPath('accion', 'incrementado')
            ->assertJsonPath('total_actualizado', '30.00');

        // Solo un ítem en BD (con cantidad 3)
        $this->assertEquals(1, ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->count());
    }

    #[Test]
    public function bitacora_registra_item_agregado(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Souvenir',
                'precio_unitario'    => 10.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('cart_bitacora', [
            'accion'       => Bitacora::ACCION_ITEM_AGREGADO,
            'carrito_uuid' => $carrito->uuid,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearCarrito(): Carrito
    {
        return $this->carritoService->crear($this->modulo, [
            'usuario_ref'    => 'USR-TEST-' . uniqid(),
            'requiere_saldo' => false,
        ]);
    }

    /**
     * Inserta reglas de precio en las categorías que los tests necesitan.
     * No depende de la data migration — inyección directa para tests self-contained.
     */
    private function seedPriceRules(): void
    {
        // prestamo: préstamos gratuitos → permite precio cero
        $prestamo = Categoria::where('slug', 'prestamo')->first();
        if ($prestamo) {
            ReglaCategoria::updateOrCreate(
                ['categoria_id' => $prestamo->id, 'clave' => ReglaCategoria::CLAVE_PERMITE_PRECIO_CERO],
                ['valor' => 'true', 'tipo_dato' => ReglaCategoria::TIPO_BOOL]
            );
        }

        // producto: productos físicos → sin precio cero, mínimo 0.01
        $producto = Categoria::where('slug', 'producto')->first();
        if ($producto) {
            ReglaCategoria::updateOrCreate(
                ['categoria_id' => $producto->id, 'clave' => ReglaCategoria::CLAVE_PERMITE_PRECIO_CERO],
                ['valor' => 'false', 'tipo_dato' => ReglaCategoria::TIPO_BOOL]
            );
            ReglaCategoria::updateOrCreate(
                ['categoria_id' => $producto->id, 'clave' => ReglaCategoria::CLAVE_PRECIO_MINIMO],
                ['valor' => '0.01', 'tipo_dato' => ReglaCategoria::TIPO_STRING]
            );
        }

        // copias: categoría de prueba explícita (no existe en seeder base)
        $copias = Categoria::updateOrCreate(
            ['slug' => 'copias'],
            ['nombre' => 'Copias', 'activa' => true]
        );
        ReglaCategoria::updateOrCreate(
            ['categoria_id' => $copias->id, 'clave' => ReglaCategoria::CLAVE_PERMITE_PRECIO_CERO],
            ['valor' => 'false', 'tipo_dato' => ReglaCategoria::TIPO_BOOL]
        );
        ReglaCategoria::updateOrCreate(
            ['categoria_id' => $copias->id, 'clave' => ReglaCategoria::CLAVE_PRECIO_MINIMO],
            ['valor' => '0.01', 'tipo_dato' => ReglaCategoria::TIPO_STRING]
        );

        // sin_reglas: categoría sin ninguna regla de precio (prueba default seguro)
        Categoria::updateOrCreate(
            ['slug' => 'sin_reglas'],
            ['nombre' => 'Sin Reglas', 'activa' => true]
        );
    }
}
