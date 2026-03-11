<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarjetaUniversitaria;
use Illuminate\Http\Request;

class TarjetaUniversitariaApiController extends Controller
{
    // GET /api/tarjetas
    public function index(Request $request)
    {
        $query = TarjetaUniversitaria::with(['usuario', 'registradoPor', 'bloqueadoPor'])
            ->whereNull('deleted_at');

        if ($request->filled('usuario_id'))  $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('estado'))      $query->where('estado', $request->estado);
        if ($request->filled('uid'))         $query->where('uid', 'ilike', '%'.$request->uid.'%');

        return response()->json($query->orderByDesc('created_at')->paginate($request->get('per_page', 15)));
    }

    // GET /api/tarjetas/{id}
    public function show($id)
    {
        return response()->json(
            TarjetaUniversitaria::with(['usuario', 'registradoPor', 'bloqueadoPor'])
                ->whereNull('deleted_at')
                ->findOrFail($id)
        );
    }

    // GET /api/tarjetas/uid/{uid}  ← buscar por UID del chip
    public function buscarPorUid($uid)
    {
        $tarjeta = TarjetaUniversitaria::with(['usuario.perfil'])
            ->whereNull('deleted_at')
            ->where('uid', $uid)
            ->first();

        if (!$tarjeta) {
            return response()->json(['message' => 'Tarjeta no encontrada.'], 404);
        }

        return response()->json($tarjeta);
    }

    // POST /api/tarjetas
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuario,id',
            'uid'        => 'required|string|max:64|unique:tarjeta_universitaria,uid',
            'estado'     => 'in:activa,bloqueada,perdida,cancelada',
        ]);

        $tarjeta = TarjetaUniversitaria::create([
            'usuario_id'                => $request->usuario_id,
            'uid'                       => $request->uid,
            'estado'                    => $request->estado ?? 'activa',
            'registrado_por_usuario_id' => $request->registrado_por_usuario_id,
            'meta_json'                 => $request->meta_json ?? [],
        ]);

        return response()->json($tarjeta->load('usuario'), 201);
    }

    // PUT /api/tarjetas/{id}
    public function update(Request $request, $id)
    {
        $tarjeta = TarjetaUniversitaria::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'uid'    => "sometimes|string|max:64|unique:tarjeta_universitaria,uid,{$id}",
            'estado' => 'sometimes|in:activa,bloqueada,perdida,cancelada',
        ]);

        $tarjeta->update($request->only(['uid', 'estado', 'motivo_bloqueo', 'meta_json']));

        return response()->json($tarjeta->fresh());
    }

    // DELETE /api/tarjetas/{id}
    public function destroy($id)
    {
        $tarjeta = TarjetaUniversitaria::whereNull('deleted_at')->findOrFail($id);
        $tarjeta->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Tarjeta eliminada.']);
    }

    // POST /api/tarjetas/{id}/bloquear
    public function bloquear(Request $request, $id)
    {
        $request->validate(['motivo_bloqueo' => 'required|string']);

        $tarjeta = TarjetaUniversitaria::whereNull('deleted_at')->findOrFail($id);
        $tarjeta->update([
            'estado'                    => 'bloqueada',
            'motivo_bloqueo'            => $request->motivo_bloqueo,
            'bloqueado_por_usuario_id'  => $request->bloqueado_por_usuario_id,
            'bloqueado_at'              => now(),
        ]);

        return response()->json(['message' => 'Tarjeta bloqueada.', 'tarjeta' => $tarjeta->fresh()]);
    }

    // POST /api/tarjetas/{id}/desbloquear
    public function desbloquear($id)
    {
        $tarjeta = TarjetaUniversitaria::whereNull('deleted_at')->findOrFail($id);
        $tarjeta->update([
            'estado'                   => 'activa',
            'motivo_bloqueo'           => null,
            'bloqueado_por_usuario_id' => null,
            'bloqueado_at'             => null,
        ]);

        return response()->json(['message' => 'Tarjeta desbloqueada.', 'tarjeta' => $tarjeta->fresh()]);
    }
}