<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class BitacoraApiTest extends TestCase
{
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = [
            'X-API-KEY' => env('API_KEYS', '4f8a2b1c9d3e7f6a0b5c8d2e1f4a7b3c6d9e2f5a8b1c4d7e0f3a6b9c2d5e8f1a'),
        ];
    }

    /** @test */
    public function bitacora_accesos_responde_200(): void
    {
        $response = $this->getJson('/api/v1/bitacora/accesos', $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function bitacora_accesos_retorna_estructura_correcta(): void
    {
        $response = $this->getJson('/api/v1/bitacora/accesos', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'total',
                 ]);
    }

    /** @test */
    public function bitacora_actividad_responde_200(): void
    {
        $response = $this->getJson('/api/v1/bitacora/actividad', $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function bitacora_actividad_retorna_estructura_correcta(): void
    {
        $response = $this->getJson('/api/v1/bitacora/actividad', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'total',
                 ]);
    }

    /** @test */
    public function bitacora_acceso_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/bitacora/accesos/999999', $this->headers);

        $response->assertStatus(404);
    }

    /** @test */
    public function bitacora_actividad_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/bitacora/actividad/999999', $this->headers);

        $response->assertStatus(404);
    }

    /** @test */
    public function bitacora_accesos_sin_api_key_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/bitacora/accesos');

        $response->assertStatus(401);
    }

    /** @test */
    public function bitacora_actividad_sin_api_key_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/bitacora/actividad');

        $response->assertStatus(401);
    }

    /** @test */
    public function sesiones_filtra_por_usuario_id(): void
    {
        $response = $this->getJson('/api/v1/sesiones?usuario_id=999999', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonFragment(['total' => 0]);
    }

    /** @test */
    public function sesiones_filtra_por_activa_true(): void
    {
        $response = $this->getJson('/api/v1/sesiones?activa=true', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure(['current_page', 'data', 'total']);
    }
}