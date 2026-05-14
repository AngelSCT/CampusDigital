<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla `catalogo` — Módulo 4.3 (Catálogo de Servicios y Productos)
 *
 * Campos requeridos por el Módulo 4.5 (Pedidos y Seguimiento):
 *   id_catalogo, nombre, descripcion, tipo,
 *   id_categoria, aplica_iva, id_impuesto, activo
 *
 * Depende de: 2026_05_13_000001_create_categoria_impuesto_tables.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalogo')) {
            Schema::create('catalogo', function (Blueprint $table) {
                $table->increments('id_catalogo');

                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();

                // 'servicio' | 'producto'
                $table->string('tipo', 20);

                $table->unsignedInteger('id_categoria')->nullable();
                $table->foreign('id_categoria')
                      ->references('id_categoria')
                      ->on('categorias')
                      ->nullOnDelete();

                $table->boolean('aplica_iva')->default(true);

                $table->unsignedInteger('id_impuesto')->nullable();
                $table->foreign('id_impuesto')
                      ->references('id_impuesto')
                      ->on('impuestos')
                      ->nullOnDelete();

                $table->boolean('activo')->default(true);

                $table->timestamp('fecha_creacion')->useCurrent();
            });

            // Índices de búsqueda/filtro frecuente
            \Illuminate\Support\Facades\DB::statement(
                'CREATE INDEX idx_catalogo__tipo     ON catalogo(tipo)'
            );
            \Illuminate\Support\Facades\DB::statement(
                'CREATE INDEX idx_catalogo__activo   ON catalogo(activo)'
            );
            \Illuminate\Support\Facades\DB::statement(
                'CREATE INDEX idx_catalogo__categoria ON catalogo(id_categoria)'
            );

            // Restricción: sólo valores permitidos para tipo
            \Illuminate\Support\Facades\DB::statement("
                ALTER TABLE catalogo
                ADD CONSTRAINT ck_catalogo__tipo
                CHECK (tipo IN ('servicio','producto'))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo');
    }
};
