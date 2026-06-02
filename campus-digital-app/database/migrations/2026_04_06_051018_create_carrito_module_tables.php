<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('categoria', 100)->nullable();
            $table->text('imagen_url')->nullable();
            $table->boolean('es_regalo')->default(false);
            $table->timestampsTz();
        });

        Schema::create('carrito_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(1);
            $table->boolean('guardado_para_despues')->default(false);
            $table->boolean('en_wishlist')->default(false);
            $table->timestampTz('ultima_actividad_at')->useCurrent();
            $table->timestampsTz();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->cascadeOnDelete();
            $table->decimal('total', 10, 2);
            $table->string('estado', 50)->default('pendiente');
            $table->string('metodo_pago', 50)->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('carrito_items');
        Schema::dropIfExists('productos');
    }
};
