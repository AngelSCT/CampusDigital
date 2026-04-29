<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Tienda;
use App\Models\Rol;
use App\Models\Pedido;
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
            }
        ])->get();

        $repartidores = Usuario::whereHas('roles', function($q) {
                $q->where('nombre', 'repartidor');
            })
            ->with(['tienda'])
            ->get();

        return Inertia::render('Admin/ProveedorPanel', [
            'tiendas'          => $tiendas,
            'repartidores'     => $repartidores,
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

    public function buscarUsuarios(Request $request)
    {
        $q = $request->input('q');
        if (strlen($q) < 3) return response()->json([]);

        $usuarios = Usuario::where('nombre', 'like', "%$q%")
            ->orWhere('apellido', 'like', "%$q%")
            ->orWhere('email', 'like', "%$q%")
            ->limit(10)
            ->get(['id', 'nombre', 'apellido', 'email']);

        return response()->json($usuarios);
    }
}
