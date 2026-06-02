<?php

namespace Tests\Unit\Catalogo;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Catalogo\Catalogo;
use App\Models\Catalogo\Inventario;
use App\Services\Catalogo\CatalogoCartResolver;
use App\Exceptions\Catalogo\CategoryUnavailableException;
use App\Exceptions\Catalogo\OutOfStockException;

/**
 * Tests de CatalogoCartResolver contra la BD real (PostgreSQL + datos del dump).
 *
 * Se usa DatabaseTransactions en lugar de RefreshDatabase para:
 * 1. Preservar los datos del dump (sin re-migrar ni seeds).
 * 2. Evitar el error "CREATE EXTENSION citext" que SQLite in-memory no soporta.
 *
 * Cada test se ejecuta dentro de una transacción que se revierte al terminar.
 */
class CatalogoCartResolverTest extends TestCase
{
    use DatabaseTransactions;

    private CatalogoCartResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(CatalogoCartResolver::class);
    }

    // ─── Test 1 ──────────────────────────────────────────────────────────────
    // id=11 Café Americano: sin inventario → sin_inventario=true, stock_rastreado=false

    /** @test */
    public function construir_payload_id11_cafe_americano_sin_inventario(): void
    {
        $catalogo = Catalogo::with(['categoria', 'inventario', 'precios'])->find(11);

        $this->assertNotNull($catalogo, 'El catálogo id=11 debe existir en la BD.');

        $payload = $this->resolver->construirPayloadItem($catalogo, 1);

        $this->assertSame('cafeteria', $payload['categoria_slug']);
        $this->assertSame('CAT-11',    $payload['referencia_externa']);
        $this->assertMatchesRegularExpression(
            '/^\d+\.\d{2}$/',
            $payload['precio_unitario'],
            'precio_unitario debe ser string con 2 decimales'
        );

        $meta = $payload['metadata'];
        $this->assertTrue($meta['sin_inventario'],   'sin_inventario debe ser true para id=11');
        $this->assertFalse($meta['stock_rastreado'], 'stock_rastreado debe ser false para id=11');
        $this->assertNull($meta['stock_snapshot'],   'stock_snapshot debe ser null para id=11');
        $this->assertSame('catalogo', $meta['origen']);
    }

    // ─── Test 2 ──────────────────────────────────────────────────────────────
    // id=1 Laptop: con inventario → stock_rastreado=true, stock_actual int >= 0

    /** @test */
    public function construir_payload_id1_laptop_con_inventario(): void
    {
        $catalogo = Catalogo::with(['categoria', 'inventario', 'precios'])->find(1);

        $this->assertNotNull($catalogo, 'El catálogo id=1 debe existir en la BD.');
        $this->assertNotNull($catalogo->inventario, 'id=1 debe tener inventario.');
        $this->assertGreaterThanOrEqual(0, $catalogo->inventario->stock_actual);

        // Verificamos stockDisponible directamente (stock_actual=10 >= 1)
        $this->assertTrue($this->resolver->stockDisponible($catalogo, 1));

        // estadoCarrito refleja stock_rastreado=true y stock_actual entero
        $estado = $this->resolver->estadoCarrito($catalogo, 1);

        $this->assertTrue($estado['stock_rastreado'],
            'stock_rastreado debe ser true cuando hay fila en inventario');
        $this->assertIsInt($estado['stock_actual']);
        $this->assertGreaterThanOrEqual(0, $estado['stock_actual']);

        // "Electrónica" no está en el mapa → cart no disponible
        $this->assertFalse($estado['cart_disponible']);
        $this->assertSame('CATEGORY_UNAVAILABLE', $estado['motivo_no_disponible']);
    }

    // ─── Test 3 ──────────────────────────────────────────────────────────────
    // Categoría fuera del mapa → CategoryUnavailableException

    /** @test */
    public function construir_payload_lanza_category_unavailable_si_categoria_no_mapeada(): void
    {
        $this->expectException(CategoryUnavailableException::class);

        // id=1 tiene categoría "Electrónica" que no está en el slug map
        $catalogo = Catalogo::with(['categoria', 'inventario', 'precios'])->find(1);
        $this->assertNotNull($catalogo);

        $this->resolver->construirPayloadItem($catalogo, 1);
    }

    // ─── Test 4 ──────────────────────────────────────────────────────────────
    // Stock agotado → OutOfStockException
    // Se inyecta un Inventario en-memoria con stock_actual=0 sobre id=11,
    // ya que el dump no tiene ningún producto con stock=0.

    /** @test */
    public function construir_payload_lanza_out_of_stock_con_stock_cero(): void
    {
        $this->expectException(OutOfStockException::class);

        // id=11 Café Americano: tiene categoría mapeada y precio vigente
        $catalogo = Catalogo::with(['categoria', 'precios'])->find(11);
        $this->assertNotNull($catalogo);

        // Stub de inventario con stock_actual = 0
        $inventarioStub               = new Inventario();
        $inventarioStub->stock_actual = 0;
        $catalogo->setRelation('inventario', $inventarioStub);

        // Con stock=0 y cantidad=1 → OutOfStockException
        $this->resolver->construirPayloadItem($catalogo, 1);
    }

    // ─── Test 5 ──────────────────────────────────────────────────────────────
    // parseReferencia('CAT-11') → 11 (int)

    /** @test */
    public function parse_referencia_formato_valido_retorna_entero(): void
    {
        $resultado = $this->resolver->parseReferencia('CAT-11');

        $this->assertSame(11, $resultado);
    }

    // ─── Test 6 ──────────────────────────────────────────────────────────────
    // parseReferencia formato inválido → null

    /** @test */
    public function parse_referencia_formato_invalido_retorna_null(): void
    {
        $resultado = $this->resolver->parseReferencia('PRODUCTO-11');

        $this->assertNull($resultado);
    }

    // ─── Test 7 ──────────────────────────────────────────────────────────────
    // estadoCarrito(id=11) → cart_disponible=true (sin_inventario permitido por config)

    /** @test */
    public function estado_carrito_id11_disponible_pese_a_no_tener_inventario(): void
    {
        $catalogo = Catalogo::with(['categoria', 'inventario', 'precios'])->find(11);
        $this->assertNotNull($catalogo);

        // Política D4: permitir_sin_inventario = true (default en config y .env)
        $estado = $this->resolver->estadoCarrito($catalogo, 1);

        $this->assertTrue($estado['cart_disponible'],
            'id=11 debe estar disponible: sin_inventario está permitido por config.');
        $this->assertFalse($estado['stock_rastreado']);
        $this->assertNull($estado['stock_actual']);
        $this->assertNull($estado['motivo_no_disponible']);
    }
}
