<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Collection;
use Mockery;

class UsuarioTest extends TestCase
{
    use WithFaker;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    

    /** @test */
    public function retorna_nombre_completo_correctamente()
    {
        $usuario = new Usuario([
            'nombre'   => 'Juan',
            'apellido' => 'Pérez',
        ]);

        $this->assertEquals('Juan Pérez', $usuario->nombre_completo);
    }

    /** @test */
    public function nombre_completo_no_tiene_espacios_extras_si_apellido_esta_vacio()
    {
        $usuario = new Usuario([
            'nombre'   => 'Ana',
            'apellido' => '',
        ]);

        $this->assertEquals('Ana', $usuario->nombre_completo);
    }

    /** @test */
    public function get_auth_password_retorna_password_hash()
    {
        $hash = Hash::make('secreto123');

        $usuario = new Usuario();
        $usuario->password_hash = $hash;

        $this->assertEquals($hash, $usuario->getAuthPassword());
    }

    /** @test */
    public function has_verified_email_retorna_valor_de_email_verificado()
    {
        $usuarioVerificado = new Usuario(['email_verificado' => true]);
        $usuarioNoVerificado = new Usuario(['email_verificado' => false]);

        $this->assertTrue($usuarioVerificado->hasVerifiedEmail());
        $this->assertFalse($usuarioNoVerificado->hasVerifiedEmail());
    }

    /** @test */
    public function get_email_for_verification_retorna_email()
    {
        $usuario = new Usuario(['email' => 'juan@campus.edu']);

        $this->assertEquals('juan@campus.edu', $usuario->getEmailForVerification());
    }


    /** @test */
    public function esta_bloqueado_retorna_false_si_bloqueado_es_false()
    {
        $usuario = new Usuario(['bloqueado' => false]);

        $this->assertFalse($usuario->estaBloqueado());
    }

    /** @test */
    public function esta_bloqueado_retorna_true_si_bloqueado_y_sin_fecha_limite()
    {
        $usuario = new Usuario([
            'bloqueado'       => true,
            'bloqueado_hasta' => null,
        ]);

        $this->assertTrue($usuario->estaBloqueado());
    }

    /** @test */
    public function esta_bloqueado_retorna_true_si_fecha_bloqueo_es_futura()
    {
        $usuario = new Usuario([
            'bloqueado'       => true,
            'bloqueado_hasta' => now()->addHours(2),
        ]);

        $this->assertTrue($usuario->estaBloqueado());
    }


    /** @test */
    public function has_role_retorna_true_cuando_el_rol_existe()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->with('nombre', 'administrador')
            ->andReturnSelf();
        $queryMock->shouldReceive('exists')
            ->andReturn(true);

        $usuario->shouldReceive('roles')
            ->andReturn($queryMock);

        $this->assertTrue($usuario->hasRole('administrador'));
    }

    /** @test */
    public function has_role_retorna_false_cuando_el_rol_no_existe()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->with('nombre', 'proveedor_area')
            ->andReturnSelf();
        $queryMock->shouldReceive('exists')
            ->andReturn(false);

        $usuario->shouldReceive('roles')
            ->andReturn($queryMock);

        $this->assertFalse($usuario->hasRole('proveedor_area'));
    }

    /** @test */
    public function has_any_role_retorna_true_con_al_menos_un_rol_valido()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereIn')
            ->with('nombre', ['administrador', 'estudiante'])
            ->andReturnSelf();
        $queryMock->shouldReceive('exists')
            ->andReturn(true);

        $usuario->shouldReceive('roles')
            ->andReturn($queryMock);

        $this->assertTrue($usuario->hasAnyRole(['administrador', 'estudiante']));
    }

    /** @test */
    public function has_any_role_retorna_false_si_no_tiene_ninguno_de_los_roles()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereIn')
            ->with('nombre', ['administrador', 'proveedor_area'])
            ->andReturnSelf();
        $queryMock->shouldReceive('exists')
            ->andReturn(false);

        $usuario->shouldReceive('roles')
            ->andReturn($queryMock);

        $this->assertFalse($usuario->hasAnyRole(['administrador', 'proveedor_area']));
    }



    /** @test */
    public function has_permission_retorna_true_cuando_el_permiso_existe()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $innerQuery = Mockery::mock();
        $innerQuery->shouldReceive('where')
            ->with('clave', 'usuarios.ver')
            ->andReturnSelf();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereHas')
            ->with('permisos', Mockery::on(function ($closure) use ($innerQuery) {
                $closure($innerQuery);
                return true;
            }))
            ->andReturnSelf();
        $queryMock->shouldReceive('exists')
            ->andReturn(true);

        $usuario->shouldReceive('roles')
            ->andReturn($queryMock);

        $this->assertTrue($usuario->hasPermission('usuarios.ver'));
    }

    /** @test */
    public function has_permission_retorna_false_cuando_no_tiene_el_permiso()
    {
        $usuario = Mockery::mock(Usuario::class)->makePartial();

        $innerQuery = Mockery::mock();
        $innerQuery->shouldReceive('where')->andReturnSelf();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereHas')
            ->andReturnSelf();
        $queryMock->shouldReceive('exists')
            ->andReturn(false);

        $usuario->shouldReceive('roles')
            ->andReturn($queryMock);

        $this->assertFalse($usuario->hasPermission('admin.total'));
    }


    /** @test */
    public function usuario_tiene_relacion_perfil()
    {
        $usuario = new Usuario();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $usuario->perfil()
        );
    }

    /** @test */
    public function usuario_tiene_relacion_sesiones()
    {
        $usuario = new Usuario();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $usuario->sesiones()
        );
    }

    /** @test */
    public function usuario_tiene_relacion_accesos()
    {
        $usuario = new Usuario();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $usuario->accesos()
        );
    }

    /** @test */
    public function usuario_tiene_relacion_actividades()
    {
        $usuario = new Usuario();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $usuario->actividades()
        );
    }

    /** @test */
    public function usuario_tiene_relacion_roles()
    {
        $usuario = new Usuario();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $usuario->roles()
        );
    }

   
    /** @test */
    public function password_hash_esta_en_campos_ocultos()
    {
        $usuario = new Usuario();
        $this->assertContains('password_hash', $usuario->getHidden());
    }

    /** @test */
    public function seguridad_json_esta_en_campos_ocultos()
    {
        $usuario = new Usuario();
        $this->assertContains('seguridad_json', $usuario->getHidden());
    }

    /** @test */
    public function fillable_contiene_campos_esperados()
    {
        $usuario = new Usuario();
        $fillable = $usuario->getFillable();

        $this->assertContains('nombre', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password_hash', $fillable);
        $this->assertContains('bloqueado', $fillable);
    }
}