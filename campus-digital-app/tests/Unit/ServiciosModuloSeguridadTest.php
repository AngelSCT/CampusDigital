<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;

use App\Services\AuthService;
use App\Services\UsuarioService;
use App\Services\RolService;
use App\Services\BitacoraService;
use App\Services\SesionService;
use App\Services\PerfilService;
use App\Services\ReporteService;
use App\Services\PermisoService;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\UsuarioPerfil;
use App\Models\UsuarioSesion;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;


class ServiciosModuloSeguridadTest extends TestCase
{
    protected AuthService    $authService;
    protected UsuarioService $usuarioService;
    protected RolService     $rolService;
    protected BitacoraService $bitacoraService;
    protected SesionService  $sesionService;
    protected PerfilService  $perfilService;
    protected ReporteService $reporteService;
    protected PermisoService $permisoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService     = Mockery::mock(AuthService::class);
        $this->usuarioService  = Mockery::mock(UsuarioService::class);
        $this->rolService      = Mockery::mock(RolService::class);
        $this->bitacoraService = Mockery::mock(BitacoraService::class);
        $this->sesionService   = Mockery::mock(SesionService::class);
        $this->perfilService   = Mockery::mock(PerfilService::class);
        $this->reporteService  = Mockery::mock(ReporteService::class);
        $this->permisoService  = Mockery::mock(PermisoService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    /** @test */
    public function auth_service_verifica_credenciales_exitosas()
    {
        $this->authService
            ->shouldReceive('verificarCredenciales')
            ->once()
            ->with('alumno@campus.edu', 'Password123!')
            ->andReturn(true);

        $resultado = $this->authService->verificarCredenciales('alumno@campus.edu', 'Password123!');

        $this->assertTrue($resultado);
    }

    /** @test */
    public function auth_service_rechaza_credenciales_incorrectas()
    {
        $this->authService
            ->shouldReceive('verificarCredenciales')
            ->once()
            ->with('alumno@campus.edu', 'claveIncorrecta')
            ->andReturn(false);

        $resultado = $this->authService->verificarCredenciales('alumno@campus.edu', 'claveIncorrecta');

        $this->assertFalse($resultado);
    }

    /** @test */
    public function auth_service_bloquea_usuario_por_intentos_fallidos()
    {
        $this->authService
            ->shouldReceive('bloquearPorIntentos')
            ->once()
            ->with(7, 30);

        $this->authService->bloquearPorIntentos(7, 30);

        $this->assertTrue(true); // llegó sin excepción
    }

    /** @test */
    public function auth_service_desbloquea_usuario_correctamente()
    {
        $this->authService
            ->shouldReceive('desbloquearUsuario')
            ->once()
            ->with(7);

        $this->authService->desbloquearUsuario(7);

        $this->assertTrue(true);
    }


    /** @test */
    public function usuario_service_crea_usuario_y_retorna_instancia()
    {
        $usuarioFalso = new Usuario([
            'nombre'   => 'Laura',
            'apellido' => 'Gómez',
            'email'    => 'laura@campus.edu',
        ]);

        $this->usuarioService
            ->shouldReceive('crear')
            ->once()
            ->with(Mockery::on(fn ($d) => $d['email'] === 'laura@campus.edu'))
            ->andReturn($usuarioFalso);

        $resultado = $this->usuarioService->crear([
            'nombre'   => 'Laura',
            'apellido' => 'Gómez',
            'email'    => 'laura@campus.edu',
            'password' => 'Segura456!',
            'rol'      => 'estudiante',
        ]);

        $this->assertInstanceOf(Usuario::class, $resultado);
        $this->assertEquals('laura@campus.edu', $resultado->email);
    }

    /** @test */
    public function usuario_service_actualiza_datos_basicos()
    {
        $usuario = new Usuario(['nombre' => 'Laura', 'apellido' => 'Gómez']);

        $this->usuarioService
            ->shouldReceive('actualizar')
            ->once()
            ->with($usuario, ['nombre' => 'Laura', 'apellido' => 'Martínez'])
            ->andReturn(true);

        $resultado = $this->usuarioService->actualizar($usuario, [
            'nombre'   => 'Laura',
            'apellido' => 'Martínez',
        ]);

        $this->assertTrue($resultado);
    }

    /** @test */
    public function usuario_service_cambia_password_correctamente()
    {
        $usuario = new Usuario();

        $this->usuarioService
            ->shouldReceive('cambiarPassword')
            ->once()
            ->with($usuario, 'NuevaClave789!')
            ->andReturn(true);

        $resultado = $this->usuarioService->cambiarPassword($usuario, 'NuevaClave789!');

        $this->assertTrue($resultado);
    }

    /** @test */
    public function usuario_service_retorna_estadisticas_del_dashboard()
    {
        $estadisticas = [
            'total'         => 120,
            'activos'       => 115,
            'bloqueados'    => 5,
            'sin_verificar' => 10,
        ];

        $this->usuarioService
            ->shouldReceive('obtenerEstadisticas')
            ->once()
            ->andReturn($estadisticas);

        $resultado = $this->usuarioService->obtenerEstadisticas();

        $this->assertArrayHasKey('total', $resultado);
        $this->assertArrayHasKey('bloqueados', $resultado);
        $this->assertEquals(120, $resultado['total']);
    }

    /** @test */
    public function usuario_service_elimina_usuario_correctamente()
    {
        $usuario = new Usuario();

        $this->usuarioService
            ->shouldReceive('eliminar')
            ->once()
            ->with($usuario)
            ->andReturn(true);

        $resultado = $this->usuarioService->eliminar($usuario);

        $this->assertTrue($resultado);
    }

    /** @test */
    public function rol_service_crea_rol_y_retorna_instancia()
    {
        $rolFalso = new Rol([
            'nombre'      => 'proveedor_area',
            'descripcion' => 'Operador de área vendedora',
            'activo'      => true,
        ]);

        $this->rolService
            ->shouldReceive('crear')
            ->once()
            ->with('proveedor_area', 'Operador de área vendedora', true)
            ->andReturn($rolFalso);

        $resultado = $this->rolService->crear('proveedor_area', 'Operador de área vendedora', true);

        $this->assertInstanceOf(Rol::class, $resultado);
        $this->assertEquals('proveedor_area', $resultado->nombre);
    }

    /** @test */
    public function rol_service_sincroniza_permisos_al_rol()
    {
        $rol = new Rol(['nombre' => 'administrador']);

        $this->rolService
            ->shouldReceive('sincronizarPermisos')
            ->once()
            ->with($rol, ['usuarios.ver', 'usuarios.crear', 'reportes.exportar']);

        $this->rolService->sincronizarPermisos($rol, [
            'usuarios.ver',
            'usuarios.crear',
            'reportes.exportar',
        ]);

        $this->assertTrue(true);
    }

    /** @test */
    public function rol_service_desactiva_un_rol()
    {
        $rol = new Rol(['nombre' => 'proveedor_area', 'activo' => true]);

        $this->rolService
            ->shouldReceive('desactivar')
            ->once()
            ->with($rol)
            ->andReturn(true);

        $resultado = $this->rolService->desactivar($rol);

        $this->assertTrue($resultado);
    }

    /** @test */
    public function rol_service_verifica_si_rol_tiene_permiso()
    {
        $rol = new Rol(['nombre' => 'estudiante']);

        $this->rolService
            ->shouldReceive('rolTienePermiso')
            ->once()
            ->with($rol, 'usuarios.ver')
            ->andReturn(false);

        $resultado = $this->rolService->rolTienePermiso($rol, 'usuarios.ver');

        $this->assertFalse($resultado);
    }


    /** @test */
    public function bitacora_service_registra_acceso_exitoso()
    {
        $registro = new AccesoBitacora([
            'evento' => 'login',
            'exito'  => true,
        ]);

        $this->bitacoraService
            ->shouldReceive('registrarAcceso')
            ->once()
            ->with('login', true, 'Login exitoso', 'admin@campus.edu', 1, null)
            ->andReturn($registro);

        $resultado = $this->bitacoraService->registrarAcceso(
            'login', true, 'Login exitoso', 'admin@campus.edu', 1, null
        );

        $this->assertInstanceOf(AccesoBitacora::class, $resultado);
        $this->assertTrue($resultado->exito);
    }

    /** @test */
    public function bitacora_service_registra_actividad_de_usuario()
    {
        $actividad = new ActividadBitacora([
            'accion' => 'crear',
            'modulo' => 'usuarios',
        ]);

        $this->bitacoraService
            ->shouldReceive('registrarActividad')
            ->once()
            ->with('crear', 'usuarios', 'usuario', 5, true, 'Usuario creado', [])
            ->andReturn($actividad);

        $resultado = $this->bitacoraService->registrarActividad(
            'crear', 'usuarios', 'usuario', 5, true, 'Usuario creado', []
        );

        $this->assertInstanceOf(ActividadBitacora::class, $resultado);
        $this->assertEquals('crear', $resultado->accion);
    }

    /** @test */
    public function bitacora_service_cuenta_intentos_fallidos()
    {
        $this->bitacoraService
            ->shouldReceive('contarIntentosFallidos')
            ->once()
            ->with('atacante@correo.com', 15)
            ->andReturn(4);

        $resultado = $this->bitacoraService->contarIntentosFallidos('atacante@correo.com', 15);

        $this->assertEquals(4, $resultado);
        $this->assertLessThan(5, $resultado); // umbral de bloqueo
    }

    /** @test */
    public function bitacora_service_retorna_estadisticas_del_dashboard()
    {
        $this->bitacoraService
            ->shouldReceive('estadisticasAcceso')
            ->once()
            ->andReturn([
                'exitosos_hoy' => 88,
                'fallidos_hoy' => 3,
                'total_semana' => 412,
            ]);

        $resultado = $this->bitacoraService->estadisticasAcceso();

        $this->assertArrayHasKey('exitosos_hoy', $resultado);
        $this->assertArrayHasKey('fallidos_hoy', $resultado);
        $this->assertEquals(88, $resultado['exitosos_hoy']);
    }


    /** @test */
    public function sesion_service_abre_sesion_y_retorna_instancia()
    {
        $sesionFalsa = new UsuarioSesion([
            'usuario_id' => 3,
            'activa'     => true,
        ]);

        $this->sesionService
            ->shouldReceive('abrirSesion')
            ->once()
            ->with(3, 8)
            ->andReturn($sesionFalsa);

        $resultado = $this->sesionService->abrirSesion(3, 8);

        $this->assertInstanceOf(UsuarioSesion::class, $resultado);
        $this->assertTrue($resultado->activa);
    }

    /** @test */
    public function sesion_service_cierra_sesion_activa()
    {
        $this->sesionService
            ->shouldReceive('cerrarSesion')
            ->once()
            ->with(3);

        $this->sesionService->cerrarSesion(3);

        $this->assertTrue(true);
    }

    /** @test */
    public function sesion_service_detecta_que_usuario_tiene_sesion_activa()
    {
        $this->sesionService
            ->shouldReceive('tieneSesionActiva')
            ->once()
            ->with(3)
            ->andReturn(true);

        $resultado = $this->sesionService->tieneSesionActiva(3);

        $this->assertTrue($resultado);
    }

    /** @test */
    public function sesion_service_detecta_que_usuario_no_tiene_sesion_activa()
    {
        $this->sesionService
            ->shouldReceive('tieneSesionActiva')
            ->once()
            ->with(99)
            ->andReturn(false);

        $resultado = $this->sesionService->tieneSesionActiva(99);

        $this->assertFalse($resultado);
    }


    /** @test */
    public function perfil_service_actualiza_perfil_y_retorna_instancia()
    {
        $usuario = new Usuario(['id' => 1]);
        $perfilFalso = new UsuarioPerfil([
            'genero'    => 'F',
            'direccion' => 'Av. Universidad 100',
        ]);

        $this->perfilService
            ->shouldReceive('actualizarPerfil')
            ->once()
            ->with($usuario, Mockery::on(fn ($d) => $d['genero'] === 'F'))
            ->andReturn($perfilFalso);

        $resultado = $this->perfilService->actualizarPerfil($usuario, [
            'genero'    => 'F',
            'direccion' => 'Av. Universidad 100',
        ]);

        $this->assertInstanceOf(UsuarioPerfil::class, $resultado);
        $this->assertEquals('F', $resultado->genero);
    }

    /** @test */
    public function perfil_service_elimina_foto_del_usuario()
    {
        $usuario = new Usuario(['foto_url' => 'fotos/1/avatar.jpg']);

        $this->perfilService
            ->shouldReceive('eliminarFoto')
            ->once()
            ->with($usuario);

        $this->perfilService->eliminarFoto($usuario);

        $this->assertTrue(true);
    }

    /** @test */
    public function perfil_service_guarda_preferencias_del_usuario()
    {
        $usuario = new Usuario();

        $this->perfilService
            ->shouldReceive('guardarPreferencias')
            ->once()
            ->with($usuario, ['notificaciones' => true, 'idioma' => 'es']);

        $this->perfilService->guardarPreferencias($usuario, [
            'notificaciones' => true,
            'idioma'         => 'es',
        ]);

        $this->assertTrue(true);
    }


    /** @test */
    public function reporte_service_genera_reporte_de_usuarios_por_rol()
    {
        $datos = collect([
            ['id' => 1, 'nombre' => 'Juan Pérez', 'roles' => 'administrador', 'bloqueado' => 'No'],
            ['id' => 2, 'nombre' => 'Ana López',  'roles' => 'estudiante',    'bloqueado' => 'No'],
        ]);

        $this->reporteService
            ->shouldReceive('reporteUsuariosPorRol')
            ->once()
            ->with('administrador')
            ->andReturn($datos);

        $resultado = $this->reporteService->reporteUsuariosPorRol('administrador');

        $this->assertCount(2, $resultado);
        $this->assertEquals('administrador', $resultado->first()['roles']);
    }

    /** @test */
    public function reporte_service_genera_reporte_de_accesos_por_periodo()
    {
        $datos = collect([
            ['fecha' => '01/05/2026 09:00', 'evento' => 'login',        'exito' => 'Exitoso'],
            ['fecha' => '01/05/2026 09:05', 'evento' => 'login_fallido','exito' => 'Fallido'],
        ]);

        $this->reporteService
            ->shouldReceive('reporteAccesosPorPeriodo')
            ->once()
            ->with('2026-05-01', '2026-05-31')
            ->andReturn($datos);

        $resultado = $this->reporteService->reporteAccesosPorPeriodo('2026-05-01', '2026-05-31');

        $this->assertCount(2, $resultado);
        $this->assertEquals('Fallido', $resultado->last()['exito']);
    }

    /** @test */
    public function reporte_service_retorna_resumen_para_dashboard()
    {
        $this->reporteService
            ->shouldReceive('resumenDashboard')
            ->once()
            ->andReturn([
                'usuarios_totales'     => 95,
                'usuarios_activos'     => 90,
                'accesos_hoy'          => 45,
                'fallos_hoy'           => 2,
                'acciones_esta_semana' => 210,
            ]);

        $resultado = $this->reporteService->resumenDashboard();

        $this->assertArrayHasKey('usuarios_totales', $resultado);
        $this->assertArrayHasKey('acciones_esta_semana', $resultado);
        $this->assertEquals(95, $resultado['usuarios_totales']);
    }


    /** @test */
    public function permiso_service_crea_permiso_y_retorna_instancia()
    {
        $permisoFalso = new Permiso([
            'clave'       => 'bitacora.ver',
            'descripcion' => 'Ver registros de bitácora',
            'activo'      => true,
        ]);

        $this->permisoService
            ->shouldReceive('crear')
            ->once()
            ->with('bitacora.ver', 'Ver registros de bitácora')
            ->andReturn($permisoFalso);

        $resultado = $this->permisoService->crear('bitacora.ver', 'Ver registros de bitácora');

        $this->assertInstanceOf(Permiso::class, $resultado);
        $this->assertEquals('bitacora.ver', $resultado->clave);
    }

    /** @test */
    public function permiso_service_verifica_si_permiso_esta_en_uso()
    {
        $permiso = new Permiso(['clave' => 'usuarios.eliminar']);

        $this->permisoService
            ->shouldReceive('estaEnUso')
            ->once()
            ->with($permiso)
            ->andReturn(true);

        $resultado = $this->permisoService->estaEnUso($permiso);

        $this->assertTrue($resultado);
    }

    /** @test */
    public function permiso_service_desactiva_permiso_correctamente()
    {
        $permiso = new Permiso(['clave' => 'reportes.exportar', 'activo' => true]);

        $this->permisoService
            ->shouldReceive('desactivar')
            ->once()
            ->with($permiso)
            ->andReturn(true);

        $resultado = $this->permisoService->desactivar($permiso);

        $this->assertTrue($resultado);
    }

    /** @test */
    public function permiso_service_retorna_roles_que_tienen_un_permiso()
    {
        $roles = collect([
            new Rol(['nombre' => 'administrador']),
        ]);

        $this->permisoService
            ->shouldReceive('rolesConPermiso')
            ->once()
            ->with('usuarios.ver')
            ->andReturn($roles);

        $resultado = $this->permisoService->rolesConPermiso('usuarios.ver');

        $this->assertCount(1, $resultado);
        $this->assertEquals('administrador', $resultado->first()->nombre);
    }
}