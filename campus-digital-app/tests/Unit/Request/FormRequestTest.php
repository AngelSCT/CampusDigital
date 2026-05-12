<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class FormRequestTest extends TestCase
{
    private function validar(array $datos, array $reglas): bool
    {
        return Validator::make($datos, $reglas)->passes();
    }

    private function errores(array $datos, array $reglas): array
    {
        return Validator::make($datos, $reglas)->errors()->toArray();
    }

    private function reglasStoreUsuario(): array
    {
        return [
            'nombre'    => ['required', 'string', 'min:2', 'max:100'],
            'apellido'  => ['required', 'string', 'min:2', 'max:100'],
            'email'     => ['required', 'email', 'max:255'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'roles'     => ['nullable', 'array'],
        ];
    }

    private function reglasUpdateUsuario(): array
    {
        return [
            'nombre'   => ['required', 'string', 'min:2', 'max:100'],
            'apellido' => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'roles'    => ['nullable', 'array'],
        ];
    }

    private function reglasStoreRol(): array
    {
        return [
            'nombre'      => ['required', 'string', 'min:2', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo'      => ['boolean'],
            'permisos'    => ['nullable', 'array'],
        ];
    }

    private function reglasStorePermiso(): array
    {
        return [
            'clave'       => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo'      => ['boolean'],
        ];
    }

    private function reglasUpdatePerfil(): array
    {
        return [
            'nombre'           => ['required', 'string', 'min:2', 'max:100'],
            'apellido'         => ['required', 'string', 'min:2', 'max:100'],
            'email'            => ['required', 'email', 'max:255'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero'           => ['nullable', 'string', 'in:masculino,femenino,otro,prefiero_no_decir'],
            'direccion'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function test_store_usuario_pasa_con_datos_validos(): void
    {
        $datos = [
            'nombre'                => 'Juan',
            'apellido'              => 'Pérez',
            'email'                 => 'juan@ejemplo.com',
            'password'              => 'secreto123',
            'password_confirmation' => 'secreto123',
        ];
        $this->assertTrue($this->validar($datos, $this->reglasStoreUsuario()));
    }

    public function test_store_usuario_falla_sin_nombre(): void
    {
        $datos = [
            'apellido'              => 'Pérez',
            'email'                 => 'juan@ejemplo.com',
            'password'              => 'secreto123',
            'password_confirmation' => 'secreto123',
        ];
        $errores = $this->errores($datos, $this->reglasStoreUsuario());
        $this->assertArrayHasKey('nombre', $errores);
    }

    public function test_store_usuario_falla_con_email_invalido(): void
    {
        $datos = [
            'nombre'                => 'Juan',
            'apellido'              => 'Pérez',
            'email'                 => 'no-es-email',
            'password'              => 'secreto123',
            'password_confirmation' => 'secreto123',
        ];
        $errores = $this->errores($datos, $this->reglasStoreUsuario());
        $this->assertArrayHasKey('email', $errores);
    }

    public function test_store_usuario_falla_con_password_corto(): void
    {
        $datos = [
            'nombre'                => 'Juan',
            'apellido'              => 'Pérez',
            'email'                 => 'juan@ejemplo.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ];
        $errores = $this->errores($datos, $this->reglasStoreUsuario());
        $this->assertArrayHasKey('password', $errores);
    }

    public function test_store_usuario_falla_sin_confirmacion_password(): void
    {
        $datos = [
            'nombre'   => 'Juan',
            'apellido' => 'Pérez',
            'email'    => 'juan@ejemplo.com',
            'password' => 'secreto123',
        ];
        $this->assertFalse($this->validar($datos, $this->reglasStoreUsuario()));
    }

    public function test_store_usuario_permite_telefono_nulo(): void
    {
        $datos = [
            'nombre'                => 'Juan',
            'apellido'              => 'Pérez',
            'email'                 => 'juan@ejemplo.com',
            'password'              => 'secreto123',
            'password_confirmation' => 'secreto123',
            'telefono'              => null,
        ];
        $this->assertTrue($this->validar($datos, $this->reglasStoreUsuario()));
    }

    public function test_update_usuario_pasa_con_datos_validos(): void
    {
        $datos = [
            'nombre'   => 'María',
            'apellido' => 'González',
            'email'    => 'maria@ejemplo.com',
        ];
        $this->assertTrue($this->validar($datos, $this->reglasUpdateUsuario()));
    }

    public function test_update_usuario_falla_sin_apellido(): void
    {
        $datos = [
            'nombre' => 'María',
            'email'  => 'maria@ejemplo.com',
        ];
        $errores = $this->errores($datos, $this->reglasUpdateUsuario());
        $this->assertArrayHasKey('apellido', $errores);
    }

    public function test_update_usuario_falla_si_roles_no_es_arreglo(): void
    {
        $datos = [
            'nombre'   => 'María',
            'apellido' => 'González',
            'email'    => 'maria@ejemplo.com',
            'roles'    => 'no-es-arreglo',
        ];
        $errores = $this->errores($datos, $this->reglasUpdateUsuario());
        $this->assertArrayHasKey('roles', $errores);
    }

    public function test_store_rol_pasa_con_datos_validos(): void
    {
        $datos = [
            'nombre'      => 'editor',
            'descripcion' => 'Puede editar contenido',
            'activo'      => true,
        ];
        $this->assertTrue($this->validar($datos, $this->reglasStoreRol()));
    }

    public function test_store_rol_falla_sin_nombre(): void
    {
        $datos = ['descripcion' => 'Sin nombre', 'activo' => true];
        $errores = $this->errores($datos, $this->reglasStoreRol());
        $this->assertArrayHasKey('nombre', $errores);
    }

    public function test_store_rol_permite_descripcion_nula(): void
    {
        $datos = ['nombre' => 'moderador', 'descripcion' => null, 'activo' => true];
        $this->assertTrue($this->validar($datos, $this->reglasStoreRol()));
    }

    public function test_store_permiso_pasa_con_clave_valida(): void
    {
        $datos = ['clave' => 'ver_usuarios', 'activo' => true];
        $this->assertTrue($this->validar($datos, $this->reglasStorePermiso()));
    }

    public function test_store_permiso_falla_sin_clave(): void
    {
        $datos = ['descripcion' => 'Sin clave'];
        $errores = $this->errores($datos, $this->reglasStorePermiso());
        $this->assertArrayHasKey('clave', $errores);
    }

    public function test_store_permiso_falla_con_clave_con_espacios(): void
    {
        $datos = ['clave' => 'ver usuarios'];
        $errores = $this->errores($datos, $this->reglasStorePermiso());
        $this->assertArrayHasKey('clave', $errores);
    }

    public function test_store_permiso_falla_con_clave_en_mayusculas(): void
    {
        $datos = ['clave' => 'VER_USUARIOS'];
        $errores = $this->errores($datos, $this->reglasStorePermiso());
        $this->assertArrayHasKey('clave', $errores);
    }

    public function test_store_permiso_acepta_clave_con_numeros(): void
    {
        $datos = ['clave' => 'modulo2_ver'];
        $this->assertTrue($this->validar($datos, $this->reglasStorePermiso()));
    }

    public function test_update_perfil_pasa_con_datos_minimos(): void
    {
        $datos = [
            'nombre'   => 'Carlos',
            'apellido' => 'López',
            'email'    => 'carlos@ejemplo.com',
        ];
        $this->assertTrue($this->validar($datos, $this->reglasUpdatePerfil()));
    }

    public function test_update_perfil_falla_con_genero_invalido(): void
    {
        $datos = [
            'nombre'   => 'Carlos',
            'apellido' => 'López',
            'email'    => 'carlos@ejemplo.com',
            'genero'   => 'robot',
        ];
        $errores = $this->errores($datos, $this->reglasUpdatePerfil());
        $this->assertArrayHasKey('genero', $errores);
    }

    public function test_update_perfil_falla_con_fecha_nacimiento_futura(): void
    {
        $datos = [
            'nombre'           => 'Carlos',
            'apellido'         => 'López',
            'email'            => 'carlos@ejemplo.com',
            'fecha_nacimiento' => now()->addYear()->toDateString(),
        ];
        $errores = $this->errores($datos, $this->reglasUpdatePerfil());
        $this->assertArrayHasKey('fecha_nacimiento', $errores);
    }

    public function test_update_perfil_falla_con_direccion_muy_larga(): void
    {
        $datos = [
            'nombre'    => 'Carlos',
            'apellido'  => 'López',
            'email'     => 'carlos@ejemplo.com',
            'direccion' => str_repeat('x', 501),
        ];
        $errores = $this->errores($datos, $this->reglasUpdatePerfil());
        $this->assertArrayHasKey('direccion', $errores);
    }

    public function test_update_perfil_permite_campos_opcionales_nulos(): void
    {
        $datos = [
            'nombre'           => 'Carlos',
            'apellido'         => 'López',
            'email'            => 'carlos@ejemplo.com',
            'telefono'         => null,
            'fecha_nacimiento' => null,
            'genero'           => null,
            'direccion'        => null,
        ];
        $this->assertTrue($this->validar($datos, $this->reglasUpdatePerfil()));
    }
}