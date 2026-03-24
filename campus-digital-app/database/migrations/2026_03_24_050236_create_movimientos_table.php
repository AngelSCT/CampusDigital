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
    Schema::create('movimientos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuario')->cascadeOnDelete();

        $table->enum('tipo', ['recarga', 'pago']);
        $table->decimal('monto', 12, 2);

        $table->morphs('referencia');

        $table->timestamp('created_at')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
