<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->foreignId('tienda_id')
                  ->nullable()
                  ->after('modulo')
                  ->constrained('tienda')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->string('tipo_entrega', 20)->default('directo')->after('tienda_id');
            // CHECK constraint added below via DB::statement in migration

            $table->foreignId('repartidor_id')
                  ->nullable()
                  ->after('tipo_entrega')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });

        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE pedido
            ADD CONSTRAINT ck_pedido__tipo_entrega
            CHECK (tipo_entrega IN ('directo','repartidor'))
        ");
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pedido DROP CONSTRAINT IF EXISTS ck_pedido__tipo_entrega");
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropForeign(['tienda_id']);
            $table->dropColumn('tienda_id');
            $table->dropForeign(['repartidor_id']);
            $table->dropColumn(['tipo_entrega', 'repartidor_id']);
        });
    }
};
