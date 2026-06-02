<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            // Nombre de la tienda que gestiona este usuario proveedor.
            // Null para estudiantes y administradores.
            $table->string('tienda', 100)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('tienda');
        });
    }
};
