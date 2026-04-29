<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Recargas\WalletController;
use App\Http\Controllers\Recargas\RecargaController;
use App\Http\Controllers\Recargas\ReportesController;

// Redirección inicial
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas protegidas por Autenticación
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard General
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [PerfilController::class, 'show'])->name('show');
        Route::post('/actualizar', [PerfilController::class, 'updateProfile'])->name('update');
        Route::post('/foto', [PerfilController::class, 'updatePhoto'])->name('photo.update');
        Route::delete('/foto', [PerfilController::class, 'deletePhoto'])->name('photo.delete');
    });

    // --- GRUPO DE ADMINISTRACIÓN (Solo rol administrador) ---
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {

        // Gestión de usuarios
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::post('usuarios/{usuario}/toggle-block', [UsuarioController::class, 'toggleBlock'])->name('usuarios.toggle-block');
        Route::post('usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');

        // Exportaciones de Usuarios
        Route::get('export/usuarios', [UsuarioController::class, 'export'])->name('usuarios.export');
        Route::get('export/usuarios-pdf', [UsuarioController::class, 'exportPdf'])->name('usuarios.export-pdf');

        // Roles y Permisos (Simplificado con Resource)
        Route::resource('roles', RolController::class);
        Route::resource('permisos', PermisoController::class);

        // Bitácoras y Reportes Administrativos
        Route::controller(BitacoraController::class)->prefix('bitacora')->name('bitacora.')->group(function () {
            Route::get('/accesos', 'accesos')->name('accesos');
            Route::get('/actividad', 'actividad')->name('actividad');
            // ... (tus otras rutas de exportación de bitácora se mantienen igual)
        });
    });

    // --- MÓDULO 8: RECARGAS Y WALLET (Accesible por Admin y Usuarios) ---
    // Lo sacamos del prefijo 'admin' para evitar conflictos de middleware
    Route::prefix('modulo_8')->name('modulo_8.')->group(function () {

        // Dashboard del Módulo 8
        Route::get('/', [ReportesController::class, 'dashboard'])->name('index');

        // Operaciones de Recarga
        Route::get('/recargar', [RecargaController::class, 'mostrarFormulario'])->name('recargar.form');

        Route::post('/recargar', [RecargaController::class, 'procesarRecarga'])->name('recargar');

        Route::post('/recargar/{id}/reintentar', [RecargaController::class, 'reintentar'])->name('recargar.reintentar');

        Route::get('/recargar/{id}/comprobante', [RecargaController::class, 'descargarComprobante'])->name('comprobante');

        // Reportes del Módulo 8
        Route::prefix('reportes')->name('reportes.')->group(function () {

            Route::get('/historial', [ReportesController::class, 'historialRecargas'])->name('historial');

            Route::get('/fallidos', [ReportesController::class, 'pagosFallidos'])->name('fallidos');
            
            Route::get('/conciliacion', [ReportesController::class, 'conciliacionPeriodo'])->name('conciliacion');
        });
    });
});
