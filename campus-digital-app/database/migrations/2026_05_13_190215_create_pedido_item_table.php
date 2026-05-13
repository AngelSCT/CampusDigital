<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pedido_item — detalle de productos por pedido.
     *
     * Cada fila representa un producto comprado dentro de un pedido,
     * con snapshot histórico del nombre y precio al momento de la venta.
     * Esto evita que cambios futuros en el catálogo afecten pedidos viejos.
     */
    public function up(): void
    {
        Schema::create('pedido_item', function (Blueprint $table) {
            $table->id();

            // Relación con el pedido
            $table->foreignId('pedido_id')
                  ->constrained('pedido')
                  ->cascadeOnDelete();

            // Referencia al producto del M4.3 (sin FK real porque es otro módulo)
            $table->unsignedBigInteger('producto_id')->index();

            // Snapshot histórico al momento de la venta
            $table->string('nombre_producto', 200);
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->boolean('aplica_iva')->default(false);
            $table->decimal('subtotal', 10, 2); // cantidad * precio_unitario (sin IVA)
            $table->decimal('iva_monto', 10, 2)->default(0);
            $table->decimal('total_linea', 10, 2); // subtotal + iva_monto

            // Metadata opcional (categoría, notas, etc.)
            $table->json('meta_json')->nullable();

            $table->timestamps();

            // Índices útiles para reportes
            $table->index(['pedido_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_item');
    }
};