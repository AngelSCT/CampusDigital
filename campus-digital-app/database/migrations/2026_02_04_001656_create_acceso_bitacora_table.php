<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acceso_bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('sesion_id')->nullable()->constrained('usuario_sesion')->onUpdate('cascade')->onDelete('set null');
            
            $table->string('email_intentado', 255)->default('');
            $table->string('evento', 50);
            
            $table->boolean('exito')->default(false);
            $table->text('detalle')->default('');
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->default('');
            $table->jsonb('meta_json')->default('{}');
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        DB::statement('ALTER TABLE acceso_bitacora ADD CONSTRAINT ck_acceso_bitacora__evento_no_vacio CHECK (length(trim(evento)) > 0)');

        Schema::table('acceso_bitacora', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_acceso_bitacora__usuario_id');
            $table->index('sesion_id', 'idx_acceso_bitacora__sesion_id');
            $table->index('evento', 'idx_acceso_bitacora__evento');
            $table->index('exito', 'idx_acceso_bitacora__exito');
            $table->index('created_at', 'idx_acceso_bitacora__created_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_acceso_bitacora__set_updated_at
            BEFORE UPDATE ON acceso_bitacora
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_acceso_bitacora__set_updated_at ON acceso_bitacora');
        Schema::dropIfExists('acceso_bitacora');
    }
};