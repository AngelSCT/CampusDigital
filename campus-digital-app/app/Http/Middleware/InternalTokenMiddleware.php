<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para endpoints internos entre módulos.
 *
 * El módulo 4.4 (Carrito/Checkout) llama a los endpoints /api/internal/saldo/*
 * usando el header X-Internal-Token con el secreto compartido.
 *
 * Configurar en .env:
 *   CART_SALDO_INTERNAL_TOKEN=tu_secreto_compartido
 *
 * Ese mismo valor debe ir en el .env del módulo 4.4:
 *   CART_SALDO_INTERNAL_TOKEN=tu_secreto_compartido
 */
class InternalTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Internal-Token', '');

        $expected = (string) env('CART_SALDO_INTERNAL_TOKEN', '');

        if ($expected === '' || !hash_equals($expected, $token)) {
            return response()->json([
                'error'   => 'TOKEN_INVALIDO',
                'mensaje' => 'Token interno inválido o ausente.',
            ], 401);
        }

        return $next($request);
    }
}
