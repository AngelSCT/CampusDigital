<?php

namespace App\Http\Middleware;

use App\Modules\Cart\Exceptions\ModuleInactiveException;
use App\Modules\Cart\Exceptions\TokenExpiredException;
use App\Modules\Cart\Exceptions\TokenInvalidException;
use App\Modules\Cart\Exceptions\TokenRevokedException;
use App\Modules\Cart\Services\ModuleTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthModuleJwt
{
    private const MENSAJES = [
        'TOKEN_MISSING'  => 'Se requiere un token de módulo en el header Authorization.',
        'TOKEN_INVALID'  => 'El token de módulo es inválido.',
        'TOKEN_EXPIRED'  => 'El token de módulo ha expirado.',
        'TOKEN_REVOKED'  => 'El token de módulo ha sido revocado.',
        'MODULE_INACTIVE' => 'El módulo está inactivo.',
        'SCOPE_DENIED'   => 'El módulo no tiene autorización para operar con esta categoría.',
    ];

    public function __construct(private readonly ModuleTokenService $tokenService) {}

    /**
     * Paso 1 — Verifica Authorization: Bearer.
     * Pasos 2-8 — Delegados a ModuleTokenService::validate().
     * Paso 9  — Verifica scope (categoría requerida) si se pasa como parámetro.
     *
     * Deja el payload disponible en $request->attributes->get('module_payload').
     */
    public function handle(Request $request, Closure $next, string $requiredCategory = null): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $this->error('TOKEN_MISSING', 401);
        }

        $token = substr($authHeader, 7);

        try {
            $payload = $this->tokenService->validate($token);
        } catch (TokenExpiredException) {
            return $this->error('TOKEN_EXPIRED', 401);
        } catch (TokenRevokedException) {
            return $this->error('TOKEN_REVOKED', 401);
        } catch (ModuleInactiveException) {
            return $this->error('MODULE_INACTIVE', 403);
        } catch (TokenInvalidException) {
            return $this->error('TOKEN_INVALID', 401);
        }

        // Paso 9: scope por categoría (opcional, configurado por ruta).
        if ($requiredCategory !== null &&
            !in_array($requiredCategory, $payload['categorias_autorizadas'] ?? [], true)) {
            return $this->error('SCOPE_DENIED', 403);
        }

        $request->attributes->set('module_payload', $payload);

        return $next($request);
    }

    private function error(string $code, int $status): Response
    {
        return response()->json([
            'error'   => $code,
            'mensaje' => self::MENSAJES[$code] ?? $code,
        ], $status);
    }
}
