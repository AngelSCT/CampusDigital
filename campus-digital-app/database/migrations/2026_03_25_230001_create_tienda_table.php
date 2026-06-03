<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tienda', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('tipo', 30)->default('otro');
            $table->text('descripcion')->default('');
            $table->boolean('activo')->default(true);
            $table->string('logo_url')->nullable();
            $table->string('color', 20)->default('#3b82f6'); // brand color
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement("
            ALTER TABLE tienda
            ADD CONSTRAINT ck_tienda__tipo
            CHECK (tipo IN ('cafeteria','papeleria','kermesse','mercadito','estudiante','otro'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tienda');
    }
};
