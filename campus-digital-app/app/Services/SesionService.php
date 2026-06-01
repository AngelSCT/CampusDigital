<?php

namespace App\Services;

use App\Models\UsuarioSesion;
use Illuminate\Support\Str;

/**
 * Servicio de gestión de sesiones de usuario.
 * Controla apertura, cierre y consulta de sesiones activas.
 */
class SesionService
{
    /**
     * Registra una nueva sesión al hacer login.
     */
    public function abrirSesion(int $usuarioId, int $horasExpiracion = 8): UsuarioSesion
    {
        return UsuarioSesion::create([
            'usuario_id' => $usuarioId,
            'session_id' => Str::uuid()->toString(),
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
            'inicia_at'  => now(),
            'expira_at'  => now()->addHours($horasExpiracion),
            'activa'     => true,
        ]);
    }

    /**
     * Cierra la sesión activa de un usuario.
     */
    public function cerrarSesion(int $usuarioId): void
    {
        UsuarioSesion::where('usuario_id', $usuarioId)
            ->where('activa', true)
            ->update([
                'activa'     => false,
                'termina_at' => now(),
            ]);
    }

    /**
     * Verifica si un usuario tiene una sesión activa y vigente.
     */
    public function tieneSesionActiva(int $usuarioId): bool
    {
        return UsuarioSesion::where('usuario_id', $usuarioId)
            ->where('activa', true)
            ->where('expira_at', '>', now())
            ->exists();
    }

    /**
     * Cierra todas las sesiones activas del sistema (uso administrativo).
     */
    public function cerrarTodasLasSesiones(): int
    {
        return UsuarioSesion::where('activa', true)->update([
            'activa'     => false,
            'termina_at' => now(),
        ]);
    }

    /**
     * Retorna las sesiones activas del sistema para el dashboard.
     */
    public function sesionesActivas(): \Illuminate\Database\Eloquent\Collection
    {
        return UsuarioSesion::with('usuario')
            ->where('activa', true)
            ->where('expira_at', '>', now())
            ->orderByDesc('inicia_at')
            ->get();
    }
}