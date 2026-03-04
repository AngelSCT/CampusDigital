<?php

//RUTAS DE LA API REST DE LA APLICACION

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\Api\RolApiController;
use App\Http\Controllers\Api\PermisoApiController;
use App\Http\Controllers\Api\RolPermisoApiController;
use App\Http\Controllers\Api\UsuarioRolApiController;
use App\Http\Controllers\Api\UsuarioPerfilApiController;
use App\Http\Controllers\Api\UsuarioSesionApiController;
use App\Http\Controllers\Api\BitacoraApiController;

// PROTEGIDAS MEDIANTE LA APIKEY
Route::middleware('api.key')->group(function () {

    // USUARIOS
    Route::apiResource('usuarios', UsuarioApiController::class);
    Route::post('usuarios/{id}/toggle-block', [UsuarioApiController::class, 'toggleBlock']);

    // ROLES
    Route::apiResource('roles', RolApiController::class);

    // PERMISOS
    Route::apiResource('permisos', PermisoApiController::class);

    // ROL-PERMISOS
    Route::get   ('rol-permisos',      [RolPermisoApiController::class, 'index']);
    Route::post  ('rol-permisos',      [RolPermisoApiController::class, 'store']);
    Route::delete('rol-permisos/{id}', [RolPermisoApiController::class, 'destroy']);

    // USUARIO-ROL
    Route::get   ('usuario-roles',      [UsuarioRolApiController::class, 'index']);
    Route::post  ('usuario-roles',      [UsuarioRolApiController::class, 'store']);
    Route::delete('usuario-roles/{id}', [UsuarioRolApiController::class, 'destroy']);

    // PERFILES
    Route::get('usuario-perfiles',      [UsuarioPerfilApiController::class, 'index']);
    Route::get('usuario-perfiles/{id}', [UsuarioPerfilApiController::class, 'show']);
    Route::put('usuario-perfiles/{id}', [UsuarioPerfilApiController::class, 'update']);

    // SESSIONES
    Route::get('sesiones',      [UsuarioSesionApiController::class, 'index']);
    Route::get('sesiones/{id}', [UsuarioSesionApiController::class, 'show']);

    // BITACORA-ACCESOS
    Route::get('bitacora/accesos',        [BitacoraApiController::class, 'accesos']);
    Route::get('bitacora/accesos/{id}',   [BitacoraApiController::class, 'acceso']);

    // BITACORA-ACTIVIDAD
    Route::get('bitacora/actividad',      [BitacoraApiController::class, 'actividad']);
    Route::get('bitacora/actividad/{id}', [BitacoraApiController::class, 'actividadItem']);
});

