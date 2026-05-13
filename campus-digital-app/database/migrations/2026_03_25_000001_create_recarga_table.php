<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recarga', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Monto que se intentó recargar
            $table->decimal('monto', 10, 2);

            // Método de pago: tarjeta, transferencia, efectivo, etc.
            $table->string('metodo_pago', 50)->default('tarjeta');

            // Estado del proceso de pago: pendiente, exitoso, fallido
            $table->string('estado', 20)->default('pendiente');

            // Referencia externa del pago (ej. ID de pasarela de pago)
            $table->string('referencia_pago', 100)->nullable();

            // Razón del fallo si el pago no fue exitoso
            $table->text('razon_fallo')->nullable();

            // Relación con el movimiento de saldo (solo si fue exitoso)
            $table->foreignId('saldo_movimiento_id')
                  ->nullable()
                  ->constrained('saldo_movimiento')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->jsonb('meta_json')->default('{}');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement("
            CREATE TRIGGER trg_recarga__set_updated_at
            BEFORE UPDATE ON recarga
            FOR EACH ROW EXECUTE FUNCTION set_updated_at()
        ");

        DB::statement("
            ALTER TABLE recarga
            ADD CONSTRAINT ck_recarga__estado
            CHECK (estado IN ('pendiente', 'exitoso', 'fallido'))
        ");

        DB::statement("
            ALTER TABLE recarga
            ADD CONSTRAINT ck_recarga__monto_positivo
            CHECK (monto > 0)
        ");

        DB::statement('CREATE INDEX idx_recarga__usuario_id ON recarga(usuario_id)');
        DB::statement('CREATE INDEX idx_recarga__estado ON recarga(estado)');
        DB::statement('CREATE INDEX idx_recarga__created_at ON recarga(created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('recarga');
    }
};
