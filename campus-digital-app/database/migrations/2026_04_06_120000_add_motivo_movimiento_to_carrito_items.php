<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrito_items', function (Blueprint $table) {
            // 'sistema' = movido automáticamente por inactividad (> 4 días)
            // 'manual'  = el usuario lo guardó a propósito
            // null      = ítem activo, nunca movido
            $table->string('motivo_movimiento', 50)->nullable()->after('en_wishlist');
        });
    }

    public function down(): void
    {
        Schema::table('carrito_items', function (Blueprint $table) {
            $table->dropColumn('motivo_movimiento');
        });
    }
};
