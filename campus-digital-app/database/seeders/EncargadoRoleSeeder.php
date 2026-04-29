<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Tienda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EncargadoRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the role if it doesn't exist
        $role = Rol::firstOrCreate(
            ['nombre' => 'encargado_tienda'],
            [
                'descripcion' => 'Encargado responsable de la operación, pedidos e inventario de un negocio específico.',
                'activo' => true
            ]
        );

        // 2. Create demo managers for existing stores
        $tiendas = Tienda::limit(2)->get();
        
        foreach ($tiendas as $index => $tienda) {
            $email = "manager" . ($index + 1) . "@campusdigital.com";
            
            $user = Usuario::firstOrCreate(
                ['email' => $email],
                [
                    'nombre' => 'Encargado',
                    'apellido' => $tienda->nombre,
                    'telefono' => '1234567890',
                    'password_hash' => Hash::make('password'),
                    'email_verificado' => true,
                    'tienda_id' => $tienda->id
                ]
            );

            if (!$user->hasRole('encargado_tienda')) {
                $user->roles()->attach($role->id);
            }
            
            $this->command->info("Creado encargado: {$email} (Tienda: {$tienda->nombre})");
        }

        $this->command->info("✅ Rol 'encargado_tienda' y usuarios de prueba creados.");
    }
}
