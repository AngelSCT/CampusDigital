<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')
                  ->constrained('pedido')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->foreignId('usuario_id')
                  ->nullable()
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            $table->text('notas')->default('');
            $table->timestampsTz();
        });

        DB::statement('CREATE INDEX idx_pedido_historial__pedido_id ON pedido_historial(pedido_id)');
        DB::statement('CREATE INDEX idx_pedido_historial__created_at ON pedido_historial(created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_historial');
    }
};