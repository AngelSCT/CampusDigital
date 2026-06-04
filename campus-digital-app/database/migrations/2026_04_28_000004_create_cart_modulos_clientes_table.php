<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_modulos_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('cart_solicitudes_modulo');
            $table->string('nombre', 120);
            $table->string('slug', 60)->unique();
            $table->string('tipo_modulo', 60);
            $table->json('categorias_autorizadas');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_modulos_clientes');
    }
};
