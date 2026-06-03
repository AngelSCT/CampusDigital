<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── productos: tienda, tipo evento, fecha y límite por usuario ───────
        Schema::table('productos', function (Blueprint $table) {
            $table->string('tienda', 100)->nullable()->default('General')->after('categoria');
            // 'producto' | 'evento' — determina el timeout dinámico del regalo
            $table->string('tipo', 50)->nullable()->default('producto')->after('tienda');
            // Solo para tipo='evento': fecha/hora de inicio del evento
            $table->timestampTz('fecha_inicio_evento')->nullable()->after('tipo');
            // Máx. unidades permitidas por usuario (propias + regalos enviados)
            $table->unsignedInteger('limite_por_usuario')->default(3)->after('fecha_inicio_evento');
        });

        // ── carrito_items: ciclo de vida completo del regalo ─────────────────
        Schema::table('carrito_items', function (Blueprint $table) {
            // es_regalo por ítem (antes era en productos, ahora es per-item de carrito)
            $table->boolean('es_regalo')->default(false)->after('motivo_movimiento');
            // Texto libre del dedicatorio
            $table->text('mensaje_dedicatorio')->nullable()->after('es_regalo');
            // null = no es regalo | 'pendiente' | 'aceptado' | 'rechazado' | 'expirado'
            $table->string('estado_regalo', 20)->nullable()->after('mensaje_dedicatorio');
            // Timeout dinámico: 24h por defecto, 1h antes del evento para tipo='evento'
            $table->timestampTz('fecha_expiracion_regalo')->nullable()->after('estado_regalo');
            // Reserva temporal de stock (dummy Módulo 4.2) — expira en 10 min
            $table->timestampTz('reservado_hasta')->nullable()->after('fecha_expiracion_regalo');
            // Hash público para validar vía GET /api/v1/regalo/validar/{hash}
            $table->string('regalo_hash', 64)->nullable()->unique()->after('reservado_hasta');
            // Usuario destinatario del regalo (quién lo recibe)
            $table->foreignId('destinatario_id')->nullable()->constrained('usuario')->nullOnDelete()->after('regalo_hash');
        });

        // ── pedidos: soporte para regalos + ventana 'Oops' + pago en caja ──
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('destinatario_id')->nullable()->constrained('usuario')->nullOnDelete()->after('metodo_pago');
            // gracia_hasta: hasta cuándo puede el comprador cancelar el regalo
            $table->timestampTz('gracia_hasta')->nullable()->after('destinatario_id');
            // notificado_destinatario: false = NO notificar hasta expirar gracia / volverse irreversible
            $table->boolean('notificado_destinatario')->default(false)->after('gracia_hasta');
            // Anti-abuso efectivo: token único para verificar pago en caja
            $table->string('pago_token', 64)->nullable()->unique()->after('notificado_destinatario');
            // Ventana de 40 min para pagar en caja (solo metodo_pago='efectivo')
            $table->timestampTz('pago_expira_en')->nullable()->after('pago_token');
        });

        // ── usuario: sanción de efectivo (shadow ban 30 días) ───────────────
        Schema::table('usuario', function (Blueprint $table) {
            // Si no es null y está en el futuro, el usuario NO puede usar efectivo
            $table->timestampTz('sancion_efectivo_hasta')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn(['sancion_efectivo_hasta']);
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['destinatario_id']);
            $table->dropUnique(['pago_token']);
            $table->dropColumn(['destinatario_id', 'gracia_hasta', 'notificado_destinatario', 'pago_token', 'pago_expira_en']);
        });

        Schema::table('carrito_items', function (Blueprint $table) {
            $table->dropForeign(['destinatario_id']);
            $table->dropUnique(['regalo_hash']);
            $table->dropColumn([
                'es_regalo', 'mensaje_dedicatorio', 'estado_regalo',
                'fecha_expiracion_regalo', 'reservado_hasta', 'regalo_hash', 'destinatario_id',
            ]);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['tienda', 'tipo', 'fecha_inicio_evento', 'limite_por_usuario']);
        });
    }
};
