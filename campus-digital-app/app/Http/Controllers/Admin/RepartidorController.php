<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RepartidorController extends Controller
{
    public function index()
    {
        $repartidores = Usuario::whereHas('roles', function($q) {
                $q->where('nombre', 'repartidor');
            })
            ->with('roles')
            ->get();

        $todosLosUsuarios = Usuario::with('roles')->get();

        return Inertia::render('Admin/Tiendas', [
            'repartidores' => $repartidores,
            'usuarios'     => $todosLosUsuarios,
            'tabActive'    => 'repartidores'
        ]);
    }

    public function toggle(Request $request, Usuario $usuario)
    {
        $rolRepartidor = Rol::where('nombre', 'repartidor')->first();
        
        if ($usuario->hasRole('repartidor')) {
            $usuario->roles()->detach($rolRepartidor->id);
            $msg = 'Rol de repartidor removido.';
        } else {
            $usuario->roles()->attach($rolRepartidor->id);
            $msg = 'Rol de repartidor asignado.';
        }

        return redirect()->back()->with('success', $msg);
    }
}
