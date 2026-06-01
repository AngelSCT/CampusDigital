<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Collection;

/**
 * Servicio de gestión de permisos del sistema.
 * Maneja el ciclo de vida de permisos y su asignación a roles.
 */
class PermisoService
{
    /**
     * Crea un nuevo permiso en el sistema.
     */
    public function crear(string $clave, string $descripcion): Permiso
    {
        return Permiso::create([
            'clave'       => $clave,
            'descripcion' => $descripcion,
            'activo'      => true,
        ]);
    }

    /**
     * Desactiva un permiso sin eliminarlo.
     */
    public function desactivar(Permiso $permiso): bool
    {
        return $permiso->update(['activo' => false]);
    }

    /**
     * Retorna todos los permisos activos agrupados por prefijo de módulo.
     */
    public function obtenerAgrupados(): array
    {
        $permisos = Permiso::where('activo', true)->orderBy('clave')->get();
        $grupos   = [];

        foreach ($permisos as $permiso) {
            $modulo = explode('.', $permiso->clave)[0] ?? 'general';
            $grupos[$modulo][] = $permiso;
        }

        return $grupos;
    }

    /**
     * Verifica si un permiso ya está asignado a algún rol activo.
     */
    public function estaEnUso(Permiso $permiso): bool
    {
        return $permiso->roles()->where('activo', true)->exists();
    }

    /**
     * Retorna los roles que tienen asignado un permiso específico.
     */
    public function rolesConPermiso(string $clave): Collection
    {
        $permiso = Permiso::where('clave', $clave)->first();

        if (!$permiso) {
            return collect();
        }

        return $permiso->roles()->where('activo', true)->get();
    }
}