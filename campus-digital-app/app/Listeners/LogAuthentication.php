<?php

namespace App\Listeners;

use App\Models\AccesoBitacora;
use App\Models\UsuarioSesion;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;

class LogAuthentication
{
    public function handleLogin(Login $event)
    {
        $usuario = $event->user;
        
        // Actualizar último login
        $usuario->update(['ultimo_login_at' => now()]);

        // Crear sesión
        $sesion = UsuarioSesion::create([
            'usuario_id' => $usuario->id,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'inicia_at' => now(),
            'expira_at' => now()->addMinutes(config('session.lifetime')),
            'activa' => true,
        ]);

        // Registrar acceso exitoso
        AccesoBitacora::create([
            'usuario_id' => $usuario->id,
            'sesion_id' => $sesion->id,
            'email_intentado' => $usuario->email,
            'evento' => 'login',
            'exito' => true,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function handleFailed(Failed $event)
    {
        AccesoBitacora::create([
            'usuario_id' => null,
            'sesion_id' => null,
            'email_intentado' => $event->credentials['email'] ?? 'N/A',
            'evento' => 'login_failed',
            'exito' => false,
            'detalle' => 'Credenciales inválidas',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function handleLogout(Logout $event)
    {
        $usuario = $event->user;
        
        if ($usuario) {
            // Cerrar sesión activa
            UsuarioSesion::where('usuario_id', $usuario->id)
                ->where('session_id', session()->getId())
                ->where('activa', true)
                ->update([
                    'termina_at' => now(),
                    'activa' => false,
                ]);

            // Registrar logout
            AccesoBitacora::create([
                'usuario_id' => $usuario->id,
                'email_intentado' => $usuario->email,
                'evento' => 'logout',
                'exito' => true,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    public function handleRegistered(Registered $event)
    {
        AccesoBitacora::create([
            'usuario_id' => $event->user->id,
            'email_intentado' => $event->user->email,
            'evento' => 'registro',
            'exito' => true,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}