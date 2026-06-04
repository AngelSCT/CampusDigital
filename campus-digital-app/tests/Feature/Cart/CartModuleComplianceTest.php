<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\LocalSaldoClient;
use App\Modules\Cart\Services\ModuleTokenService;
use App\Modules\Cart\Services\NullPedidoCreator;
use App\Modules\Cart\Services\SaldoClient;
use App\Modules\Cart\Services\SaldoConfirmed;
use App\Modules\Cart\Services\SaldoInsufficientFunds;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

/**
 * CartModuleComplianceTest — 12 tests de cumplimiento del módulo Carrito.
 *
 * Verifica el contrato completo de la API /api/cart/* incluyendo:
 * - Ciclo de vida del carrito (crear, agregar, eliminar, checkout)
 * - Reglas de precio por categoría
 * - Integración con Saldo (suficiente, insuficiente, sin saldo)
 * - Protección contra doble checkout
 * - Endpoint de comprobante (autenticado, seguridad)
 */
class CartModuleComplianceTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private \App\Models\Cart\ModuloCliente $moduloComprobantes;
    private string $token;
    private string $tokenComprobantes;
    private CarritoService $carritoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();
        $this->seedReglasDePrecios();

        // Tablas de Saldo (necesarias para Tests 6 y 7 con LocalSaldoClient)
        $this->crearTablasSaldoParaTests();

        $tokenService        = new ModuleTokenService();
        $this->carritoService = new CarritoService();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'compliance-test',
            'tipo_modulo'            => 'catalogo',
            'categorias_autorizadas' => ['prestamo', 'producto', 'copias', 'sin_reglas'],
        ]);
        $par          = $tokenService->issuePair($this->modulo);
        $this->token  = $par['access_token'];

        // Módulo para tests de comprobante
        $this->moduloComprobantes = $this->createTestModuloCliente([
            'slug'                   => 'comprobantes-compliance',
            'tipo_modulo'            => 'comprobantes',
            'categorias_autorizadas' => [],
        ]);
        $parC                     = $tokenService->issuePair($this->moduloComprobantes);
        $this->tokenComprobantes  = $parC['access_token'];
    }

    // ─── TEST 1: Crear carrito ────────────────────────────────────────────────

    #[Test]
    public function test1_crear_carrito_devuelve_201_con_uuid_y_estado_abierto(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/cart/carritos', [
                'usuario_ref'       => '1',
                'requiere_saldo'    => false,
                'expira_en_minutos' => 120,
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['carrito_uuid', 'estado', 'total'])
            ->assertJsonPath('estado', Carrito::ESTADO_ABIERTO)
            ->assertJsonPath('total', '0.00');
    }

    // ─── TEST 2: Agregar ítem y recalcular total ──────────────────────────────

    #[Test]
    public function test2_agregar_item_recalcula_total(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'CAT-7',
                'nombre'             => 'Café Americano',
                'precio_unitario'    => 18.00,
                'cantidad'           => 2,
            ])
            ->assertStatus(201)
            ->assertJsonPath('total_actualizado', '36.00');

        $this->assertDatabaseHas('cart_carritos', [
            'uuid'  => (string) $carrito->uuid,
            'total' => '36.00',
        ]);
    }

    // ─── TEST 3: Eliminar ítem y recalcular total ─────────────────────────────

    #[Test]
    public function test3_eliminar_item_recalcula_total(): void
    {
        $carrito = $this->crearCarrito();

        // Ítem 1
        $res1 = $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'CAT-1',
                'nombre'             => 'Producto A',
                'precio_unitario'    => 10.00,
                'cantidad'           => 1,
            ]);
        $item1Id = $res1->json('item_id');

        // Ítem 2
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'CAT-2',
                'nombre'             => 'Producto B',
                'precio_unitario'    => 5.00,
                'cantidad'           => 1,
            ]);

        $this->assertDatabaseHas('cart_carritos', ['uuid' => (string) $carrito->uuid, 'total' => '15.00']);

        // Eliminar ítem 1
        $this->withToken($this->token)
            ->deleteJson("/api/cart/carritos/{$carrito->uuid}/items/{$item1Id}")
            ->assertOk()
            ->assertJsonPath('total_actualizado', '5.00');

        $this->assertDatabaseHas('cart_carritos', ['uuid' => (string) $carrito->uuid, 'total' => '5.00']);
    }

    // ─── TEST 4: Regla precio cero ────────────────────────────────────────────

    #[Test]
    public function test4_precio_cero_bloqueado_en_categoria_pagada_y_permitido_en_gratuita(): void
    {
        $carrito = $this->crearCarrito();

        // copias: precio cero bloqueado
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'copias',
                'referencia_externa' => 'CAT-X',
                'nombre'             => 'Copia',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'BUSINESS_RULE_VIOLATION');

        // prestamo: precio cero permitido
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'LIBRO-001',
                'nombre'             => 'Libro gratis',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(201);
    }

    // ─── TEST 5: Checkout sin saldo (requiere_saldo=false) ────────────────────

    #[Test]
    public function test5_checkout_sin_saldo_confirma_sin_llamar_saldo_client(): void
    {
        // Stub que registra si fue llamado
        $saldoLlamado = false;
        $stub = new class($saldoLlamado) extends SaldoClient {
            public bool $llamado = false;
            public function reservar(string $u, float $m, string $c, string $s, string $co): \App\Modules\Cart\Services\SaldoResult
            {
                $this->llamado = true;
                return new SaldoInsufficientFunds();
            }
            public function confirmar(string $r, string $c): bool { $this->llamado = true; return true; }
            public function liberar(string $r): bool { $this->llamado = true; return true; }
            public function cargoForzoso(string $u, float $m, string $c, string $co, ?string $e = null): \App\Modules\Cart\Services\SaldoResult
            {
                $this->llamado = true;
                return new \App\Modules\Cart\Services\CargoForzosoCobrado();
            }
        };
        $this->app->instance(SaldoClient::class, $stub);

        $carrito = $this->crearCarritoConItem(requiereSaldo: false);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO);

        $this->assertFalse($stub->llamado, 'SaldoClient NO debe ser llamado cuando requiere_saldo=false');
        $this->assertNotNull($carrito->fresh()->confirmed_at);
    }

    // ─── TEST 6: Checkout con saldo suficiente ────────────────────────────────

    #[Test]
    public function test6_checkout_con_saldo_suficiente_confirma_y_decrementa_saldo(): void
    {
        // Usa LocalSaldoClient con SaldoMonedero real en SQLite
        config(['cart.saldo.local_mode' => true]);
        $this->app->bind(SaldoClient::class, LocalSaldoClient::class);

        // Crear monedero con saldo suficiente
        $usuarioId = 42;
        \App\Models\SaldoMonedero::create([
            'usuario_id'       => $usuarioId,
            'saldo_disponible' => 100.00,
            'saldo_retenido'   => 0.00,
        ]);

        $carrito = $this->crearCarritoConItem(requiereSaldo: true, usuarioRef: (string) $usuarioId);
        $carrito->update(['total' => '18.00']); // producto $18

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO);

        // Verificar que el saldo decrementó
        $monedero = \App\Models\SaldoMonedero::where('usuario_id', $usuarioId)->first();
        $this->assertLessThan(100.00, (float) $monedero->saldo_disponible,
            'saldo_disponible debe haber decrementado tras el checkout con saldo');
    }

    // ─── TEST 7: Checkout con saldo insuficiente ──────────────────────────────

    #[Test]
    public function test7_checkout_con_saldo_insuficiente_devuelve_402(): void
    {
        config(['cart.saldo.local_mode' => true]);
        $this->app->bind(SaldoClient::class, LocalSaldoClient::class);

        $usuarioId = 99;
        \App\Models\SaldoMonedero::create([
            'usuario_id'       => $usuarioId,
            'saldo_disponible' => 5.00,  // menos que el precio del ítem
            'saldo_retenido'   => 0.00,
        ]);

        $carrito = $this->crearCarritoConItem(requiereSaldo: true, usuarioRef: (string) $usuarioId);
        $carrito->update(['total' => '18.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(402)
            ->assertJsonPath('error', 'SALDO_INSUFICIENTE');

        // Carrito debe seguir abierto
        $this->assertEquals(Carrito::ESTADO_ABIERTO, $carrito->fresh()->estado);
    }

    // ─── TEST 8: Doble checkout bloqueado ─────────────────────────────────────

    #[Test]
    public function test8_doble_checkout_bloqueado_por_estado_procesando(): void
    {
        $carrito = $this->crearCarritoConItem();

        // Simular carrito en procesando_checkout
        $carrito->update(['estado' => Carrito::ESTADO_PROCESANDO_CHECKOUT]);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(409)
            ->assertJsonPath('error', 'CART_STATE_ERROR');
    }

    // ─── TEST 9: Histórico paginado ───────────────────────────────────────────

    #[Test]
    public function test9_historico_retorna_carritos_del_modulo(): void
    {
        $usuarioRef = 'USR-HIST-99';

        // Crear 3 carritos confirmados
        for ($i = 0; $i < 3; $i++) {
            $carrito = $this->carritoService->crear($this->modulo, [
                'usuario_ref'    => $usuarioRef,
                'requiere_saldo' => false,
            ]);
            $carrito->update(['estado' => Carrito::ESTADO_CONFIRMADO, 'confirmed_at' => now()]);
        }

        $response = $this->withToken($this->token)
            ->getJson('/api/cart/historico')
            ->assertOk()
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);

        // Todos pertenecen al módulo (el historial devuelve 'uuid' del modelo Carrito)
        $uuids = collect($response->json('data'))->pluck('uuid')->filter()->all();
        $this->assertGreaterThanOrEqual(3, count($uuids));
        foreach ($uuids as $uuid) {
            $this->assertDatabaseHas('cart_carritos', ['uuid' => $uuid]);
        }
    }

    // ─── TEST 10: Comprobante de carrito confirmado ───────────────────────────

    #[Test]
    public function test10_comprobante_de_carrito_confirmado_devuelve_200(): void
    {
        $carrito = $this->crearCarritoConItem();
        $carrito->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO,
            'confirmed_at' => now(),
            'total'        => '18.00',
        ]);

        $response = $this->withToken($this->tokenComprobantes)
            ->getJson("/api/cart/comprobantes/{$carrito->uuid}")
            ->assertOk()
            ->assertJsonStructure(['carrito_uuid', 'fecha_confirmacion', 'usuario_ref', 'modulo', 'items', 'total', 'estado'])
            ->assertJsonPath('carrito_uuid', (string) $carrito->uuid)
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO);

        // Verificar que NO expone IDs de FK internos ni metadata cruda
        $json = $response->json();
        $this->assertArrayNotHasKey('modulo_id', $json, 'No debe exponer FK modulo_id');
        $this->assertArrayNotHasKey('metadata', $json, 'No debe exponer metadata interna del carrito');
    }

    // ─── TEST 11: Comprobante de carrito abierto → 404 ───────────────────────

    #[Test]
    public function test11_comprobante_de_carrito_abierto_devuelve_404(): void
    {
        $carrito = $this->crearCarrito();
        // Estado = 'abierto'

        $this->withToken($this->tokenComprobantes)
            ->getJson("/api/cart/comprobantes/{$carrito->uuid}")
            ->assertNotFound()
            ->assertJsonPath('error', 'COMPROBANTE_NO_ENCONTRADO');
    }

    // ─── TEST 12: Token inválido → 401 ────────────────────────────────────────

    #[Test]
    public function test12_token_invalido_en_comprobante_devuelve_401(): void
    {
        $carrito = $this->crearCarrito();
        $carrito->update(['estado' => Carrito::ESTADO_CONFIRMADO]);

        $this->withToken('token-invalido-obviamente')
            ->getJson("/api/cart/comprobantes/{$carrito->uuid}")
            ->assertStatus(401);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearCarrito(bool $requiereSaldo = false, string $usuarioRef = '1'): Carrito
    {
        return $this->carritoService->crear($this->modulo, [
            'usuario_ref'    => $usuarioRef,
            'requiere_saldo' => $requiereSaldo,
        ]);
    }

    private function crearCarritoConItem(bool $requiereSaldo = false, string $usuarioRef = '1'): Carrito
    {
        $carrito = $this->crearCarrito($requiereSaldo, $usuarioRef);

        $cat = Categoria::where('slug', 'producto')->first();
        ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $cat->id,
            'referencia_externa' => 'CAT-7',
            'nombre'             => 'Café Americano',
            'precio_unitario'    => 18.00,
            'cantidad'           => 1,
            'added_at'           => now(),
        ]);
        $carrito->update(['total' => '18.00']);

        return $carrito->fresh(['modulo']);
    }

    private function seedReglasDePrecios(): void
    {
        $reglas = [
            'prestamo' => ['permite_precio_cero' => 'true'],
            'producto' => ['permite_precio_cero' => 'false', 'precio_minimo' => '0.01'],
            'copias'   => ['permite_precio_cero' => 'false', 'precio_minimo' => '0.01'],
        ];

        // sin_reglas: sin reglas (default seguro del servicio)
        \App\Models\Cart\Categoria::updateOrCreate(['slug' => 'sin_reglas'], ['nombre' => 'Sin Reglas', 'activa' => true]);
        \App\Models\Cart\Categoria::updateOrCreate(['slug' => 'copias'], ['nombre' => 'Copias', 'activa' => true]);

        foreach ($reglas as $slug => $reglasMap) {
            $cat = \App\Models\Cart\Categoria::where('slug', $slug)->first();
            if (!$cat) continue;
            foreach ($reglasMap as $clave => $valor) {
                \App\Models\Cart\ReglaCategoria::updateOrCreate(
                    ['categoria_id' => $cat->id, 'clave' => $clave],
                    ['valor' => $valor, 'tipo_dato' => $clave === 'permite_precio_cero' ? 'bool' : 'string']
                );
            }
        }
    }

    /**
     * Crea tablas mínimas de saldo para Tests 6 y 7 (SQLite in-memory).
     * Semánticamente equivalente a las migraciones reales; omite FK y triggers PostgreSQL.
     */
    private function crearTablasSaldoParaTests(): void
    {
        if (!Schema::hasTable('saldo_monedero')) {
            Schema::create('saldo_monedero', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuario_id')->unique();
                $table->decimal('saldo_disponible', 10, 2)->default(0.00);
                $table->decimal('saldo_retenido', 10, 2)->default(0.00);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saldo_movimiento')) {
            Schema::create('saldo_movimiento', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedBigInteger('saldo_monedero_id');
                $table->string('tipo', 20)->default('cargo');
                $table->decimal('monto', 10, 2);
                $table->decimal('saldo_anterior', 10, 2);
                $table->decimal('saldo_nuevo', 10, 2);
                $table->string('modulo', 50)->nullable();
                $table->string('concepto', 255)->nullable();
                $table->unsignedBigInteger('operador_usuario_id')->nullable();
                $table->unsignedBigInteger('tarjeta_lectura_id')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }
}
