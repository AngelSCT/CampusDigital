<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Rol;
use App\Models\Permiso;
use Mockery;

class RolPermisoTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    /** @test */
    public function rol_tiene_fillable_correcto()
    {
        $rol = new Rol();
        $fillable = $rol->getFillable();

        $this->assertContains('nombre', $fillable);
        $this->assertContains('descripcion', $fillable);
        $this->assertContains('activo', $fillable);
    }

    /** @test */
    public function rol_castea_activo_como_booleano()
    {
        $rol = new Rol(['activo' => 1]);
        $this->assertIsBool($rol->activo);
        $this->assertTrue($rol->activo);
    }

    /** @test */
    public function rol_inactivo_castea_correctamente()
    {
        $rol = new Rol(['activo' => 0]);
        $this->assertFalse($rol->activo);
    }

    /** @test */
    public function rol_usa_tabla_correcta()
    {
        $rol = new Rol();
        $this->assertEquals('rol', $rol->getTable());
    }


    /** @test */
    public function rol_tiene_relacion_permisos_many_to_many()
    {
        $rol = new Rol();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $rol->permisos()
        );
    }

    /** @test */
    public function rol_tiene_relacion_usuarios_many_to_many()
    {
        $rol = new Rol();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $rol->usuarios()
        );
    }

    /** @test */
    public function relacion_permisos_usa_tabla_pivot_correcta()
    {
        $rol = new Rol();
        $relacion = $rol->permisos();

        $this->assertEquals('rol_permiso', $relacion->getTable());
    }

    /** @test */
    public function relacion_usuarios_usa_tabla_pivot_correcta()
    {
        $rol = new Rol();
        $relacion = $rol->usuarios();

        $this->assertEquals('usuario_rol', $relacion->getTable());
    }

    /** @test */
    public function permiso_tiene_fillable_correcto()
    {
        $permiso = new Permiso();
        $fillable = $permiso->getFillable();

        $this->assertContains('clave', $fillable);
        $this->assertContains('descripcion', $fillable);
        $this->assertContains('activo', $fillable);
    }

    /** @test */
    public function permiso_castea_activo_como_booleano()
    {
        $permiso = new Permiso(['activo' => 1]);
        $this->assertIsBool($permiso->activo);
        $this->assertTrue($permiso->activo);
    }

    /** @test */
    public function permiso_inactivo_castea_correctamente()
    {
        $permiso = new Permiso(['activo' => 0]);
        $this->assertFalse($permiso->activo);
    }

    /** @test */
    public function permiso_usa_tabla_correcta()
    {
        $permiso = new Permiso();
        $this->assertEquals('permiso', $permiso->getTable());
    }


    /** @test */
    public function permiso_tiene_relacion_roles_many_to_many()
    {
        $permiso = new Permiso();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $permiso->roles()
        );
    }

    /** @test */
    public function relacion_roles_desde_permiso_usa_pivot_correcta()
    {
        $permiso = new Permiso();
        $relacion = $permiso->roles();

        $this->assertEquals('rol_permiso', $relacion->getTable());
    }


    /** @test */
    public function rol_activo_puede_identificarse_correctamente()
    {
        $activo   = new Rol(['activo' => true]);
        $inactivo = new Rol(['activo' => false]);

        $this->assertTrue($activo->activo);
        $this->assertFalse($inactivo->activo);
    }

    /** @test */
    public function permiso_activo_puede_identificarse_correctamente()
    {
        $activo   = new Permiso(['activo' => true]);
        $inactivo = new Permiso(['activo' => false]);

        $this->assertTrue($activo->activo);
        $this->assertFalse($inactivo->activo);
    }

    /** @test */
    public function rol_puede_construirse_con_atributos_validos()
    {
        $rol = new Rol([
            'nombre'      => 'estudiante',
            'descripcion' => 'Rol para alumnos del campus',
            'activo'      => true,
        ]);

        $this->assertEquals('estudiante', $rol->nombre);
        $this->assertEquals('Rol para alumnos del campus', $rol->descripcion);
        $this->assertTrue($rol->activo);
    }

    /** @test */
    public function permiso_puede_construirse_con_clave_descriptiva()
    {
        $permiso = new Permiso([
            'clave'       => 'usuarios.ver',
            'descripcion' => 'Permite ver la lista de usuarios',
            'activo'      => true,
        ]);

        $this->assertEquals('usuarios.ver', $permiso->clave);
        $this->assertTrue($permiso->activo);
    }
}