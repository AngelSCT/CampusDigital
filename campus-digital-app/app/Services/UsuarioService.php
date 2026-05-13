<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\UsuarioPerfil;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de gestión de usuarios.
 * Maneja creación, actualización, asignación de roles y eliminación.
 */
class UsuarioService
{
    /**
     * Crea un nuevo usuario junto con su perfil vacío.
     */
    public function crear(array $datos): Usuario
    {
        return DB::transaction(function () use ($datos) {
            $usuario = Usuario::create([
                'nombre'        => $datos['nombre'],
                'apellido'      => $datos['apellido'],
                'email'         => $datos['email'],
                'telefono'      => $datos['telefono'] ?? null,
                'password_hash' => Hash::make($datos['password']),
                'email_verificado' => false,
                'bloqueado'     => false,
            ]);

            UsuarioPerfil::create([
                'usuario_id' => $usuario->id,
            ]);

            if (!empty($datos['rol'])) {
                $this->asignarRol($usuario, $datos['rol']);
            }

            return $usuario;
        });
    }

    /**
     * Actualiza los datos básicos de un usuario existente.
     */
    public function actualizar(Usuario $usuario, array $datos): bool
    {
        $campos = array_filter([
            'nombre'   => $datos['nombre']   ?? null,
            'apellido' => $datos['apellido'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
        ]);

        return $usuario->update($campos);
    }

    /**
     * Asigna un rol a un usuario, evitando duplicados.
     */
    public function asignarRol(Usuario $usuario, string $nombreRol): void
    {
        $rol = Rol::where('nombre', $nombreRol)->where('activo', true)->first();

        if (!$rol) {
            Log::error("Rol '{$nombreRol}' no encontrado o inactivo.");
            return;
        }

        if (!$usuario->hasRole($nombreRol)) {
            $usuario->roles()->attach($rol->id);
        }
    }

    /**
     * Revoca un rol específico de un usuario.
     */
    public function revocarRol(Usuario $usuario, string $nombreRol): void
    {
        $rol = Rol::where('nombre', $nombreRol)->first();

        if ($rol) {
            $usuario->roles()->detach($rol->id);
        }
    }

    /**
     * Cambia la contraseña de un usuario.
     */
    public function cambiarPassword(Usuario $usuario, string $nuevaPassword): bool
    {
        return $usuario->update([
            'password_hash' => Hash::make($nuevaPassword),
        ]);
    }

    /**
     * Elimina (soft delete) un usuario del sistema.
     */
    public function eliminar(Usuario $usuario): bool
    {
        return $usuario->delete();
    }

    /**
     * Retorna estadísticas básicas de usuarios para el dashboard.
     */
    public function obtenerEstadisticas(): array
    {
        return [
            'total'         => Usuario::count(),
            'activos'       => Usuario::where('bloqueado', false)->count(),
            'bloqueados'    => Usuario::where('bloqueado', true)->count(),
            'sin_verificar' => Usuario::where('email_verificado', false)->count(),
        ];
    }
}