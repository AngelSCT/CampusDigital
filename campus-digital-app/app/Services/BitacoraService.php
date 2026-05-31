<?php

namespace App\Services;

use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use App\Models\UsuarioSesion;
use Illuminate\Support\Facades\Auth;

/**
 * Servicio de bitácora.
 * Registra accesos y actividades relevantes del sistema de forma centralizada.
 */
class BitacoraService
{
    /**
     * Registra un evento de acceso (login, logout, bloqueo, etc.).
     */
    public function registrarAcceso(
        string  $evento,
        bool    $exito,
        string  $detalle    = '',
        ?string $email      = null,
        ?int    $usuarioId  = null,
        ?int    $sesionId   = null
    ): AccesoBitacora {
        return AccesoBitacora::create([
            'usuario_id'      => $usuarioId,
            'sesion_id'       => $sesionId,
            'email_intentado' => $email,
            'evento'          => $evento,
            'exito'           => $exito,
            'detalle'         => $detalle,
            'ip'              => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }

    /**
     * Registra una acción del usuario sobre un recurso del sistema.
     */
    public function registrarActividad(
        string  $accion,
        string  $modulo,
        string  $targetTabla = '',
        ?int    $targetId    = null,
        bool    $exito       = true,
        string  $detalle     = '',
        array   $meta        = []
    ): ActividadBitacora {
        $usuario = Auth::user();

        return ActividadBitacora::create([
            'usuario_id'   => $usuario?->id,
            'sesion_id'    => null,
            'accion'       => $accion,
            'modulo'       => $modulo,
            'target_tabla' => $targetTabla,
            'target_id'    => $targetId,
            'exito'        => $exito,
            'detalle'      => $detalle,
            'ip'           => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'meta_json'    => $meta ?: null,
        ]);
    }

    /**
     * Obtiene los últimos N accesos del sistema para el dashboard.
     */
    public function ultimosAccesos(int $limite = 20): \Illuminate\Database\Eloquent\Collection
    {
        return AccesoBitacora::with('usuario')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get();
    }

    /**
     * Cuenta intentos fallidos de login para un email en los últimos N minutos.
     */
    public function contarIntentosFallidos(string $email, int $minutos = 15): int
    {
        return AccesoBitacora::where('email_intentado', $email)
            ->where('exito', false)
            ->where('evento', 'login_fallido')
            ->where('created_at', '>=', now()->subMinutes($minutos))
            ->count();
    }

    /**
     * Retorna actividad reciente de un usuario específico.
     */
    public function actividadDeUsuario(int $usuarioId, int $limite = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActividadBitacora::where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get();
    }

    /**
     * Retorna estadísticas de accesos para el dashboard de seguridad.
     */
    public function estadisticasAcceso(): array
    {
        return [
            'exitosos_hoy'   => AccesoBitacora::where('exito', true)
                ->whereDate('created_at', today())
                ->count(),
            'fallidos_hoy'   => AccesoBitacora::where('exito', false)
                ->whereDate('created_at', today())
                ->count(),
            'total_semana'   => AccesoBitacora::where('created_at', '>=', now()->subWeek())->count(),
        ];
    }
}