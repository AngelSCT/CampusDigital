<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_bitacora', function (Blueprint $table) {
            $table->id();
            $table->string('accion', 80);
            $table->foreignId('modulo_id')->nullable()->constrained('cart_modulos_clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->char('jti', 36)->nullable();
            $table->char('carrito_uuid', 36)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('payload')->nullable();
            // Tabla de solo inserción: no tiene updated_at.
            $table->timestamp('created_at')->useCurrent();

            $table->index('accion');
            $table->index(['modulo_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_bitacora');
    }
};
