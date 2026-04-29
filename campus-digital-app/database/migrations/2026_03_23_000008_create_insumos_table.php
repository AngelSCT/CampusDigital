<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->increments('id_insumo');
            $table->string('nombre_insumo');
            $table->integer('stock_actual')->default(0);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->index('deleted_at', 'idx_insumos__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_insumos__set_updated_at
            BEFORE UPDATE ON insumos
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_insumos__set_updated_at ON insumos');
        Schema::dropIfExists('insumos');
    }
};
