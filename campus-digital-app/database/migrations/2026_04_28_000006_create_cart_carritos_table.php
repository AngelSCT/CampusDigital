<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_carritos', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('modulo_id')->constrained('cart_modulos_clientes');
            $table->string('usuario_ref', 120);
            // [v1.1 C5] Estados ampliados para flujo de Saldo con pago diferido.
            // String en lugar de enum para compatibilidad SQLite; validado en Carrito::ESTADO_*
            $table->string('estado', 50)->default('abierto');
            $table->boolean('requiere_saldo')->default(false);
            $table->decimal('total', 10, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('expira_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['modulo_id', 'estado']);
            $table->index('usuario_ref');
            $table->index('expira_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_carritos');
    }
};
