<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            // Eliminar la FK existente antes de cambiar la columna
            $table->dropForeign(['producto_id']);
            // Hacer nullable + recrear FK con nullOnDelete
            $table->unsignedBigInteger('producto_id')->nullable()->change();
            $table->foreign('producto_id')
                  ->references('id')
                  ->on('productos')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_detalles', function (Blueprint $table) {
            //
        });
    }
};
