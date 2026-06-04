<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RepartidorProveedorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->tienda_id) abort(403, 'No tienes una tienda asignada.');

        $repartidores = Usuario::where('tienda_id', $user->tienda_id)
            ->whereHas('roles', function($q) {
                $q->where('nombre', 'repartidor');
            })
            ->with('roles')
            ->get();

        return Inertia::render('Proveedor/Repartidores', [
            'repartidores' => $repartidores,
            'tienda'       => $user->tienda,
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        if (strlen($q) < 3) return response()->json([]);

        $usuarios = Usuario::where(function($query) use ($q) {
                $query->where('nombre', 'like', "%$q%")
                      ->orWhere('apellido', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
            })
            ->limit(10)
            ->get(['id', 'nombre', 'apellido', 'email']);

        return response()->json($usuarios);
    }

    public function asignar(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuario,id',
        ]);

        $me = $request->user();
        $usuario = Usuario::findOrFail($request->usuario_id);
        
        // Asignar a la misma tienda
        $usuario->update(['tienda_id' => $me->tienda_id]);
        
        // Asegurar que tenga el rol de repartidor
        $rolRepartidor = Rol::where('nombre', 'repartidor')->first();
        if ($rolRepartidor && !$usuario->hasRole('repartidor')) {
            $usuario->roles()->attach($rolRepartidor->id);
        }

        return redirect()->back()->with('success', 'Repartidor asignado correctamente a tu equipo.');
    }

    public function desvincular(Request $request, Usuario $usuario)
    {
        $me = $request->user();
        if ($usuario->tienda_id !== $me->tienda_id) {
            abort(403);
        }

        // Quitar de la tienda
        $usuario->update(['tienda_id' => null]);
        
        // Opcional: ¿Quitarle el rol de repartidor? 
        // Por ahora solo lo quitamos de la tienda del proveedor.
        
        return redirect()->back()->with('success', 'Repartidor removido de tu equipo.');
    }
}
