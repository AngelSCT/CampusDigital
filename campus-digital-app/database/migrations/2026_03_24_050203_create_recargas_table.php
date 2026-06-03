<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('recargas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuario')->cascadeOnDelete();
        $table->decimal('monto', 12, 2);
        $table->enum('metodo_pago', ['tarjeta', 'efectivo']);
        $table->enum('estado', ['pendiente', 'exitosa', 'fallida'])->default('pendiente');
        $table->string('referencia')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recargas');
    }
};
