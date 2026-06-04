<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Actualiza cart_carritos_estado_check para incluir 'procesando_checkout',
 * estado introducido en Fase 0 Tanda 2 pero ausente en el CHECK de producción.
 *
 * Estados válidos tras esta migración:
 *   abierto, procesando_checkout, confirmado, cancelado, expirado,
 *   confirmado_pendiente_conciliacion, revertido
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cart_carritos DROP CONSTRAINT IF EXISTS cart_carritos_estado_check');

        DB::statement("
            ALTER TABLE cart_carritos
            ADD CONSTRAINT cart_carritos_estado_check
            CHECK (estado IN (
                'abierto',
                'procesando_checkout',
                'confirmado',
                'cancelado',
                'expirado',
                'confirmado_pendiente_conciliacion',
                'revertido'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cart_carritos DROP CONSTRAINT IF EXISTS cart_carritos_estado_check');

        DB::statement("
            ALTER TABLE cart_carritos
            ADD CONSTRAINT cart_carritos_estado_check
            CHECK (estado IN (
                'abierto',
                'confirmado',
                'cancelado',
                'expirado',
                'confirmado_pendiente_conciliacion',
                'revertido'
            ))
        ");
    }
};
