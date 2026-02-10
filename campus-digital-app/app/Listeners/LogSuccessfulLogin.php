<?php

namespace App\Listeners;

use App\Models\AccesoBitacora;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        AccesoBitacora::create([
            'usuario_id' => $event->user->id,
            'email_intentado' => $event->user->email,
            'evento' => 'login_success',
            'exito' => true,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'detalle' => 'Login exitoso',
        ]);

        // Actualizar último login
        $event->user->update([
            'ultimo_login_at' => now(),
        ]);
    }
}