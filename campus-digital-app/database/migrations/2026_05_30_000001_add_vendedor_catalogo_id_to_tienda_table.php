<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * INTEGRACIÓN MÓDULO 4.3 → 4.9
 *
 * Agrega el campo `vendedor_catalogo_id` a la tabla `tienda`.
 *
 * Este campo es el vínculo entre una tienda del módulo 4.9 y su
 * vendedor correspondiente en el catálogo del módulo 4.3.
 *
 * El ProductoController lo usa para saber qué id_vendedor consultar
 * al endpoint GET /api/catalogo-integracion/vendedor/{id_vendedor}.
 *
 * Es nullable: si es NULL, la tienda no tiene catálogo 4.3 asociado
 * y el panel sigue funcionando normalmente con productos propios.
 *
 * Para ejecutar:
 *   php artisan migrate
 *
 * Para asignar el id_vendedor a una tienda (desde tinker o seeder):
 *   Tienda::find(1)->update(['vendedor_catalogo_id' => 3]);
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tienda', function (Blueprint $table) {
            $table->unsignedBigInteger('vendedor_catalogo_id')
                  ->nullable()
                  ->after('activo')
                  ->comment('id_vendedor en el catálogo del módulo 4.3');
        });
    }

    public function down(): void
    {
        Schema::table('tienda', function (Blueprint $table) {
            $table->dropColumn('vendedor_catalogo_id');
        });
    }
};
