<?php
namespace App\Listeners;

use App\Models\AccesoBitacora;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        AccesoBitacora::create([
            'usuario_id'      => null,
            'email_intentado' => $event->credentials['email'] ?? '',
            'evento'          => 'login_failed',
            'exito'           => false,
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
            'detalle'         => 'Intento de login fallido',
        ]);

        Log::channel('daily')->warning('LogFailedLogin: Intento de login fallido.', [
            'email_intentado' => $event->credentials['email'] ?? 'N/A',
            'ip'              => request()->ip(),
        ]);
    }
}