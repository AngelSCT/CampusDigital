<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_movimiento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreignId('saldo_monedero_id')
                  ->constrained('saldo_monedero')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // abono = dinero entra | cargo = dinero sale
            $table->string('tipo', 20)->default('cargo');

            $table->decimal('monto', 10, 2);
            $table->decimal('saldo_anterior', 10, 2);
            $table->decimal('saldo_nuevo', 10, 2);

            // ¿Desde qué módulo vino este movimiento?
            $table->string('modulo', 50)->default('otro');
            // cafeteria | copias | souvenirs | biblioteca | recarga | rfid | otro

            $table->string('concepto', 255)->default('');

            // Para rastrear a qué pedido u operación está ligado (nullable)
            $table->string('referencia_tabla', 63)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();

            // Quién procesó (operador o sistema)
            $table->foreignId('operador_usuario_id')
                  ->nullable()
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // Si se realizó con tarjeta RFID
            $table->foreignId('tarjeta_lectura_id')
                  ->nullable()
                  ->constrained('tarjeta_lectura')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->jsonb('meta_json')->default('{}');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement("
            CREATE TRIGGER trg_saldo_movimiento__set_updated_at
            BEFORE UPDATE ON saldo_movimiento
            FOR EACH ROW EXECUTE FUNCTION set_updated_at()
        ");

        DB::statement("
            ALTER TABLE saldo_movimiento
            ADD CONSTRAINT ck_saldo_movimiento__tipo
            CHECK (tipo IN ('abono','cargo'))
        ");

        DB::statement("
            ALTER TABLE saldo_movimiento
            ADD CONSTRAINT ck_saldo_movimiento__monto_positivo
            CHECK (monto > 0)
        ");

        // Índices útiles para reportes
        DB::statement('CREATE INDEX idx_saldo_movimiento__usuario_id ON saldo_movimiento(usuario_id)');
        DB::statement('CREATE INDEX idx_saldo_movimiento__tipo ON saldo_movimiento(tipo)');
        DB::statement('CREATE INDEX idx_saldo_movimiento__modulo ON saldo_movimiento(modulo)');
        DB::statement('CREATE INDEX idx_saldo_movimiento__created_at ON saldo_movimiento(created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_movimiento');
    }
};