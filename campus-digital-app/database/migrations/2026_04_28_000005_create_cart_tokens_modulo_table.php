<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_tokens_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('cart_modulos_clientes')->cascadeOnDelete();
            $table->char('jti', 36)->unique();
            $table->string('tipo', 10);                              // valores: access|refresh
            $table->string('estado', 20)->default('activo');          // valores: activo|revocado|expirado
            $table->timestamp('emitido_at');
            $table->timestamp('expira_at');
            // [v1.1 C2] Marca la entrega one-time del JWT al admin; NULL = aún no entregado.
            $table->timestamp('entregado_at')->nullable();
            $table->timestamp('revocado_at')->nullable();
            $table->foreignId('revocado_por')->nullable()->constrained('usuario')->nullOnDelete();
            $table->string('motivo_revocacion', 255)->nullable();
            // [v1.1 C6] jti del token compañero emitido en el mismo par (access ↔ refresh).
            $table->char('pair_jti', 36)->nullable();
            // [v1.1 C6] jti del token anterior del mismo tipo en la cadena de rotación. NULL = par inicial.
            $table->char('replaces_jti', 36)->nullable();
            $table->timestamps();

            $table->index(['modulo_id', 'estado']);
            $table->index('expira_at');
            $table->index('pair_jti');
            $table->index('replaces_jti');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_tokens_modulo');
    }
};
