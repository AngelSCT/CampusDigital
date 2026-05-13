<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiAutenticacionTest extends TestCase
{
    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiKey = env('API_KEYS', '4f8a2b1c9d3e7f6a0b5c8d2e1f4a7b3c6d9e2f5a8b1c4d7e0f3a6b9c2d5e8f1a');
    }

    /** @test */
    public function rechaza_peticion_sin_api_key_en_usuarios(): void
    {
        $response = $this->getJson('/api/v1/usuarios');
        $response->assertStatus(401);
    }

    /** @test */
    public function rechaza_peticion_sin_api_key_en_tarjetas(): void
    {
        $response = $this->getJson('/api/v1/tarjetas');
        $response->assertStatus(401);
    }

    /** @test */
    public function rechaza_peticion_sin_api_key_en_sesiones(): void
    {
        $response = $this->getJson('/api/v1/sesiones');
        $response->assertStatus(401);
    }

    /** @test */
    public function rechaza_peticion_sin_api_key_en_roles(): void
    {
        $response = $this->getJson('/api/v1/roles');
        $response->assertStatus(401);
    }

    /** @test */
    public function rechaza_api_key_incorrecta(): void
    {
        $response = $this->getJson('/api/v1/usuarios', [
            'X-API-KEY' => 'clave-incorrecta-123',
        ]);
        $response->assertStatus(401);
    }

    /** @test */
    public function acepta_peticion_con_api_key_valida_en_usuarios(): void
    {
        $response = $this->getJson('/api/v1/usuarios', [
            'X-API-KEY' => $this->apiKey,
        ]);
        $response->assertStatus(200);
    }

    /** @test */
    public function acepta_peticion_con_api_key_valida_en_tarjetas(): void
    {
        $response = $this->getJson('/api/v1/tarjetas', [
            'X-API-KEY' => $this->apiKey,
        ]);
        $response->assertStatus(200);
    }

    /** @test */
    public function acepta_peticion_con_api_key_valida_en_sesiones(): void
    {
        $response = $this->getJson('/api/v1/sesiones', [
            'X-API-KEY' => $this->apiKey,
        ]);
        $response->assertStatus(200);
    }

    /** @test */
    public function acepta_peticion_con_api_key_valida_en_roles(): void
    {
        $response = $this->getJson('/api/v1/roles', [
            'X-API-KEY' => $this->apiKey,
        ]);
        $response->assertStatus(200);
    }

    /** @test */
    public function acepta_peticion_con_api_key_valida_en_permisos(): void
    {
        $response = $this->getJson('/api/v1/permisos', [
            'X-API-KEY' => $this->apiKey,
        ]);
        $response->assertStatus(200);
    }
}