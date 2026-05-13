<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->increments('id_ubicacion');
            $table->string('edificio', 120)->default('');
            $table->string('aula_departamento', 120)->default('');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->index('edificio',          'idx_ubicaciones__edificio');
            $table->index('deleted_at',        'idx_ubicaciones__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_ubicaciones__set_updated_at
            BEFORE UPDATE ON ubicaciones
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_ubicaciones__set_updated_at ON ubicaciones');
        Schema::dropIfExists('ubicaciones');
    }
};
