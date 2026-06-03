<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: saldo_reserva
 *
 * Tabla de reservas temporales de saldo (holds) creadas por el módulo 4.4
 * durante el proceso de checkout con el SaldoClient.
 *
 * También modifica el CHECK constraint de saldo_monedero para permitir
 * que saldo_disponible sea negativo (necesario para cargo-forzoso).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tabla saldo_reserva ────────────────────────────────────────────

        Schema::create('saldo_reserva', function (Blueprint $table) {
            $table->id();

            // UUID único que sirve como reserva_id para el módulo 4.4
            $table->uuid('uuid')->unique();

            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('saldo_monedero_id')
                  ->constrained('saldo_monedero')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Monto retenido en esta reserva
            $table->decimal('monto', 10, 2);

            // Identificador del carrito del módulo 4.4
            $table->string('carrito_uuid', 36)->nullable();

            // Slug del módulo que solicitó la reserva (ej. 'cafeteria')
            $table->string('modulo_slug', 50)->nullable();

            $table->string('concepto', 255)->default('');

            // pendiente | confirmada | liberada | expirada
            $table->string('estado', 20)->default('pendiente');

            // Momento en que la reserva expira (TTL = 5 min por defecto)
            $table->timestampTz('expira_at');

            // Movimiento de saldo vinculado una vez que se confirma
            $table->foreignId('saldo_movimiento_id')
                  ->nullable()
                  ->constrained('saldo_movimiento')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->timestampsTz();
        });

        DB::statement("
            ALTER TABLE saldo_reserva
            ADD CONSTRAINT ck_saldo_reserva__estado
            CHECK (estado IN ('pendiente','confirmada','liberada','expirada'))
        ");

        DB::statement("
            ALTER TABLE saldo_reserva
            ADD CONSTRAINT ck_saldo_reserva__monto_positivo
            CHECK (monto > 0)
        ");

        DB::statement('CREATE INDEX idx_saldo_reserva__uuid        ON saldo_reserva(uuid)');
        DB::statement('CREATE INDEX idx_saldo_reserva__usuario_id  ON saldo_reserva(usuario_id)');
        DB::statement('CREATE INDEX idx_saldo_reserva__estado      ON saldo_reserva(estado)');
        DB::statement('CREATE INDEX idx_saldo_reserva__expira_at   ON saldo_reserva(expira_at)');
        DB::statement('CREATE INDEX idx_saldo_reserva__carrito_uuid ON saldo_reserva(carrito_uuid)');

        // ── 2. Ajustar CHECK de saldo_monedero para cargo-forzoso ─────────────
        //
        // El endpoint POST /api/internal/saldo/cargo-forzoso puede dejar
        // saldo_disponible en negativo (deuda diferida del módulo 4.4).
        // Se elimina la restricción "saldo_disponible >= 0" y solo se mantiene
        // "saldo_retenido >= 0".
        //
        // La restricción de saldo positivo se aplica a nivel de aplicación
        // en SaldoMovimientoApiController (saldo insuficiente → 422).

        DB::statement('ALTER TABLE saldo_monedero DROP CONSTRAINT IF EXISTS ck_saldo_monedero__saldo_no_negativo');

        DB::statement("
            ALTER TABLE saldo_monedero
            ADD CONSTRAINT ck_saldo_monedero__saldo_retenido_no_negativo
            CHECK (saldo_retenido >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_reserva');

        // Restaurar constraint original
        DB::statement('ALTER TABLE saldo_monedero DROP CONSTRAINT IF EXISTS ck_saldo_monedero__saldo_retenido_no_negativo');

        DB::statement("
            ALTER TABLE saldo_monedero
            ADD CONSTRAINT ck_saldo_monedero__saldo_no_negativo
            CHECK (saldo_disponible >= 0 AND saldo_retenido >= 0)
        ");
    }
};
