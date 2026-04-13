<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            // Matrícula institucional — parte local del email (ej. "a20260001").
            // Se usa para validar identidad en el flujo de regalos (Módulo 4.4).
            $table->string('matricula', 50)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropUnique(['matricula']);
            $table->dropColumn('matricula');
        });
    }
};
