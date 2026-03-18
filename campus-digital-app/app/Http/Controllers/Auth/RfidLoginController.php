<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TarjetaUniversitaria;
use App\Models\AccesoBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RfidLoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|max:64',
            'pin' => 'required|digits:4',
        ]);

        $uid     = strtoupper(trim($data['uid']));
        $tarjeta = TarjetaUniversitaria::where('uid', $uid)
            ->whereNull('deleted_at')
            ->with('usuario')
            ->first();

        if (!$tarjeta) {
            $this->bitacoraFallida(null, '', 'rfid_login_failed',
                'UID no registrado: ' . $uid, $request);

            return back()->withErrors(['uid' => 'Tarjeta no reconocida en el sistema.']);
        }

        if (!$tarjeta->pin_hash) {
            $this->bitacoraFallida($tarjeta->usuario_id,
                $tarjeta->usuario?->email ?? '', 'rfid_login_failed',
                'PIN no configurado para esta tarjeta.', $request);

            return back()->withErrors(['pin' => 'Esta tarjeta no tiene PIN configurado. Ingresa a tu perfil y configura tu PIN primero.']);
        }

        if (!Hash::check($data['pin'], $tarjeta->pin_hash)) {
            $this->bitacoraFallida($tarjeta->usuario_id,
                $tarjeta->usuario?->email ?? '', 'rfid_login_failed',
                'PIN incorrecto para tarjeta UID: ' . $uid, $request);

            return back()->withErrors(['pin' => 'PIN incorrecto.']);
        }

        if ($tarjeta->estaBloqueada()) {
            $this->bitacoraFallida($tarjeta->usuario_id,
                $tarjeta->usuario?->email ?? '', 'rfid_login_failed',
                'Tarjeta en estado "' . $tarjeta->estado . '". Motivo: ' . ($tarjeta->motivo_bloqueo ?? 'Sin motivo'),
                $request);

            $msg = match ($tarjeta->estado) {
                'bloqueada' => 'Tu tarjeta está bloqueada. Contacta a administración.',
                'perdida'   => 'Esta tarjeta fue reportada como perdida.',
                'cancelada' => 'Esta tarjeta ha sido cancelada.',
                default     => 'Tarjeta no disponible.',
            };

            return back()->withErrors(['uid' => $msg]);
        }

        if (!$tarjeta->estaActiva()) {
            $this->bitacoraFallida($tarjeta->usuario_id,
                $tarjeta->usuario?->email ?? '', 'rfid_login_failed',
                'Tarjeta en estado: ' . $tarjeta->estado, $request);

            return back()->withErrors(['uid' => 'Tu tarjeta no está activa.']);
        }

        $usuario = $tarjeta->usuario;

        if (!$usuario || $usuario->deleted_at) {
            return back()->withErrors(['uid' => 'El usuario asociado a esta tarjeta no está disponible.']);
        }

        if ($usuario->estaBloqueado()) {
            $this->bitacoraFallida($usuario->id, $usuario->email,
                'rfid_login_failed', 'Usuario bloqueado. Acceso por tarjeta denegado.', $request);

            return back()->withErrors(['uid' => 'Tu cuenta está bloqueada. Contacta a administración.']);
        }

        if (!$usuario->hasVerifiedEmail()) {
            $this->bitacoraFallida($usuario->id, $usuario->email,
                'rfid_login_failed', 'Email no verificado.', $request);

            return back()->withErrors(['uid' => 'Debes verificar tu correo electrónico antes de usar la tarjeta.']);
        }
        
        Auth::login($usuario, false);
        $request->session()->regenerate();

        $tarjeta->update(['ultimo_uso_at' => now()]);

        AccesoBitacora::create([
            'usuario_id'      => $usuario->id,
            'sesion_id'       => null,
            'email_intentado' => $usuario->email,
            'evento'          => 'rfid_login_success',
            'exito'           => true,
            'detalle'         => 'Login exitoso por tarjeta RFID/NFC con PIN. UID: ' . $uid,
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent() ?? '',
        ]);

        return redirect()->intended(route('dashboard'));
    }

    private function bitacoraFallida(?int $usuarioId, string $email, string $evento, string $detalle, Request $request): void
    {
        AccesoBitacora::create([
            'usuario_id'      => $usuarioId,
            'sesion_id'       => null,
            'email_intentado' => $email,
            'evento'          => $evento,
            'exito'           => false,
            'detalle'         => $detalle,
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent() ?? '',
        ]);
    }
}