<?php

namespace App\Http\Controllers\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\SolicitudModulo;
use App\Modules\Cart\Services\ModuleTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SolicitudController extends Controller
{
    public function __construct(private readonly ModuleTokenService $tokenService) {}

    public function index(Request $request): Response
    {
        $estado = $request->get('estado');

        $query = SolicitudModulo::with('moduloCliente')
            ->latest();

        if ($estado && in_array($estado, ['pendiente', 'aprobada', 'rechazada'], true)) {
            $query->where('estado', $estado);
        }

        return Inertia::render('Admin/Cart/SolicitudesIndex', [
            'solicitudes' => $query->paginate(20)->withQueryString(),
            'filtroEstado' => $estado,
        ]);
    }

    public function show(SolicitudModulo $solicitud): Response
    {
        return Inertia::render('Admin/Cart/SolicitudDetalle', [
            'solicitud' => $solicitud->load('moduloCliente', 'revisor'),
        ]);
    }

    public function aprobar(Request $request, SolicitudModulo $solicitud): RedirectResponse
    {
        if ($solicitud->estado !== SolicitudModulo::ESTADO_PENDIENTE) {
            return back()->with('error', 'Esta solicitud ya fue procesada.');
        }

        $slug = Str::slug($solicitud->tipo_modulo);

        $modulo = ModuloCliente::create([
            'solicitud_id'           => $solicitud->id,
            'nombre'                 => $solicitud->nombre_modulo,
            'slug'                   => $slug,
            'tipo_modulo'            => $solicitud->tipo_modulo,
            'categorias_autorizadas' => $solicitud->categorias_solicitadas,
            'activo'                 => true,
        ]);

        // Emite el par de tokens; devuelve los JWTs en claro (one-time).
        $jwtPair = $this->tokenService->issuePair($modulo);

        $solicitud->update([
            'estado'       => SolicitudModulo::ESTADO_APROBADA,
            'revisado_por' => auth()->id(),
            'revisado_at'  => now(),
        ]);

        // Flash de sesión: los JWTs solo viajan en este request; no se persisten.
        session()->flash('jwt_pair', [
            'access_token'  => $jwtPair['access_token'],
            'refresh_token' => $jwtPair['refresh_token'],
        ]);

        return redirect()->route('admin.cart.token.show', ['folio' => $solicitud->folio]);
    }

    public function rechazar(Request $request, SolicitudModulo $solicitud): RedirectResponse
    {
        $validated = $request->validate([
            'motivo_rechazo' => ['required', 'string', 'min:10'],
        ]);

        if ($solicitud->estado !== SolicitudModulo::ESTADO_PENDIENTE) {
            return back()->with('error', 'Esta solicitud ya fue procesada.');
        }

        $solicitud->update([
            'estado'          => SolicitudModulo::ESTADO_RECHAZADA,
            'motivo_rechazo'  => $validated['motivo_rechazo'],
            'revisado_por'    => auth()->id(),
            'revisado_at'     => now(),
        ]);

        Bitacora::create([
            'accion'     => 'modulo.solicitud_rechazada',
            'user_id'    => auth()->id(),
            'ip_address' => $request->ip(),
            'payload'    => [
                'folio'          => $solicitud->folio,
                'motivo_rechazo' => $validated['motivo_rechazo'],
            ],
        ]);

        return redirect()->route('admin.cart.solicitudes.index')
            ->with('success', 'Solicitud rechazada correctamente.');
    }
}
