<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\SolicitudModulo;
use PHPUnit\Framework\Attributes\Test;

class PublicApiTest extends CartTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCategorias();
    }

    // ── GET /api/public/categorias ───────────────────────────────────────────

    #[Test]
    public function listar_categorias_devuelve_200_con_estructura_correcta(): void
    {
        $response = $this->getJson('/api/public/categorias');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['slug', 'nombre', 'descripcion', 'reglas_base'],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(5, $data);

        // Verifica que reglas_base contiene las claves esperadas para 'producto'
        $producto = collect($data)->firstWhere('slug', 'producto');
        $this->assertNotNull($producto);
        $this->assertArrayHasKey('cantidad_maxima', $producto['reglas_base']);
        $this->assertArrayHasKey('requiere_saldo', $producto['reglas_base']);
        $this->assertArrayHasKey('permite_pago_diferido', $producto['reglas_base']);

        // Los valores deben estar casteados al tipo correcto
        $this->assertIsInt($producto['reglas_base']['cantidad_maxima']);
        $this->assertIsBool($producto['reglas_base']['requiere_saldo']);
    }

    // ── POST /api/public/modulos/solicitud ───────────────────────────────────

    #[Test]
    public function crear_solicitud_valida_devuelve_201_con_folio(): void
    {
        $response = $this->postJson('/api/public/modulos/solicitud', [
            'nombre_modulo'          => 'Biblioteca Central',
            'tipo_modulo'            => 'biblioteca',
            'categorias_solicitadas' => ['prestamo', 'reserva'],
            'contacto_nombre'        => 'Ana García',
            'contacto_email'         => 'ana@universidad.edu',
            'descripcion'            => 'Módulo de préstamos y reservas bibliográficas.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['folio', 'mensaje', 'estado'])
            ->assertJsonPath('estado', 'pendiente');

        $folio = $response->json('folio');
        // Formato esperado: BIB-xxxxxx (3 letras, guion, 6 hex)
        $this->assertMatchesRegularExpression('/^[A-Z]{3}-[0-9A-F]{6}$/', $folio);
    }

    #[Test]
    public function crear_solicitud_con_email_invalido_devuelve_422(): void
    {
        $response = $this->postJson('/api/public/modulos/solicitud', [
            'nombre_modulo'          => 'Cafetería',
            'tipo_modulo'            => 'cafeteria',
            'categorias_solicitadas' => ['producto'],
            'contacto_nombre'        => 'Luis',
            'contacto_email'         => 'no-es-un-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contacto_email']);
    }

    #[Test]
    public function crear_solicitud_con_categoria_inexistente_devuelve_422(): void
    {
        $response = $this->postJson('/api/public/modulos/solicitud', [
            'nombre_modulo'          => 'Fotocopiadora',
            'tipo_modulo'            => 'copias',
            'categorias_solicitadas' => ['producto', 'slug_inexistente'],
            'contacto_nombre'        => 'Marta',
            'contacto_email'         => 'marta@uni.edu',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['categorias_solicitadas.1']);
    }

    #[Test]
    public function crear_solicitud_con_tipo_modulo_ya_pendiente_devuelve_409(): void
    {
        // Crear la solicitud inicial
        SolicitudModulo::create([
            'folio'                  => 'CAF-000001',
            'nombre_modulo'          => 'Cafetería A',
            'tipo_modulo'            => 'cafeteria',
            'categorias_solicitadas' => ['producto'],
            'contacto_nombre'        => 'Carlos',
            'contacto_email'         => 'carlos@uni.edu',
            'estado'                 => SolicitudModulo::ESTADO_PENDIENTE,
        ]);

        // Intentar crear otra del mismo tipo_modulo
        $response = $this->postJson('/api/public/modulos/solicitud', [
            'nombre_modulo'          => 'Cafetería B',
            'tipo_modulo'            => 'cafeteria',
            'categorias_solicitadas' => ['producto'],
            'contacto_nombre'        => 'Diana',
            'contacto_email'         => 'diana@uni.edu',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error', 'SOLICITUD_EXISTENTE');
    }

    // ── GET /api/public/modulos/solicitud/{folio}/estado ─────────────────────

    #[Test]
    public function consultar_estado_con_folio_existente_devuelve_200(): void
    {
        SolicitudModulo::create([
            'folio'                  => 'BIB-ABCDEF',
            'nombre_modulo'          => 'Biblioteca',
            'tipo_modulo'            => 'biblioteca',
            'categorias_solicitadas' => ['prestamo'],
            'contacto_nombre'        => 'Eva',
            'contacto_email'         => 'eva@uni.edu',
            'estado'                 => SolicitudModulo::ESTADO_PENDIENTE,
        ]);

        $response = $this->getJson('/api/public/modulos/solicitud/BIB-ABCDEF/estado');

        $response->assertOk()
            ->assertJsonStructure(['folio', 'estado', 'creada_at'])
            ->assertJsonPath('folio', 'BIB-ABCDEF')
            ->assertJsonPath('estado', 'pendiente');

        // No debe exponer datos sensibles
        $this->assertArrayNotHasKey('contacto_email', $response->json());
        $this->assertArrayNotHasKey('contacto_nombre', $response->json());
        $this->assertArrayNotHasKey('nombre_modulo', $response->json());
    }

    #[Test]
    public function consultar_estado_con_folio_inexistente_devuelve_404(): void
    {
        $response = $this->getJson('/api/public/modulos/solicitud/XXX-000000/estado');

        $response->assertNotFound();
    }

    #[Test]
    public function estado_rechazada_incluye_motivo_rechazo(): void
    {
        SolicitudModulo::create([
            'folio'                  => 'SOV-111111',
            'nombre_modulo'          => 'Souvenirs',
            'tipo_modulo'            => 'souvenirs',
            'categorias_solicitadas' => ['producto'],
            'contacto_nombre'        => 'Fer',
            'contacto_email'         => 'fer@uni.edu',
            'estado'                 => SolicitudModulo::ESTADO_RECHAZADA,
            'motivo_rechazo'         => 'Módulo duplicado con uno ya existente.',
        ]);

        $response = $this->getJson('/api/public/modulos/solicitud/SOV-111111/estado');

        $response->assertOk()
            ->assertJsonPath('estado', 'rechazada')
            ->assertJsonStructure(['motivo_rechazo']);
    }

    // ── Bitácora ─────────────────────────────────────────────────────────────

    #[Test]
    public function crear_solicitud_registra_en_bitacora(): void
    {
        $this->postJson('/api/public/modulos/solicitud', [
            'nombre_modulo'          => 'Asociación Estudiantil',
            'tipo_modulo'            => 'asociacion',
            'categorias_solicitadas' => ['ticket'],
            'contacto_nombre'        => 'Roberto',
            'contacto_email'         => 'roberto@uni.edu',
        ])->assertStatus(201);

        $this->assertDatabaseHas('cart_bitacora', [
            'accion' => Bitacora::ACCION_MODULO_SOLICITUD,
        ]);
    }
}
