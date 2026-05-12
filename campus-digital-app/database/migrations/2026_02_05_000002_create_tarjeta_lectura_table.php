<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarjeta_lectura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarjeta_id')
                ->nullable()
                ->constrained('tarjeta_universitaria')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->string('uid_leido', 64)->comment('UID que se intentó leer');
            $table->string('modulo', 50)->default('otro')
                ->comment('cafeteria, copias, souvenirs, biblioteca, acceso, otro');
            $table->string('tipo_lectura', 50)->default('acceso')
                ->comment('acceso, consumo, consulta_saldo, confirmacion_entrega');
            $table->boolean('exito')->default(true);
            $table->text('detalle')->default('');
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->default('');
            $table->foreignId('operador_usuario_id')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->jsonb('meta_json')->default('{}');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjeta_lectura');
    }
};