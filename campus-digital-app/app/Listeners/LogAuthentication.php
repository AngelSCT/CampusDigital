<?php
namespace App\Listeners;

use App\Models\AccesoBitacora;
use App\Models\UsuarioSesion;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class LogAuthentication
{
    public function handleLogin(Login $event)
    {
        $usuario = $event->user;

        $usuario->update(['ultimo_login_at' => now()]);

        $sesion = UsuarioSesion::create([
            'usuario_id' => $usuario->id,
            'session_id' => session()->getId(),
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
            'inicia_at'  => now(),
            'expira_at'  => now()->addMinutes(config('session.lifetime')),
            'activa'     => true,
        ]);

        AccesoBitacora::create([
            'usuario_id'      => $usuario->id,
            'sesion_id'       => $sesion->id,
            'email_intentado' => $usuario->email,
            'evento'          => 'login',
            'exito'           => true,
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);

        Log::channel('daily')->info('LogAuthentication@handleLogin: Login exitoso.', [
            'usuario_id' => $usuario->id,
            'email'      => $usuario->email,
            'sesion_id'  => $sesion->id,
            'ip'         => request()->ip(),
        ]);
    }

    public function handleFailed(Failed $event)
    {
        AccesoBitacora::create([
            'usuario_id'      => null,
            'sesion_id'       => null,
            'email_intentado' => $event->credentials['email'] ?? 'N/A',
            'evento'          => 'login_failed',
            'exito'           => false,
            'detalle'         => 'Credenciales inválidas',
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);

        Log::channel('daily')->warning('LogAuthentication@handleFailed: Intento de login fallido.', [
            'email_intentado' => $event->credentials['email'] ?? 'N/A',
            'ip'              => request()->ip(),
        ]);
    }

    public function handleLogout(Logout $event)
    {
        $usuario = $event->user;

        if ($usuario) {
            UsuarioSesion::where('usuario_id', $usuario->id)
                ->where('session_id', session()->getId())
                ->where('activa', true)
                ->update([
                    'termina_at' => now(),
                    'activa'     => false,
                ]);

            AccesoBitacora::create([
                'usuario_id'      => $usuario->id,
                'email_intentado' => $usuario->email,
                'evento'          => 'logout',
                'exito'           => true,
                'ip'              => request()->ip(),
                'user_agent'      => request()->userAgent(),
            ]);

            Log::channel('daily')->info('LogAuthentication@handleLogout: Logout exitoso.', [
                'usuario_id' => $usuario->id,
                'email'      => $usuario->email,
                'ip'         => request()->ip(),
            ]);
        }
    }

    public function handleRegistered(Registered $event)
    {
        AccesoBitacora::create([
            'usuario_id'      => $event->user->id,
            'email_intentado' => $event->user->email,
            'evento'          => 'registro',
            'exito'           => true,
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);

        Log::channel('daily')->info('LogAuthentication@handleRegistered: Nuevo usuario registrado.', [
            'usuario_id' => $event->user->id,
            'email'      => $event->user->email,
            'ip'         => request()->ip(),
        ]);
    }
}