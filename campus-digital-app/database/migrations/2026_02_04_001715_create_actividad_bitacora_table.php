<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('sesion_id')->nullable()->constrained('usuario_sesion')->onUpdate('cascade')->onDelete('set null');
            
            $table->string('accion', 80);
            $table->string('modulo', 80)->default('seguridad');
            $table->string('target_tabla', 63)->default('');
            $table->bigInteger('target_id')->nullable();
            
            $table->boolean('exito')->default(true);
            $table->text('detalle')->default('');
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->default('');
            $table->jsonb('meta_json')->default('{}');
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        DB::statement('ALTER TABLE actividad_bitacora ADD CONSTRAINT ck_actividad_bitacora__accion_no_vacia CHECK (length(trim(accion)) > 0)');

        Schema::table('actividad_bitacora', function (Blueprint $table) {
            $table->index('usuario_id', 'idx_actividad_bitacora__usuario_id');
            $table->index('sesion_id', 'idx_actividad_bitacora__sesion_id');
            $table->index('accion', 'idx_actividad_bitacora__accion');
            $table->index('modulo', 'idx_actividad_bitacora__modulo');
            $table->index('target_tabla', 'idx_actividad_bitacora__target_tabla');
            $table->index('created_at', 'idx_actividad_bitacora__created_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_actividad_bitacora__set_updated_at
            BEFORE UPDATE ON actividad_bitacora
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actividad_bitacora__set_updated_at ON actividad_bitacora');
        Schema::dropIfExists('actividad_bitacora');
    }
};