<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermisosSeeder::class,
            UsuariosPruebaSeeder::class,
            ProductSeeder::class,
            CarritoSeeder::class,
            PedidoSeeder::class,
            CatalogoSeeder::class,
        ]);

        // CategoriasSeeder inserta datos base del módulo Carrito-servicio.
        // Usa updateOrCreate, por lo que es idempotente y seguro repetirlo.
        if (!app()->environment('production')) {
            $this->call([
                CategoriasSeeder::class,
                RolesCartSeeder::class,  // crea el rol 'admin_carrito'
            ]);
        }
    }
}