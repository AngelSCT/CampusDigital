<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\ModuleTokenService;
use PHPUnit\Framework\Attributes\Test;

class CartPrivateApiTest extends CartTestCase
{
    private ModuloCliente $biblioteca;
    private string $tokenBiblioteca;
    private ModuleTokenService $tokenService;
    private CarritoService $carritoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        $this->tokenService   = new ModuleTokenService();
        $this->carritoService = new CarritoService();

        // Módulo Biblioteca: puede usar 'prestamo' y 'reserva'.
        $this->biblioteca = $this->createTestModuloCliente([
            'slug'                   => 'biblioteca',
            'tipo_modulo'            => 'biblioteca',
            'categorias_autorizadas' => ['prestamo', 'reserva'],
        ]);

        $par = $this->tokenService->issuePair($this->biblioteca);
        $this->tokenBiblioteca = $par['access_token'];
    }

    // ─── Helper: crea carrito ───────────────────────────────────────────────

    private function crearCarrito(string $usuarioRef = 'MAT-001'): Carrito
    {
        return $this->carritoService->crear($this->biblioteca, [
            'usuario_ref'      => $usuarioRef,
            'requiere_saldo'   => false,
            'expira_en_minutos' => 60,
        ]);
    }

    private function agregarItemPrestamo(Carrito $carrito, int $cantidad = 1): ItemCarrito
    {
        $categoria = Categoria::where('slug', 'prestamo')->first();
        return ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $categoria->id,
            'referencia_externa' => 'ISBN-001',
            'nombre'             => 'Libro Test',
            'precio_unitario'    => '0.00',
            'cantidad'           => $cantidad,
            'added_at'           => now(),
        ]);
    }

    // ─── Casos 11-25: Reglas de negocio ─────────────────────────────────────

    #[Test]
    public function crear_carrito_valido_devuelve_201(): void
    {
        $this->withToken($this->tokenBiblioteca)
            ->postJson('/api/cart/carritos', [
                'usuario_ref'      => 'MAT-2024-001',
                'requiere_saldo'   => false,
                'expira_en_minutos' => 30,
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['carrito_uuid', 'estado', 'modulo', 'total', 'items', 'expira_at'])
            ->assertJsonPath('estado', Carrito::ESTADO_ABIERTO);
    }

    #[Test]
    public function agregar_item_dentro_de_limites_devuelve_201(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'ISBN-XYZ',
                'nombre'             => 'Cálculo — Stewart',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
                'duracion_horas'     => 48,
            ])
            ->assertStatus(201)
            ->assertJsonPath('accion', 'creado');
    }

    #[Test]
    public function agregar_item_que_excede_cantidad_maxima_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();
        // 'prestamo' tiene cantidad_maxima=5; enviamos 10.
        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'ISBN-ABC',
                'nombre'             => 'Libro X',
                'precio_unitario'    => 0.00,
                'cantidad'           => 10,
            ])
            ->assertUnprocessable();
    }

    #[Test]
    public function agregar_item_que_excede_duracion_maxima_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();
        // 'prestamo' tiene duracion_maxima_horas=168; enviamos 200.
        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'ISBN-DUR',
                'nombre'             => 'Libro Largo',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
                'duracion_horas'     => 200,
            ])
            ->assertUnprocessable();
    }

    #[Test]
    public function agregar_item_de_categoria_no_autorizada_devuelve_403(): void
    {
        $carrito = $this->crearCarrito();
        // Biblioteca no tiene 'producto' en sus categorías autorizadas.
        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'producto',
                'referencia_externa' => 'PROD-001',
                'nombre'             => 'Café',
                'precio_unitario'    => 30.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(403)
            ->assertJsonPath('error', 'SCOPE_DENIED');
    }

    #[Test]
    public function agregar_item_duplicado_incrementa_cantidad_cambio_3(): void
    {
        $carrito = $this->crearCarrito();
        $payload = [
            'categoria_slug'     => 'prestamo',
            'referencia_externa' => 'ISBN-DUP',
            'nombre'             => 'Álgebra Lineal',
            'precio_unitario'    => 0.00,
            'cantidad'           => 2,
        ];

        // Primera inserción
        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", $payload)
            ->assertStatus(201)
            ->assertJsonPath('accion', 'creado')
            ->assertJsonPath('cantidad_actual', 2);

        // Segunda inserción: debe incrementar a 4
        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", $payload)
            ->assertOk()
            ->assertJsonPath('accion', 'incrementado')
            ->assertJsonPath('cantidad_actual', 4);
    }

    #[Test]
    public function incremento_que_excede_cantidad_maxima_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();
        $payload = [
            'categoria_slug'     => 'prestamo',
            'referencia_externa' => 'ISBN-MAX',
            'nombre'             => 'Física',
            'precio_unitario'    => 0.00,
            'cantidad'           => 4,  // prestamo cantidad_maxima=5
        ];

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", $payload)
            ->assertStatus(201);

        // Intentar sumar 4 más → total 8 > 5
        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", $payload)
            ->assertUnprocessable();
    }

    #[Test]
    public function remover_item_existente_devuelve_200(): void
    {
        $carrito = $this->crearCarrito();
        $item    = $this->agregarItemPrestamo($carrito);

        $this->withToken($this->tokenBiblioteca)
            ->deleteJson("/api/cart/carritos/{$carrito->uuid}/items/{$item->id}")
            ->assertOk()
            ->assertJsonStructure(['mensaje', 'total_actualizado']);

        $this->assertEquals(ItemCarrito::ESTADO_REMOVIDO, $item->fresh()->estado_item);
    }

    #[Test]
    public function checkout_de_carrito_vacio_devuelve_422(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertUnprocessable();
    }

    #[Test]
    public function checkout_exitoso_con_requiere_saldo_false(): void
    {
        $carrito = $this->crearCarrito();
        $this->agregarItemPrestamo($carrito);

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO)
            ->assertJsonStructure(['carrito_uuid', 'estado', 'total', 'confirmed_at']);
    }

    #[Test]
    public function checkout_de_carrito_ya_confirmado_devuelve_409(): void
    {
        $carrito = $this->crearCarrito();
        $this->agregarItemPrestamo($carrito);
        $carrito->update(['estado' => Carrito::ESTADO_CONFIRMADO, 'confirmed_at' => now()]);

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(409);
    }

    #[Test]
    public function cancelar_carrito_abierto_devuelve_200(): void
    {
        $carrito = $this->crearCarrito();

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/cancelar")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CANCELADO);
    }

    #[Test]
    public function devolver_item_en_categoria_sin_devolucion_devuelve_422(): void
    {
        // 'producto' no permite devolución
        $carrito  = $this->crearCarrito();
        // Agregar ítem de 'producto' directamente (el módulo biblioteca no tiene 'producto',
        // pero podemos crear el ítem directo en BD para probar la regla de negocio).
        $catProd = Categoria::where('slug', 'producto')->first();
        $item = ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $catProd->id,
            'referencia_externa' => 'PROD-X',
            'nombre'             => 'Café',
            'precio_unitario'    => '30.00',
            'cantidad'           => 1,
            'added_at'           => now(),
        ]);

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items/{$item->id}/devolver")
            ->assertUnprocessable()
            ->assertJsonPath('error', 'BUSINESS_RULE_VIOLATION');
    }

    #[Test]
    public function devolver_item_en_categoria_con_devolucion_devuelve_200(): void
    {
        // 'prestamo' sí permite devolución
        $carrito = $this->crearCarrito();
        $item    = $this->agregarItemPrestamo($carrito);

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items/{$item->id}/devolver")
            ->assertOk()
            ->assertJsonPath('estado_item', ItemCarrito::ESTADO_DEVUELTO);
    }

    #[Test]
    public function carrito_expirado_no_acepta_operaciones(): void
    {
        $carrito = $this->crearCarrito();
        // Marcar como expirado en el pasado
        $carrito->update(['expira_at' => now()->subHour()]);

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/items", [
                'categoria_slug'     => 'prestamo',
                'referencia_externa' => 'ISBN-EXP',
                'nombre'             => 'Libro expirado',
                'precio_unitario'    => 0.00,
                'cantidad'           => 1,
            ])
            ->assertStatus(409);
    }

    // ─── Sección 6.3: Autorización por módulo ────────────────────────────────

    #[Test]
    public function modulo_cafeteria_no_puede_leer_carrito_de_biblioteca(): void
    {
        // Crear carrito bajo biblioteca
        $carrito = $this->crearCarrito();

        // Crear módulo Cafetería y obtener su token
        $cafeteria = $this->createTestModuloCliente([
            'slug'                   => 'cafeteria',
            'tipo_modulo'            => 'cafeteria',
            'categorias_autorizadas' => ['producto'],
        ]);
        $parCafe    = $this->tokenService->issuePair($cafeteria);
        $tokenCafe  = $parCafe['access_token'];

        // Cafetería intenta leer el carrito de Biblioteca → 403
        $this->withToken($tokenCafe)
            ->getJson("/api/cart/carritos/{$carrito->uuid}")
            ->assertStatus(403)
            ->assertJsonPath('error', 'OWNERSHIP_DENIED');
    }

    #[Test]
    public function modulo_valido_accede_a_su_propio_historico(): void
    {
        $this->crearCarrito('MAT-H01');
        $this->crearCarrito('MAT-H02');

        $this->withToken($this->tokenBiblioteca)
            ->getJson('/api/cart/historico')
            ->assertOk()
            ->assertJsonStructure(['data', 'total', 'per_page']);
    }

    #[Test]
    public function bitacora_registra_item_agregado_y_carrito_checkout(): void
    {
        $carrito = $this->crearCarrito();
        $this->agregarItemPrestamo($carrito);

        $this->withToken($this->tokenBiblioteca)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout");

        $this->assertDatabaseHas('cart_bitacora', ['accion' => Bitacora::ACCION_CARRITO_CHECKOUT]);
        $this->assertDatabaseHas('cart_bitacora', ['accion' => Bitacora::ACCION_CARRITO_CREADO]);
    }
}
