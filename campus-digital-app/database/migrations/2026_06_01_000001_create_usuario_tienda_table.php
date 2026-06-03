<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla pivote usuario_tienda
        Schema::create('usuario_tienda', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
                  
            $table->foreignId('tienda_id')
                  ->constrained('tienda')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->timestamps();

            // Evitar duplicados
            $table->unique(['usuario_id', 'tienda_id']);
        });

        // 2. Poblar la tabla pivote con las asignaciones existentes
        $usuariosConTienda = DB::table('usuario')
            ->whereNotNull('tienda_id')
            ->get(['id', 'tienda_id', 'created_at', 'updated_at']);

        foreach ($usuariosConTienda as $user) {
            // Verificar si la tienda existe antes de insertar para evitar fallos de integridad
            $tiendaExiste = DB::table('tienda')->where('id', $user->tienda_id)->exists();
            if ($tiendaExiste) {
                DB::table('usuario_tienda')->insertOrIgnore([
                    'usuario_id' => $user->id,
                    'tienda_id'  => $user->tienda_id,
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_tienda');
    }
};
