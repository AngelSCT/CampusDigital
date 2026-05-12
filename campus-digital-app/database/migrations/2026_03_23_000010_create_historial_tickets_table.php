<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_tickets', function (Blueprint $table) {
            $table->increments('id_historial');

            $table->unsignedInteger('id_ticket');
            $table->foreign('id_ticket')
                  ->references('id_ticket')
                  ->on('tickets')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('id_usuario')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->string('estado_nuevo');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('historial_tickets', function (Blueprint $table) {
            $table->index('id_ticket',  'idx_historial_tickets__id_ticket');
            $table->index('id_usuario', 'idx_historial_tickets__id_usuario');
            $table->index('deleted_at', 'idx_historial_tickets__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_historial_tickets__set_updated_at
            BEFORE UPDATE ON historial_tickets
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_historial_tickets__set_updated_at ON historial_tickets');
        Schema::dropIfExists('historial_tickets');
    }
};
