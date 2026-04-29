<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_solicitudes_modulo', function (Blueprint $table) {
            $table->id();
            $table->char('folio', 12)->unique();
            $table->string('nombre_modulo', 120);
            $table->string('tipo_modulo', 60);
            $table->json('categorias_solicitadas');
            $table->string('contacto_nombre', 120);
            $table->string('contacto_email', 180);
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('pendiente'); // valores: pendiente|aprobada|rechazada
            $table->text('motivo_rechazo')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('usuario')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_solicitudes_modulo');
    }
};
