<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar roles
        $roles = [
            ['nombre' => 'estudiante', 'descripcion' => 'Usuario final que consume servicios digitales'],
            ['nombre' => 'proveedor_area', 'descripcion' => 'Proveedor o área interna que atiende solicitudes'],
            ['nombre' => 'administrador', 'descripcion' => 'Admin con acceso total'],
        ];

        foreach ($roles as $rol) {
            DB::table('rol')->insertOrIgnore($rol);
        }

        // Insertar permisos
        $permisos = [
            ['clave' => 'user.read', 'descripcion' => 'Consultar usuarios'],
            ['clave' => 'user.write', 'descripcion' => 'Crear/editar usuarios'],
            ['clave' => 'role.read', 'descripcion' => 'Consultar roles'],
            ['clave' => 'role.write', 'descripcion' => 'Administrar roles'],
            ['clave' => 'permission.read', 'descripcion' => 'Consultar permisos'],
            ['clave' => 'permission.write', 'descripcion' => 'Administrar permisos'],
            ['clave' => 'audit.read', 'descripcion' => 'Consultar bitácoras'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permiso')->insertOrIgnore($permiso);
        }

        // Asignar todos los permisos al administrador
        $adminRol = DB::table('rol')->where('nombre', 'administrador')->first();
        $todosPermisos = DB::table('permiso')->get();

        foreach ($todosPermisos as $permiso) {
            DB::table('rol_permiso')->insertOrIgnore([
                'rol_id' => $adminRol->id,
                'permiso_id' => $permiso->id,
            ]);
        }

        // Asignar permisos limitados a proveedor_area
        $proveedorRol = DB::table('rol')->where('nombre', 'proveedor_area')->first();
        $permisosProveedor = DB::table('permiso')->whereIn('clave', ['user.read', 'audit.read'])->get();

        foreach ($permisosProveedor as $permiso) {
            DB::table('rol_permiso')->insertOrIgnore([
                'rol_id' => $proveedorRol->id,
                'permiso_id' => $permiso->id,
            ]);
        }

        // Asignar permisos limitados a estudiante
        $estudianteRol = DB::table('rol')->where('nombre', 'estudiante')->first();
        $permisosEstudiante = DB::table('permiso')->where('clave', 'user.read')->get();

        foreach ($permisosEstudiante as $permiso) {
            DB::table('rol_permiso')->insertOrIgnore([
                'rol_id' => $estudianteRol->id,
                'permiso_id' => $permiso->id,
            ]);
        }
    }
}