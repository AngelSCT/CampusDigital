<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_sesion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->onUpdate('cascade')->onDelete('restrict');
            
            $table->string('session_id', 255)->unique();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->default('');
            
            $table->timestampTz('inicia_at')->useCurrent();
            $table->timestampTz('expira_at')->nullable();
            $table->timestampTz('termina_at')->nullable();
            
            $table->boolean('activa')->default(true);
            $table->jsonb('meta_json')->default('{}');
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::table('usuario_sesion', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_usuario_sesion__usuario_id');
            $table->index('session_id', 'idx_usuario_sesion__session_id');
            $table->index('activa', 'idx_usuario_sesion__activa');
        });

        DB::unprepared('
            CREATE TRIGGER trg_usuario_sesion__set_updated_at
            BEFORE UPDATE ON usuario_sesion
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuario_sesion__set_updated_at ON usuario_sesion');
        Schema::dropIfExists('usuario_sesion');
    }
};