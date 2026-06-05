<?php

use Inertia\Inertia;
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
use App\Http\Controllers\Pedidos\PedidoController;
use App\Http\Controllers\Pedidos\PedidoDashboardController;
use App\Http\Controllers\Pedidos\PedidoReporteController;
use App\Http\Controllers\RecargaController;
use App\Http\Controllers\Archivos\ArchivoController;
use App\Http\Controllers\Admin\AreaController as AdminAreaController;
use App\Http\Controllers\Admin\CategoriaTicketController;
use App\Http\Controllers\Admin\UbicacionController;
use App\Http\Controllers\Admin\EquipoActivoController;
use App\Http\Controllers\Admin\MantenimientoPreventivoController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\AsignacionTecnicaController;
use App\Http\Controllers\Admin\InsumoController;
use App\Http\Controllers\Admin\GastoTicketController;
use App\Http\Controllers\Admin\HistorialTicketController;
use App\Http\Controllers\CartController;
// Módulo 8 – namespace propio para evitar conflicto con RecargaController del monedero
use App\Http\Controllers\Recargas\WalletController;
use App\Http\Controllers\Recargas\RecargaController as RecargasRecargaController;
use App\Http\Controllers\Recargas\ReportesController;
use App\Http\Controllers\Proveedor\PedidoOperativoController;
use App\Http\Controllers\Proveedor\ProductoController;
use App\Http\Controllers\Api\PedidoApiController;
use App\Http\Controllers\Api\ProviderApiController;
use App\Http\Controllers\Admin\TiendaController;
use App\Http\Controllers\Admin\AdminProveedorController;
use App\Http\Controllers\Admin\RepartidorController;
use App\Http\Controllers\Admin\RepartidorRoleController;
use App\Http\Controllers\StoreManager\ManagerOperativoController;

// Redirección inicial
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\CatalogoDashboardController;
use App\Http\Controllers\PrecioController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ReglaController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\CatalogoVendedorController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\CatalogoUsuarioDemoController;
use App\Http\Controllers\InventarioController;

Route::get('/reglas', [ReglaController::class, 'index']);
Route::get('/reglas/create', [ReglaController::class, 'create']);
Route::post('/reglas', [ReglaController::class, 'store']);
Route::get('/reglas/{id}/edit', [ReglaController::class, 'edit']);
Route::put('/reglas/{id}', [ReglaController::class, 'update']);
Route::delete('/reglas/{id}', [ReglaController::class, 'destroy']);

Route::get('/vendedores', [VendedorController::class, 'index'])->name('vendedores.index');
Route::get('/vendedores/create', [VendedorController::class, 'create'])->name('vendedores.create');
Route::post('/vendedores', [VendedorController::class, 'store'])->name('vendedores.store');
Route::get('/vendedores/{id}/edit', [VendedorController::class, 'edit'])->name('vendedores.edit');
Route::put('/vendedores/{id}', [VendedorController::class, 'update'])->name('vendedores.update');
Route::delete('/vendedores/{id}', [VendedorController::class, 'destroy'])->name('vendedores.destroy');

Route::get('/disponibilidad', [DisponibilidadController::class, 'index']);
Route::get('/disponibilidad/create', [DisponibilidadController::class, 'create']);
Route::post('/disponibilidad', [DisponibilidadController::class, 'store']);
Route::get('/disponibilidad/{id}/edit', [DisponibilidadController::class, 'edit']);
Route::put('/disponibilidad/{id}', [DisponibilidadController::class, 'update']);
Route::delete('/disponibilidad/{id}', [DisponibilidadController::class, 'destroy']);

Route::get('/areas', [AreaController::class, 'index']);
Route::get('/areas/create', [AreaController::class, 'create']);
Route::post('/areas', [AreaController::class, 'store']);
Route::post('/areas/quick-store', [AreaController::class, 'quickStore'])->name('areas.quick-store');
Route::get('/areas/{id}/edit', [AreaController::class, 'edit']);
Route::put('/areas/{id}', [AreaController::class, 'update']);
Route::delete('/areas/{id}', [AreaController::class, 'destroy']);

Route::get('/catalogo-dashboard', [CatalogoDashboardController::class, 'index']);
Route::get('/catalogo-usuario-demo', [CatalogoUsuarioDemoController::class, 'index'])->name('catalogo-usuario-demo.index');

Route::get('/precios', [PrecioController::class, 'index']);
Route::get('/precios/create', [PrecioController::class, 'create']);
Route::post('/precios', [PrecioController::class, 'store']);

Route::get('/movimientos', [MovimientoController::class, 'index']);
Route::get('/movimientos/create', [MovimientoController::class, 'create']);
Route::post('/movimientos', [MovimientoController::class, 'store']);

Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
Route::get('/inventario/agregar-stock', [InventarioController::class, 'createAddStock'])->name('inventario.add-stock');
Route::post('/inventario/agregar-stock', [InventarioController::class, 'storeAddStock'])->name('inventario.store-add-stock');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/create', [CatalogoController::class, 'create'])->name('catalogo.create');
Route::post('/catalogo', [CatalogoController::class, 'store'])->name('catalogo.store');
Route::post('/catalogo/bulk-update', [CatalogoController::class, 'bulkUpdate'])->name('catalogo.bulk-update');
Route::patch('/catalogo/{id}/quick-update', [CatalogoController::class, 'quickUpdate'])->name('catalogo.quick-update');
Route::get('/catalogo/{id}/edit', [CatalogoController::class, 'edit'])->name('catalogo.edit');
Route::get('/catalogo/mi-carrito',
    [App\Http\Controllers\Catalogo\CarritoViewController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('catalogo.mi-carrito');
Route::get('/catalogo/{id}', [CatalogoController::class, 'show'])->middleware(['auth'])->name('catalogo.show');
Route::put('/catalogo/{id}', [CatalogoController::class, 'update'])->name('catalogo.update');
Route::delete('/catalogo/{id}', [CatalogoController::class, 'destroy'])->name('catalogo.destroy');

Route::get('/catalogo-vendedor', [CatalogoVendedorController::class, 'index'])->name('catalogo-vendedor.index');
Route::get('/catalogo-vendedor/create', [CatalogoVendedorController::class, 'create'])->name('catalogo-vendedor.create');
Route::post('/catalogo-vendedor', [CatalogoVendedorController::class, 'store'])->name('catalogo-vendedor.store');
Route::get('/catalogo-vendedor/{id}/edit', [CatalogoVendedorController::class, 'edit'])->name('catalogo-vendedor.edit');
Route::put('/catalogo-vendedor/{id}', [CatalogoVendedorController::class, 'update'])->name('catalogo-vendedor.update');
Route::delete('/catalogo-vendedor/{id}', [CatalogoVendedorController::class, 'destroy'])->name('catalogo-vendedor.destroy');

Route::get('/promociones', [PromocionController::class, 'index'])->name('promociones.index');
Route::get('/promociones/create', [PromocionController::class, 'create'])->name('promociones.create');
Route::post('/promociones', [PromocionController::class, 'store'])->name('promociones.store');
Route::get('/promociones/{id}/edit', [PromocionController::class, 'edit'])->name('promociones.edit');
Route::put('/promociones/{id}', [PromocionController::class, 'update'])->name('promociones.update');
Route::delete('/promociones/{id}', [PromocionController::class, 'destroy'])->name('promociones.destroy');

Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create');
Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
Route::post('/categorias/quick-store', [CategoriaController::class, 'quickStore'])->name('categorias.quick-store');
Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit');
Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return inertia('Auth/Login');
})->name('login');


Route::post('/auth/rfid-login', [RfidLoginController::class, 'login'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('rfid.login');

Route::post('/simulador/uid', function (Request $request) {
    $uid = strtoupper(trim($request->input('uid', '')));
    if (!$uid) {
        return response()->json(['error' => 'UID vacío'], 400)
            ->header('Access-Control-Allow-Origin', '*');
    }
    Cache::put('simulador_uid_pendiente', $uid, 30);
    Cache::put('simulador_uid_timestamp', now()->toIso8601String(), 30);
    return response()->json([
        'ok'  => true,
        'uid' => $uid
    ])->header('Access-Control-Allow-Origin', '*');
});

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
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

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
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


// ── PROVEEDOR (Módulo 4.9 – Preview) ─────────────────────────────────────────
Route::middleware(['auth'])->prefix('proveedor')->name('proveedor.')->group(function () {
    Route::get ('/pedidos',                        [\App\Http\Controllers\ProveedorController::class, 'index'])->name('pedidos');
    Route::post('/pedidos/{pedido}/cancelar',       [\App\Http\Controllers\ProveedorController::class, 'cancelarPedido'])->name('cancelar');
});

// ── CARRITO / TIENDA (solo auth, sin verified) ───────────────────────────────
Route::middleware(['auth'])->group(function () {
    // Rutas sin parámetro — deben ir ANTES de las rutas con {item}/{pedido}
    Route::get  ('/tienda',                               [\App\Http\Controllers\CatalogoEstudianteController::class, 'index'])->name('tienda.index');
    Route::get  ('/carrito',                              [CartController::class, 'indexWeb'])->name('carrito.index');
    Route::post ('/carrito/confirmar',                    [CartController::class, 'confirmarPedido'])->name('carrito.confirmar');
    Route::get  ('/carrito/exito',                        [CartController::class, 'exitoPedido'])->name('carrito.exito');
    Route::get  ('/carrito/dashboard',                    [CartController::class, 'dashboardData'])->name('carrito.dashboard');
    Route::get  ('/carrito/dashboard/exportar',           [CartController::class, 'exportarCSV'])->name('carrito.exportar');
    Route::get  ('/carrito/mis-regalos-recibidos',        [CartController::class, 'misRegalosRecibidos'])->name('carrito.mis-regalos');
    Route::post ('/carrito/regalos/{pedido}/aceptar',             [CartController::class, 'aceptarRegalo'])->name('carrito.regalos.aceptar');
    Route::post ('/carrito/regalos/{pedido}/rechazar',            [CartController::class, 'rechazarRegaloEscrow'])->name('carrito.regalos.rechazar');
    Route::post ('/carrito/regalos/{pedido}/cancelar-remitente',  [CartController::class, 'cancelarRegaloRemitente'])->name('carrito.regalos.cancelar-remitente');
    Route::post ('/carrito/validar-destinatario',         [CartController::class, 'validarDestinatario'])->name('carrito.validar-destinatario');
    // Rutas con parámetro
    Route::patch('/carrito/{itemId}/guardar',              [CartController::class, 'guardarParaDespues'])->name('carrito.guardar');
    Route::patch('/carrito/{item}/mover-al-carrito',      [CartController::class, 'moverAlCarrito'])->name('carrito.mover');
    Route::patch('/carrito/{itemId}/regalo',              [CartController::class, 'marcarRegalo'])->name('carrito.regalo');
    Route::post ('/carrito/{item}/rechazar-regalo',       [CartController::class, 'rechazarRegalo'])->name('carrito.rechazar-regalo');
    // ── Nuevo sistema: aceptar/rechazar regalo de cart_carritos ──────────────
    Route::post ('/carrito/cart-regalos/{uuid}/aceptar', [CartController::class, 'aceptarRegaloBeta'])->name('carrito.cart-regalo.aceptar');
    Route::post ('/carrito/cart-regalos/{uuid}/rechazar',[CartController::class, 'rechazarRegaloBeta'])->name('carrito.cart-regalo.rechazar');
    Route::post ('/carrito/pedido/{pedido}/cancelar-gracia', [CartController::class, 'cancelarConGracia'])->name('carrito.cancelar-gracia');
    Route::post ('/carrito/admin/pedidos-expirados',         [CartController::class, 'procesarPedidosExpirados'])->name('carrito.pedidos-expirados');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── MONEDERO / RECARGAS ──────────────────────────────────────────────
    Route::prefix('monedero')->name('monedero.')->group(function () {
        Route::get('/mi-saldo',  [RecargaController::class, 'miSaldo'])->name('mi-saldo');
        Route::get('/recargas',  [RecargaController::class, 'index'])->name('recargas');
        Route::post('/recargas', [RecargaController::class, 'store'])->name('recargas.store');
    });

    Route::get('/sin-permiso', function () {
        return inertia('Errors/SinPermiso');
    })->name('sin-permiso');

    Route::get('/lector',                   [TarjetaLecturaController::class, 'index'])->name('lector.index');
    Route::post('/lector/leer',             [TarjetaLecturaController::class, 'leer'])->name('lector.leer');
    Route::post('/lector/confirmar-pedido', [TarjetaLecturaController::class, 'confirmarPedido'])->name('lector.confirmar-pedido');

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

    // ── REPARTIDOR (INDIVIDUAL) ──────────────────────────────────
    Route::middleware(['role:repartidor'])->prefix('repartidor')->name('repartidor.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Repartidor\RepartidorDashboardController::class, 'index'])->name('dashboard');
        Route::post('/pedidos/{pedido}/estado', [\App\Http\Controllers\Repartidor\RepartidorDashboardController::class, 'actualizarEstado'])->name('pedido.estado');
    });

    Route::middleware(['role:proveedor_area'])->prefix('proveedor')->name('proveedor.')->group(function () {
        Route::get('/operativo', [App\Http\Controllers\DashboardController::class, 'dashboardProveedor'])->name('operativo.index');
        
        // Inventario
        Route::get('/inventario', [App\Http\Controllers\Proveedor\ProductoController::class, 'index'])->name('inventario.index');
        Route::post('/inventario', [App\Http\Controllers\Proveedor\ProductoController::class, 'store'])->name('inventario.store');
        Route::put('/inventario/{producto}', [App\Http\Controllers\Proveedor\ProductoController::class, 'update'])->name('inventario.update');
        Route::delete('/inventario/{producto}', [App\Http\Controllers\Proveedor\ProductoController::class, 'destroy'])->name('inventario.destroy');

        // Repartidores (Nuevo)
        Route::get('/repartidores', [App\Http\Controllers\Proveedor\RepartidorProveedorController::class, 'index'])->name('repartidores.index');
        Route::get('/api/usuarios/buscar', [App\Http\Controllers\Proveedor\RepartidorProveedorController::class, 'search'])->name('repartidores.search');
        Route::post('/repartidores/asignar', [App\Http\Controllers\Proveedor\RepartidorProveedorController::class, 'asignar'])->name('repartidores.asignar');
        Route::delete('/repartidores/{usuario}', [App\Http\Controllers\Proveedor\RepartidorProveedorController::class, 'desvincular'])->name('repartidores.destroy');

        Route::get('/reportes', function() {
            return Inertia::render('Proveedor/Reportes');
        })->name('reportes.index');
    });


    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {
        // Rutas de Tiendas
        Route::get('/tiendas',           [TiendaController::class, 'dashboard'])->name('tiendas.index');
        Route::get('/tiendas/gestion',   [TiendaController::class, 'index'])->name('tiendas.manage');
        Route::post('/tiendas',          [TiendaController::class, 'store'])->name('tiendas.store');
        Route::put('/tiendas/{tienda}',  [TiendaController::class, 'update'])->name('tiendas.update');
        Route::delete('/tiendas/{tienda}', [TiendaController::class, 'destroy'])->name('tiendas.destroy');

        // Rutas de Proveedores
        Route::get('/proveedores',         [AdminProveedorController::class, 'dashboard'])->name('proveedores.index');
        Route::get('/proveedores/gestion', [AdminProveedorController::class, 'index'])->name('proveedores.manage');
        Route::get('/api/usuarios/buscar', [AdminProveedorController::class, 'buscarUsuarios'])->name('proveedores.search');
        Route::post('/proveedores/{usuario}/asignar', [AdminProveedorController::class, 'asignarTienda'])->name('proveedores.asignar');
        Route::post('/proveedores/asignar-rol', [AdminProveedorController::class, 'asignarRolProveedor'])->name('proveedores.asignar-rol');
        Route::delete('/proveedores/{usuario}/quitar-rol', [AdminProveedorController::class, 'quitarRolProveedor'])->name('proveedores.quitar-rol');

        // Rutas de Repartidores
        Route::get('/repartidores', [RepartidorController::class, 'index'])->name('repartidores.index');
        Route::post('/repartidores/{usuario}/toggle', [RepartidorController::class, 'toggle'])->name('repartidores.toggle');
        Route::post('/repartidores/asignar', [RepartidorController::class, 'asignar'])->name('repartidores.asignar');
        Route::delete('/repartidores/{usuario}', [RepartidorController::class, 'desvincular'])->name('repartidores.destroy');


        Route::prefix('tarjetas')->name('tarjetas.')->group(function () {
            Route::get('/dashboard', [TarjetaDashboardController::class, 'index'])->middleware('permission:card.read.any')->name('dashboard');
            Route::get('/',          [TarjetaController::class, 'index'])->middleware('permission:card.read.any')->name('index');
            Route::get('/create',    [TarjetaController::class, 'create'])->middleware('permission:card.write')->name('create');
            Route::post('/',         [TarjetaController::class, 'store'])->middleware('permission:card.write')->name('store');
            Route::get('/{tarjeta}',        [TarjetaController::class, 'show'])->middleware('permission:card.read.any')->name('show');
            Route::get('/{tarjeta}/edit',   [TarjetaController::class, 'edit'])->middleware('permission:card.write')->name('edit');
            Route::put('/{tarjeta}',        [TarjetaController::class, 'update'])->middleware('permission:card.write')->name('update');
            Route::delete('/{tarjeta}',     [TarjetaController::class, 'destroy'])->middleware('permission:card.write')->name('destroy');
            Route::post('/{tarjeta}/toggle-block', [TarjetaController::class, 'toggleBlock'])->middleware('permission:card.block')->name('toggle-block');
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
            Route::get('/',              [UsuarioController::class, 'index'])->middleware('permission:user.read')->name('index');
            Route::get('/create',        [UsuarioController::class, 'create'])->middleware('permission:user.write')->name('create');
            Route::get('/export',        [UsuarioController::class, 'export'])->middleware('permission:report.users')->name('export');
            Route::get('/export-by-role',[UsuarioController::class, 'exportByRole'])->middleware('permission:report.users')->name('export-by-role');
            Route::get('/export-pdf',    [UsuarioController::class, 'exportPdf'])->middleware('permission:report.users')->name('export-pdf');
            Route::get('/export-by-role-pdf',[UsuarioController::class, 'exportByRolePdf'])->middleware('permission:report.users')->name('export-by-role-pdf');
            Route::post('/',             [UsuarioController::class, 'store'])->middleware('permission:user.write')->name('store');
            Route::get('/{usuario}',     [UsuarioController::class, 'show'])->middleware('permission:user.show')->name('show');
            Route::get('/{usuario}/edit',[UsuarioController::class, 'edit'])->middleware('permission:user.write')->name('edit');
            Route::put('/{usuario}',     [UsuarioController::class, 'update'])->middleware('permission:user.write')->name('update');
            Route::delete('/{usuario}',  [UsuarioController::class, 'destroy'])->middleware('permission:user.delete')->name('destroy');
            Route::post('/{usuario}/toggle-block',   [UsuarioController::class, 'toggleBlock'])->middleware('permission:user.block')->name('toggle-block');
            Route::post('/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->middleware('permission:user.write')->name('reset-password');
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
            Route::get('/',          [AdminAreaController::class, 'index'])->name('index');
            Route::post('/',         [AdminAreaController::class, 'store'])->name('store');
            Route::get('/{area}',    [AdminAreaController::class, 'show'])->name('show');
            Route::put('/{area}',    [AdminAreaController::class, 'update'])->name('update');
            Route::delete('/{area}', [AdminAreaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('categorias-ticket')->name('categorias-ticket.')->group(function () {
            Route::get('/',                     [CategoriaTicketController::class, 'index'])->name('index');
            Route::post('/',                    [CategoriaTicketController::class, 'store'])->name('store');
            Route::get('/{categoriaTicket}',    [CategoriaTicketController::class, 'show'])->name('show');
            Route::put('/{categoriaTicket}',    [CategoriaTicketController::class, 'update'])->name('update');
            Route::delete('/{categoriaTicket}', [CategoriaTicketController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('ubicaciones')->name('ubicaciones.')->group(function () {
            Route::get('/',              [UbicacionController::class, 'index'])->name('index');
            Route::post('/',             [UbicacionController::class, 'store'])->name('store');
            Route::get('/{ubicacion}',   [UbicacionController::class, 'show'])->name('show');
            Route::put('/{ubicacion}',   [UbicacionController::class, 'update'])->name('update');
            Route::delete('/{ubicacion}',[UbicacionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('equipos-activos')->name('equipos-activos.')->group(function () {
            Route::get('/',                 [EquipoActivoController::class, 'index'])->name('index');
            Route::post('/',                [EquipoActivoController::class, 'store'])->name('store');
            Route::get('/{equipoActivo}',   [EquipoActivoController::class, 'show'])->name('show');
            Route::put('/{equipoActivo}',   [EquipoActivoController::class, 'update'])->name('update');
            Route::delete('/{equipoActivo}',[EquipoActivoController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('mantenimientos-preventivos')->name('mantenimientos-preventivos.')->group(function () {
            Route::get('/',                           [MantenimientoPreventivoController::class, 'index'])->name('index');
            Route::post('/',                          [MantenimientoPreventivoController::class, 'store'])->name('store');
            Route::get('/{mantenimientoPreventivo}',  [MantenimientoPreventivoController::class, 'show'])->name('show');
            Route::put('/{mantenimientoPreventivo}',  [MantenimientoPreventivoController::class, 'update'])->name('update');
            Route::delete('/{mantenimientoPreventivo}',[MantenimientoPreventivoController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/',          [TicketController::class, 'index'])->name('index');
            Route::post('/',         [TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}',  [TicketController::class, 'show'])->name('show');
            Route::put('/{ticket}',  [TicketController::class, 'update'])->name('update');
            Route::delete('/{ticket}',[TicketController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('asignaciones-tecnicas')->name('asignaciones-tecnicas.')->group(function () {
            Route::get('/',                       [AsignacionTecnicaController::class, 'index'])->name('index');
            Route::post('/',                      [AsignacionTecnicaController::class, 'store'])->name('store');
            Route::get('/{asignacionTecnica}',    [AsignacionTecnicaController::class, 'show'])->name('show');
            Route::put('/{asignacionTecnica}',    [AsignacionTecnicaController::class, 'update'])->name('update');
            Route::delete('/{asignacionTecnica}', [AsignacionTecnicaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('insumos')->name('insumos.')->group(function () {
            Route::get('/',          [InsumoController::class, 'index'])->name('index');
            Route::post('/',         [InsumoController::class, 'store'])->name('store');
            Route::get('/{insumo}',  [InsumoController::class, 'show'])->name('show');
            Route::put('/{insumo}',  [InsumoController::class, 'update'])->name('update');
            Route::delete('/{insumo}',[InsumoController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('gastos-ticket')->name('gastos-ticket.')->group(function () {
            Route::get('/',               [GastoTicketController::class, 'index'])->name('index');
            Route::post('/',              [GastoTicketController::class, 'store'])->name('store');
            Route::get('/{gastoTicket}',  [GastoTicketController::class, 'show'])->name('show');
            Route::put('/{gastoTicket}',  [GastoTicketController::class, 'update'])->name('update');
            Route::delete('/{gastoTicket}',[GastoTicketController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('historial-tickets')->name('historial-tickets.')->group(function () {
            Route::get('/',                    [HistorialTicketController::class, 'index'])->name('index');
            Route::post('/',                   [HistorialTicketController::class, 'store'])->name('store');
            Route::get('/{historialTicket}',   [HistorialTicketController::class, 'show'])->name('show');
            Route::put('/{historialTicket}',   [HistorialTicketController::class, 'update'])->name('update');
            Route::delete('/{historialTicket}',[HistorialTicketController::class, 'destroy'])->name('destroy');
        });

        // ── MÓDULO 4.2: MONEDERO DIGITAL ──────────────────────────────────────
        Route::prefix('monedero')->name('monedero.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Admin\MonederoAnalyticsController::class, 'index'])
                ->name('dashboard');

            Route::prefix('reportes')->name('reportes.')->group(function () {
                Route::get('/',                [App\Http\Controllers\Admin\MonederoReportesController::class, 'index'])->name('index');
                Route::get('/estado-cuenta',   [App\Http\Controllers\Admin\MonederoReportesController::class, 'estadoCuenta'])->name('estado-cuenta');
                Route::get('/movimientos',     [App\Http\Controllers\Admin\MonederoReportesController::class, 'movimientos'])->name('movimientos');
                Route::get('/uso-categoria',   [App\Http\Controllers\Admin\MonederoReportesController::class, 'usoCategoria'])->name('uso-categoria');
            });

            Route::prefix('exportes')->name('exportes.')->group(function () {
                Route::get('/estado-cuenta/pdf', [App\Http\Controllers\Admin\MonederoReportesController::class, 'exportEstadoCuentaPDF'])->name('estado-cuenta-pdf');
                Route::get('/estado-cuenta/csv', [App\Http\Controllers\Admin\MonederoReportesController::class, 'exportEstadoCuentaCSV'])->name('estado-cuenta-csv');
                Route::get('/movimientos/pdf',   [App\Http\Controllers\Admin\MonederoReportesController::class, 'exportMovimientosPDF'])->name('movimientos-pdf');
                Route::get('/movimientos/csv',   [App\Http\Controllers\Admin\MonederoReportesController::class, 'exportMovimientosCSV'])->name('movimientos-csv');
                Route::get('/uso-categoria/pdf', [App\Http\Controllers\Admin\MonederoReportesController::class, 'exportUsoCategoriaPDF'])->name('uso-categoria-pdf');
                Route::get('/uso-categoria/csv', [App\Http\Controllers\Admin\MonederoReportesController::class, 'exportUsoCategoriaCSV'])->name('uso-categoria-csv');
            });

            Route::prefix('reglas')->name('reglas.')->group(function () {
                Route::get('/',           [App\Http\Controllers\Admin\MonederoReglasController::class, 'index'])->name('index');
                Route::get('/create',     [App\Http\Controllers\Admin\MonederoReglasController::class, 'create'])->name('create');
                Route::post('/',          [App\Http\Controllers\Admin\MonederoReglasController::class, 'store'])->name('store');
                Route::get('/{regla}',    [App\Http\Controllers\Admin\MonederoReglasController::class, 'show'])->name('show');
                Route::get('/{regla}/edit', [App\Http\Controllers\Admin\MonederoReglasController::class, 'edit'])->name('edit');
                Route::put('/{regla}',    [App\Http\Controllers\Admin\MonederoReglasController::class, 'update'])->name('update');
                Route::delete('/{regla}', [App\Http\Controllers\Admin\MonederoReglasController::class, 'destroy'])->name('destroy');
            });
        });
    });
    
    // ── Módulo 4.5: Pedidos ───────────────────────────────────────
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/panel/dashboard',             [PedidoDashboardController::class, 'index'])->name('dashboard');
        Route::get('/panel/operador',              [PedidoController::class, 'operador'])->name('operador');
        Route::get('/panel/reportes',              [PedidoReporteController::class, 'index'])->name('reportes');
        Route::get('/panel/reportes/export-csv',   [PedidoReporteController::class, 'exportCsv'])->name('reportes.export-csv');
        Route::get('/panel/reportes/export-excel', [PedidoReporteController::class, 'exportExcel'])->name('reportes.export-excel');
        Route::get('/panel/reportes/export-pdf',   [PedidoReporteController::class, 'exportPdf'])->name('reportes.export-pdf');
        Route::get('/',                        [PedidoController::class, 'index'])->name('index');
        Route::get('/{pedido}',             [PedidoController::class, 'show'])->name('show')->whereNumber('pedido');
        Route::post('/{pedido}/cancelar',   [PedidoController::class, 'cancelar'])->name('cancelar')->whereNumber('pedido');
        Route::get('/{pedido}/estado-json', [PedidoController::class, 'estadoJson'])->name('estado-json')->whereNumber('pedido');
        Route::post('/{pedido}/estado',     [PedidoController::class, 'cambiarEstado'])->name('cambiar-estado')->whereNumber('pedido');
        Route::get('/{id}/ticket-pdf', [PedidoController::class, 'ticketPdf'])->name('ticket-pdf')->whereNumber('id');
        Route::get('/panel/retrasados', [PedidoDashboardController::class, 'retrasados'])->name('retrasados');
    });

    // ── MÓDULO 8: RECARGAS Y WALLET ──────────────────────────────────────
    Route::prefix('modulo_8')->name('modulo_8.')->group(function () {
        Route::get('/', [ReportesController::class, 'dashboard'])->name('index');
        Route::get('/recargar',                  [RecargasRecargaController::class, 'mostrarFormulario'])->name('recargar.form');
        Route::post('/recargar',                 [RecargasRecargaController::class, 'procesarRecarga'])->name('recargar');
        Route::post('/recargar/{id}/reintentar', [RecargasRecargaController::class, 'reintentar'])->name('recargar.reintentar');
        Route::get('/recargar/{id}/comprobante', [RecargasRecargaController::class, 'descargarComprobante'])->name('comprobante');
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/historial',    [ReportesController::class, 'historialRecargas'])->name('historial');
            Route::get('/fallidos',     [ReportesController::class, 'pagosFallidos'])->name('fallidos');
            Route::get('/conciliacion', [ReportesController::class, 'conciliacionPeriodo'])->name('conciliacion');
        });
        Route::get('/saldo',        [WalletController::class, 'saldo'])->name('saldo');
        Route::post('/pagar',       [WalletController::class, 'pagar'])->name('pagar');
        Route::get('/movimientos',  [WalletController::class, 'movimientos'])->name('movimientos');
        Route::get('/comprobantes', [WalletController::class, 'comprobantes'])->name('comprobantes');
    });
});

// ── MÓDULO 4.7: RESERVAS Y TURNOS ──────────────────────────────────────────────

// Frontend (usuarios autenticados)
Route::middleware(['auth'])->group(function () {
    // Reservas
    Route::prefix('reservas')->name('reservas.')->group(function () {
        Route::get('/',                      [App\Http\Controllers\ReservaController::class, 'index'])->name('index');
        Route::get('/crear/{recurso}',       [App\Http\Controllers\ReservaController::class, 'create'])->name('create');
        Route::post('/',                     [App\Http\Controllers\ReservaController::class, 'store'])->name('store');
        Route::get('/{reserva}',             [App\Http\Controllers\ReservaController::class, 'show'])->name('show');
        Route::delete('/{reserva}',          [App\Http\Controllers\ReservaController::class, 'destroy'])->name('destroy');
    });

    // Turnos
    Route::prefix('turnos')->name('turnos.')->group(function () {
        Route::get('/',            [App\Http\Controllers\TurnoController::class, 'index'])->name('index');
        Route::post('/',           [App\Http\Controllers\TurnoController::class, 'store'])->name('store');
        Route::get('/mi-turno',    [App\Http\Controllers\TurnoController::class, 'miTurno'])->name('mi-turno');
        Route::delete('/{turno}',  [App\Http\Controllers\TurnoController::class, 'destroy'])->name('destroy');
    });
});

// Admin (auth + role:administrador)
Route::middleware(['auth', 'role:administrador'])
    ->prefix('admin/reservas')
    ->name('admin.reservas.')
    ->group(function () {
        // Recursos
        Route::resource('recursos', App\Http\Controllers\Admin\RecursoController::class);

        // Reservas
        Route::get('/',                      [App\Http\Controllers\Admin\ReservaAdminController::class, 'index'])->name('index');
        Route::post('/',                     [App\Http\Controllers\Admin\ReservaAdminController::class, 'store'])->name('store');
        Route::get('/{reserva}',             [App\Http\Controllers\Admin\ReservaAdminController::class, 'show'])->name('show');
        Route::patch('/{reserva}',           [App\Http\Controllers\Admin\ReservaAdminController::class, 'update'])->name('update');
        Route::delete('/{reserva}',          [App\Http\Controllers\Admin\ReservaAdminController::class, 'destroy'])->name('destroy');

        // Turnos
        Route::prefix('turnos')->name('turnos.')->group(function () {
            Route::get('/',                    [App\Http\Controllers\Admin\TurnoAdminController::class, 'index'])->name('index');
            Route::post('/{turno}/llamar',     [App\Http\Controllers\Admin\TurnoAdminController::class, 'llamar'])->name('llamar');
            Route::post('/{turno}/atender',    [App\Http\Controllers\Admin\TurnoAdminController::class, 'atender'])->name('atender');
            Route::post('/{turno}/no-show',    [App\Http\Controllers\Admin\TurnoAdminController::class, 'noShow'])->name('no-show');
            Route::delete('/{turno}',          [App\Http\Controllers\Admin\TurnoAdminController::class, 'destroy'])->name('destroy');
        });

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\ReservaDashboardController::class, 'index'])->name('dashboard');

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/por-recurso',     [App\Http\Controllers\Admin\ReservaReporteController::class, 'porRecurso'])->name('por-recurso');
            Route::get('/por-usuario',     [App\Http\Controllers\Admin\ReservaReporteController::class, 'porUsuario'])->name('por-usuario');
            Route::get('/infrautilizados', [App\Http\Controllers\Admin\ReservaReporteController::class, 'infrautilizados'])->name('infrautilizados');
        });
    });

// ── MÓDULO CARRITO — Panel Admin ──────────────────────────────────────────────

// ── MÓDULO CARRITO — Panel de Administración (requiere auth + rol admin_carrito) ──
Route::middleware(['auth', 'role.cart.admin'])
    ->prefix('admin/cart')
    ->name('admin.cart.')
    ->group(function () {
        Route::get('solicitudes',                              [\App\Http\Controllers\Admin\Cart\SolicitudController::class,   'index'])   ->name('solicitudes.index');
        Route::get('solicitudes/{solicitud}',                  [\App\Http\Controllers\Admin\Cart\SolicitudController::class,   'show'])    ->name('solicitudes.show');
        Route::post('solicitudes/{solicitud}/aprobar',         [\App\Http\Controllers\Admin\Cart\SolicitudController::class,   'aprobar']) ->name('solicitudes.aprobar');
        Route::post('solicitudes/{solicitud}/rechazar',        [\App\Http\Controllers\Admin\Cart\SolicitudController::class,   'rechazar'])->name('solicitudes.rechazar');
        Route::get('solicitudes/{folio}/token',                [\App\Http\Controllers\Admin\Cart\TokenEntregaController::class,'show'])    ->name('token.show');

        Route::get('modulos',                                  [\App\Http\Controllers\Admin\Cart\ModulosController::class,    'index'])              ->name('modulos.index');
        Route::get('modulos/{modulo}',                         [\App\Http\Controllers\Admin\Cart\ModulosController::class,    'show'])               ->name('modulos.show');
        Route::post('modulos/{modulo}/revocar',                [\App\Http\Controllers\Admin\Cart\ModulosController::class,    'revocarToken'])        ->name('modulos.revocar');
        Route::post('modulos/{modulo}/forzar-refresh',         [\App\Http\Controllers\Admin\Cart\ModulosController::class,    'forzarRefresh'])       ->name('modulos.forzar-refresh');
        Route::get('bitacora',                                 [\App\Http\Controllers\Admin\Cart\BitacoraController::class,      'index'])   ->name('bitacora.index');
        Route::get('dashboard',                                [\App\Http\Controllers\Admin\Cart\CartDashboardController::class,  'index'])   ->name('dashboard');
        Route::get('reportes/consumos-por-periodo',            [\App\Http\Controllers\Admin\Cart\CartReportController::class,      'consumosPorPeriodo'])   ->name('reportes.consumos');
        Route::get('reportes/carritos-abandonados',            [\App\Http\Controllers\Admin\Cart\CartReportController::class,      'carritosAbandonados']) ->name('reportes.abandonados');
        Route::get('reportes/consumo-por-categoria',           [\App\Http\Controllers\Admin\Cart\CartReportController::class,      'consumoPorCategoria']) ->name('reportes.categorias');
    });

// ── DEMO — Módulo Biblioteca ──────────────────────────────────────────────────
Route::prefix('demo/biblioteca')->name('demo.biblioteca.')->group(function () {
    Route::get('prestamo', [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'index'])->name('prestamo');
    Route::prefix('cart-proxy')->name('cart-proxy.')->group(function () {
        Route::post('carritos',                         [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'proxyCreateCart']) ->name('carritos.create');
        Route::get('carritos/{uuid}',                   [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'proxyGetCart'])    ->name('carritos.show');
        Route::post('carritos/{uuid}/items',            [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'proxyAddItem'])    ->name('carritos.items.add');
        Route::delete('carritos/{uuid}/items/{itemId}', [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'proxyRemoveItem']) ->name('carritos.items.remove');
        Route::post('carritos/{uuid}/checkout',         [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'proxyCheckout'])   ->name('carritos.checkout');
        Route::post('carritos/{uuid}/cancelar',         [\App\Http\Controllers\Demo\Biblioteca\PrestamoController::class, 'proxyCancel'])     ->name('carritos.cancel');
    });
});

    // ── MÓDULO 8: RECARGAS Y WALLET ──────────────────────────────────────
    Route::prefix('modulo_8')->name('modulo_8.')->group(function () {

        Route::get('/', [ReportesController::class, 'dashboard'])->name('index');

        Route::get('/recargar',                  [RecargasRecargaController::class, 'mostrarFormulario'])->name('recargar.form');
        Route::post('/recargar',                 [RecargasRecargaController::class, 'procesarRecarga'])->name('recargar');
        Route::post('/recargar/{id}/reintentar', [RecargasRecargaController::class, 'reintentar'])->name('recargar.reintentar');
        Route::get('/recargar/{id}/comprobante', [RecargasRecargaController::class, 'descargarComprobante'])->name('comprobante');

        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/historial',    [ReportesController::class, 'historialRecargas'])->name('historial');
            Route::get('/fallidos',     [ReportesController::class, 'pagosFallidos'])->name('fallidos');
            Route::get('/conciliacion', [ReportesController::class, 'conciliacionPeriodo'])->name('conciliacion');
        });

        Route::get('/saldo',        [WalletController::class, 'saldo'])->name('saldo');
        Route::post('/pagar',       [WalletController::class, 'pagar'])->name('pagar');
        Route::get('/movimientos',  [WalletController::class, 'movimientos'])->name('movimientos');
        Route::get('/comprobantes', [WalletController::class, 'comprobantes'])->name('comprobantes');
    });

// ── CATÁLOGO — Cart Proxy (Capa A3/A4) ──────────────────────────────────────
Route::prefix('catalogo/cart-proxy')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::post('/carritos',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'crearCarrito']);
        Route::get('/carritos/{uuid}',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'obtenerCarrito']);
        Route::post('/carritos/{uuid}/items',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'agregarItem']);
        Route::delete('/carritos/{uuid}/items/{item_id}',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'eliminarItem']);
        Route::post('/carritos/{uuid}/checkout',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'checkout']);
        Route::post('/carritos/{uuid}/cancelar',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'cancelar']);
        Route::get('/historico',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'historico']);
        Route::post('/validar-destinatario',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'validarDestinatario']);
        Route::patch('/carritos/{uuid}/items/{item_id}/regalo',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'marcarRegalo']);
        Route::get('/comprobante/{uuid}',
            [App\Http\Controllers\Catalogo\CatalogoCartProxyController::class, 'obtenerComprobante'])
            ->name('catalogo.cart-proxy.comprobante');
    });
