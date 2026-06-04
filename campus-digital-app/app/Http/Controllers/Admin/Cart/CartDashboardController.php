<?php

namespace App\Http\Controllers\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Services\CartDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class CartDashboardController extends Controller
{
    public function index(CartDashboardService $service): Response
    {
        return Inertia::render('Admin/Cart/Dashboard', [
            'data'    => $service->resumen(),
            'horario' => $service->consumoPorHorario(),
        ]);
    }
}
