<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos_activos', function (Blueprint $table) {
            $table->increments('id_equipo');

            $table->unsignedInteger('id_categoria');
            $table->foreign('id_categoria')
                  ->references('id_categoria')
                  ->on('categorias_ticket')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->unsignedInteger('id_ubicacion');
            $table->foreign('id_ubicacion')
                  ->references('id_ubicacion')
                  ->on('ubicaciones')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->string('nombre_equipo', 120)->default('');
            $table->string('estado_actual', 50)->default('activo');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('equipos_activos', function (Blueprint $table) {
            $table->index('id_categoria',  'idx_equipos_activos__id_categoria');
            $table->index('id_ubicacion',  'idx_equipos_activos__id_ubicacion');
            $table->index('estado_actual', 'idx_equipos_activos__estado_actual');
            $table->index('deleted_at',    'idx_equipos_activos__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_equipos_activos__set_updated_at
            BEFORE UPDATE ON equipos_activos
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_equipos_activos__set_updated_at ON equipos_activos');
        Schema::dropIfExists('equipos_activos');
    }
};
