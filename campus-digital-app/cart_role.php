<?php
$rol = App\Models\Rol::firstOrCreate(
    ['nombre' => 'admin_carrito'],
    ['descripcion' => 'Admin del módulo carrito', 'activo' => true]
);
$admin = App\Models\Usuario::where('email', 'admin@campusdigital.com')->first();
$admin->roles()->syncWithoutDetaching([$rol->id]);
echo "OK: rol admin_carrito asignado a admin\n";