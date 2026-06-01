<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\AccesoBitacora;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de autenticación y control de acceso.
 * Centraliza la lógica de login, logout y verificación de credenciales.
 */
class AuthService
{
    /**
     * Verifica las credenciales del usuario y registra el intento en bitácora.
     */
    public function verificarCredenciales(string $email, string $password): bool
    {
        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario) {
            $this->registrarIntento($email, false, 'Usuario no encontrado');
            return false;
        }

        if ($usuario->estaBloqueado()) {
            $this->registrarIntento($email, false, 'Cuenta bloqueada', $usuario->id);
            return false;
        }

        $valido = Hash::check($password, $usuario->getAuthPassword());
        $this->registrarIntento($email, $valido, $valido ? 'Login exitoso' : 'Contraseña incorrecta', $usuario->id);

        return $valido;
    }

    /**
     * Bloquea una cuenta de usuario por intentos fallidos excesivos.
     */
    public function bloquearPorIntentos(int $usuarioId, int $minutos = 30): void
    {
        Usuario::where('id', $usuarioId)->update([
            'bloqueado'       => true,
            'bloqueado_hasta' => now()->addMinutes($minutos),
        ]);

        Log::warning("Usuario {$usuarioId} bloqueado por {$minutos} minutos.");
    }

    /**
     * Desbloquea manualmente una cuenta de usuario.
     */
    public function desbloquearUsuario(int $usuarioId): void
    {
        Usuario::where('id', $usuarioId)->update([
            'bloqueado'       => false,
            'bloqueado_hasta' => null,
        ]);
    }

    /**
     * Registra un intento de acceso en la bitácora.
     */
    private function registrarIntento(string $email, bool $exito, string $detalle, ?int $usuarioId = null): void
    {
        AccesoBitacora::create([
            'usuario_id'      => $usuarioId,
            'email_intentado' => $email,
            'evento'          => $exito ? 'login' : 'login_fallido',
            'exito'           => $exito,
            'detalle'         => $detalle,
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }
}