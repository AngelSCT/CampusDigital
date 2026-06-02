<?php

//RUTAS DE LA API REST DE LA APLICACION ANADIR EN EL ORDEN CORRESPONDIENTE PORFAVOR

use Illuminate\Support\Facades\Route;

// MODULO 4.1
use App\Http\Controllers\Api\AreaApiController;
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\Api\RolApiController;
use App\Http\Controllers\Api\PermisoApiController;
use App\Http\Controllers\Api\RolPermisoApiController;
use App\Http\Controllers\Api\UsuarioRolApiController;
use App\Http\Controllers\Api\UsuarioPerfilApiController;
use App\Http\Controllers\Api\UsuarioSesionApiController;
use App\Http\Controllers\Api\BitacoraApiController;

// MODULO TICKETS
use App\Http\Controllers\Api\CategoriaTicketApiController;
use App\Http\Controllers\Api\UbicacionApiController;
use App\Http\Controllers\Api\EquipoActivoApiController;
use App\Http\Controllers\Api\MantenimientoPreventivoApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\AsignacionTecnicaApiController;
use App\Http\Controllers\Api\InsumoApiController;
use App\Http\Controllers\Api\GastoTicketApiController;
use App\Http\Controllers\Api\HistorialTicketApiController;

// MODULO 4.10
use App\Http\Controllers\Api\TarjetaUniversitariaApiController;
use App\Http\Controllers\Api\TarjetaLecturaApiController;
use App\Http\Controllers\Api\SaldoMonederoApiController;
use App\Http\Controllers\Api\SaldoMovimientoApiController;
use App\Http\Controllers\Api\PedidoApiController;

// MODULO CARRITO / TIENDA
use App\Http\Controllers\CartController;
use App\Http\Controllers\Api\CartApiController;

// MODULO RECARGAS (US2)
use App\Http\Controllers\Api\RecargaApiController;

// RUTAS EXTRA CON UID Y EL PIN
use App\Http\Controllers\Api\RfidApiController;

// MODULO INVENTARIO
use App\Http\Controllers\Api\InventarioApiController;
use App\Http\Controllers\Api\CatalogoApiController;
use App\Http\Controllers\Api\CatalogoVendedorApiController;
use App\Http\Controllers\Api\CategoriaApiController;
use App\Http\Controllers\Api\PromocionApiController;
use App\Http\Controllers\Api\PrecioApiController;
use App\Http\Controllers\Api\DisponibilidadApiController;
use App\Http\Controllers\Api\ReglaApiController;
use App\Http\Controllers\Api\MovimientoApiController;

// ── INTEGRACIÓN 4.3 → 4.9 ────────────────────────────────────────────────────
use App\Http\Controllers\Api\CatalogoIntegracionApiController;

Route::middleware('api.key')->group(function () {

    Route::apiResource('areas', AreaApiController::class);
    Route::apiResource('categorias-ticket', CategoriaTicketApiController::class);
    Route::apiResource('ubicaciones', UbicacionApiController::class);
    Route::apiResource('equipos-activos', EquipoActivoApiController::class);
    Route::apiResource('mantenimientos-preventivos', MantenimientoPreventivoApiController::class);
    Route::apiResource('tickets', TicketApiController::class);
    Route::apiResource('asignaciones-tecnicas', AsignacionTecnicaApiController::class);
    Route::apiResource('insumos', InsumoApiController::class);
    Route::apiResource('gastos-ticket', GastoTicketApiController::class);
    Route::apiResource('historial-tickets', HistorialTicketApiController::class);
    Route::apiResource('inventario', InventarioApiController::class);
    Route::get('inventario-stock-bajo', [InventarioApiController::class, 'stockBajo']);

    // Control de CRUDs adicionales (catalogos, vendedores, categorias, promociones, precios, disponibilidades, reglas, movimientos)
    Route::apiResource('catalogos', CatalogoApiController::class);
    Route::apiResource('vendedores', CatalogoVendedorApiController::class);
    Route::apiResource('categorias', CategoriaApiController::class);
    Route::apiResource('promociones', PromocionApiController::class);
    Route::apiResource('precios', PrecioApiController::class);
    Route::apiResource('disponibilidades', DisponibilidadApiController::class);
    Route::apiResource('reglas', ReglaApiController::class);
    Route::apiResource('movimientos', MovimientoApiController::class);

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

    Route::get   ('pedidos',                        [PedidoApiController::class, 'index']);
    Route::post  ('pedidos',                        [PedidoApiController::class, 'store']);
    Route::get   ('pedidos/{id}',                   [PedidoApiController::class, 'show']);
    Route::put   ('pedidos/{id}',                   [PedidoApiController::class, 'update']);
    Route::delete('pedidos/{id}',                   [PedidoApiController::class, 'destroy']);
    Route::post  ('pedidos/{id}/estado',            [PedidoApiController::class, 'cambiarEstado']);
    Route::post  ('pedidos/{id}/confirmar-tarjeta', [PedidoApiController::class, 'confirmarConTarjeta']);

    // ── RECARGAS (US2) ──────────────────────────────────────────────────────────
    Route::get ('recargas',                      [RecargaApiController::class, 'index']);
    Route::post('recargas',                      [RecargaApiController::class, 'store']);
    Route::get ('recargas/{id}',                 [RecargaApiController::class, 'show']);
    Route::get ('recargas/usuario/{usuario_id}', [RecargaApiController::class, 'porUsuario']);

    // ── INTEGRACIÓN MÓDULO 4.3 → 4.9 ─────────────────────────────────────────
    // Catálogo por vendedor: precio + disponibilidad + regla ya resueltos.
    // Consumido por el panel operativo del módulo 4.9 (ProductoController).
    Route::get('catalogo-integracion/vendedores',             [CatalogoIntegracionApiController::class, 'vendedores']);
    Route::get('catalogo-integracion/vendedor/{id_vendedor}', [CatalogoIntegracionApiController::class, 'porVendedor']);
});


// ── CARRITO / TIENDA (auth) ─────────────────────────────────────────────────
Route::middleware('auth')->prefix('carrito')->group(function () {
    Route::get   ('/',                   [CartController::class, 'index']);         // ver carrito + saldo monedero
    Route::post  ('/',                   [CartController::class, 'store']);         // agregar producto
    Route::patch ('{item}/wishlist',     [CartController::class, 'moverWishlist']); // mover a/desde wishlist
    Route::patch ('{item}/regalo',       [CartController::class, 'marcarRegalo']);  // marcar como regalo
    Route::post  ('limpiar',             [CartController::class, 'limpiarInactivos']); // limpiar inactivos
    Route::post  ('checkout',            [CartController::class, 'checkout']);      // procesar checkout
});

// ── VALIDACIÓN PÚBLICA DE TICKETS (sin API key, para escaneo QR) ───────────
Route::get('/v1/validar-ticket/{hash}', [CartApiController::class, 'validarHash']);

// ── VALIDACIÓN PÚBLICA DE REGALOS (sin API key, para el destinatario) ──────
Route::get('/v1/regalo/validar/{hash}', [CartController::class, 'validarRegalo']);

// ── CARRITO API v1 (interoperabilidad módulo 4.4) ──────────────────────────
Route::middleware('api.key')->prefix('v1/carrito')->group(function () {
    Route::get('usuarios/{id}/stats',    [CartApiController::class, 'getUserStats']);
    Route::get('stats/global',           [CartApiController::class, 'getGlobalStats']);
    Route::get('usuarios/{id}/historial',[CartApiController::class, 'getOrderHistory']);
});

// ── MÓDULO CARRITO — Endpoints privados (requieren JWT de módulo) ──────────
Route::middleware('auth.module.jwt')->prefix('cart')->group(function () {
    Route::post  ('carritos',                                [\App\Http\Controllers\Api\Cart\CarritoController::class, 'store']);
    Route::get   ('carritos/{uuid}',                         [\App\Http\Controllers\Api\Cart\CarritoController::class, 'show']);
    Route::post  ('carritos/{uuid}/items',                   [\App\Http\Controllers\Api\Cart\ItemController::class,    'store']);
    Route::delete('carritos/{uuid}/items/{item_id}',         [\App\Http\Controllers\Api\Cart\ItemController::class,    'destroy']);
    Route::post  ('carritos/{uuid}/checkout',                [\App\Http\Controllers\Api\Cart\CheckoutController::class,'checkout']);
    Route::post  ('carritos/{uuid}/cancelar',                [\App\Http\Controllers\Api\Cart\CarritoController::class, 'cancelar']);
    Route::get   ('historico',                               [\App\Http\Controllers\Api\Cart\CarritoController::class, 'historico']);
    Route::post  ('carritos/{uuid}/items/{item_id}/devolver',[\App\Http\Controllers\Api\Cart\ItemController::class,    'devolver']);
});

// ── MÓDULO CARRITO — Token refresh (sin auth.module.jwt; recibe refresh, no access) ──
Route::post('cart/tokens/refresh', [\App\Http\Controllers\Cart\TokenRefreshController::class, 'refresh']);

// ── MÓDULO CARRITO — APIs Públicas (sin autenticación) ─────────────────────
Route::prefix('public')->group(function () {
    Route::get('categorias', [\App\Http\Controllers\Cart\CategoriasPublicController::class, 'index']);
    Route::post('modulos/solicitud', [\App\Http\Controllers\Cart\SolicitudModuloController::class, 'store']);
    Route::get('modulos/solicitud/{folio}/estado', [\App\Http\Controllers\Cart\SolicitudModuloController::class, 'estado']);
});

Route::prefix('rfid')->group(function () {

    Route::post('/auth', [RfidApiController::class, 'auth']);

    Route::middleware('api.key')->group(function () {
        Route::post('/verificar',        [RfidApiController::class, 'verificar']);
        Route::get ('/usuario/{uid}',    [RfidApiController::class, 'datosUsuario']);
        Route::get ('/saldo/{uid}',      [RfidApiController::class, 'saldo']);
        Route::get ('/historial/{uid}',  [RfidApiController::class, 'historial']);
        Route::get ('/pedidos/{uid}',    [RfidApiController::class, 'pedidosPendientes']);
        Route::get ('/lecturas/{uid}',   [RfidApiController::class, 'lecturas']);
    });
});
