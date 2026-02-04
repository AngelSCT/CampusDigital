<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('rol')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('permiso_id')->constrained('permiso')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->unique(['rol_id', 'permiso_id'], 'uq_rol_permiso__rol_permiso');
            $table->index('rol_id', 'idx_rol_permiso__rol_id');
            $table->index('permiso_id', 'idx_rol_permiso__permiso_id');
        });

        DB::unprepared('
            CREATE TRIGGER trg_rol_permiso__set_updated_at
            BEFORE UPDATE ON rol_permiso
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_rol_permiso__set_updated_at ON rol_permiso');
        Schema::dropIfExists('rol_permiso');
    }
};