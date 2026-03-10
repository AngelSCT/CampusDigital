<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->string('numero_folio', 30)->unique();

            $table->string('estado', 30)->default('creado');

            $table->string('modulo', 50)->default('otro');

            $table->decimal('total', 10, 2)->default(0.00);
            $table->text('descripcion')->default('');
            $table->text('notas')->default('');

            $table->foreignId('operador_usuario_id')
                  ->nullable()
                  ->constrained('usuario')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->boolean('confirmado_con_tarjeta')->default(false);
            $table->timestamp('confirmado_at')->nullable();
            $table->foreignId('tarjeta_lectura_id')
                  ->nullable()
                  ->constrained('tarjeta_lectura')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->boolean('cobrado_de_saldo')->default(false);
            $table->foreignId('saldo_movimiento_id')
                  ->nullable()
                  ->constrained('saldo_movimiento')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->jsonb('meta_json')->default('{}');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement("
            CREATE TRIGGER trg_pedido__set_updated_at
            BEFORE UPDATE ON pedido
            FOR EACH ROW EXECUTE FUNCTION set_updated_at()
        ");

        DB::statement("
            ALTER TABLE pedido
            ADD CONSTRAINT ck_pedido__estado
            CHECK (estado IN ('creado','aceptado','en_proceso','listo','entregado','cancelado'))
        ");

        DB::statement('CREATE INDEX idx_pedido__usuario_id ON pedido(usuario_id)');
        DB::statement('CREATE INDEX idx_pedido__estado ON pedido(estado)');
        DB::statement('CREATE INDEX idx_pedido__modulo ON pedido(modulo)');
        DB::statement('CREATE INDEX idx_pedido__created_at ON pedido(created_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido');
    }
};