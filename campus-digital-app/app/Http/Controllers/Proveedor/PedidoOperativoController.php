<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PedidoOperativoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->tienda_id) {
            return Inertia::render('Error', [
                'status' => 403,
                'message' => 'No tienes una tienda asignada. Contacta al administrador.'
            ]);
        }

        $tienda = \App\Models\Tienda::withCount(['productos', 'operadores'])->findOrFail($user->tienda_id);

        $stats = [
            'pedidos_hoy'     => $tienda->pedidos()->whereDate('created_at', today())->count(),
            'ventas_hoy'      => $tienda->pedidos()->whereDate('created_at', today())->where('estado', 'entregado')->sum('total'),
            'pedidos_pendientes' => $tienda->pedidos()->whereIn('estado', ['creado', 'aceptado', 'en_proceso'])->count(),
        ];

        $repartidores = \App\Models\Usuario::whereHas('roles', function ($query) {
            $query->where('nombre', 'repartidor');
        })->get();

        return Inertia::render('Admin/TiendaOperativa', [
            'tienda'       => $tienda,
            'stats'        => $stats,
            'tipos'        => \App\Models\Tienda::TIPOS,
            'colores'      => \App\Models\Tienda::TIPO_COLORES,
            'repartidores' => $repartidores,
            'isProvider'   => true
        ]);
    }

    public function reportes(Request $request)
    {
        $user = $request->user();
        if (!$user->tienda_id) abort(403);
        
        $tienda = \App\Models\Tienda::findOrFail($user->tienda_id);

        return Inertia::render('Proveedor/Reportes', [
            'tienda' => $tienda
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $modulo = $user->modulo ?? 'otro';

        $validated = $request->validate([
            'usuario_id' => 'required|exists:usuario,id',
            'total' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'notas' => 'nullable|string',
        ]);

        $tienda = \App\Models\Tienda::findOrFail($user->tienda_id);

        $pedido = Pedido::create([
            'usuario_id' => $validated['usuario_id'],
            'numero_folio' => Pedido::generarFolio(),
            'estado' => 'creado',
            'modulo' => $tienda->tipo, // Use store type as modulo
            'tienda_id' => $tienda->id,
            'total' => $validated['total'],
            'descripcion' => $validated['descripcion'],
            'notas' => $validated['notas'] ?? '',
            'meta_json' => ['manual' => true, 'created_by' => $user->id]
        ]);

        return back()->with('success', "Pedido {$pedido->numero_folio} creado correctamente");
    }

    public function searchUsers(Request $request)
    {
        $search = $request->query('q');
        if (strlen($search) < 3) return response()->json([]);

        $users = \App\Models\Usuario::where('nombre', 'ilike', "%{$search}%")
            ->orWhere('apellido', 'ilike', "%{$search}%")
            ->orWhere('email', 'ilike', "%{$search}%")
            ->limit(10)
            ->get(['id', 'nombre', 'apellido', 'email']);

        return response()->json($users);
    }
}
