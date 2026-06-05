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

    if (!app()->environment('production')) {
        $this->call([
            CategoriasSeeder::class,
            RolesCartSeeder::class,
            RecursosDemoSeeder::class,
            ReservasDemoSeeder::class,
        ]);
    }
}
}