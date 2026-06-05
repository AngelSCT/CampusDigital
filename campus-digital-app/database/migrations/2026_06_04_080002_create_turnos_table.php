<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Módulo 4.7 — Turnos
 *
 * Tabla: turnos
 * Sistema de turnos / cola virtual para atención al usuario o
 * recolección de pedidos.
 *
 * Estados:
 *   - esperando:  en cola
 *   - llamado:    ya fue llamado, esperando que el usuario se presente
 *   - atendido:   fue atendido
 *   - no_show:    no se presentó
 *   - cancelado:  cancelado por el usuario
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->increments('id_turno');

            $table->foreignId('id_usuario')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // atencion | recoleccion | cafeteria | biblioteca | general
            $table->string('tipo_turno', 30)->default('general');

            // Código visible: ej. "A001", "B012", "R034"
            $table->string('numero_turno', 20)->unique();

            // esperando | llamado | atendido | no_show | cancelado
            $table->string('estado', 20)->default('esperando');

            // Posición actual en cola (se actualiza al asignar)
            $table->unsignedInteger('posicion')->default(0);

            $table->unsignedInteger('id_recurso')->nullable();
            $table->foreign('id_recurso')
                  ->references('id_recurso')
                  ->on('recursos')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // Opcional: vincular con un pedido para recolección
            $table->string('pedido_referencia', 36)->nullable();

            $table->text('notas')->nullable();

            $table->timestampTz('llamado_at')->nullable();
            $table->timestampTz('atendido_at')->nullable();
            $table->timestampTz('cancelado_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('turnos', function (Blueprint $table) {
            $table->index('id_usuario',  'idx_turnos__id_usuario');
            $table->index('tipo_turno',  'idx_turnos__tipo_turno');
            $table->index('estado',      'idx_turnos__estado');
            $table->index('posicion',    'idx_turnos__posicion');
            $table->index('id_recurso',  'idx_turnos__id_recurso');
            $table->index('created_at',  'idx_turnos__created_at');
            $table->index('deleted_at',  'idx_turnos__deleted_at');
        });

        DB::statement("
            ALTER TABLE turnos
            ADD CONSTRAINT ck_turnos__tipo
            CHECK (tipo_turno IN ('atencion','recoleccion','cafeteria','biblioteca','general'))
        ");

        DB::statement("
            ALTER TABLE turnos
            ADD CONSTRAINT ck_turnos__estado
            CHECK (estado IN ('esperando','llamado','atendido','no_show','cancelado'))
        ");

        DB::unprepared('
            CREATE TRIGGER trg_turnos__set_updated_at
            BEFORE UPDATE ON turnos
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_turnos__set_updated_at ON turnos');
        Schema::dropIfExists('turnos');
    }
};
