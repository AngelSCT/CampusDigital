<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_reglas_categoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('cart_categorias')->cascadeOnDelete();
            $table->string('clave', 80);
            $table->string('valor', 255);
            $table->string('tipo_dato', 20); // valores: int|bool|string|json — validado en ReglaCategoria::TIPO_*
            $table->timestamps();

            $table->unique(['categoria_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_reglas_categoria');
    }
};
