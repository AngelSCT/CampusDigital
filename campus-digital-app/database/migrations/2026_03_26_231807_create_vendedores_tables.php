<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->increments('id_categoria');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        if (!Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->increments('id_area');
                $table->string('nombre', 100);
            });
        }

        if (!Schema::hasTable('impuestos')) {
            Schema::create('impuestos', function (Blueprint $table) {
                $table->increments('id_impuesto');
                $table->string('nombre', 50)->nullable();
                $table->decimal('porcentaje', 5, 2)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        if (!Schema::hasTable('promociones')) {
            Schema::create('promociones', function (Blueprint $table) {
                $table->increments('id_promocion');
                $table->string('nombre', 150)->nullable();
                $table->text('descripcion')->nullable();
                $table->string('tipo', 50)->nullable();
                $table->decimal('valor', 10, 2)->nullable();
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->boolean('activa')->default(true);
            });
        }

        if (!Schema::hasTable('vendedores')) {
            Schema::create('vendedores', function (Blueprint $table) {
                $table->increments('id_vendedor');
                $table->string('nombre', 150);
                $table->string('email', 255)->unique();
                $table->string('telefono', 20)->nullable();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_registro')->useCurrent();
            });
        }

        if (!Schema::hasTable('catalogo')) {
            Schema::create('catalogo', function (Blueprint $table) {
                $table->increments('id_catalogo');
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->string('tipo', 20);
                $table->unsignedInteger('id_categoria')->nullable();
                $table->boolean('aplica_iva')->default(true);
                $table->unsignedInteger('id_impuesto')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();

                $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
                $table->foreign('id_impuesto')->references('id_impuesto')->on('impuestos');
            });
        }

        if (!Schema::hasTable('catalogo_area')) {
            Schema::create('catalogo_area', function (Blueprint $table) {
                $table->unsignedInteger('id_catalogo');
                $table->unsignedInteger('id_area');

                $table->primary(['id_catalogo', 'id_area']);
                $table->foreign('id_catalogo')->references('id_catalogo')->on('catalogo')->cascadeOnDelete();
                $table->foreign('id_area')->references('id_area')->on('areas')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('precios')) {
            Schema::create('precios', function (Blueprint $table) {
                $table->increments('id_precio');
                $table->unsignedInteger('id_catalogo');
                $table->decimal('precio', 10, 2);
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();

                $table->foreign('id_catalogo')->references('id_catalogo')->on('catalogo')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('disponibilidad')) {
            Schema::create('disponibilidad', function (Blueprint $table) {
                $table->increments('id_disponibilidad');
                $table->unsignedInteger('id_catalogo');
                $table->string('dia_semana', 15);
                $table->time('hora_inicio');
                $table->time('hora_fin');
                $table->boolean('disponible')->default(true);

                $table->foreign('id_catalogo')->references('id_catalogo')->on('catalogo')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('reglas')) {
            Schema::create('reglas', function (Blueprint $table) {
                $table->increments('id_regla');
                $table->unsignedInteger('id_catalogo');
                $table->text('descripcion');
                $table->string('tipo_regla', 50)->nullable();

                $table->foreign('id_catalogo')->references('id_catalogo')->on('catalogo')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('movimientos')) {
            Schema::create('movimientos', function (Blueprint $table) {
                $table->increments('id_movimiento');
                $table->unsignedInteger('id_catalogo');
                $table->integer('cantidad');
                $table->timestamp('fecha')->useCurrent();

                $table->foreign('id_catalogo')->references('id_catalogo')->on('catalogo')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('catalogo_vendedor')) {
            Schema::create('catalogo_vendedor', function (Blueprint $table) {
                $table->increments('id_cv');
                $table->unsignedInteger('id_vendedor');
                $table->unsignedInteger('id_catalogo_base')->nullable();
                $table->string('nombre_personalizado', 150);
                $table->text('descripcion_personalizada')->nullable();
                $table->string('tipo', 20);
                $table->unsignedInteger('id_categoria')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_creacion')->useCurrent();

                $table->foreign('id_vendedor')->references('id_vendedor')->on('vendedores')->cascadeOnDelete();
                $table->foreign('id_catalogo_base')->references('id_catalogo')->on('catalogo')->nullOnDelete();
                $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
            });
        }

        if (!Schema::hasTable('precios_vendedor')) {
            Schema::create('precios_vendedor', function (Blueprint $table) {
                $table->increments('id_precio_v');
                $table->unsignedInteger('id_cv');
                $table->decimal('precio', 10, 2);
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();

                $table->foreign('id_cv')->references('id_cv')->on('catalogo_vendedor')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('disponibilidad_vendedor')) {
            Schema::create('disponibilidad_vendedor', function (Blueprint $table) {
                $table->increments('id_disp_v');
                $table->unsignedInteger('id_cv');
                $table->string('dia_semana', 15);
                $table->time('hora_inicio');
                $table->time('hora_fin');
                $table->boolean('disponible')->default(true);

                $table->foreign('id_cv')->references('id_cv')->on('catalogo_vendedor')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('reglas_vendedor')) {
            Schema::create('reglas_vendedor', function (Blueprint $table) {
                $table->increments('id_regla_v');
                $table->unsignedInteger('id_cv');
                $table->text('descripcion');
                $table->string('tipo_regla', 50)->nullable();

                $table->foreign('id_cv')->references('id_cv')->on('catalogo_vendedor')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('promociones_catalogo')) {
            Schema::create('promociones_catalogo', function (Blueprint $table) {
                $table->unsignedInteger('id_promocion');
                $table->unsignedInteger('id_catalogo');

                $table->primary(['id_promocion', 'id_catalogo']);
                $table->foreign('id_promocion')->references('id_promocion')->on('promociones')->cascadeOnDelete();
                $table->foreign('id_catalogo')->references('id_catalogo')->on('catalogo')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('promociones_vendedor')) {
            Schema::create('promociones_vendedor', function (Blueprint $table) {
                $table->unsignedInteger('id_promocion');
                $table->unsignedInteger('id_cv');

                $table->primary(['id_promocion', 'id_cv']);
                $table->foreign('id_promocion')->references('id_promocion')->on('promociones')->cascadeOnDelete();
                $table->foreign('id_cv')->references('id_cv')->on('catalogo_vendedor')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones_vendedor');
        Schema::dropIfExists('promociones_catalogo');
        Schema::dropIfExists('reglas_vendedor');
        Schema::dropIfExists('disponibilidad_vendedor');
        Schema::dropIfExists('precios_vendedor');
        Schema::dropIfExists('catalogo_vendedor');
        Schema::dropIfExists('movimientos');
        Schema::dropIfExists('reglas');
        Schema::dropIfExists('disponibilidad');
        Schema::dropIfExists('precios');
        Schema::dropIfExists('catalogo_area');
        Schema::dropIfExists('catalogo');
        Schema::dropIfExists('vendedores');
        Schema::dropIfExists('promociones');
        Schema::dropIfExists('impuestos');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('categorias');
    }
};
