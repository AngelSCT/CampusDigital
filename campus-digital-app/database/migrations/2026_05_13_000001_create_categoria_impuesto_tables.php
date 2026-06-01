<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tablas de soporte para el Módulo 4.3 (Catálogo)
 *
 * Crea las tablas `categorias` e `impuestos` si aún no existen.
 * Usa guardas `if (!Schema::hasTable(...))` para ser idempotente
 * en entornos que ya corrieron create_vendedores_tables.php.
 *
 * Orden de ejecución: debe correr ANTES de create_catalogo_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── categorias ──────────────────────────────────────────────────────
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->increments('id_categoria');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // ── impuestos ────────────────────────────────────────────────────────
        if (!Schema::hasTable('impuestos')) {
            Schema::create('impuestos', function (Blueprint $table) {
                $table->increments('id_impuesto');
                $table->string('nombre', 50);           // p. ej. "IVA 16%"
                $table->decimal('porcentaje', 5, 2);    // p. ej. 16.00
                $table->boolean('activo')->default(true);
            });
        }
    }

    public function down(): void
    {
        // Solo se eliminan si no hay FK apuntando a ellas (catalogo debe caer primero)
        Schema::dropIfExists('impuestos');
        Schema::dropIfExists('categorias');
    }
};
