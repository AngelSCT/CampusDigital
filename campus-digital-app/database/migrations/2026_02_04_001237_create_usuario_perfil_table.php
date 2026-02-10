<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_perfil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique('uq_usuario_perfil__usuario_id')->constrained('usuario')->onUpdate('cascade')->onDelete('restrict');
            
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 30)->default('');
            $table->text('direccion')->default('');
            $table->jsonb('preferencias_json')->default('{}');
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->index('usuario_id', 'idx_usuario_perfil__usuario_id');
        });

        DB::unprepared('
            CREATE TRIGGER trg_usuario_perfil__set_updated_at
            BEFORE UPDATE ON usuario_perfil
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuario_perfil__set_updated_at ON usuario_perfil');
        Schema::dropIfExists('usuario_perfil');
    }
};