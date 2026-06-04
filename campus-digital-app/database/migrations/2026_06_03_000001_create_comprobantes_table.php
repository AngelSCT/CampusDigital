<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->string('carrito_uuid', 100)->nullable()->unique()->after('id');
            $table->string('ticket_id', 50)->nullable()->unique()->after('carrito_uuid');
            $table->string('folio', 50)->nullable()->after('ticket_id');
            $table->string('usuario_ref', 50)->nullable()->after('folio');
            $table->string('modulo_origen', 50)->nullable()->after('usuario_ref');
            $table->jsonb('items')->default('[]')->after('modulo_origen');
            $table->timestampTz('fecha_confirmacion')->nullable()->after('items');
            $table->jsonb('data_raw')->default('{}')->after('fecha_confirmacion');
            $table->string('estado', 30)->default('confirmado')->after('data_raw');
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropColumn([
                'carrito_uuid', 'ticket_id', 'folio', 'usuario_ref',
                'modulo_origen', 'items', 'fecha_confirmacion', 'data_raw',
                'estado', 'deleted_at',
            ]);
        });
    }
};
