<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_ticket', function (Blueprint $table) {
            $table->increments('id_categoria');

            $table->unsignedInteger('id_area');
            $table->foreign('id_area')
                  ->references('id_area')
                  ->on('area')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->string('nombre_categoria', 120)->default('');
            $table->integer('tiempo_sla_horas')->default(0);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('categorias_ticket', function (Blueprint $table) {
            $table->index('id_area',           'idx_categorias_ticket__id_area');
            $table->index('nombre_categoria',  'idx_categorias_ticket__nombre_categoria');
            $table->index('deleted_at',        'idx_categorias_ticket__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_categorias_ticket__set_updated_at
            BEFORE UPDATE ON categorias_ticket
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_categorias_ticket__set_updated_at ON categorias_ticket');
        Schema::dropIfExists('categorias_ticket');
    }
};
