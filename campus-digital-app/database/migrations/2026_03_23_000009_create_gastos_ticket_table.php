<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_ticket', function (Blueprint $table) {
            $table->increments('id_gasto');

            $table->unsignedInteger('id_ticket');
            $table->foreign('id_ticket')
                  ->references('id_ticket')
                  ->on('tickets')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->unsignedInteger('id_insumo');
            $table->foreign('id_insumo')
                  ->references('id_insumo')
                  ->on('insumos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->integer('cantidad');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('gastos_ticket', function (Blueprint $table) {
            $table->index('id_ticket',  'idx_gastos_ticket__id_ticket');
            $table->index('id_insumo',  'idx_gastos_ticket__id_insumo');
            $table->index('deleted_at', 'idx_gastos_ticket__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_gastos_ticket__set_updated_at
            BEFORE UPDATE ON gastos_ticket
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_gastos_ticket__set_updated_at ON gastos_ticket');
        Schema::dropIfExists('gastos_ticket');
    }
};
