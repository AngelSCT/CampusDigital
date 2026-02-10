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
        // Obtener roles
        $rolAdmin = Rol::where('nombre', 'administrador')->first();
        $rolProveedor = Rol::where('nombre', 'proveedor_area')->first();
        $rolEstudiante = Rol::where('nombre', 'estudiante')->first();

        // Crear usuario administrador
        $admin = Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@campusdigital.com',
            'telefono' => '1234567890',
            'password_hash' => Hash::make('password'),
            'email_verificado' => true,
        ]);
        $admin->roles()->attach($rolAdmin->id);
        $admin->perfil()->create([]);

        // Crear usuario proveedor
        $proveedor = Usuario::create([
            'nombre' => 'Proveedor',
            'apellido' => 'Cafetería',
            'email' => 'proveedor@campusdigital.com',
            'telefono' => '0987654321',
            'password_hash' => Hash::make('password'),
            'email_verificado' => true,
        ]);
        $proveedor->roles()->attach($rolProveedor->id);
        $proveedor->perfil()->create([]);

        // Crear usuario estudiante
        $estudiante = Usuario::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'estudiante@campusdigital.com',
            'telefono' => '5555555555',
            'password_hash' => Hash::make('password'),
            'email_verificado' => true,
        ]);
        $estudiante->roles()->attach($rolEstudiante->id);
        $estudiante->perfil()->create([
            'fecha_nacimiento' => '2000-01-15',
            'genero' => 'masculino',
        ]);

        $this->command->info('Usuarios de prueba creados:');
        $this->command->info('Admin: admin@campusdigital.com / password');
        $this->command->info('Proveedor: proveedor@campusdigital.com / password');
        $this->command->info('Estudiante: estudiante@campusdigital.com / password');
    }
}