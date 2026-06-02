<?php
namespace App\Listeners;

use App\Models\AccesoBitacora;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        if ($event->user) {
            AccesoBitacora::create([
                'usuario_id'      => $event->user->id,
                'email_intentado' => $event->user->email,
                'evento'          => 'logout',
                'exito'           => true,
                'ip'              => request()->ip(),
                'user_agent'      => request()->userAgent(),
                'detalle'         => 'Logout exitoso',
            ]);

            Log::channel('daily')->info('LogSuccessfulLogout: Logout exitoso.', [
                'usuario_id' => $event->user->id,
                'email'      => $event->user->email,
                'ip'         => request()->ip(),
            ]);
        }
    }
}