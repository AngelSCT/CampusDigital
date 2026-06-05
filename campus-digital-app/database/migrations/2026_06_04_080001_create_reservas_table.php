<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Módulo 4.7 — Reservas
 *
 * Tabla: reservas
 * Una reserva vincula un usuario con un recurso para un rango horario.
 *
 * Estados:
 *   - pendiente:  creada pero aún no validada/aprobada
 *   - confirmada: activa, ocupa el recurso en su horario
 *   - cancelada:  cancelada por el usuario o el admin
 *   - no_show:    el usuario no se presentó
 *   - completada: el tiempo de reserva pasó y se marcó como usada
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->increments('id_reserva');

            $table->unsignedInteger('id_recurso');
            $table->foreign('id_recurso')
                  ->references('id_recurso')
                  ->on('recursos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('id_usuario')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->timestampTz('fecha_inicio');
            $table->timestampTz('fecha_fin');

            // pendiente | confirmada | cancelada | no_show | completada
            $table->string('estado', 20)->default('pendiente');

            $table->text('proposito')->nullable();

            // Si el recurso tiene costo, se cobra al confirmar
            $table->boolean('cobro_saldo')->default(false);
            $table->decimal('monto_cobrado', 10, 2)->nullable();

            $table->foreignId('id_usuario_cancelacion')
                  ->nullable()
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->timestampTz('cancelada_at')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->timestampTz('check_in_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->index('id_recurso',  'idx_reservas__id_recurso');
            $table->index('id_usuario',  'idx_reservas__id_usuario');
            $table->index('estado',      'idx_reservas__estado');
            $table->index('fecha_inicio','idx_reservas__fecha_inicio');
            $table->index('fecha_fin',   'idx_reservas__fecha_fin');
            $table->index('deleted_at',  'idx_reservas__deleted_at');
        });

        DB::statement("
            ALTER TABLE reservas
            ADD CONSTRAINT ck_reservas__estado
            CHECK (estado IN ('pendiente','confirmada','cancelada','no_show','completada'))
        ");

        DB::statement("
            ALTER TABLE reservas
            ADD CONSTRAINT ck_reservas__fechas_validas
            CHECK (fecha_fin > fecha_inicio)
        ");

        DB::statement("
            ALTER TABLE reservas
            ADD CONSTRAINT ck_reservas__monto_no_negativo
            CHECK (monto_cobrado IS NULL OR monto_cobrado >= 0)
        ");

        // Índice GIST no soportado en esta versión, usamos índice compuesto
        DB::statement('CREATE INDEX idx_reservas__rango ON reservas (id_recurso, fecha_inicio, fecha_fin)');

        DB::unprepared('
            CREATE TRIGGER trg_reservas__set_updated_at
            BEFORE UPDATE ON reservas
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_reservas__set_updated_at ON reservas');
        Schema::dropIfExists('reservas');
    }
};
