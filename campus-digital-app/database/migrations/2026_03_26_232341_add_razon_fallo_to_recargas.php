<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->text('razon_fallo')->nullable()->after('referencia');
        });
    }

    public function down(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->dropColumn('razon_fallo');
        });
    }
};