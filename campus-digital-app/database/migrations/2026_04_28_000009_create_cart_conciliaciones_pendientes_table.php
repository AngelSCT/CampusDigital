<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_conciliaciones_pendientes', function (Blueprint $table) {
            $table->id();
            // UUID del carrito (referencia desnormalizada para lookups rápidos).
            $table->char('carrito_uuid', 36);
            $table->foreignId('modulo_id')->constrained('cart_modulos_clientes');
            $table->string('usuario_ref', 120);
            $table->decimal('monto', 10, 2);
            $table->unsignedInteger('intentos')->default(0);
            $table->timestamp('ultimo_intento_at')->nullable();
            $table->timestamp('proximo_intento_at')->nullable();
            // String en lugar de enum para compatibilidad SQLite; validado en ConciliacionPendiente::ESTADO_*
            $table->string('estado_conciliacion', 30)->default('pendiente');
            $table->json('respuesta_saldo')->nullable();
            $table->timestamps();

            // FK sobre UUID (cart_carritos.uuid es UNIQUE CHAR(36)).
            $table->foreign('carrito_uuid')->references('uuid')->on('cart_carritos')->cascadeOnDelete();

            $table->index(['estado_conciliacion', 'proximo_intento_at']);
            $table->index(['usuario_ref', 'estado_conciliacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_conciliaciones_pendientes');
    }
};
