<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Inertia\Inertia;

class CarritoViewController extends Controller
{
    /**
     * GET /catalogo/mi-carrito
     *
     * Muestra el carrito activo del catálogo embebido,
     * sin redirigir al módulo externo de campus digital.
     */
    public function index(): \Inertia\Response
    {
        $uuid    = session('catalogo_carrito_uuid') ?? session('cart_uuid');
        $carrito = null;

        if ($uuid) {
            $baseUrl = rtrim(config('cart.api.base_url'), '/');
            $token   = Cache::get('cart_module_access_token', config('cart.client.module_token'));

            try {
                $resp = Http::withToken($token)->get("{$baseUrl}/carritos/{$uuid}");

                if ($resp->successful()) {
                    $carrito = $resp->json();
                    // Refrescar UUID en sesión
                    session(['catalogo_carrito_uuid' => $uuid]);
                } elseif ($resp->status() === 404 || $resp->status() === 409) {
                    // Carrito expirado / en estado terminal → limpiar sesión
                    session()->forget(['cart_uuid', 'catalogo_carrito_uuid']);
                    $uuid = null;
                }
            } catch (ConnectionException) {
                // Cart API no disponible: muestra carrito vacío con aviso
            }
        }

        return Inertia::render('Catalogo/MiCarrito', [
            'carrito'      => $carrito,
            'carrito_uuid' => $uuid,
        ]);
    }
}
