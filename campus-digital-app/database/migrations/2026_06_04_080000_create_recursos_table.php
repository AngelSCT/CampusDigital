<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Módulo 4.7 — Recursos Reservables
 *
 * Tabla: recursos
 * Almacena los recursos físicos que pueden ser reservados:
 *   - Salas de estudio / reunión
 *   - Laboratorios
 *   - Equipos
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->increments('id_recurso');

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            // sala | laboratorio | equipo
            $table->string('tipo', 30)->default('sala');

            $table->unsignedInteger('capacidad')->default(1);

            $table->unsignedInteger('id_ubicacion')->nullable();
            $table->foreign('id_ubicacion')
                  ->references('id_ubicacion')
                  ->on('ubicaciones')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // disponible | mantenimiento | inactivo
            $table->string('estado', 30)->default('disponible');

            // Costo por hora de uso (0 = gratuito). Se usa en validación de saldo.
            $table->decimal('costo_por_hora', 10, 2)->default(0);

            // Imagen opcional del recurso
            $table->string('imagen_url', 500)->nullable();

            // Horario de disponibilidad: JSON con días/horarios
            // ej: {"lunes": ["08:00-20:00"], "martes": ["08:00-20:00"]}
            $table->json('horarios')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('recursos', function (Blueprint $table) {
            $table->index('tipo',        'idx_recursos__tipo');
            $table->index('estado',      'idx_recursos__estado');
            $table->index('id_ubicacion','idx_recursos__id_ubicacion');
            $table->index('deleted_at',  'idx_recursos__deleted_at');
        });

        DB::statement("
            ALTER TABLE recursos
            ADD CONSTRAINT ck_recursos__tipo
            CHECK (tipo IN ('sala','laboratorio','equipo'))
        ");

        DB::statement("
            ALTER TABLE recursos
            ADD CONSTRAINT ck_recursos__estado
            CHECK (estado IN ('disponible','mantenimiento','inactivo'))
        ");

        DB::statement("
            ALTER TABLE recursos
            ADD CONSTRAINT ck_recursos__capacidad_positiva
            CHECK (capacidad > 0)
        ");

        DB::statement("
            ALTER TABLE recursos
            ADD CONSTRAINT ck_recursos__costo_no_negativo
            CHECK (costo_por_hora >= 0)
        ");

        DB::unprepared('
            CREATE TRIGGER trg_recursos__set_updated_at
            BEFORE UPDATE ON recursos
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_recursos__set_updated_at ON recursos');
        Schema::dropIfExists('recursos');
    }
};
