<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items_carrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrito_id')->constrained('cart_carritos')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('cart_categorias');
            $table->string('referencia_externa', 180);
            $table->string('nombre', 255);
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedInteger('cantidad');
            $table->unsignedInteger('duracion_horas')->nullable();
            $table->string('estado_item', 20)->default('activo'); // valores: activo|removido|devuelto
            $table->json('metadata')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            $table->index(['carrito_id', 'estado_item']);
            $table->index(['carrito_id', 'referencia_externa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items_carrito');
    }
};
