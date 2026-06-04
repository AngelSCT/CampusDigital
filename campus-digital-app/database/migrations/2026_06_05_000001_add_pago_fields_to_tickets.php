<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add payment-related columns to the tickets table.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('costo_total', 10, 2)->default(0.00);
            $table->string('carrito_uuid', 36)->nullable();
            $table->string('estado_pago', 30)->default('sin_cobro');
            $table->timestampTz('fecha_pago')->nullable();

            $table->index('estado_pago');
            $table->index('carrito_uuid');
        });

        DB::statement("
            ALTER TABLE tickets
            ADD CONSTRAINT chk_tickets_estado_pago
            CHECK (estado_pago IN ('sin_cobro', 'pendiente_pago', 'pagado', 'cancelado'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS chk_tickets_estado_pago');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['estado_pago']);
            $table->dropIndex(['carrito_uuid']);
            $table->dropColumn(['costo_total', 'carrito_uuid', 'estado_pago', 'fecha_pago']);
        });
    }
};
