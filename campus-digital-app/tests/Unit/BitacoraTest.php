<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use App\Models\Usuario;
use App\Models\UsuarioSesion;
use Mockery;

class BitacoraTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    /** @test */
    public function acceso_bitacora_usa_tabla_correcta()
    {
        $registro = new AccesoBitacora();
        $this->assertEquals('acceso_bitacora', $registro->getTable());
    }

    /** @test */
    public function acceso_bitacora_tiene_fillable_correcto()
    {
        $registro = new AccesoBitacora();
        $fillable  = $registro->getFillable();

        $this->assertContains('usuario_id', $fillable);
        $this->assertContains('sesion_id', $fillable);
        $this->assertContains('email_intentado', $fillable);
        $this->assertContains('evento', $fillable);
        $this->assertContains('exito', $fillable);
        $this->assertContains('ip', $fillable);
        $this->assertContains('user_agent', $fillable);
        $this->assertContains('meta_json', $fillable);
    }

    /** @test */
    public function acceso_bitacora_castea_exito_como_booleano()
    {
        $exitoso  = new AccesoBitacora(['exito' => 1]);
        $fallido  = new AccesoBitacora(['exito' => 0]);

        $this->assertIsBool($exitoso->exito);
        $this->assertTrue($exitoso->exito);
        $this->assertFalse($fallido->exito);
    }

    /** @test */
    public function acceso_bitacora_castea_meta_json_como_array()
    {
        $registro = new AccesoBitacora([
            'meta_json' => json_encode(['navegador' => 'Chrome', 'so' => 'Windows']),
        ]);

        // Al asignarlo como array directamente también funciona
        $registro2 = new AccesoBitacora();
        $registro2->meta_json = ['navegador' => 'Firefox'];

        $this->assertIsArray($registro2->meta_json);
        $this->assertEquals('Firefox', $registro2->meta_json['navegador']);
    }

    /** @test */
    public function acceso_bitacora_puede_construirse_con_evento_login()
    {
        $registro = new AccesoBitacora([
            'usuario_id'      => 1,
            'email_intentado' => 'alumno@campus.edu',
            'evento'          => 'login',
            'exito'           => true,
            'ip'              => '192.168.1.10',
            'user_agent'      => 'Mozilla/5.0',
        ]);

        $this->assertEquals('login', $registro->evento);
        $this->assertEquals('192.168.1.10', $registro->ip);
        $this->assertTrue($registro->exito);
    }

    /** @test */
    public function acceso_bitacora_puede_construirse_con_evento_login_fallido()
    {
        $registro = new AccesoBitacora([
            'email_intentado' => 'desconocido@x.com',
            'evento'          => 'login_fallido',
            'exito'           => false,
            'detalle'         => 'Contraseña incorrecta',
            'ip'              => '10.0.0.5',
        ]);

        $this->assertEquals('login_fallido', $registro->evento);
        $this->assertFalse($registro->exito);
        $this->assertEquals('Contraseña incorrecta', $registro->detalle);
    }


    /** @test */
    public function acceso_bitacora_tiene_relacion_usuario()
    {
        $registro = new AccesoBitacora();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $registro->usuario()
        );
    }

    /** @test */
    public function acceso_bitacora_tiene_relacion_sesion()
    {
        $registro = new AccesoBitacora();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $registro->sesion()
        );
    }


    /** @test */
    public function actividad_bitacora_usa_tabla_correcta()
    {
        $registro = new ActividadBitacora();
        $this->assertEquals('actividad_bitacora', $registro->getTable());
    }

    /** @test */
    public function actividad_bitacora_tiene_fillable_correcto()
    {
        $registro = new ActividadBitacora();
        $fillable  = $registro->getFillable();

        $this->assertContains('usuario_id', $fillable);
        $this->assertContains('accion', $fillable);
        $this->assertContains('modulo', $fillable);
        $this->assertContains('target_tabla', $fillable);
        $this->assertContains('target_id', $fillable);
        $this->assertContains('exito', $fillable);
        $this->assertContains('ip', $fillable);
    }

    /** @test */
    public function actividad_bitacora_castea_exito_como_booleano()
    {
        $exitosa  = new ActividadBitacora(['exito' => 1]);
        $fallida  = new ActividadBitacora(['exito' => 0]);

        $this->assertTrue($exitosa->exito);
        $this->assertFalse($fallida->exito);
    }

    /** @test */
    public function actividad_bitacora_castea_meta_json_como_array()
    {
        $registro = new ActividadBitacora();
        $registro->meta_json = ['campo' => 'email', 'valor_nuevo' => 'nuevo@campus.edu'];

        $this->assertIsArray($registro->meta_json);
        $this->assertArrayHasKey('campo', $registro->meta_json);
    }

    /** @test */
    public function actividad_bitacora_puede_construirse_con_accion_crear_usuario()
    {
        $registro = new ActividadBitacora([
            'usuario_id'   => 2,
            'accion'       => 'crear',
            'modulo'       => 'usuarios',
            'target_tabla' => 'usuario',
            'target_id'    => 15,
            'exito'        => true,
            'ip'           => '172.16.0.1',
        ]);

        $this->assertEquals('crear', $registro->accion);
        $this->assertEquals('usuarios', $registro->modulo);
        $this->assertEquals('usuario', $registro->target_tabla);
        $this->assertTrue($registro->exito);
    }

    /** @test */
    public function actividad_bitacora_puede_construirse_con_accion_eliminar()
    {
        $registro = new ActividadBitacora([
            'accion'       => 'eliminar',
            'modulo'       => 'roles',
            'target_tabla' => 'rol',
            'target_id'    => 3,
            'exito'        => true,
            'detalle'      => 'Rol proveedor eliminado por administrador',
        ]);

        $this->assertEquals('eliminar', $registro->accion);
        $this->assertEquals('rol', $registro->target_tabla);
        $this->assertEquals(3, $registro->target_id);
    }


    /** @test */
    public function actividad_bitacora_tiene_relacion_usuario()
    {
        $registro = new ActividadBitacora();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $registro->usuario()
        );
    }

    /** @test */
    public function actividad_bitacora_tiene_relacion_sesion()
    {
        $registro = new ActividadBitacora();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $registro->sesion()
        );
    }


    /** @test */
    public function evento_logout_se_registra_correctamente()
    {
        $registro = new AccesoBitacora([
            'usuario_id' => 5,
            'evento'     => 'logout',
            'exito'      => true,
            'ip'         => '10.10.0.2',
        ]);

        $this->assertEquals('logout', $registro->evento);
        $this->assertTrue($registro->exito);
    }

    /** @test */
    public function evento_bloqueo_se_registra_correctamente()
    {
        $registro = new AccesoBitacora([
            'usuario_id' => 7,
            'evento'     => 'cuenta_bloqueada',
            'exito'      => false,
            'detalle'    => 'Demasiados intentos fallidos',
            'ip'         => '10.10.0.9',
        ]);

        $this->assertEquals('cuenta_bloqueada', $registro->evento);
        $this->assertFalse($registro->exito);
    }
}