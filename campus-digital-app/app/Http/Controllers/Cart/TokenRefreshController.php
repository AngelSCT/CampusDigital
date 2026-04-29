<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Exceptions\ModuleInactiveException;
use App\Modules\Cart\Exceptions\TokenExpiredException;
use App\Modules\Cart\Exceptions\TokenInvalidException;
use App\Modules\Cart\Exceptions\TokenReuseDetectedException;
use App\Modules\Cart\Exceptions\TokenRevokedException;
use App\Modules\Cart\Services\ModuleTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenRefreshController extends Controller
{
    public function __construct(private readonly ModuleTokenService $tokenService) {}

    /**
     * POST /api/cart/tokens/refresh
     *
     * Recibe un refresh_token, emite par nuevo, revoca par viejo.
     * NO usa el middleware auth.module.jwt (valida refresh, no access).
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return $this->error('TOKEN_MISSING', 'Se requiere el campo refresh_token.', 401);
        }

        try {
            $result = $this->tokenService->rotate($refreshToken);
            return response()->json($result);
        } catch (TokenReuseDetectedException) {
            return $this->error('TOKEN_REUSE_DETECTED',
                'Reuso de refresh token detectado. Todos los tokens del módulo han sido revocados.', 401);
        } catch (TokenExpiredException) {
            return $this->error('TOKEN_EXPIRED', 'El refresh token ha expirado.', 401);
        } catch (TokenRevokedException) {
            return $this->error('TOKEN_REVOKED', 'El refresh token ha sido revocado.', 401);
        } catch (ModuleInactiveException) {
            return $this->error('MODULE_INACTIVE', 'El módulo está inactivo.', 403);
        } catch (TokenInvalidException) {
            return $this->error('TOKEN_INVALID', 'El refresh token es inválido.', 401);
        }
    }

    private function error(string $code, string $mensaje, int $status): JsonResponse
    {
        return response()->json(['error' => $code, 'mensaje' => $mensaje], $status);
    }
}
