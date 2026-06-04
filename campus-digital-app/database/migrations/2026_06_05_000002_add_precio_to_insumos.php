<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add precio_unitario column to the insumos table.
     */
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('precio_unitario', 10, 2)->default(0.00);
        });

        DB::statement("
            ALTER TABLE insumos
            ADD CONSTRAINT chk_insumos_precio_unitario
            CHECK (precio_unitario >= 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE insumos DROP CONSTRAINT IF EXISTS chk_insumos_precio_unitario');

        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn('precio_unitario');
        });
    }
};
