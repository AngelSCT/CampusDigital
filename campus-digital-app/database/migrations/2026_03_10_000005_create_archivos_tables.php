<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivo_carpeta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->string('nombre', 200);
            $table->unsignedBigInteger('padre_id')->nullable();
            $table->string('ruta', 500)->default('');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('padre_id')
                  ->references('id')
                  ->on('archivo_carpeta')
                  ->nullOnDelete();

            $table->index('usuario_id',  'idx_archivo_carpeta__usuario_id');
            $table->index('padre_id',    'idx_archivo_carpeta__padre_id');
        });

        DB::unprepared('
            CREATE TRIGGER trg_archivo_carpeta__set_updated_at
            BEFORE UPDATE ON archivo_carpeta
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');

        Schema::create('archivo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            $table->unsignedBigInteger('carpeta_id')->nullable();
            $table->string('nombre_original',   300);
            $table->string('nombre_almacenado', 300);
            $table->string('ruta',      500);
            $table->string('mime_type', 100)->default('');
            $table->string('extension',  20)->default('');
            $table->unsignedBigInteger('tamanio')->default(0);
            $table->boolean('visto_admin')->default(false);
            $table->timestampTz('visto_admin_at')->nullable();
            $table->unsignedBigInteger('visto_por')->nullable();
            $table->text('notas_admin')->default('');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('carpeta_id')
                  ->references('id')
                  ->on('archivo_carpeta')
                  ->nullOnDelete();

            $table->foreign('visto_por')
                  ->references('id')
                  ->on('usuario')
                  ->nullOnDelete();

            $table->index('usuario_id',   'idx_archivo__usuario_id');
            $table->index('carpeta_id',   'idx_archivo__carpeta_id');
            $table->index('visto_admin',  'idx_archivo__visto_admin');
            $table->index('extension',    'idx_archivo__extension');
            $table->index('created_at',   'idx_archivo__created_at');
        });

        DB::unprepared('
            CREATE TRIGGER trg_archivo__set_updated_at
            BEFORE UPDATE ON archivo
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_archivo__set_updated_at ON archivo');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_archivo_carpeta__set_updated_at ON archivo_carpeta');
        Schema::dropIfExists('archivo');
        Schema::dropIfExists('archivo_carpeta');
    }
};