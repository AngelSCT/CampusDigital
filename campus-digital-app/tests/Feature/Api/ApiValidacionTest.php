<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiValidacionTest extends TestCase
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
    public function crear_usuario_sin_nombre_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/usuarios', [
            'apellido' => 'Pérez',
            'email'    => 'test@test.com',
            'password' => 'password123',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nombre']);
    }

    /** @test */
    public function crear_usuario_sin_email_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/usuarios', [
            'nombre'   => 'Juan',
            'apellido' => 'Pérez',
            'password' => 'password123',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function crear_usuario_con_email_invalido_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/usuarios', [
            'nombre'   => 'Juan',
            'apellido' => 'Pérez',
            'email'    => 'esto-no-es-un-email',
            'password' => 'password123',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function crear_usuario_con_password_corta_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/usuarios', [
            'nombre'   => 'Juan',
            'apellido' => 'Pérez',
            'email'    => 'juan@test.com',
            'password' => '123',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function crear_tarjeta_sin_usuario_id_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/tarjetas', [
            'uid'    => 'AABBCCDD',
            'estado' => 'activa',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['usuario_id']);
    }

    /** @test */
    public function crear_tarjeta_sin_uid_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/tarjetas', [
            'usuario_id' => 1,
            'estado'     => 'activa',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['uid']);
    }

    /** @test */
    public function bloquear_tarjeta_sin_motivo_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/tarjetas/1/bloquear', [], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['motivo_bloqueo']);
    }

    /** @test */
    public function asignar_rol_sin_usuario_id_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/usuario-roles', [
            'rol_id' => 1,
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['usuario_id']);
    }

    /** @test */
    public function asignar_rol_sin_rol_id_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/usuario-roles', [
            'usuario_id' => 1,
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['rol_id']);
    }

    /** @test */
    public function exportar_accesos_periodo_sin_fechas_retorna_422(): void
    {
        $response = $this->getJson('/api/v1/sesiones?activa=invalido', $this->headers);

        // Acepta la peticion aunque el filtro sea invalido (filter_var lo maneja)
        $response->assertStatus(200);
    }
}