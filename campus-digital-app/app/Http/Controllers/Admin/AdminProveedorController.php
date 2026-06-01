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
            ->with(['roles', 'tienda', 'tiendas'])
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
            'tienda_ids' => 'required|array',
            'tienda_ids.*' => 'exists:tienda,id'
        ]);

        $tiendaIds = $request->tienda_ids;
        $usuario->tiendas()->sync($tiendaIds);

        // Compatibilidad: Guardar la primera tienda en el campo tienda_id tradicional
        $mainTiendaId = count($tiendaIds) > 0 ? $tiendaIds[0] : null;
        $usuario->update(['tienda_id' => $mainTiendaId]);

        return redirect()->back()->with('success', 'Tiendas asignadas correctamente.');
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

    public function asignarRolProveedor(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuario,id'
        ]);

        $usuario = Usuario::findOrFail($request->usuario_id);
        $rolProveedor = Rol::where('nombre', 'proveedor_area')->first();

        if (!$usuario->hasRole('proveedor_area')) {
            $usuario->roles()->attach($rolProveedor->id);
        }

        return redirect()->back()->with('success', 'Usuario dado de alta como proveedor.');
    }

    public function quitarRolProveedor(Usuario $usuario)
    {
        $rolProveedor = Rol::where('nombre', 'proveedor_area')->first();
        $usuario->roles()->detach($rolProveedor->id);
        
        // Desvincular tiendas
        $usuario->tiendas()->sync([]);
        $usuario->update(['tienda_id' => null]);

        return redirect()->back()->with('success', 'Usuario removido de los proveedores.');
    }
}
