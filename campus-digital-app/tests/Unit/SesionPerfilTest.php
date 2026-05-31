<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsuarioSesion;
use App\Models\UsuarioPerfil;
use Mockery;

class SesionPerfilTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    /** @test */
    public function sesion_usa_tabla_correcta()
    {
        $sesion = new UsuarioSesion();
        $this->assertEquals('usuario_sesion', $sesion->getTable());
    }

    /** @test */
    public function sesion_tiene_fillable_correcto()
    {
        $sesion  = new UsuarioSesion();
        $fillable = $sesion->getFillable();

        $this->assertContains('usuario_id', $fillable);
        $this->assertContains('session_id', $fillable);
        $this->assertContains('ip', $fillable);
        $this->assertContains('user_agent', $fillable);
        $this->assertContains('inicia_at', $fillable);
        $this->assertContains('expira_at', $fillable);
        $this->assertContains('termina_at', $fillable);
        $this->assertContains('activa', $fillable);
        $this->assertContains('meta_json', $fillable);
    }

    /** @test */
    public function sesion_castea_activa_como_booleano()
    {
        $activa   = new UsuarioSesion(['activa' => 1]);
        $inactiva = new UsuarioSesion(['activa' => 0]);

        $this->assertIsBool($activa->activa);
        $this->assertTrue($activa->activa);
        $this->assertFalse($inactiva->activa);
    }

    /** @test */
    public function sesion_castea_meta_json_como_array()
    {
        $sesion = new UsuarioSesion();
        $sesion->meta_json = ['dispositivo' => 'laptop', 'os' => 'macOS'];

        $this->assertIsArray($sesion->meta_json);
        $this->assertEquals('laptop', $sesion->meta_json['dispositivo']);
    }

    /** @test */
    public function sesion_puede_construirse_con_datos_completos()
    {
        $inicio  = now();
        $expira  = now()->addHours(8);

        $sesion = new UsuarioSesion([
            'usuario_id' => 3,
            'session_id' => 'abc123xyz',
            'ip'         => '192.168.0.100',
            'user_agent' => 'Chrome/120',
            'inicia_at'  => $inicio,
            'expira_at'  => $expira,
            'activa'     => true,
        ]);

        $this->assertEquals(3, $sesion->usuario_id);
        $this->assertEquals('abc123xyz', $sesion->session_id);
        $this->assertTrue($sesion->activa);
    }

    /** @test */
    public function sesion_inactiva_se_construye_correctamente()
    {
        $sesion = new UsuarioSesion([
            'activa'     => false,
            'termina_at' => now(),
        ]);

        $this->assertFalse($sesion->activa);
        $this->assertNotNull($sesion->termina_at);
    }

    /** @test */
    public function sesion_tiene_relacion_usuario()
    {
        $sesion = new UsuarioSesion();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $sesion->usuario()
        );
    }

    /** @test */
    public function sesion_tiene_relacion_accesos()
    {
        $sesion = new UsuarioSesion();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $sesion->accesos()
        );
    }

    /** @test */
    public function sesion_tiene_relacion_actividades()
    {
        $sesion = new UsuarioSesion();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $sesion->actividades()
        );
    }


    /** @test */
    public function perfil_usa_tabla_correcta()
    {
        $perfil = new UsuarioPerfil();
        $this->assertEquals('usuario_perfil', $perfil->getTable());
    }

    /** @test */
    public function perfil_tiene_fillable_correcto()
    {
        $perfil  = new UsuarioPerfil();
        $fillable = $perfil->getFillable();

        $this->assertContains('usuario_id', $fillable);
        $this->assertContains('fecha_nacimiento', $fillable);
        $this->assertContains('genero', $fillable);
        $this->assertContains('direccion', $fillable);
        $this->assertContains('preferencias_json', $fillable);
    }

    /** @test */
    public function perfil_castea_preferencias_json_como_array()
    {
        $perfil = new UsuarioPerfil();
        $perfil->preferencias_json = ['notificaciones' => true, 'idioma' => 'es'];

        $this->assertIsArray($perfil->preferencias_json);
        $this->assertTrue($perfil->preferencias_json['notificaciones']);
        $this->assertEquals('es', $perfil->preferencias_json['idioma']);
    }

    /** @test */
    public function perfil_puede_construirse_con_datos_validos()
    {
        $perfil = new UsuarioPerfil([
            'usuario_id'       => 1,
            'fecha_nacimiento' => '2000-05-15',
            'genero'           => 'M',
            'direccion'        => 'Calle Falsa 123, Celaya, Gto.',
        ]);

        $this->assertEquals(1, $perfil->usuario_id);
        $this->assertEquals('M', $perfil->genero);
        $this->assertEquals('Calle Falsa 123, Celaya, Gto.', $perfil->direccion);
    }

    /** @test */
    public function perfil_sin_preferencias_json_devuelve_null_o_array_vacio()
    {
        $perfil = new UsuarioPerfil();

        // Sin asignar, el cast no aplica hasta que se accede al atributo con valor
        $this->assertNull($perfil->preferencias_json);
    }


    /** @test */
    public function perfil_tiene_relacion_usuario()
    {
        $perfil = new UsuarioPerfil();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $perfil->usuario()
        );
    }


    /** @test */
    public function sesion_activa_tiene_expiracion_en_el_futuro()
    {
        $sesion = new UsuarioSesion([
            'activa'    => true,
            'expira_at' => now()->addMinutes(30),
        ]);

        $this->assertTrue($sesion->activa);
        $this->assertTrue($sesion->expira_at->isFuture());
    }

    /** @test */
    public function sesion_sin_fecha_termina_se_considera_abierta()
    {
        $sesion = new UsuarioSesion([
            'activa'     => true,
            'termina_at' => null,
        ]);

        $this->assertNull($sesion->termina_at);
        $this->assertTrue($sesion->activa);
    }

    /** @test */
    public function sesion_con_fecha_termina_se_considera_cerrada()
    {
        $sesion = new UsuarioSesion([
            'activa'     => false,
            'termina_at' => now()->subMinutes(10),
        ]);

        $this->assertFalse($sesion->activa);
        $this->assertNotNull($sesion->termina_at);
    }
}