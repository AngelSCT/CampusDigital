<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarjeta_universitaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('uid', 64)->unique()->comment('UID único del chip RFID/NFC');
            $table->enum('estado', ['activa', 'bloqueada', 'perdida', 'cancelada'])->default('activa');
            $table->text('motivo_bloqueo')->nullable();
            $table->foreignId('registrado_por_usuario_id')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreignId('bloqueado_por_usuario_id')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->timestamp('bloqueado_at')->nullable();
            $table->jsonb('meta_json')->default('{}');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjeta_universitaria');
    }
};