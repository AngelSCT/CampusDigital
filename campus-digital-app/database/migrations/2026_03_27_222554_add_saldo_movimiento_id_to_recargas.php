<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->unsignedBigInteger('saldo_movimiento_id')->nullable()->after('meta_json');
            $table->foreign('saldo_movimiento_id')->references('id')->on('movimientos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->dropForeign(['saldo_movimiento_id']);
            $table->dropColumn('saldo_movimiento_id');
        });
    }
};