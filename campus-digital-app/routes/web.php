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
use App\Http\Controllers\Archivos\ArchivoController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\CategoriaTicketController;
use App\Http\Controllers\Admin\UbicacionController;
use App\Http\Controllers\Admin\EquipoActivoController;


Route::get('/', function () {
    return redirect()->route('login');
});


Route::post('/auth/rfid-login', [RfidLoginController::class, 'login'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('rfid.login');


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

Route::options('/simulador/uid', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});

Route::get('/simulador/uid-pendiente', function () {
        return response()->json([
            'uid'       => Cache::get('simulador_uid_pendiente'),
            'timestamp' => Cache::get('simulador_uid_timestamp'),
        ]);
})->name('simulador.uid-pendiente');

Route::post('/simulador/login-rfid', function (Request $request) {
    $uid = strtoupper(trim($request->input('uid', '')));
    $pin = $request->input('pin', '');

    if (!$uid || !$pin) {
        return response()->json(['error' => 'Datos incompletos'], 400)
            ->header('Access-Control-Allow-Origin', '*');
    }

    Cache::put('simulador_login_uid', $uid, 60);
    Cache::put('simulador_login_pin', $pin, 60);
    Cache::put('simulador_login_timestamp', now()->toIso8601String(), 60);

    return response()->json(['ok' => true])
        ->header('Access-Control-Allow-Origin', '*');
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::options('/simulador/login-rfid', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});

Route::get('/simulador/login-pendiente', function () {
    return response()->json([
        'uid'       => Cache::get('simulador_login_uid'),
        'pin'       => Cache::get('simulador_login_pin'),
        'timestamp' => Cache::get('simulador_login_timestamp'),
    ]);
})->name('simulador.login-pendiente');

Route::post('/simulador/limpiar-login', function () {
    Cache::forget('simulador_login_uid');
    Cache::forget('simulador_login_pin');
    Cache::forget('simulador_login_timestamp');
    return response()->json(['ok' => true]);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sin-permiso', function () {
        return inertia('Errors/SinPermiso');
    })->name('sin-permiso');

    Route::get('/lector',                  [TarjetaLecturaController::class, 'index'])->name('lector.index');
    Route::post('/lector/leer',            [TarjetaLecturaController::class, 'leer'])->name('lector.leer');
    Route::post('/lector/confirmar-pedido',[TarjetaLecturaController::class, 'confirmarPedido'])->name('lector.confirmar-pedido');


    Route::middleware(['role'])
        ->prefix('mi-tarjeta')
        ->name('mi-tarjeta.')
        ->group(function () {
            Route::get('/', [MiTarjetaController::class, 'show'])
                ->middleware('permission:card.read')
                ->name('show');
            Route::post('/escanear', [MiTarjetaController::class, 'simularEscaneo'])
                ->middleware('permission:card.auth')
                ->name('escanear');
            Route::get('/pin',  [MiTarjetaController::class, 'showPin'])->name('pin');
            Route::post('/pin', [MiTarjetaController::class, 'updatePin'])->name('pin.store');
        });

    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/',            [PerfilController::class, 'show'])->name('show');
        Route::post('/actualizar', [PerfilController::class, 'updateProfile'])->name('update');
        Route::post('/foto',       [PerfilController::class, 'updatePhoto'])->name('photo.update');
        Route::delete('/foto',     [PerfilController::class, 'deletePhoto'])->name('photo.delete');
    });

    Route::prefix('archivos')->name('archivos.')->middleware('permission:file.read')->group(function () {
        Route::get('/',                                    [ArchivoController::class, 'index'])->name('index');
        Route::post('/carpeta',                            [ArchivoController::class, 'crearCarpeta'])->middleware('permission:file.write')->name('carpeta.crear');
        Route::delete('/carpeta/{carpeta}',                [ArchivoController::class, 'eliminarCarpeta'])->middleware('permission:file.delete')->name('carpeta.eliminar');
        Route::post('/carpeta/{carpeta}/renombrar',        [ArchivoController::class, 'renombrarCarpeta'])->middleware('permission:file.write')->name('carpeta.renombrar');
        Route::post('/subir',                              [ArchivoController::class, 'subir'])->middleware('permission:file.write')->name('subir');
        Route::get('/{archivo}/descargar',                 [ArchivoController::class, 'descargar'])->name('descargar');
        Route::get('/{archivo}/previsualizar',             [ArchivoController::class, 'previsualizar'])->name('previsualizar');
        Route::delete('/{archivo}',                        [ArchivoController::class, 'eliminarArchivo'])->middleware('permission:file.delete')->name('eliminar');
        Route::post('/{archivo}/marcar-visto',             [ArchivoController::class, 'marcarVisto'])->middleware('permission:file.admin')->name('marcar-visto');
        Route::post('/{archivo}/desmarcar-visto',          [ArchivoController::class, 'desmarcarVisto'])->middleware('permission:file.admin')->name('desmarcar-visto');
        Route::post('/{archivo}/nota',                     [ArchivoController::class, 'agregarNota'])->middleware('permission:file.admin')->name('nota');
    });

    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {

        Route::prefix('tarjetas')->name('tarjetas.')->group(function () {

            Route::get('/dashboard', [TarjetaDashboardController::class, 'index'])
                ->middleware('permission:card.read.any')
                ->name('dashboard');

            Route::get('/', [TarjetaController::class, 'index'])
                ->middleware('permission:card.read.any')
                ->name('index');

            Route::get('/create', [TarjetaController::class, 'create'])
                ->middleware('permission:card.write')
                ->name('create');

            Route::post('/', [TarjetaController::class, 'store'])
                ->middleware('permission:card.write')
                ->name('store');

            Route::get('/{tarjeta}', [TarjetaController::class, 'show'])
                ->middleware('permission:card.read.any')
                ->name('show');

            Route::get('/{tarjeta}/edit', [TarjetaController::class, 'edit'])
                ->middleware('permission:card.write')
                ->name('edit');

            Route::put('/{tarjeta}', [TarjetaController::class, 'update'])
                ->middleware('permission:card.write')
                ->name('update');

            Route::delete('/{tarjeta}', [TarjetaController::class, 'destroy'])
                ->middleware('permission:card.write')
                ->name('destroy');

            Route::post('/{tarjeta}/toggle-block', [TarjetaController::class, 'toggleBlock'])
                ->middleware('permission:card.block')
                ->name('toggle-block');

            Route::prefix('reportes')->name('reportes.')->middleware('permission:report.cards')->group(function () {
                Route::get('/index',                 [TarjetaReporteController::class, 'index'])->name('index');
                Route::get('/export-csv',            [TarjetaReporteController::class, 'exportCsv'])->name('export-csv');
                Route::get('/export-incidentes',     [TarjetaReporteController::class, 'exportIncidentesCsv'])->name('export-incidentes');
                Route::get('/export-lecturas-pdf',   [TarjetaReporteController::class, 'exportLecturasPdf'])->name('export-lecturas-pdf');
                Route::get('/export-modulo-csv',     [TarjetaReporteController::class, 'exportModuloCsv'])->name('export-modulo-csv');
                Route::get('/export-modulo-pdf',     [TarjetaReporteController::class, 'exportModuloPdf'])->name('export-modulo-pdf');
                Route::get('/export-incidentes-pdf', [TarjetaReporteController::class, 'exportIncidentesPdf'])->name('export-incidentes-pdf');
            });
        });


        Route::prefix('usuarios')->name('usuarios.')->group(function () {


            Route::get('/', [UsuarioController::class, 'index'])
                ->middleware('permission:user.read')
                ->name('index');


            Route::get('/create', [UsuarioController::class, 'create'])
                ->middleware('permission:user.write')
                ->name('create');

            Route::get('/export',            [UsuarioController::class, 'export'])->middleware('permission:report.users')->name('export');
            Route::get('/export-by-role',    [UsuarioController::class, 'exportByRole'])->middleware('permission:report.users')->name('export-by-role');
            Route::get('/export-pdf',        [UsuarioController::class, 'exportPdf'])->middleware('permission:report.users')->name('export-pdf');
            Route::get('/export-by-role-pdf',[UsuarioController::class, 'exportByRolePdf'])->middleware('permission:report.users')->name('export-by-role-pdf');

            Route::post('/', [UsuarioController::class, 'store'])
                ->middleware('permission:user.write')
                ->name('store');

            Route::get('/{usuario}', [UsuarioController::class, 'show'])
                ->middleware('permission:user.show')
                ->name('show');

            Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])
                ->middleware('permission:user.write')
                ->name('edit');

            Route::put('/{usuario}', [UsuarioController::class, 'update'])
                ->middleware('permission:user.write')
                ->name('update');

            Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])
                ->middleware('permission:user.delete')
                ->name('destroy');

            Route::post('/{usuario}/toggle-block', [UsuarioController::class, 'toggleBlock'])
                ->middleware('permission:user.block')
                ->name('toggle-block');

            Route::post('/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])
                ->middleware('permission:user.write')
                ->name('reset-password');

        });


        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',           [RolController::class, 'index'])->middleware('permission:role.read')->name('index');
            Route::get('/create',     [RolController::class, 'create'])->middleware('permission:role.write')->name('create');
            Route::post('/',          [RolController::class, 'store'])->middleware('permission:role.write')->name('store');
            Route::get('/{rol}',      [RolController::class, 'show'])->middleware('permission:role.show')->name('show'); 
            Route::get('/{rol}/edit', [RolController::class, 'edit'])->middleware('permission:role.write')->name('edit');
            Route::put('/{rol}',      [RolController::class, 'update'])->middleware('permission:role.write')->name('update');
            Route::delete('/{rol}',   [RolController::class, 'destroy'])->middleware('permission:role.delete')->name('destroy');
        });


        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/',               [PermisoController::class, 'index'])->middleware('permission:permission.read')->name('index');
            Route::get('/create',         [PermisoController::class, 'create'])->middleware('permission:permission.write')->name('create');
            Route::post('/',              [PermisoController::class, 'store'])->middleware('permission:permission.write')->name('store');
            Route::get('/{permiso}',      [PermisoController::class, 'show'])->middleware('permission:permission.show')->name('show');  
            Route::get('/{permiso}/edit', [PermisoController::class, 'edit'])->middleware('permission:permission.write')->name('edit');
            Route::put('/{permiso}',      [PermisoController::class, 'update'])->middleware('permission:permission.write')->name('update');
            Route::delete('/{permiso}',   [PermisoController::class, 'destroy'])->middleware('permission:permission.delete')->name('destroy');
        });

        Route::prefix('bitacora')->name('bitacora.')->middleware('permission:audit.read')->group(function () {
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


        Route::prefix('reportes')->name('reportes.')->middleware('permission:report.users')->group(function () {
            Route::get('/usuarios',         [ReporteController::class, 'usuarios'])->name('usuarios');
            Route::get('/accesos',          [ReporteController::class, 'accesos'])->name('accesos');
            Route::get('/actividad',        [ReporteController::class, 'actividad'])->name('actividad');
            Route::get('/usuarios-export',  [ReporteController::class, 'exportUsuarios'])->name('usuarios.export');
            Route::get('/accesos-export',   [ReporteController::class, 'exportAccesos'])->name('accesos.export');
            Route::get('/actividad-export', [ReporteController::class, 'exportActividad'])->name('actividad.export');
        });

        Route::prefix('areas')->name('areas.')->group(function () {
            Route::get('/',          [AreaController::class, 'index'])->name('index');
            Route::post('/',         [AreaController::class, 'store'])->name('store');
            Route::get('/{area}',    [AreaController::class, 'show'])->name('show');
            Route::put('/{area}',    [AreaController::class, 'update'])->name('update');
            Route::delete('/{area}', [AreaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('categorias-ticket')->name('categorias-ticket.')->group(function () {
            Route::get('/',                       [CategoriaTicketController::class, 'index'])->name('index');
            Route::post('/',                      [CategoriaTicketController::class, 'store'])->name('store');
            Route::get('/{categoriaTicket}',      [CategoriaTicketController::class, 'show'])->name('show');
            Route::put('/{categoriaTicket}',      [CategoriaTicketController::class, 'update'])->name('update');
            Route::delete('/{categoriaTicket}',   [CategoriaTicketController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('ubicaciones')->name('ubicaciones.')->group(function () {
            Route::get('/',              [UbicacionController::class, 'index'])->name('index');
            Route::post('/',             [UbicacionController::class, 'store'])->name('store');
            Route::get('/{ubicacion}',   [UbicacionController::class, 'show'])->name('show');
            Route::put('/{ubicacion}',   [UbicacionController::class, 'update'])->name('update');
            Route::delete('/{ubicacion}',[UbicacionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('equipos-activos')->name('equipos-activos.')->group(function () {
            Route::get('/',                [EquipoActivoController::class, 'index'])->name('index');
            Route::post('/',               [EquipoActivoController::class, 'store'])->name('store');
            Route::get('/{equipoActivo}',  [EquipoActivoController::class, 'show'])->name('show');
            Route::put('/{equipoActivo}',  [EquipoActivoController::class, 'update'])->name('update');
            Route::delete('/{equipoActivo}',[EquipoActivoController::class, 'destroy'])->name('destroy');
        });
    });
});