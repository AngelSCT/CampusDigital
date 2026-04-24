<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agrega campos de módulo, concepto y saldos a la tabla movimientos.
 * Necesario para registrar en qué módulo ocurrió cada transacción y llevar
 * un historial completo con el saldo antes y después de cada movimiento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            // Módulo donde ocurrió la transacción (cafeteria, copias, souvenirs, etc.)
            $table->string('modulo', 50)->nullable()->after('estado');

            // Descripción del concepto de la transacción
            $table->string('concepto', 255)->nullable()->after('modulo');

            // Saldo antes y después del movimiento (para historial completo)
            $table->decimal('saldo_anterior', 12, 2)->nullable()->after('concepto');
            $table->decimal('saldo_nuevo', 12, 2)->nullable()->after('saldo_anterior');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropColumn(['modulo', 'concepto', 'saldo_anterior', 'saldo_nuevo']);
        });
    }
};
