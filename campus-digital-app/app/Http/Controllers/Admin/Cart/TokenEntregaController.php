<?php

namespace App\Http\Controllers\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Bitacora;
use App\Models\Cart\SolicitudModulo;
use App\Models\Cart\TokenModulo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TokenEntregaController extends Controller
{
    /**
     * Pantalla one-time de entrega del JWT (cambio C2 del documento v1.1).
     *
     * Primera carga: lee los tokens del flash de sesión, marca entregado_at.
     * Cargas posteriores: entregado_at != NULL → solo muestra mensaje "ya entregado".
     * Todas las visitas se registran en bitácora con accion='token.visualizado'.
     */
    public function show(Request $request, string $folio): Response
    {
        $solicitud = SolicitudModulo::where('folio', $folio)
            ->where('estado', SolicitudModulo::ESTADO_APROBADA)
            ->with('moduloCliente')
            ->firstOrFail();

        $modulo = $solicitud->moduloCliente;

        // Busca el último access token ACTIVO del módulo.
        // Funciona para el par inicial (issuePair) y para post-forzarRefresh (nuevo par activo).
        $accessTokenRecord = $modulo
            ? TokenModulo::where('modulo_id', $modulo->id)
                ->where('tipo', TokenModulo::TIPO_ACCESS)
                ->where('estado', TokenModulo::ESTADO_ACTIVO)
                ->latest('id')
                ->first()
            : null;

        $alreadyDelivered = $accessTokenRecord && $accessTokenRecord->entregado_at !== null;

        $jwtPair = null;

        if (!$alreadyDelivered) {
            $jwtPair = session('jwt_pair');

            if ($jwtPair && $accessTokenRecord) {
                // Marca entregado_at en el access activo y en su refresh compañero (pair_jti).
                $accessTokenRecord->update(['entregado_at' => now()]);
                if ($accessTokenRecord->pair_jti) {
                    TokenModulo::where('jti', $accessTokenRecord->pair_jti)
                        ->update(['entregado_at' => now()]);
                }
            }
        }

        // Toda visita se audita (incluyendo las "ya entregado").
        Bitacora::create([
            'accion'     => Bitacora::ACCION_TOKEN_VISUALIZADO,
            'modulo_id'  => $modulo?->id,
            'user_id'    => auth()->id(),
            'ip_address' => $request->ip(),
            'payload'    => [
                'folio'            => $folio,
                'already_delivered' => $alreadyDelivered,
            ],
        ]);

        return Inertia::render('Admin/Cart/TokenEntregaUnaVez', [
            'solicitud'        => $solicitud->only('folio', 'nombre_modulo', 'tipo_modulo'),
            'modulo'           => $modulo?->only('id', 'nombre', 'slug'),
            'accessToken'      => $jwtPair['access_token'] ?? null,
            'refreshToken'     => $jwtPair['refresh_token'] ?? null,
            'alreadyDelivered' => $alreadyDelivered,
        ]);
    }
}
