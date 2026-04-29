<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga el rol 'admin_carrito'.
 * Usa el sistema de roles existente del monorepo (Usuario::hasAnyRole).
 */
class EnsureCartAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['mensaje' => 'No autenticado.'], 401)
                : redirect()->route('login');
        }

        if (!auth()->user()->hasAnyRole(['admin_carrito'])) {
            abort(403, 'No tienes acceso al panel de administración del carrito.');
        }

        return $next($request);
    }
}
