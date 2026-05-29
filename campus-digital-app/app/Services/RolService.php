<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de gestión de roles y permisos.
 * Centraliza la lógica de asignación y control de acceso basado en roles.
 */
class RolService
{
    /**
     * Crea un nuevo rol en el sistema.
     */
    public function crear(string $nombre, string $descripcion, bool $activo = true): Rol
    {
        return Rol::create([
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
            'activo'      => $activo,
        ]);
    }

    /**
     * Asigna un conjunto de permisos a un rol, reemplazando los existentes.
     */
    public function sincronizarPermisos(Rol $rol, array $claves): void
    {
        $ids = Permiso::whereIn('clave', $claves)
            ->where('activo', true)
            ->pluck('id')
            ->toArray();

        $rol->permisos()->sync($ids);

        Log::info("Permisos del rol '{$rol->nombre}' actualizados.", ['permisos' => $claves]);
    }

    /**
     * Agrega un permiso individual a un rol sin afectar los demás.
     */
    public function agregarPermiso(Rol $rol, string $clave): void
    {
        $permiso = Permiso::where('clave', $clave)->where('activo', true)->first();

        if (!$permiso) {
            Log::warning("Permiso '{$clave}' no encontrado o inactivo.");
            return;
        }

        $rol->permisos()->syncWithoutDetaching([$permiso->id]);
    }

    /**
     * Elimina un permiso específico de un rol.
     */
    public function quitarPermiso(Rol $rol, string $clave): void
    {
        $permiso = Permiso::where('clave', $clave)->first();

        if ($permiso) {
            $rol->permisos()->detach($permiso->id);
        }
    }

    /**
     * Desactiva un rol sin eliminarlo del sistema.
     */
    public function desactivar(Rol $rol): bool
    {
        return $rol->update(['activo' => false]);
    }

    /**
     * Reactiva un rol previamente desactivado.
     */
    public function activar(Rol $rol): bool
    {
        return $rol->update(['activo' => true]);
    }

    /**
     * Retorna todos los roles activos con sus permisos cargados.
     */
    public function obtenerRolesActivos(): \Illuminate\Database\Eloquent\Collection
    {
        return Rol::where('activo', true)->with('permisos')->get();
    }

    /**
     * Verifica si un rol tiene un permiso específico por clave.
     */
    public function rolTienePermiso(Rol $rol, string $clave): bool
    {
        return $rol->permisos()->where('clave', $clave)->exists();
    }
}