<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiEstructuraRespuestaTest extends TestCase
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
    public function usuarios_retorna_estructura_paginada(): void
    {
        $response = $this->getJson('/api/v1/usuarios', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'first_page_url',
                     'last_page',
                     'per_page',
                     'total',
                 ]);
    }

    /** @test */
    public function tarjetas_retorna_estructura_paginada(): void
    {
        $response = $this->getJson('/api/v1/tarjetas', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'first_page_url',
                     'last_page',
                     'per_page',
                     'total',
                 ]);
    }

    /** @test */
    public function sesiones_retorna_estructura_paginada(): void
    {
        $response = $this->getJson('/api/v1/sesiones', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'per_page',
                     'total',
                 ]);
    }

    /** @test */
    public function roles_retorna_estructura_paginada(): void
    {
        $response = $this->getJson('/api/v1/roles', $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function usuario_roles_retorna_arreglo(): void
    {
        $response = $this->getJson('/api/v1/usuario-roles', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonIsArray();
    }

    /** @test */
    public function areas_retorna_estructura_paginada(): void
    {
        $response = $this->getJson('/api/v1/areas', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'total',
                 ]);
    }

    /** @test */
    public function permisos_retorna_estructura_paginada(): void
    {
        $response = $this->getJson('/api/v1/permisos', $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data',
                     'total',
                 ]);
    }

    /** @test */
    public function tarjeta_uid_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/tarjetas/uid/UID-QUE-NO-EXISTE-XYZ', $this->headers);

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Tarjeta no encontrada.']);
    }

    /** @test */
    public function usuario_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/usuarios/999999', $this->headers);

        $response->assertStatus(404);
    }

    /** @test */
    public function tarjeta_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/tarjetas/999999', $this->headers);

        $response->assertStatus(404);
    }
}