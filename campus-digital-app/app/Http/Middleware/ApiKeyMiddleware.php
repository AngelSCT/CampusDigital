<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json([
                'error'   => 'API key requerida.',
                'hint'    => 'Agrega el header: X-API-KEY: TOKEN-DEL-ENV'
            ], 401);
        }

        // REVISION DE LAS KEYS
        $keysValidas = array_map(
            'trim',
            explode(',', env('API_KEYS', ''))
        );

        if (!in_array($apiKey, $keysValidas, true)) {
            return response()->json([
                'error' => 'API key inválida.'
            ], 403);
        }

        return $next($request);
    }
}