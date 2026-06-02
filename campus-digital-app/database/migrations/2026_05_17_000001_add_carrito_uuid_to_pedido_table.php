<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Integración Checkout → Pedidos.
 *
 * Cambios:
 *  1. Agrega `carrito_uuid` (unique, nullable) a la tabla `pedido`.
 *     Garantiza idempotencia: un Carrito = a lo sumo un Pedido.
 *
 *  2. Hace nullable `usuario_id`.
 *     El módulo Carrito identifica usuarios por `usuario_ref` (string/matrícula),
 *     no por FK integer. El equipo de Pedidos puede resolver la FK en un
 *     proceso asíncrono usando meta_json['usuario_ref'].
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->uuid('carrito_uuid')->nullable()->unique()->after('id');
        });

        // Hacer usuario_id nullable: los Pedidos originados desde Checkout
        // no tienen FK directa al usuario (usan usuario_ref string).
        DB::statement('ALTER TABLE pedido ALTER COLUMN usuario_id DROP NOT NULL');

        DB::statement('CREATE INDEX idx_pedido__carrito_uuid ON pedido(carrito_uuid)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_pedido__carrito_uuid');

        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('carrito_uuid');
        });

        // Restaurar NOT NULL (asumiendo no hay filas con usuario_id null)
        DB::statement('ALTER TABLE pedido ALTER COLUMN usuario_id SET NOT NULL');
    }
};
