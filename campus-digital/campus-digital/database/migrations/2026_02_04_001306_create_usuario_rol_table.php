<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('rol_id')->constrained('rol')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('asignado_por_usuario_id')->nullable()->constrained('usuario')->onUpdate('cascade')->onDelete('set null');
            $table->timestampTz('asignado_at')->useCurrent();
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->unique(['usuario_id', 'rol_id'], 'uq_usuario_rol__usuario_rol');
            $table->index('usuario_id', 'idx_usuario_rol__usuario_id');
            $table->index('rol_id', 'idx_usuario_rol__rol_id');
        });

        DB::unprepared('
            CREATE TRIGGER trg_usuario_rol__set_updated_at
            BEFORE UPDATE ON usuario_rol
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuario_rol__set_updated_at ON usuario_rol');
        Schema::dropIfExists('usuario_rol');
    }
};