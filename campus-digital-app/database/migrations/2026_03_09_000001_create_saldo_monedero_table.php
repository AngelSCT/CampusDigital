<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_monedero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                  ->unique()
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->decimal('saldo_disponible', 10, 2)->default(0.00);
            $table->decimal('saldo_retenido', 10, 2)->default(0.00); 
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement("
            CREATE TRIGGER trg_saldo_monedero__set_updated_at
            BEFORE UPDATE ON saldo_monedero
            FOR EACH ROW EXECUTE FUNCTION set_updated_at()
        ");

        DB::statement("
            ALTER TABLE saldo_monedero
            ADD CONSTRAINT ck_saldo_monedero__saldo_no_negativo
            CHECK (saldo_disponible >= 0 AND saldo_retenido >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_monedero');
    }
};