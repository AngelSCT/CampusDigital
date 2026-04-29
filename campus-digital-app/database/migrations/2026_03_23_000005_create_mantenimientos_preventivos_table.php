<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos_preventivos', function (Blueprint $table) {
            $table->increments('id_preventivo');

            $table->unsignedInteger('id_equipo');
            $table->foreign('id_equipo')
                  ->references('id_equipo')
                  ->on('equipos_activos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->date('proxima_fecha_programada');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('mantenimientos_preventivos', function (Blueprint $table) {
            $table->index('id_equipo',                'idx_mantenimientos_preventivos__id_equipo');
            $table->index('proxima_fecha_programada', 'idx_mantenimientos_preventivos__proxima_fecha');
            $table->index('deleted_at',               'idx_mantenimientos_preventivos__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_mantenimientos_preventivos__set_updated_at
            BEFORE UPDATE ON mantenimientos_preventivos
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_mantenimientos_preventivos__set_updated_at ON mantenimientos_preventivos');
        Schema::dropIfExists('mantenimientos_preventivos');
    }
};
