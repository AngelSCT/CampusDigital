<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_tecnicas', function (Blueprint $table) {
            $table->increments('id_asignacion');

            $table->unsignedInteger('id_ticket');
            $table->foreign('id_ticket')
                  ->references('id_ticket')
                  ->on('tickets')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('id_usuario_tecnico')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('asignaciones_tecnicas', function (Blueprint $table) {
            $table->index('id_ticket',          'idx_asignaciones_tecnicas__id_ticket');
            $table->index('id_usuario_tecnico', 'idx_asignaciones_tecnicas__id_usuario_tecnico');
            $table->index('deleted_at',         'idx_asignaciones_tecnicas__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_asignaciones_tecnicas__set_updated_at
            BEFORE UPDATE ON asignaciones_tecnicas
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_asignaciones_tecnicas__set_updated_at ON asignaciones_tecnicas');
        Schema::dropIfExists('asignaciones_tecnicas');
    }
};
