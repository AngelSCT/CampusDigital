<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Tienda;
use App\Models\Rol;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminProveedorController extends Controller
{
    public function dashboard()
    {
        $tiendas = Tienda::withCount([
            'usuarios as operadores_count' => function($q) {
                $q->whereHas('roles', fn($r) => $r->where('nombre', 'proveedor_area'));
            },
            'pedidos as pedidos_pendientes' => function($q) {
                $q->where('estado', 'creado');
            },
            'pedidos as pedidos_proceso' => function($q) {
                $q->whereIn('estado', ['aceptado', 'en_proceso']);
            },
            'pedidos as pedidos_listos' => function($q) {
                $q->where('estado', 'listo');
            }
        ])->get();

        $repartidores = Usuario::whereHas('roles', function($q) {
                $q->where('nombre', 'repartidor');
            })
            ->withCount(['pedidosRepartidor as pedidos_entregados' => function($q) {
                $q->where('estado', 'entregado');
            }])
            ->get();

        $pedidosRecientes = Pedido::with(['usuario', 'tienda'])
            ->whereIn('estado', ['creado', 'aceptado', 'en_proceso', 'listo'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/ProveedorPanel', [
            'tiendas'          => $tiendas,
            'repartidores'     => $repartidores,
            'pedidosRecientes' => $pedidosRecientes,
            'tipos'            => Tienda::TIPOS
        ]);
    }

    public function index()
    {
        $proveedores = Usuario::whereHas('roles', function($q) {
                $q->where('nombre', 'proveedor_area');
            })
            ->with(['roles', 'tienda'])
            ->get();

        $tiendas = Tienda::all();
        
        $repartidores = Usuario::whereHas('roles', function($q) {
                $q->where('nombre', 'repartidor');
            })
            ->with(['roles', 'tienda'])
            ->get();

        return Inertia::render('Admin/Tiendas', [
            'proveedores'  => $proveedores,
            'tiendas'      => $tiendas,
            'repartidores' => $repartidores,
            'tabActive'    => 'proveedores' 
        ]);
    }

    public function asignarTienda(Request $request, Usuario $usuario)
    {
        $request->validate([
            'tienda_id' => 'required|exists:tienda,id'
        ]);

        $usuario->update(['tienda_id' => $request->tienda_id]);

        return redirect()->back()->with('success', 'Tienda asignada correctamente.');
    }
}
