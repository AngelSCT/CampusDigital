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
            
            $table->text('token_hash')->unique('uq_usuario_sesion__token_hash');
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->default('');
            $table->string('dispositivo', 120)->default('');
            $table->string('creado_por', 30)->default('login');
            $table->timestampTz('expira_at');
            $table->timestampTz('revocado_at')->nullable();
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->index('usuario_id', 'idx_usuario_sesion__usuario_id');
            $table->index('expira_at', 'idx_usuario_sesion__expira_at');
            $table->index('revocado_at', 'idx_usuario_sesion__revocado_at');
        });

        DB::statement('ALTER TABLE usuario_sesion ADD CONSTRAINT ck_usuario_sesion__expira_future CHECK (expira_at > created_at)');

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