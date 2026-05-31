<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use App\Models\UsuarioSesion;
use App\Models\UsuarioPerfil;
use Mockery;


class UsuarioBloqueoTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function usuario_no_bloqueado_puede_acceder()
    {
        $usuario = new Usuario([
            'bloqueado'       => false,
            'bloqueado_hasta' => null,
        ]);

        $this->assertFalse($usuario->estaBloqueado());
    }

    /** @test */
    public function usuario_bloqueado_permanentemente_no_puede_acceder()
    {
        $usuario = new Usuario([
            'bloqueado'       => true,
            'bloqueado_hasta' => null,
        ]);

        $this->assertTrue($usuario->estaBloqueado());
    }

    /** @test */
    public function usuario_bloqueado_con_fecha_futura_sigue_bloqueado()
    {
        $usuario = new Usuario([
            'bloqueado'       => true,
            'bloqueado_hasta' => now()->addDay(),
        ]);

        $this->assertTrue($usuario->estaBloqueado());
    }

    /** @test */
    public function usuario_desbloqueado_tiene_flag_en_false()
    {
        $usuario = new Usuario(['bloqueado' => false]);

        $this->assertFalse($usuario->bloqueado);
    }

    /** @test */
    public function usuario_bloqueado_tiene_flag_en_true()
    {
        $usuario = new Usuario(['bloqueado' => true]);

        $this->assertTrue($usuario->bloqueado);
    }

    /** @test */
    public function bloqueado_hasta_se_castea_como_datetime()
    {
        $usuario = new Usuario([
            'bloqueado'       => true,
            'bloqueado_hasta' => '2099-12-31 23:59:59',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $usuario->bloqueado_hasta);
    }

    /** @test */
    public function usuario_sin_bloqueo_tiene_bloqueado_hasta_nulo()
    {
        $usuario = new Usuario([
            'bloqueado'       => false,
            'bloqueado_hasta' => null,
        ]);

        $this->assertNull($usuario->bloqueado_hasta);
    }
}



class AccesoBitacoraEventosTest extends TestCase
{
    /** @test */
    public function evento_login_exitoso_tiene_exito_en_true()
    {
        $registro = new AccesoBitacora([
            'evento' => 'login',
            'exito'  => true,
            'ip'     => '10.0.0.1',
        ]);

        $this->assertEquals('login', $registro->evento);
        $this->assertTrue($registro->exito);
    }

    /** @test */
    public function evento_login_fallido_tiene_exito_en_false()
    {
        $registro = new AccesoBitacora([
            'evento'          => 'login_fallido',
            'exito'           => false,
            'email_intentado' => 'fake@campus.edu',
            'detalle'         => 'Credenciales inválidas',
        ]);

        $this->assertFalse($registro->exito);
        $this->assertEquals('login_fallido', $registro->evento);
    }

    /** @test */
    public function evento_logout_se_almacena_con_datos_correctos()
    {
        $registro = new AccesoBitacora([
            'usuario_id' => 4,
            'evento'     => 'logout',
            'exito'      => true,
        ]);

        $this->assertEquals('logout', $registro->evento);
        $this->assertEquals(4, $registro->usuario_id);
    }

    /** @test */
    public function evento_cuenta_bloqueada_registra_ip()
    {
        $registro = new AccesoBitacora([
            'evento'  => 'cuenta_bloqueada',
            'exito'   => false,
            'ip'      => '172.16.5.20',
            'detalle' => 'Superó 5 intentos fallidos',
        ]);

        $this->assertEquals('172.16.5.20', $registro->ip);
        $this->assertFalse($registro->exito);
    }

    /** @test */
    public function meta_json_permite_guardar_datos_adicionales_del_acceso()
    {
        $registro = new AccesoBitacora();
        $registro->meta_json = [
            'intentos_previos' => 4,
            'navegador'        => 'Safari',
            'pais'             => 'MX',
        ];

        $this->assertIsArray($registro->meta_json);
        $this->assertEquals(4, $registro->meta_json['intentos_previos']);
        $this->assertEquals('MX', $registro->meta_json['pais']);
    }

    /** @test */
    public function acceso_sin_usuario_id_puede_registrarse_para_intentos_anonimos()
    {
        $registro = new AccesoBitacora([
            'usuario_id'      => null,
            'email_intentado' => 'noexiste@correo.com',
            'evento'          => 'login_fallido',
            'exito'           => false,
        ]);

        $this->assertNull($registro->usuario_id);
        $this->assertFalse($registro->exito);
    }

    /** @test */
    public function acceso_bitacora_tiene_relacion_belongs_to_usuario()
    {
        $registro = new AccesoBitacora();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $registro->usuario()
        );
    }
}


class RolPermisoAsignacionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function rol_administrador_puede_crearse_con_nombre_correcto()
    {
        $rol = new Rol([
            'nombre'      => 'administrador',
            'descripcion' => 'Acceso total al sistema',
            'activo'      => true,
        ]);

        $this->assertEquals('administrador', $rol->nombre);
        $this->assertTrue($rol->activo);
    }

    /** @test */
    public function rol_estudiante_puede_crearse_activo()
    {
        $rol = new Rol([
            'nombre'  => 'estudiante',
            'activo'  => true,
        ]);

        $this->assertTrue($rol->activo);
        $this->assertEquals('estudiante', $rol->nombre);
    }

    /** @test */
    public function rol_inactivo_no_permite_acceso_por_flag()
    {
        $rol = new Rol([
            'nombre'  => 'proveedor_area',
            'activo'  => false,
        ]);

        $this->assertFalse($rol->activo);
    }

    /** @test */
    public function permiso_con_clave_usuarios_ver_se_construye_bien()
    {
        $permiso = new Permiso([
            'clave'       => 'usuarios.ver',
            'descripcion' => 'Ver listado de usuarios',
            'activo'      => true,
        ]);

        $this->assertEquals('usuarios.ver', $permiso->clave);
        $this->assertTrue($permiso->activo);
    }

    /** @test */
    public function permiso_inactivo_tiene_flag_en_false()
    {
        $permiso = new Permiso([
            'clave'   => 'reportes.exportar',
            'activo'  => false,
        ]);

        $this->assertFalse($permiso->activo);
    }

    /** @test */
    public function has_role_retorna_false_para_rol_que_no_tiene()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $query = Mockery::mock();
        $query->shouldReceive('where')->with('nombre', 'administrador')->andReturnSelf();
        $query->shouldReceive('exists')->andReturn(false);

        $usuario->shouldReceive('roles')->andReturn($query);

        $this->assertFalse($usuario->hasRole('administrador'));
    }

    /** @test */
    public function has_permission_retorna_true_cuando_rol_tiene_permiso()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $innerQuery = Mockery::mock();
        $innerQuery->shouldReceive('where')->andReturnSelf();

        $query = Mockery::mock();
        $query->shouldReceive('whereHas')
            ->with('permisos', Mockery::any())
            ->andReturnSelf();
        $query->shouldReceive('exists')->andReturn(true);

        $usuario->shouldReceive('roles')->andReturn($query);

        $this->assertTrue($usuario->hasPermission('usuarios.ver'));
    }
}