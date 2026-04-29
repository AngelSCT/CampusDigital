<?php

namespace App\Http\Controllers\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\TokenModulo;
use App\Modules\Cart\Services\ModuleTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModulosController extends Controller
{
    public function __construct(private readonly ModuleTokenService $tokenService) {}

    public function index(): Response
    {
        $modulos = ModuloCliente::with([
            'tokens' => fn($q) => $q->orderByDesc('id'),
        ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Cart/ModulosIndex', [
            'modulos' => $modulos,
        ]);
    }

    public function show(ModuloCliente $modulo): Response
    {
        return Inertia::render('Admin/Cart/ModuloDetalle', [
            'modulo' => $modulo->load(['solicitud', 'tokens' => fn($q) => $q->orderByDesc('id')]),
        ]);
    }

    /**
     * Revoca los tokens activos del módulo. No emite tokens nuevos.
     * El par (pair_jti) se revoca automáticamente vía ModuleTokenService::revoke().
     * Motivo es opcional; si se omite usa 'manual'.
     */
    public function revocarToken(Request $request, ModuloCliente $modulo): RedirectResponse
    {
        $request->validate([
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $motivo = $request->input('motivo') ?: TokenModulo::MOTIVO_MANUAL;

        $activeTokens = TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->get();

        foreach ($activeTokens as $token) {
            $this->tokenService->revoke($token->jti, $motivo, auth()->id());
        }

        return redirect()->route('admin.cart.modulos.show', $modulo)
            ->with('success', 'Tokens revocados correctamente.');
    }

    /**
     * Revoca el par activo actual y emite un par nuevo.
     * Redirige a la pantalla one-time (TokenEntregaController@show) vía flash.
     */
    public function forzarRefresh(Request $request, ModuloCliente $modulo): RedirectResponse
    {
        // Revoca todos los pares activos del módulo.
        $activeTokens = TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->get();

        foreach ($activeTokens as $token) {
            $this->tokenService->revoke($token->jti, 'rotacion_admin', auth()->id());
        }

        // Emite par nuevo.
        $jwtPair = $this->tokenService->issuePair($modulo);

        Bitacora::create([
            'accion'     => 'token.rotacion_admin',
            'modulo_id'  => $modulo->id,
            'user_id'    => auth()->id(),
            'ip_address' => $request->ip(),
            'payload'    => ['tipo' => 'forzar_refresh_admin'],
        ]);

        session()->flash('jwt_pair', [
            'access_token'  => $jwtPair['access_token'],
            'refresh_token' => $jwtPair['refresh_token'],
        ]);

        $modulo->load('solicitud');

        return redirect()->route('admin.cart.token.show', ['folio' => $modulo->solicitud->folio]);
    }
}
