<?php

namespace App\Http\Controllers\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BitacoraController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Bitacora::query()->latest('id');

        if ($acciones = $request->input('accion')) {
            $lista = is_array($acciones) ? $acciones : explode(',', $acciones);
            $lista = array_filter(array_map('trim', $lista));
            if (!empty($lista)) {
                $query->whereIn('accion', $lista);
            }
        }

        if ($moduloId = $request->input('modulo_id')) {
            $query->where('modulo_id', $moduloId);
        }

        if ($desde = $request->input('desde')) {
            $query->whereDate('created_at', '>=', $desde);
        }

        if ($hasta = $request->input('hasta')) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $bitacoras = $query->paginate(25)->withQueryString();

        $accionesDisponibles = Bitacora::distinct()->orderBy('accion')->pluck('accion');
        $modulos = ModuloCliente::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Admin/Cart/BitacoraIndex', [
            'bitacoras'          => $bitacoras,
            'filtros'            => $request->only(['accion', 'modulo_id', 'desde', 'hasta', 'user_id']),
            'accionesDisponibles' => $accionesDisponibles,
            'modulos'            => $modulos,
        ]);
    }
}
