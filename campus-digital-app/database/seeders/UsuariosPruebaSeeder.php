<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin     = Rol::where('nombre', 'administrador')->first();
        $rolProveedor = Rol::where('nombre', 'proveedor_area')->first();
        $rolEstudiante = Rol::where('nombre', 'estudiante')->first();

        // ── Admin ─────────────────────────────────────────────────────────────
        $admin = Usuario::updateOrCreate(
            ['email' => 'admin@campusdigital.com'],
            [
                'nombre'           => 'Admin',
                'apellido'         => 'Sistema',
                'telefono'         => '1234567890',
                'password_hash'    => Hash::make('password'),
                'email_verificado' => true,
                'tienda'           => null,
                'matricula'        => '00000001',
            ]
        );
        $admin->roles()->syncWithoutDetaching([$rolAdmin->id]);
        $admin->perfil()->firstOrCreate([]);

        // ── Proveedor Cafetería ───────────────────────────────────────────────
        $proveedor = Usuario::updateOrCreate(
            ['email' => 'proveedor@campusdigital.com'],
            [
                'nombre'           => 'Proveedor',
                'apellido'         => 'Cafetería',
                'telefono'         => '0987654321',
                'password_hash'    => Hash::make('password'),
                'email_verificado' => true,
                'tienda'           => 'Cafetería',
                'matricula'        => '99999999',
            ]
        );
        $proveedor->roles()->syncWithoutDetaching([$rolProveedor->id]);
        $proveedor->perfil()->firstOrCreate([]);

        // ── Estudiante ────────────────────────────────────────────────────────
        $estudiante = Usuario::updateOrCreate(
            ['email' => 'estudiante@campusdigital.com'],
            [
                'nombre'           => 'Juan',
                'apellido'         => 'Pérez',
                'telefono'         => '5555555555',
                'password_hash'    => Hash::make('password'),
                'email_verificado' => true,
                'tienda'           => null,
                'matricula'        => '20260001',
            ]
        );
        $estudiante->roles()->syncWithoutDetaching([$rolEstudiante->id]);
        $estudiante->perfil()->firstOrCreate(
            [],
            ['fecha_nacimiento' => '2000-01-15', 'genero' => 'masculino']
        );

        $this->command->info('Usuarios de prueba listos (idempotente):');
        $this->command->info('  Admin:      admin@campusdigital.com / password');
        $this->command->info('  Proveedor:  proveedor@campusdigital.com / password  [tienda: Cafetería]');
        $this->command->info('  Estudiante: estudiante@campusdigital.com / password');
    }
}