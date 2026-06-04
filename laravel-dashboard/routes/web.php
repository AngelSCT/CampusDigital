<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// ── Redirect root ─────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('dashboard'));

// ── Auth ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Protected ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Recharge
    Route::get('/recargar', [RechargeController::class, 'index'])->name('recharge.index');
    Route::post('/recargar', [RechargeController::class, 'store'])->name('recharge.store');

    // Transactions / movements
    Route::get('/movimientos', [TransactionController::class, 'index'])->name('movements.index');

    // Wallet detail
    Route::get('/monedero', [WalletController::class, 'index'])->name('wallet.index');

    // Profile / config
    Route::get('/configuracion', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/configuracion/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/configuracion/contrasena', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Support
    Route::get('/soporte', [SupportController::class, 'index'])->name('support.index');
    Route::post('/soporte', [SupportController::class, 'store'])->name('support.store');
});
