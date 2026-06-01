<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class RepartidorRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the role if it doesn't exist
        $role = Rol::firstOrCreate(
            ['nombre' => 'repartidor'],
            [
                'descripcion' => 'Personal encargado de la entrega de pedidos a domicilio o puntos de entrega.',
                'activo' => true
            ]
        );

        // 2. Assign the role to some demo users if needed
        // For example, assign to user with ID 3 if it exists
        $user = Usuario::find(3);
        if ($user && !$user->hasRole('repartidor')) {
            $user->roles()->attach($role->id);
            $this->command->info("Rol 'repartidor' asignado al usuario ID: {$user->id}");
        }

        $this->command->info("✅ Rol 'repartidor' verificado/creado correctamente.");
    }
}
