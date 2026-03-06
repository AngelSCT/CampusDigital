<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarjeta_universitaria', function (Blueprint $table) {
            // PIN hasheado — null significa que el usuario aún no lo ha configurado
            $table->text('pin_hash')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('tarjeta_universitaria', function (Blueprint $table) {
            $table->dropColumn('pin_hash');
        });
    }
};