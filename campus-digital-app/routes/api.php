<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Recargas\WalletController;
use App\Http\Controllers\ComprobanteController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// no middleware para pruebas locales -> debe ser cambiado a api.key

Route::prefix('wallet')->group(function () {

    Route::get('/saldo', [WalletController::class, 'saldo']);

    Route::post('/recargar', [WalletController::class, 'recargar']);

    Route::post('/pagar', [WalletController::class, 'pagar']);

    Route::get('/movimientos', [WalletController::class, 'movimientos']);

    Route::get('/comprobantes', [WalletController::class, 'comprobantes']);
});

Route::prefix('comprobantes')->group(function () {

    Route::get('/', [ComprobanteController::class, 'index']);
    
    Route::get('/{id}', [ComprobanteController::class, 'show']);

    Route::post('/', [ComprobanteController::class, 'store']);
});