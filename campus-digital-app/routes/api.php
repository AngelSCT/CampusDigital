<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComprobanteController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ... otras rutas que ya existan ...

// Rutas para Comprobantes
Route::apiResource('comprobantes', ComprobanteController::class);
Route::get('comprobantes/{id}/pdf', [ComprobanteController::class, 'generarPDF']);