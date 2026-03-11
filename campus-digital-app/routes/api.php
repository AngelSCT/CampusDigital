<?php

//RUTAS DE LA API REST DE LA APLICACION

use Illuminate\Support\Facades\Route;

// MÓDULO 4.1 — USUARIOS Y SEGURIDAD
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\Api\RolApiController;
use App\Http\Controllers\Api\PermisoApiController;
use App\Http\Controllers\Api\RolPermisoApiController;
use App\Http\Controllers\Api\UsuarioRolApiController;
use App\Http\Controllers\Api\UsuarioPerfilApiController;
use App\Http\Controllers\Api\UsuarioSesionApiController;
use App\Http\Controllers\Api\BitacoraApiController;

// MÓDULO 4.10 — TARJETA UNIVERSITARIA (RFID/NFC)
use App\Http\Controllers\Api\TarjetaUniversitariaApiController;
use App\Http\Controllers\Api\TarjetaLecturaApiController;

// MÓDULO 4.2 — SALDO DIGITAL (MONEDERO)
use App\Http\Controllers\Api\SaldoMonederoApiController;
use App\Http\Controllers\Api\SaldoMovimientoApiController;

// MÓDULO 4.5 — PEDIDOS Y SEGUIMIENTO
use App\Http\Controllers\Api\PedidoApiController;

// PROTEGIDAS MEDIANTE LA APIKEY
Route::middleware('api.key')->group(function () {

    // ── USUARIOS ─────────────────────────────────────────────────────────────
    Route::apiResource('usuarios', UsuarioApiController::class);
    Route::post('usuarios/{id}/toggle-block', [UsuarioApiController::class, 'toggleBlock']);

    // ── ROLES ─────────────────────────────────────────────────────────────────
    Route::apiResource('roles', RolApiController::class);

    // ── PERMISOS ──────────────────────────────────────────────────────────────
    Route::apiResource('permisos', PermisoApiController::class);

    // ── ROL-PERMISOS ──────────────────────────────────────────────────────────
    Route::get   ('rol-permisos',      [RolPermisoApiController::class, 'index']);
    Route::post  ('rol-permisos',      [RolPermisoApiController::class, 'store']);
    Route::delete('rol-permisos/{id}', [RolPermisoApiController::class, 'destroy']);

    // ── USUARIO-ROL ───────────────────────────────────────────────────────────
    Route::get   ('usuario-roles',      [UsuarioRolApiController::class, 'index']);
    Route::post  ('usuario-roles',      [UsuarioRolApiController::class, 'store']);
    Route::delete('usuario-roles/{id}', [UsuarioRolApiController::class, 'destroy']);

    // ── PERFILES ──────────────────────────────────────────────────────────────
    Route::get('usuario-perfiles',      [UsuarioPerfilApiController::class, 'index']);
    Route::get('usuario-perfiles/{id}', [UsuarioPerfilApiController::class, 'show']);
    Route::put('usuario-perfiles/{id}', [UsuarioPerfilApiController::class, 'update']);

    // ── SESIONES ──────────────────────────────────────────────────────────────
    Route::get('sesiones',      [UsuarioSesionApiController::class, 'index']);
    Route::get('sesiones/{id}', [UsuarioSesionApiController::class, 'show']);

    // ── BITACORA-ACCESOS ──────────────────────────────────────────────────────
    Route::get('bitacora/accesos',        [BitacoraApiController::class, 'accesos']);
    Route::get('bitacora/accesos/{id}',   [BitacoraApiController::class, 'acceso']);

    // ── BITACORA-ACTIVIDAD ────────────────────────────────────────────────────
    Route::get('bitacora/actividad',      [BitacoraApiController::class, 'actividad']);
    Route::get('bitacora/actividad/{id}', [BitacoraApiController::class, 'actividadItem']);

    // ── TARJETAS UNIVERSITARIAS ───────────────────────────────────────────────
    Route::get   ('tarjetas',                  [TarjetaUniversitariaApiController::class, 'index']);
    Route::post  ('tarjetas',                  [TarjetaUniversitariaApiController::class, 'store']);
    Route::get   ('tarjetas/uid/{uid}',        [TarjetaUniversitariaApiController::class, 'buscarPorUid']);
    Route::get   ('tarjetas/{id}',             [TarjetaUniversitariaApiController::class, 'show']);
    Route::put   ('tarjetas/{id}',             [TarjetaUniversitariaApiController::class, 'update']);
    Route::delete('tarjetas/{id}',             [TarjetaUniversitariaApiController::class, 'destroy']);
    Route::post  ('tarjetas/{id}/bloquear',    [TarjetaUniversitariaApiController::class, 'bloquear']);
    Route::post  ('tarjetas/{id}/desbloquear', [TarjetaUniversitariaApiController::class, 'desbloquear']);

    // ── LECTURAS DE TARJETA ───────────────────────────────────────────────────
    Route::get ('tarjeta-lecturas',      [TarjetaLecturaApiController::class, 'index']);
    Route::post('tarjeta-lecturas',      [TarjetaLecturaApiController::class, 'store']);
    Route::get ('tarjeta-lecturas/{id}', [TarjetaLecturaApiController::class, 'show']);

    // ── SALDO MONEDERO ────────────────────────────────────────────────────────
    Route::get ('saldo-monederos',                      [SaldoMonederoApiController::class, 'index']);
    Route::post('saldo-monederos',                      [SaldoMonederoApiController::class, 'store']);
    Route::get ('saldo-monederos/usuario/{usuario_id}', [SaldoMonederoApiController::class, 'porUsuario']);
    Route::get ('saldo-monederos/{id}',                 [SaldoMonederoApiController::class, 'show']);

    // ── SALDO MOVIMIENTOS ─────────────────────────────────────────────────────
    Route::get ('saldo-movimientos',      [SaldoMovimientoApiController::class, 'index']);
    Route::post('saldo-movimientos',      [SaldoMovimientoApiController::class, 'store']);
    Route::get ('saldo-movimientos/{id}', [SaldoMovimientoApiController::class, 'show']);

    // ── PEDIDOS ───────────────────────────────────────────────────────────────
    Route::get   ('pedidos',                        [PedidoApiController::class, 'index']);
    Route::post  ('pedidos',                        [PedidoApiController::class, 'store']);
    Route::get   ('pedidos/{id}',                   [PedidoApiController::class, 'show']);
    Route::put   ('pedidos/{id}',                   [PedidoApiController::class, 'update']);
    Route::delete('pedidos/{id}',                   [PedidoApiController::class, 'destroy']);
    Route::post  ('pedidos/{id}/estado',            [PedidoApiController::class, 'cambiarEstado']);
    Route::post  ('pedidos/{id}/confirmar-tarjeta', [PedidoApiController::class, 'confirmarConTarjeta']);
});