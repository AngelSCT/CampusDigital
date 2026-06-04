<?php

namespace App\Services;

/**
 * Capa de abstracción hacia APIs externas de otros módulos del Campus Digital.
 *
 * Todos los métodos son DUMMIES por ahora.
 * Cuando el módulo correspondiente esté listo, reemplaza el cuerpo del método
 * con una llamada HTTP real (ej. Http::get(...)) sin romper el contrato.
 *
 * Módulos destino:
 *   - consultarSaldo / descontarSaldo  → Módulo 4.2 (Monedero)
 *   - verificarStock                   → Módulo de Inventarios
 *   - obtenerDatosUsuario              → Módulo 4.1 (Usuarios/Perfiles)
 */
class ExternalModulesService
{
    /**
     * Consulta el saldo disponible de un usuario en el Monedero (Módulo 4.2).
     *
     * @return array{saldo_disponible: float, saldo_retenido: float, ok: bool}
     */
    public function consultarSaldo(int $usuarioId): array
    {
        // TODO: reemplazar con Http::get("/api/monedero/saldo/{$usuarioId}")
        return [
            'saldo_disponible' => 0.0,
            'saldo_retenido'   => 0.0,
            'ok'               => true,
        ];
    }

    /**
     * Descuenta un monto del monedero del usuario (Módulo 4.2).
     *
     * @return array{ok: bool, nuevo_saldo: float, mensaje: string}
     */
    public function descontarSaldo(int $usuarioId, float $monto, string $concepto): array
    {
        // TODO: reemplazar con Http::post("/api/monedero/descontar", [...])
        return [
            'ok'          => true,
            'nuevo_saldo' => 0.0,
            'mensaje'     => '[DUMMY] Saldo descontado correctamente.',
        ];
    }

    /**
     * Verifica el stock disponible de un producto en el Módulo de Inventarios.
     *
     * @return array{disponible: int, tiene_stock: bool, ok: bool}
     */
    public function verificarStock(int $productoId, int $cantidadRequerida = 1): array
    {
        // TODO: reemplazar con Http::get("/api/inventario/producto/{$productoId}")
        return [
            'disponible'  => 999,
            'tiene_stock' => true,
            'ok'          => true,
        ];
    }

    /**
     * Obtiene los datos de perfil de un usuario (Módulo 4.1).
     *
     * @return array{id: int, nombre: string, email: string, ok: bool}
     */
    public function obtenerDatosUsuario(int $usuarioId): array
    {
        // TODO: reemplazar con Http::get("/api/usuarios/{$usuarioId}")
        return [
            'id'     => $usuarioId,
            'nombre' => '[DUMMY]',
            'email'  => '[DUMMY]',
            'ok'     => true,
        ];
    }

    /**
     * Devuelve un monto al monedero del comprador cuando un regalo es
     * rechazado o expira (Módulo 4.5 Monedero).
     *
     * @return array{ok: bool, nuevo_saldo: float, mensaje: string}
     */
    public function reembolsarSaldo(int $usuarioId, float $monto, string $concepto): array
    {
        // TODO: reemplazar con Http::post("/api/monedero/reembolsar", [...])
        return [
            'ok'          => true,
            'nuevo_saldo' => 0.0,
            'mensaje'     => "[DUMMY] Reembolso de \${$monto} procesado para usuario #{$usuarioId}. Concepto: {$concepto}",
        ];
    }

    /**
     * Libera (devuelve) unidades de stock al Módulo de Inventarios
     * cuando un regalo no se concreta.
     *
     * @return array{ok: bool, stock_actual: int}
     */
    public function liberarStock(int $productoId, int $cantidad): array
    {
        // TODO: reemplazar con Http::post("/api/inventario/liberar", [...])
        return [
            'ok'           => true,
            'stock_actual' => 0,
            'mensaje'      => "[DUMMY] {$cantidad} unidad(es) del producto #{$productoId} devueltas al inventario.",
        ];
    }

    /**
     * Valida que una matrícula corresponda exactamente al primer nombre
     * del estudiante en el Módulo 4.10 (Tarjeta Universitaria).
     *
     * @return array{valido: bool, destinatario_id: int|null, mensaje: string}
     */
    public function validarIdentidadDestinatario(string $matricula, string $primerNombre): array
    {
        // TODO: reemplazar con Http::post("/api/modulo410/validar-identidad", [...])
        // La respuesta NUNCA debe revelar qué campo falló (privacidad).
        return [
            'valido'          => false,
            'destinatario_id' => null,
            'mensaje'         => '[DUMMY] Validación pendiente de integración con Módulo 4.10.',
        ];
    }

    /**
     * Notifica al destinatario que tiene un regalo pendiente de aceptar.
     * Solo debe llamarse una vez expirado el período de gracia 'Oops'.
     *
     * @return array{ok: bool, canal: string}
     */
    public function notificarDestinatario(int $destinatarioId, string $mensajePersonalizado = ''): array
    {
        // TODO: reemplazar con Http::post("/api/notificaciones/regalo", [...])
        return [
            'ok'     => true,
            'canal'  => '[DUMMY] email/push',
            'mensaje' => "[DUMMY] Notificación enviada al usuario #{$destinatarioId}.",
        ];
    }

    /**
     * Registra un adeudo en el Módulo de Monedero (4.2) cuando un pedido
     * de efectivo expira sin que el usuario haya pagado en caja.
     *
     * @return array{ok: bool, adeudo_id: int|null, mensaje: string}
     */
    public function registrarAdeudo(int $usuarioId, float $monto, string $concepto): array
    {
        // TODO: reemplazar con Http::post("/api/monedero/adeudo", [...])
        return [
            'ok'       => true,
            'adeudo_id' => null,
            'mensaje'  => "[DUMMY] Adeudo de \${$monto} registrado para usuario #{$usuarioId}. Concepto: {$concepto}",
        ];
    }

    /**
     * Notifica al Módulo 4.2 que un monto ya fue movido a saldo_retenido localmente.
     * Internamente el módulo registra el hold en su cuenta puente (escrow).
     * El saldo permanece "Bloqueado" hasta que el destinatario acepte (Módulo 4.5)
     * o el emisor cancele en el período de gracia (reembolso → saldo_retenido → saldo_disponible).
     *
     * Simula POST /monedero/escrow/hold
     *
     * @param bool $esRegalo  true = mover a cuenta puente; false = retención temporal de stock
     * @return array{ok: bool, hold_id: int|null, estado: string, mensaje: string}
     */
    public function retenerSaldo(int $usuarioId, float $monto, string $concepto, bool $esRegalo = true): array
    {
        // TODO: reemplazar con Http::post("/api/monedero/escrow/hold", ['es_regalo' => $esRegalo, ...])
        return [
            'ok'      => true,
            'hold_id' => null,
            'estado'  => 'bloqueado',
            'mensaje' => "[DUMMY] \${$monto} en escrow para usuario #{$usuarioId} (esRegalo={$esRegalo}). Concepto: {$concepto}",
        ];
    }

    /**
     * Registra una merma en el Módulo de Inventarios (Módulo 4.10).
     * Se llama cuando un producto se cancela por estar dañado, no solo por falta de stock.
     *
     * Simula POST /inventario/merma
     *
     * @return array{ok: bool, merma_id: int|null, mensaje: string}
     */
    public function registrarMerma(int $productoId, int $cantidad, string $motivo = 'dañado'): array
    {
        // TODO: reemplazar con Http::post("/api/inventario/merma", [...])
        return [
            'ok'       => true,
            'merma_id' => null,
            'mensaje'  => "[DUMMY] Merma de {$cantidad} uds. registrada para producto #{$productoId}. Motivo: {$motivo}",
        ];
    }

    /**
     * Consulta cuántos pedidos de efectivo han expirado para un usuario
     * dentro de una ventana de tiempo (usado para shadow ban).
     *
     * @return array{ok: bool, total_expirados: int}
     */
    public function verificarPenalizaciones(int $usuarioId, string $desde): array
    {
        // TODO: reemplazar con Http::get("/api/monedero/penalizaciones/{$usuarioId}", [...])
        return [
            'ok'              => true,
            'total_expirados' => 0,
        ];
    }

    /**
     * Notifica al Módulo de Usuarios (4.1) que se aplicó una sanción de
     * 30 días al método de pago efectivo para el usuario indicado.
     *
     * @return array{ok: bool, sancion_hasta: string|null}
     */
    public function aplicarSancionEfectivo(int $usuarioId, string $sancionHasta): array
    {
        // TODO: reemplazar con Http::post("/api/usuarios/{$usuarioId}/sanciones", [...])
        return [
            'ok'           => true,
            'sancion_hasta' => $sancionHasta,
            'mensaje'      => "[DUMMY] Sanción de efectivo aplicada hasta {$sancionHasta} para usuario #{$usuarioId}.",
        ];
    }
}
