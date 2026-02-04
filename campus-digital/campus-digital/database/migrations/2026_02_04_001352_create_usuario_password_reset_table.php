<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_password_reset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->onUpdate('cascade')->onDelete('restrict');
            
            $table->text('token_hash')->unique('uq_usuario_password_reset__token_hash');
            $table->timestampTz('solicitado_at')->useCurrent();
            $table->timestampTz('expira_at');
            $table->timestampTz('usado_at')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->default('');
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->index('usuario_id', 'idx_usuario_password_reset__usuario_id');
            $table->index('expira_at', 'idx_usuario_password_reset__expira_at');
        });

        DB::statement('ALTER TABLE usuario_password_reset ADD CONSTRAINT ck_usuario_password_reset__expira_future CHECK (expira_at > solicitado_at)');

        DB::unprepared('
            CREATE TRIGGER trg_usuario_password_reset__set_updated_at
            BEFORE UPDATE ON usuario_password_reset
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuario_password_reset__set_updated_at ON usuario_password_reset');
        Schema::dropIfExists('usuario_password_reset');
    }
};