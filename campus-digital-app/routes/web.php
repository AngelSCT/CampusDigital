<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;                   
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\BitacoraController;
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

// Login por tarjeta RFID
Route::post('/auth/rfid-login', [RfidLoginController::class, 'login'])
    ->middleware('guest')
    ->name('rfid.login');

// ── Simulador móvil ──────────────────────────────────────────────────
// El móvil deposita el UID aquí (sin auth ni CSRF)
Route::post('/simulador/uid', function (Request $request) {
    $uid = strtoupper(trim($request->input('uid', '')));
    if (!$uid) {
        return response()->json(['error' => 'UID vacío'], 400)
            ->header('Access-Control-Allow-Origin', '*');
    }

    Cache::put('simulador_uid_pendiente', $uid, 30);
    Cache::put('simulador_uid_timestamp', now()->toIso8601String(), 30);

    return response()->json(['ok' => true, 'uid' => $uid])
        ->header('Access-Control-Allow-Origin', '*');
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// CORS preflight del móvil
Route::options('/simulador/uid', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});
// ────────────────────────────────────────────────────────────────────

// Rutas autenticadas
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Lector RFID (dentro de auth para que el polling funcione) ────
    Route::get('/lector', [TarjetaLecturaController::class, 'index'])->name('lector.index');
    Route::post('/lector/leer', [TarjetaLecturaController::class, 'leer'])->name('lector.leer');
    Route::post('/lector/confirmar-pedido', [TarjetaLecturaController::class, 'confirmarPedido'])->name('lector.confirmar-pedido');

    // La PC web consulta esto cada 1.5s para detectar UID del móvil
    Route::get('/simulador/uid-pendiente', function () {
        return response()->json([
            'uid'       => Cache::get('simulador_uid_pendiente'),
            'timestamp' => Cache::get('simulador_uid_timestamp'),
        ]);
    })->name('simulador.uid-pendiente');
    // ─────────────────────────────────────────────────────────────────

    // Mi tarjeta
    Route::middleware(['role:estudiante,proveedor_area,administrador'])
        ->prefix('mi-tarjeta')
        ->name('mi-tarjeta.')
        ->group(function () {
            Route::get('/',          [MiTarjetaController::class, 'show'])->name('show');
            Route::post('/escanear', [MiTarjetaController::class, 'simularEscaneo'])->name('escanear');
            Route::get('/pin',       [MiTarjetaController::class, 'showPin'])->name('pin');
            Route::post('/pin',      [MiTarjetaController::class, 'updatePin'])->name('pin.store');
        });

    // Perfil
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/',                [PerfilController::class, 'show'])->name('show');
        Route::post('/actualizar',     [PerfilController::class, 'updateProfile'])->name('update');
        Route::post('/foto',           [PerfilController::class, 'updatePhoto'])->name('photo.update');
        Route::delete('/foto',         [PerfilController::class, 'deletePhoto'])->name('photo.delete');
    });

    // Administración
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {

        // Módulo Tarjetas
        Route::prefix('tarjetas')->name('tarjetas.')->group(function () {
            Route::get('/dashboard', [TarjetaDashboardController::class, 'index'])->name('dashboard');
            Route::get('/',                        [TarjetaController::class, 'index'])->name('index');
            Route::get('/create',                  [TarjetaController::class, 'create'])->name('create');
            Route::post('/',                       [TarjetaController::class, 'store'])->name('store');
            Route::get('/{tarjeta}',               [TarjetaController::class, 'show'])->name('show');
            Route::get('/{tarjeta}/edit',          [TarjetaController::class, 'edit'])->name('edit');
            Route::put('/{tarjeta}',               [TarjetaController::class, 'update'])->name('update');
            Route::delete('/{tarjeta}',            [TarjetaController::class, 'destroy'])->name('destroy');
            Route::post('/{tarjeta}/toggle-block', [TarjetaController::class, 'toggleBlock'])->name('toggle-block');

            Route::get('/reportes/index',                 [TarjetaReporteController::class, 'index'])->name('reportes.index');
            Route::get('/reportes/export-csv',            [TarjetaReporteController::class, 'exportCsv'])->name('reportes.export-csv');
            Route::get('/reportes/export-incidentes',     [TarjetaReporteController::class, 'exportIncidentesCsv'])->name('reportes.export-incidentes');
            Route::get('/reportes/export-lecturas-pdf',   [TarjetaReporteController::class, 'exportLecturasPdf'])->name('reportes.export-lecturas-pdf');
            Route::get('/reportes/export-modulo-csv',     [TarjetaReporteController::class, 'exportModuloCsv'])->name('reportes.export-modulo-csv');
            Route::get('/reportes/export-modulo-pdf',     [TarjetaReporteController::class, 'exportModuloPdf'])->name('reportes.export-modulo-pdf');
            Route::get('/reportes/export-incidentes-pdf', [TarjetaReporteController::class, 'exportIncidentesPdf'])->name('reportes.export-incidentes-pdf');
        });

        // Usuarios
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/',                        [UsuarioController::class, 'index'])->name('index');
            Route::get('/create',                  [UsuarioController::class, 'create'])->name('create');
            Route::post('/',                       [UsuarioController::class, 'store'])->name('store');
            Route::get('/{usuario}/edit',          [UsuarioController::class, 'edit'])->name('edit');
            Route::put('/{usuario}',               [UsuarioController::class, 'update'])->name('update');
            Route::delete('/{usuario}',            [UsuarioController::class, 'destroy'])->name('destroy');
            Route::post('/{usuario}/toggle-block', [UsuarioController::class, 'toggleBlock'])->name('toggle-block');
            Route::post('/{usuario}/reset-password',[UsuarioController::class, 'resetPassword'])->name('reset-password');
            Route::get('/export',                  [UsuarioController::class, 'export'])->name('export');
            Route::get('/export-by-role',          [UsuarioController::class, 'exportByRole'])->name('export-by-role');
            Route::get('/export-pdf',              [UsuarioController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-by-role-pdf',      [UsuarioController::class, 'exportByRolePdf'])->name('export-by-role-pdf');
        });

        // Roles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',           [RolController::class, 'index'])->name('index');
            Route::get('/create',     [RolController::class, 'create'])->name('create');
            Route::post('/',          [RolController::class, 'store'])->name('store');
            Route::get('/{rol}/edit', [RolController::class, 'edit'])->name('edit');
            Route::put('/{rol}',      [RolController::class, 'update'])->name('update');
            Route::delete('/{rol}',   [RolController::class, 'destroy'])->name('destroy');
        });

        // Permisos
        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/',               [PermisoController::class, 'index'])->name('index');
            Route::get('/create',         [PermisoController::class, 'create'])->name('create');
            Route::post('/',              [PermisoController::class, 'store'])->name('store');
            Route::get('/{permiso}/edit', [PermisoController::class, 'edit'])->name('edit');
            Route::put('/{permiso}',      [PermisoController::class, 'update'])->name('update');
            Route::delete('/{permiso}',   [PermisoController::class, 'destroy'])->name('destroy');
        });

        // Bitácora
        Route::prefix('bitacora')->name('bitacora.')->group(function () {
            Route::get('/accesos',                      [BitacoraController::class, 'accesos'])->name('accesos');
            Route::get('/actividad',                    [BitacoraController::class, 'actividad'])->name('actividad');
            Route::get('/export-accesos',               [BitacoraController::class, 'exportAccesos'])->name('export-accesos');
            Route::get('/export-actividad',             [BitacoraController::class, 'exportActividad'])->name('export-actividad');
            Route::get('/export-accesos-pdf',           [BitacoraController::class, 'exportAccesosPdf'])->name('export-accesos-pdf');
            Route::get('/export-accesos-periodo',       [BitacoraController::class, 'exportAccesosPeriodo'])->name('export-accesos-periodo');
            Route::get('/export-accesos-periodo-pdf',   [BitacoraController::class, 'exportAccesosPeriodoPdf'])->name('export-accesos-periodo-pdf');
            Route::get('/export-actividad-pdf',         [BitacoraController::class, 'exportActividadPdf'])->name('export-actividad-pdf');
            Route::get('/export-actividad-periodo',     [BitacoraController::class, 'exportActividadPeriodo'])->name('export-actividad-periodo');
            Route::get('/export-actividad-periodo-pdf', [BitacoraController::class, 'exportActividadPeriodoPdf'])->name('export-actividad-periodo-pdf');
            Route::get('/export-actividad-modulo',      [BitacoraController::class, 'exportActividadModulo'])->name('export-actividad-modulo');
            Route::get('/export-actividad-modulo-pdf',  [BitacoraController::class, 'exportActividadModuloPdf'])->name('export-actividad-modulo-pdf');
        });

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/usuarios',         [ReporteController::class, 'usuarios'])->name('usuarios');
            Route::get('/accesos',          [ReporteController::class, 'accesos'])->name('accesos');
            Route::get('/actividad',        [ReporteController::class, 'actividad'])->name('actividad');
            Route::get('/usuarios-export',  [ReporteController::class, 'exportUsuarios'])->name('usuarios.export');
            Route::get('/accesos-export',   [ReporteController::class, 'exportAccesos'])->name('accesos.export');
            Route::get('/actividad-export', [ReporteController::class, 'exportActividad'])->name('actividad.export');
        });
    });
});