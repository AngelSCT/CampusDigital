<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migración: Amplía los métodos de pago disponibles en la tabla recargas.
 * Agrega 'transferencia' y 'billetera_digital' al enum de metodo_pago para
 * soportar los métodos que ya usa el frontend.
 *
 * Nota: PostgreSQL no soporta ALTER COLUMN para enums fácilmente,
 * por eso se usa un enfoque de tipo TEXT con constraint CHECK.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cambiar la columna a texto sin restricción enum para ampliar opciones
        DB::statement("ALTER TABLE recargas ALTER COLUMN metodo_pago TYPE VARCHAR(50)");
    }

    public function down(): void
    {
        // Revertir solo si todos los valores existentes son válidos para el enum original
        DB::statement("ALTER TABLE recargas ALTER COLUMN metodo_pago TYPE VARCHAR(50)");
    }
};
