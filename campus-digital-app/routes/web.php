<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\BitacoraController;

//CONTROLADORES DEL MODULO 1O
use App\Http\Controllers\Admin\TarjetaController;
use App\Http\Controllers\Admin\TarjetaDashboardController;
use App\Http\Controllers\Admin\TarjetaReporteController;
use App\Http\Controllers\TarjetaLecturaController;
use App\Http\Controllers\Auth\RfidLoginController;
use App\Http\Controllers\MiTarjetaController;


// Ruta principal
Route::get('/', function () {
    return redirect()->route('login');
});

// Login por tarjeta RFID (no requiere auth, igual que el login normal)
Route::post('/auth/rfid-login', [RfidLoginController::class, 'login'])
    ->middleware('guest')
    ->name('rfid.login');

// Rutas autenticadas
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:estudiante,proveedor_area,administrador'])
        ->prefix('mi-tarjeta')
        ->name('mi-tarjeta.')
        ->group(function () {
            Route::get('/',         [MiTarjetaController::class, 'show'])->name('show');
            Route::post('/escanear',[MiTarjetaController::class, 'simularEscaneo'])->name('escanear');
            Route::get('/pin',      [MiTarjetaController::class, 'showPin'])->name('pin');
            Route::post('/pin',     [MiTarjetaController::class, 'updatePin'])->name('pin.store');
        });

    // Lector simulado — para administradores y proveedores
    Route::middleware(['role:administrador,proveedor_area'])->group(function () {
        Route::get('/lector', [TarjetaLecturaController::class, 'index'])->name('lector.index');
        Route::post('/lector/leer', [TarjetaLecturaController::class, 'leer'])->name('lector.leer');
        Route::post('/lector/confirmar-pedido', [TarjetaLecturaController::class, 'confirmarPedido'])->name('lector.confirmar-pedido');
    });

    // Perfil de usuario
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [PerfilController::class, 'show'])->name('show');
        Route::post('/actualizar', [PerfilController::class, 'updateProfile'])->name('update');
        Route::post('/foto', [PerfilController::class, 'updatePhoto'])->name('photo.update');
        Route::delete('/foto', [PerfilController::class, 'deletePhoto'])->name('photo.delete');
    });

    // Rutas de administración (solo para administradores)
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {


        // Módulo Tarjetas
        Route::prefix('tarjetas')->name('tarjetas.')->group(function () {

            // Dashboard del módulo
            Route::get('/dashboard', [TarjetaDashboardController::class, 'index'])->name('dashboard');

            // CRUD tarjetas
            Route::get('/',                      [TarjetaController::class, 'index'])->name('index');
            Route::get('/create',                [TarjetaController::class, 'create'])->name('create');
            Route::post('/',                     [TarjetaController::class, 'store'])->name('store');
            Route::get('/{tarjeta}',             [TarjetaController::class, 'show'])->name('show');
            Route::get('/{tarjeta}/edit',        [TarjetaController::class, 'edit'])->name('edit');
            Route::put('/{tarjeta}',             [TarjetaController::class, 'update'])->name('update');
            Route::delete('/{tarjeta}',          [TarjetaController::class, 'destroy'])->name('destroy');
            Route::post('/{tarjeta}/toggle-block', [TarjetaController::class, 'toggleBlock'])->name('toggle-block');

            // Reportes
            Route::get('/reportes/index',                [TarjetaReporteController::class, 'index'])->name('reportes.index');
            Route::get('/reportes/export-csv',           [TarjetaReporteController::class, 'exportCsv'])->name('reportes.export-csv');
            Route::get('/reportes/export-incidentes',    [TarjetaReporteController::class, 'exportIncidentesCsv'])->name('reportes.export-incidentes');
            Route::get('/reportes/export-lecturas-pdf',  [TarjetaReporteController::class, 'exportLecturasPdf'])->name('reportes.export-lecturas-pdf');
            Route::get('/reportes/export-modulo-csv',    [TarjetaReporteController::class, 'exportModuloCsv'])->name('reportes.export-modulo-csv');
            Route::get('/reportes/export-modulo-pdf',    [TarjetaReporteController::class, 'exportModuloPdf'])->name('reportes.export-modulo-pdf');
            Route::get('/reportes/export-incidentes-pdf',[TarjetaReporteController::class, 'exportIncidentesPdf'])->name('reportes.export-incidentes-pdf');
        
        });
        
        // Gestión de usuarios
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('index');
            Route::get('/create', [UsuarioController::class, 'create'])->name('create');
            Route::post('/', [UsuarioController::class, 'store'])->name('store');
            Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])->name('edit');
            Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
            Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])->name('destroy');
            Route::post('/{usuario}/toggle-block', [UsuarioController::class, 'toggleBlock'])->name('toggle-block');
            Route::post('/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('reset-password');
            Route::get('/export', [UsuarioController::class, 'export'])->name('export');
            Route::get('/export-by-role', [UsuarioController::class, 'exportByRole'])->name('export-by-role');
            Route::get('/export-pdf', [UsuarioController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-by-role-pdf', [UsuarioController::class, 'exportByRolePdf'])->name('export-by-role-pdf');
        });

        // Gestión de roles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolController::class, 'index'])->name('index');
            Route::get('/create', [RolController::class, 'create'])->name('create');
            Route::post('/', [RolController::class, 'store'])->name('store');
            Route::get('/{rol}/edit', [RolController::class, 'edit'])->name('edit');
            Route::put('/{rol}', [RolController::class, 'update'])->name('update');
            Route::delete('/{rol}', [RolController::class, 'destroy'])->name('destroy');
        });

        // Gestión de permisos
        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/', [PermisoController::class, 'index'])->name('index');
            Route::get('/create', [PermisoController::class, 'create'])->name('create');
            Route::post('/', [PermisoController::class, 'store'])->name('store');
            Route::get('/{permiso}/edit', [PermisoController::class, 'edit'])->name('edit');
            Route::put('/{permiso}', [PermisoController::class, 'update'])->name('update');
            Route::delete('/{permiso}', [PermisoController::class, 'destroy'])->name('destroy');
        });

        // Bitacoras
        Route::prefix('bitacora')->name('bitacora.')->group(function () {
            Route::get('/accesos', [BitacoraController::class, 'accesos'])->name('accesos');
            Route::get('/actividad', [BitacoraController::class, 'actividad'])->name('actividad');
            Route::get('/export-accesos', [BitacoraController::class, 'exportAccesos'])->name('export-accesos');
            Route::get('/export-actividad', [BitacoraController::class, 'exportActividad'])->name('export-actividad');
            Route::get('/export-accesos-pdf', [BitacoraController::class, 'exportAccesosPdf'])->name('export-accesos-pdf');
            Route::get('/export-accesos-periodo', [BitacoraController::class, 'exportAccesosPeriodo'])->name('export-accesos-periodo');
            Route::get('/export-accesos-periodo-pdf', [BitacoraController::class, 'exportAccesosPeriodoPdf'])->name('export-accesos-periodo-pdf');
            Route::get('/export-actividad-pdf', [BitacoraController::class, 'exportActividadPdf'])->name('export-actividad-pdf');
            Route::get('/export-actividad-periodo', [BitacoraController::class, 'exportActividadPeriodo'])->name('export-actividad-periodo');
            Route::get('/export-actividad-periodo-pdf', [BitacoraController::class, 'exportActividadPeriodoPdf'])->name('export-actividad-periodo-pdf');
            Route::get('/export-actividad-modulo', [BitacoraController::class, 'exportActividadModulo'])->name('export-actividad-modulo');
            Route::get('/export-actividad-modulo-pdf', [BitacoraController::class, 'exportActividadModuloPdf'])->name('export-actividad-modulo-pdf');
        });

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/usuarios', [ReporteController::class, 'usuarios'])->name('usuarios');
            Route::get('/accesos', [ReporteController::class, 'accesos'])->name('accesos');
            Route::get('/actividad', [ReporteController::class, 'actividad'])->name('actividad');
            Route::get('/usuarios-export', [ReporteController::class, 'exportUsuarios'])->name('usuarios.export');
            Route::get('/accesos-export', [ReporteController::class, 'exportAccesos'])->name('accesos.export');
            Route::get('/actividad-export', [ReporteController::class, 'exportActividad'])->name('actividad.export');
        });
    });
});
