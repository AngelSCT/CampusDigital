<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use Illuminate\Http\Request;

class BitacoraApiController extends Controller
{
    // GET /api/bitacora/accesos
    public function accesos(Request $request)
    {
        $query = AccesoBitacora::with(['usuario', 'sesion'])->whereNull('deleted_at');

        if ($request->filled('usuario_id'))    $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('evento'))        $query->where('evento', $request->evento);
        if ($request->filled('exito'))         $query->where('exito', filter_var($request->exito, FILTER_VALIDATE_BOOLEAN));
        if ($request->filled('email'))         $query->where('email_intentado', 'ilike', '%'.$request->email.'%');
        if ($request->filled('desde'))         $query->where('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))         $query->where('created_at', '<=', $request->hasta);

        return response()->json($query->orderByDesc('created_at')->paginate($request->get('per_page', 20)));
    }

    // GET /api/bitacora/accesos/{id}
    public function acceso($id)
    {
        return response()->json(
            AccesoBitacora::with(['usuario', 'sesion'])->whereNull('deleted_at')->findOrFail($id)
        );
    }

    // GET /api/bitacora/actividad
    public function actividad(Request $request)
    {
        $query = ActividadBitacora::with(['usuario', 'sesion'])->whereNull('deleted_at');

        if ($request->filled('usuario_id'))    $query->where('usuario_id', $request->usuario_id);
        if ($request->filled('accion'))        $query->where('accion', 'ilike', '%'.$request->accion.'%');
        if ($request->filled('modulo'))        $query->where('modulo', $request->modulo);
        if ($request->filled('target_tabla'))  $query->where('target_tabla', $request->target_tabla);
        if ($request->filled('exito'))         $query->where('exito', filter_var($request->exito, FILTER_VALIDATE_BOOLEAN));
        if ($request->filled('desde'))         $query->where('created_at', '>=', $request->desde);
        if ($request->filled('hasta'))         $query->where('created_at', '<=', $request->hasta);

        return response()->json($query->orderByDesc('created_at')->paginate($request->get('per_page', 20)));
    }

    // GET /api/bitacora/actividad/{id}
    public function actividadItem($id)
    {
        return response()->json(
            ActividadBitacora::with(['usuario', 'sesion'])->whereNull('deleted_at')->findOrFail($id)
        );
    }
}