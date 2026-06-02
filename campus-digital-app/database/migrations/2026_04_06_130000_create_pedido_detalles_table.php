<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            // Snapshot del precio al momento de la compra (el precio puede cambiar luego)
            $table->string('nombre_producto', 200);
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedInteger('cantidad');
            $table->decimal('subtotal', 10, 2);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
