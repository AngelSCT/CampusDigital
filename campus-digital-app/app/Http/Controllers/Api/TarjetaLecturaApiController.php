<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TarjetaLecturaResource;
use App\Models\TarjetaLectura;
use Illuminate\Http\Request;

class TarjetaLecturaApiController extends Controller
{
    // GET /api/tarjeta-lecturas
    public function index(Request $request)
    {
        $query = TarjetaLectura::with(['tarjeta.usuario', 'operador', 'pedido'])
            ->whereNull('deleted_at');

        if ($request->filled('tarjeta_id'))     $query->where('tarjeta_id', $request->tarjeta_id);
        if ($request->filled('uid_leido'))      $query->where('uid_leido', $request->uid_leido);
        if ($request->filled('modulo'))         $query->where('modulo', $request->modulo);
        if ($request->filled('tipo_lectura'))   $query->where('tipo_lectura', $request->tipo_lectura);
        if ($request->filled('exito'))          $query->where('exito', filter_var($request->exito, FILTER_VALIDATE_BOOLEAN));
        if ($request->filled('operador_id'))    $query->where('operador_usuario_id', $request->operador_id);
        if ($request->filled('pedido_id'))      $query->where('pedido_id', $request->pedido_id);
        if ($request->filled('desde'))          $query->where('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))          $query->where('created_at', '<=', $request->hasta);

        return TarjetaLecturaResource::collection($query->orderByDesc('created_at')->paginate($request->get('per_page', 20)));
    }

    // GET /api/tarjeta-lecturas/{id}
    public function show($id)
    {
        return new TarjetaLecturaResource(
            TarjetaLectura::with(['tarjeta.usuario', 'operador', 'pedido'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // POST /api/tarjeta-lecturas  ← registrar una lectura nueva (lo usa el lector físico/simulado)
    public function store(Request $request)
    {
        $request->validate([
            'uid_leido'    => 'required|string|max:64',
            'modulo'       => 'required|string|max:50',
            'tipo_lectura' => 'required|string|max:50',
            'exito'        => 'boolean',
        ]);

        // Buscar la tarjeta por UID
        $tarjeta = \App\Models\TarjetaUniversitaria::where('uid', $request->uid_leido)
            ->whereNull('deleted_at')
            ->first();

        $lectura = TarjetaLectura::create([
            'tarjeta_id'          => $tarjeta?->id,
            'uid_leido'           => $request->uid_leido,
            'modulo'              => $request->modulo,
            'tipo_lectura'        => $request->tipo_lectura,
            'exito'               => $request->exito ?? ($tarjeta && $tarjeta->estado === 'activa'),
            'detalle'             => $request->detalle ?? '',
            'ip'                  => $request->ip(),
            'user_agent'          => $request->userAgent() ?? '',
            'operador_usuario_id' => $request->operador_usuario_id,
            'pedido_id'           => $request->pedido_id,
            'meta_json'           => $request->meta_json ?? [],
        ]);

        return (new TarjetaLecturaResource($lectura->load(['tarjeta.usuario', 'operador'])))->response()->setStatusCode(201);
    }
}