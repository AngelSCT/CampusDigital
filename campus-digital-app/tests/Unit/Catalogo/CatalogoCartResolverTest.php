<?php

namespace Tests\Unit\Catalogo;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Catalogo\Catalogo;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Inventario;
use App\Models\Catalogo\Precio;
use App\Services\Catalogo\CatalogoCartResolver;
use App\Exceptions\Catalogo\CategoryUnavailableException;
use App\Exceptions\Catalogo\OutOfStockException;

/**
 * Tests de CatalogoCartResolver.
 *
 * Crea sus propios registros dentro de una transacción que se revierte al terminar.
 * No depende del dump ni de IDs fijos de BD.
 *
 * Nota: usa DatabaseTransactions (no RefreshDatabase) para preservar extensiones
 * PostgreSQL (citext, etc.) y evitar re-migraciones costosas.
 */
class CatalogoCartResolverTest extends TestCase
{
    use DatabaseTransactions;

    private CatalogoCartResolver $resolver;

    /** Categoría que SÍ aparece en el slug-map del CatalogoCartResolver */
    private const CAT_MAPEADA = 'Copias e Impresiones';

    /** Categoría que NO aparece en el slug-map */
    private const CAT_NO_MAPEADA = 'Electronica_NoMapa_TestOnly';

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(CatalogoCartResolver::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function crearCategoria(string $nombre): Categoria
    {
        return Categoria::firstOrCreate(
            ['nombre' => $nombre],
            ['descripcion' => 'Cat test', 'activo' => true]
        );
    }

    private function crearProducto(Categoria $cat, bool $conInventario = false, int $stock = 10): Catalogo
    {
        $prod = Catalogo::create([
            'nombre'       => 'Producto Test ' . uniqid(),
            'descripcion'  => 'Descripción de prueba',
            'tipo'         => 'producto',
            'id_categoria' => $cat->id_categoria,
            'activo'       => true,
            'aplica_iva'   => false,
        ]);

        // Precio vigente (fecha_inicio hoy, fecha_fin null = sin vencimiento)
        Precio::create([
            'id_catalogo' => $prod->id_catalogo,
            'precio'      => 18.00,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin'    => null,
        ]);

        if ($conInventario) {
            Inventario::create([
                'id_catalogo'  => $prod->id_catalogo,
                'stock_actual' => $stock,
            ]);
        }

        return $prod->load(['categoria', 'inventario', 'precios']);
    }

    // ─── Test 1 ───────────────────────────────────────────────────────────────
    // Producto sin inventario → sin_inventario=true, stock_rastreado=false

    /** @test */
    public function construir_payload_sin_inventario(): void
    {
        $cat     = $this->crearCategoria(self::CAT_MAPEADA);
        $prod    = $this->crearProducto($cat, conInventario: false);
        $payload = $this->resolver->construirPayloadItem($prod, 1);

        $this->assertStringStartsWith('CAT-', $payload['referencia_externa']);
        $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $payload['precio_unitario'],
            'precio_unitario debe ser string con 2 decimales');

        $meta = $payload['metadata'];
        $this->assertTrue($meta['sin_inventario'],   'sin_inventario debe ser true sin fila de inventario');
        $this->assertFalse($meta['stock_rastreado'], 'stock_rastreado debe ser false sin inventario');
        $this->assertNull($meta['stock_snapshot'],   'stock_snapshot debe ser null sin inventario');
        $this->assertSame('catalogo', $meta['origen']);
    }

    // ─── Test 2 ───────────────────────────────────────────────────────────────
    // Producto con inventario → stock_rastreado=true, cart_disponible=true

    /** @test */
    public function construir_payload_con_inventario(): void
    {
        $cat  = $this->crearCategoria(self::CAT_MAPEADA);
        $prod = $this->crearProducto($cat, conInventario: true, stock: 10);

        $this->assertTrue($this->resolver->stockDisponible($prod, 1));

        $estado = $this->resolver->estadoCarrito($prod, 1);

        $this->assertTrue($estado['cart_disponible']);
        $this->assertTrue($estado['stock_rastreado']);
        $this->assertIsInt($estado['stock_actual']);
        $this->assertGreaterThanOrEqual(0, $estado['stock_actual']);
        $this->assertNull($estado['motivo_no_disponible']);
    }

    // ─── Test 3 ───────────────────────────────────────────────────────────────
    // Categoría fuera del mapa → CategoryUnavailableException

    /** @test */
    public function construir_payload_lanza_category_unavailable_si_categoria_no_mapeada(): void
    {
        $this->expectException(CategoryUnavailableException::class);

        $cat  = $this->crearCategoria(self::CAT_NO_MAPEADA);
        $prod = $this->crearProducto($cat, conInventario: true, stock: 5);
        $this->resolver->construirPayloadItem($prod, 1);
    }

    // ─── Test 4 ───────────────────────────────────────────────────────────────
    // Stock agotado → OutOfStockException

    /** @test */
    public function construir_payload_lanza_out_of_stock_con_stock_cero(): void
    {
        $this->expectException(OutOfStockException::class);

        // Usar categoría MAPEADA para que llegue a la validación de stock
        // (con categoría no mapeada se lanza CategoryUnavailableException antes)
        $cat  = $this->crearCategoria(self::CAT_MAPEADA);
        $prod = $this->crearProducto($cat, conInventario: true, stock: 10);

        // Sobreescribir la relación con stub de stock=0
        $inventarioStub               = new Inventario();
        $inventarioStub->stock_actual = 0;
        $prod->setRelation('inventario', $inventarioStub);

        $this->resolver->construirPayloadItem($prod, 1);
    }

    // ─── Test 5 ───────────────────────────────────────────────────────────────
    // parseReferencia formato válido → entero

    /** @test */
    public function parse_referencia_formato_valido_retorna_entero(): void
    {
        $this->assertSame(11, $this->resolver->parseReferencia('CAT-11'));
    }

    // ─── Test 6 ───────────────────────────────────────────────────────────────
    // parseReferencia formato inválido → null

    /** @test */
    public function parse_referencia_formato_invalido_retorna_null(): void
    {
        $this->assertNull($this->resolver->parseReferencia('PRODUCTO-11'));
    }

    // ─── Test 7 ───────────────────────────────────────────────────────────────
    // estadoCarrito sin inventario → cart_disponible=true (Política D4)

    /** @test */
    public function estado_carrito_disponible_pese_a_no_tener_inventario(): void
    {
        $cat    = $this->crearCategoria(self::CAT_MAPEADA);
        $prod   = $this->crearProducto($cat, conInventario: false);
        $estado = $this->resolver->estadoCarrito($prod, 1);

        $this->assertTrue($estado['cart_disponible'],
            'Producto sin inventario debe estar disponible: sin_inventario está permitido por config.');
        $this->assertFalse($estado['stock_rastreado'],
            'stock_rastreado debe ser false cuando no hay fila de inventario');
        $this->assertNull($estado['stock_actual']);
        $this->assertNull($estado['motivo_no_disponible']);
    }
}
