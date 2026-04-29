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

        // Token de acceso inicial (par emitido por issuePair, replaces_jti = null).
        $accessTokenRecord = $modulo
            ? TokenModulo::where('modulo_id', $modulo->id)
                ->where('tipo', TokenModulo::TIPO_ACCESS)
                ->whereNull('replaces_jti')
                ->latest('id')
                ->first()
            : null;

        $alreadyDelivered = $accessTokenRecord && $accessTokenRecord->entregado_at !== null;

        $jwtPair = null;

        if (!$alreadyDelivered) {
            $jwtPair = session('jwt_pair'); // disponible solo si venimos del redirect de aprobar

            if ($jwtPair && $accessTokenRecord) {
                // Primera entrega: marca entregado_at en el par inicial (access + refresh).
                TokenModulo::where('modulo_id', $modulo->id)
                    ->whereNull('replaces_jti')
                    ->update(['entregado_at' => now()]);
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
