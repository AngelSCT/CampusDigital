<?php

//RUTAS DE LA API REST DE LA APLICACION

use Illuminate\Support\Facades\Route;

// MODULO 4.1
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\Api\RolApiController;
use App\Http\Controllers\Api\PermisoApiController;
use App\Http\Controllers\Api\RolPermisoApiController;
use App\Http\Controllers\Api\UsuarioRolApiController;
use App\Http\Controllers\Api\UsuarioPerfilApiController;
use App\Http\Controllers\Api\UsuarioSesionApiController;
use App\Http\Controllers\Api\BitacoraApiController;

// MODULO 4.10
use App\Http\Controllers\Api\TarjetaUniversitariaApiController;
use App\Http\Controllers\Api\TarjetaLecturaApiController;
use App\Http\Controllers\Api\SaldoMonederoApiController;
use App\Http\Controllers\Api\SaldoMovimientoApiController;
use App\Http\Controllers\Api\PedidoApiController;

Route::middleware('api.key')->group(function () {

    Route::apiResource('usuarios', UsuarioApiController::class);
    Route::post('usuarios/{id}/toggle-block', [UsuarioApiController::class, 'toggleBlock']);
    Route::apiResource('roles', RolApiController::class);
    Route::apiResource('permisos', PermisoApiController::class);

    Route::get   ('rol-permisos',      [RolPermisoApiController::class, 'index']);
    Route::post  ('rol-permisos',      [RolPermisoApiController::class, 'store']);
    Route::delete('rol-permisos/{id}', [RolPermisoApiController::class, 'destroy']);

    Route::get   ('usuario-roles',      [UsuarioRolApiController::class, 'index']);
    Route::post  ('usuario-roles',      [UsuarioRolApiController::class, 'store']);
    Route::delete('usuario-roles/{id}', [UsuarioRolApiController::class, 'destroy']);

    Route::get('usuario-perfiles',      [UsuarioPerfilApiController::class, 'index']);
    Route::get('usuario-perfiles/{id}', [UsuarioPerfilApiController::class, 'show']);
    Route::put('usuario-perfiles/{id}', [UsuarioPerfilApiController::class, 'update']);

    Route::get('sesiones',      [UsuarioSesionApiController::class, 'index']);
    Route::get('sesiones/{id}', [UsuarioSesionApiController::class, 'show']);

    Route::get('bitacora/accesos',        [BitacoraApiController::class, 'accesos']);
    Route::get('bitacora/accesos/{id}',   [BitacoraApiController::class, 'acceso']);

    Route::get('bitacora/actividad',      [BitacoraApiController::class, 'actividad']);
    Route::get('bitacora/actividad/{id}', [BitacoraApiController::class, 'actividadItem']);

    Route::get   ('tarjetas',                  [TarjetaUniversitariaApiController::class, 'index']);
    Route::post  ('tarjetas',                  [TarjetaUniversitariaApiController::class, 'store']);
    Route::get   ('tarjetas/uid/{uid}',        [TarjetaUniversitariaApiController::class, 'buscarPorUid']);
    Route::get   ('tarjetas/{id}',             [TarjetaUniversitariaApiController::class, 'show']);
    Route::put   ('tarjetas/{id}',             [TarjetaUniversitariaApiController::class, 'update']);
    Route::delete('tarjetas/{id}',             [TarjetaUniversitariaApiController::class, 'destroy']);
    Route::post  ('tarjetas/{id}/bloquear',    [TarjetaUniversitariaApiController::class, 'bloquear']);
    Route::post  ('tarjetas/{id}/desbloquear', [TarjetaUniversitariaApiController::class, 'desbloquear']);

    Route::get ('tarjeta-lecturas',      [TarjetaLecturaApiController::class, 'index']);
    Route::post('tarjeta-lecturas',      [TarjetaLecturaApiController::class, 'store']);
    Route::get ('tarjeta-lecturas/{id}', [TarjetaLecturaApiController::class, 'show']);

    Route::get ('saldo-monederos',                      [SaldoMonederoApiController::class, 'index']);
    Route::post('saldo-monederos',                      [SaldoMonederoApiController::class, 'store']);
    Route::get ('saldo-monederos/usuario/{usuario_id}', [SaldoMonederoApiController::class, 'porUsuario']);
    Route::get ('saldo-monederos/{id}',                 [SaldoMonederoApiController::class, 'show']);

    Route::get ('saldo-movimientos',      [SaldoMovimientoApiController::class, 'index']);
    Route::post('saldo-movimientos',      [SaldoMovimientoApiController::class, 'store']);
    Route::get ('saldo-movimientos/{id}', [SaldoMovimientoApiController::class, 'show']);

    // ── Pedidos ──────────────────────────────────────────────────
// Rutas FIJAS primero (antes de /{id}) para evitar que 'operador' se interprete como ID
Route::get   ('pedidos/operador/cola',          [PedidoApiController::class, 'colaOperador']);

// Rutas principales
Route::get   ('pedidos',                        [PedidoApiController::class, 'index']);
Route::post  ('pedidos',                        [PedidoApiController::class, 'store']);
Route::get   ('pedidos/{id}',                   [PedidoApiController::class, 'show'])->whereNumber('id');
Route::put   ('pedidos/{id}',                   [PedidoApiController::class, 'update'])->whereNumber('id');
Route::delete('pedidos/{id}',                   [PedidoApiController::class, 'destroy'])->whereNumber('id');

// Acciones sobre un pedido
Route::get   ('pedidos/{id}/historial',         [PedidoApiController::class, 'historial'])->whereNumber('id');
Route::post  ('pedidos/{id}/estado',            [PedidoApiController::class, 'cambiarEstado'])->whereNumber('id');
Route::post  ('pedidos/{id}/cancelar',          [PedidoApiController::class, 'cancelar'])->whereNumber('id');
Route::post  ('pedidos/{id}/confirmar-tarjeta', [PedidoApiController::class, 'confirmarConTarjeta'])->whereNumber('id');
});