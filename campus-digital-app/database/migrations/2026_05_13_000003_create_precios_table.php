<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Tabla `precios` — Módulo 4.3 (Catálogo de Servicios y Productos)
 *
 * Historial de precios por producto/servicio con vigencia por fechas.
 * Un registro con fecha_fin NULL indica que el precio no tiene vencimiento.
 *
 * Campos requeridos por el Módulo 4.5 (Pedidos y Seguimiento):
 *   id_precio, id_catalogo, precio, fecha_inicio, fecha_fin
 *
 * Filtros disponibles en GET /api/precios:
 *   ?id_catalogo=X           → todos los precios de un ítem
 *   ?id_catalogo=X&vigente=true → precio vigente hoy
 *
 * Depende de: 2026_05_13_000002_create_catalogo_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('precios')) {
            Schema::create('precios', function (Blueprint $table) {
                $table->increments('id_precio');

                $table->unsignedInteger('id_catalogo');
                $table->foreign('id_catalogo')
                      ->references('id_catalogo')
                      ->on('catalogo')
                      ->cascadeOnDelete();

                $table->decimal('precio', 10, 2);   // precio sin IVA

                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable(); // NULL = vigente indefinidamente
            });

            // Índice para la consulta de precio vigente (usada por Módulo 4.5)
            DB::statement(
                'CREATE INDEX idx_precios__id_catalogo ON precios(id_catalogo)'
            );
            DB::statement(
                'CREATE INDEX idx_precios__fechas ON precios(id_catalogo, fecha_inicio, fecha_fin)'
            );

            // Garantiza precio positivo
            DB::statement("
                ALTER TABLE precios
                ADD CONSTRAINT ck_precios__precio_positivo
                CHECK (precio >= 0)
            ");

            // fecha_inicio <= fecha_fin cuando fecha_fin no es NULL
            DB::statement("
                ALTER TABLE precios
                ADD CONSTRAINT ck_precios__fechas_coherentes
                CHECK (fecha_fin IS NULL OR fecha_inicio <= fecha_fin)
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('precios');
    }
};
