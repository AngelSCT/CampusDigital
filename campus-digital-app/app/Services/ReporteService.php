<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\AccesoBitacora;
use App\Models\ActividadBitacora;
use Illuminate\Support\Collection;

/**
 * Servicio de generación de reportes del módulo de seguridad.
 * Provee datos estructurados para exportación a CSV y PDF.
 */
class ReporteService
{
    /**
     * Genera el reporte de usuarios por rol con filtros opcionales.
     */
    public function reporteUsuariosPorRol(?string $rol = null): Collection
    {
        $query = Usuario::with('roles')
            ->select('id', 'nombre', 'apellido', 'email', 'email_verificado', 'bloqueado', 'created_at');

        if ($rol) {
            $query->whereHas('roles', fn ($q) => $q->where('nombre', $rol));
        }

        return $query->get()->map(function ($u) {
            return [
                'id'             => $u->id,
                'nombre'         => $u->nombre_completo,
                'email'          => $u->email,
                'roles'          => $u->roles->pluck('nombre')->join(', '),
                'verificado'     => $u->email_verificado ? 'Sí' : 'No',
                'bloqueado'      => $u->bloqueado ? 'Sí' : 'No',
                'fecha_registro' => $u->created_at->format('d/m/Y'),
            ];
        });
    }

    /**
     * Genera el reporte de accesos por periodo.
     */
    public function reporteAccesosPorPeriodo(string $desde, string $hasta): Collection
    {
        return AccesoBitacora::with('usuario')
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($a) {
                return [
                    'fecha'   => $a->created_at->format('d/m/Y H:i'),
                    'usuario' => $a->usuario?->nombre_completo ?? $a->email_intentado,
                    'evento'  => $a->evento,
                    'exito'   => $a->exito ? 'Exitoso' : 'Fallido',
                    'ip'      => $a->ip,
                ];
            });
    }

    /**
     * Genera el reporte de actividad por módulo.
     */
    public function reporteActividadPorModulo(string $modulo): Collection
    {
        return ActividadBitacora::with('usuario')
            ->where('modulo', $modulo)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($a) {
                return [
                    'fecha'   => $a->created_at->format('d/m/Y H:i'),
                    'usuario' => $a->usuario?->nombre_completo ?? 'Sistema',
                    'accion'  => $a->accion,
                    'tabla'   => $a->target_tabla,
                    'id'      => $a->target_id,
                    'exito'   => $a->exito ? 'Sí' : 'No',
                    'detalle' => $a->detalle,
                ];
            });
    }

    /**
     * Resumen ejecutivo de actividad para el dashboard principal.
     */
    public function resumenDashboard(): array
    {
        return [
            'usuarios_totales'     => Usuario::count(),
            'usuarios_activos'     => Usuario::where('bloqueado', false)->count(),
            'accesos_hoy'          => AccesoBitacora::whereDate('created_at', today())->count(),
            'fallos_hoy'           => AccesoBitacora::whereDate('created_at', today())->where('exito', false)->count(),
            'acciones_esta_semana' => ActividadBitacora::where('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }
}