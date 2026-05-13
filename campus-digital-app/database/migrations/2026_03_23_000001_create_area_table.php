<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area', function (Blueprint $table) {
            $table->increments('id_area');
            $table->string('name_area', 120)->default('');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('area', function (Blueprint $table) {
            $table->index('name_area', 'idx_area__name_area');
            $table->index('deleted_at', 'idx_area__deleted_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_area__set_updated_at
            BEFORE UPDATE ON area
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_area__set_updated_at ON area');
        Schema::dropIfExists('area');
    }
};
