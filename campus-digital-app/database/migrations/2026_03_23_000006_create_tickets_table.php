<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id_ticket');

            $table->foreignId('id_usuario_solicitante')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->unsignedInteger('id_categoria');
            $table->foreign('id_categoria')
                  ->references('id_categoria')
                  ->on('categorias_ticket')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->unsignedInteger('id_equipo')->nullable();
            $table->foreign('id_equipo')
                  ->references('id_equipo')
                  ->on('equipos_activos')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->string('estado', 50)->default('abierto');
            $table->string('prioridad', 30)->default('media');
            $table->timestampTz('fecha_creacion')->useCurrent();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('id_usuario_solicitante', 'idx_tickets__id_usuario_solicitante');
            $table->index('id_categoria',           'idx_tickets__id_categoria');
            $table->index('id_equipo',              'idx_tickets__id_equipo');
            $table->index('estado',                 'idx_tickets__estado');
            $table->index('prioridad',              'idx_tickets__prioridad');
            $table->index('fecha_creacion',         'idx_tickets__fecha_creacion');
            $table->index('deleted_at',             'idx_tickets__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_tickets__set_updated_at
            BEFORE UPDATE ON tickets
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_tickets__set_updated_at ON tickets');
        Schema::dropIfExists('tickets');
    }
};
