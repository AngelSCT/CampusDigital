<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega columna carrito_uuid a la tabla pedido.
 *
 * Propósito: garantizar idempotencia (un carrito = máximo un pedido).
 * Cuando el M4.4 (Carrito) llama a CrearPedidoDesdeCheckout,
 * pasa el UUID de su carrito. Si el pedido ya existe para ese UUID,
 * el Service retorna el existente en lugar de crear duplicado.
 *
 * Decisión arquitectónica: columna unique SIN FK a cart_carritos
 * para no acoplar el esquema del M4.5 al del M4.4.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->string('carrito_uuid', 100)
                ->nullable()
                ->unique()
                ->after('meta_json');
        });

        // Hacer usuario_id nullable para pedidos-regalo/escrow (acuerdo con M4.4)
        Schema::table('pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('carrito_uuid');
        });
    }
};