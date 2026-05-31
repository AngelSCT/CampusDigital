<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cargo', 'abono']);
            $table->string('category');
            $table->decimal('amount', 10, 2);
            $table->string('reference')->unique();
            $table->string('description')->nullable();
            $table->enum('status', ['pendiente', 'completado', 'fallido'])->default('completado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
