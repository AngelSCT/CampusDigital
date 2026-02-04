<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero crear la tabla sin la columna email
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            
            // Información personal
            $table->string('nombre', 120)->default('');
            $table->string('apellido', 120)->default('');
            $table->string('telefono', 30)->default('');
            $table->text('foto_url')->default('');
            $table->text('password_hash')->nullable(false);
            
            // Estado y seguridad
            $table->boolean('email_verificado')->default(false);
            $table->timestampTz('ultimo_login_at')->nullable();
            $table->boolean('bloqueado')->default(false);
            $table->timestampTz('bloqueado_hasta')->nullable();
            $table->jsonb('seguridad_json')->default('{}');
            
            // Timestamps
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        // Ahora agregar la columna email con tipo CITEXT
        DB::statement('ALTER TABLE usuario ADD COLUMN email CITEXT NOT NULL');

        // Agregar constraints
        DB::statement('ALTER TABLE usuario ADD CONSTRAINT uq_usuario__email UNIQUE (email)');
        DB::statement('ALTER TABLE usuario ADD CONSTRAINT ck_usuario__email_no_vacio CHECK (length(trim(email::text)) > 3)');
        DB::statement('ALTER TABLE usuario ADD CONSTRAINT ck_usuario__nombre_len CHECK (length(nombre) <= 120)');
        DB::statement('ALTER TABLE usuario ADD CONSTRAINT ck_usuario__apellido_len CHECK (length(apellido) <= 120)');

        // Índices
        Schema::table('usuario', function (Blueprint $table) {
            $table->index('email', 'idx_usuario__email');
            $table->index('bloqueado', 'idx_usuario__bloqueado');
            $table->index('email_verificado', 'idx_usuario__email_verificado');
            $table->index('deleted_at', 'idx_usuario__deleted_at');
        });

        // Trigger
        DB::unprepared('
            CREATE TRIGGER trg_usuario__set_updated_at
            BEFORE UPDATE ON usuario
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_usuario__set_updated_at ON usuario');
        Schema::dropIfExists('usuario');
    }
};