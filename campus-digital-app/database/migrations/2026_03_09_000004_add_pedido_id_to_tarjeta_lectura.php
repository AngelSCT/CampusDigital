<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarjeta_lectura', function (Blueprint $table) {
            // Liga una lectura a un pedido cuando tipo_lectura = 'confirmacion_entrega'
            $table->foreignId('pedido_id')
                  ->nullable()
                  ->after('operador_usuario_id')
                  ->constrained('pedido')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tarjeta_lectura', function (Blueprint $table) {
            $table->dropForeign(['pedido_id']);
            $table->dropColumn('pedido_id');
        });
    }
};