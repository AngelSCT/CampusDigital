<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saldo_reglas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->onDelete('set null');
            $table->enum('tipo_limite', ['diario', 'semanal', 'mensual'])->default('diario');
            $table->decimal('monto_limite', 10, 2)->comment('Monto límite en la unidad de moneda');
            $table->string('modulo')->nullable()->comment('Módulo específico (cafeteria, copias, etc) o NULL para todos');
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas rápidas
            $table->index('usuario_id');
            $table->index(['tipo_limite', 'activo']);
            $table->index('modulo');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_reglas');
    }
};
