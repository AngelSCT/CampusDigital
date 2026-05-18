<?php

namespace App\Modules\Cart\Contracts;

use App\Models\Cart\Carrito;

/**
 * Contrato que el módulo de Pedidos debe implementar para crear un Pedido
 * a partir de un Carrito confirmado.
 *
 * El módulo Checkout es propietario de esta interfaz. El módulo Pedidos
 * provee la implementación concreta (EloquentPedidoCreator o equivalente).
 *
 * Nota para el equipo de Pedidos:
 *   - crearDesdeCarrito() se llama DENTRO de una DB::transaction junto con
 *     la confirmación del Carrito. Si lanza excepción, ambas escrituras
 *     se revierten (rollback automático).
 *   - cancelarPedidoDeCarrito() se llama FUERA de transacción, como
 *     compensación post-commit. Debe ser idempotente.
 */
interface PedidoCreatorInterface
{
    /**
     * Crea un Pedido a partir del Carrito confirmado.
     *
     * Idempotente: si ya existe un Pedido para este carrito_uuid, no crea
     * otro y retorna silenciosamente.
     *
     * @param  Carrito $carrito  Carrito cuyo estado acaba de cambiar a 'confirmado'
     *                           dentro de la transacción activa.
     * @throws \RuntimeException Si no es posible crear el Pedido.
     */
    public function crearDesdeCarrito(Carrito $carrito): void;

    /**
     * Cancela el Pedido asociado al carrito (compensación post-commit).
     *
     * Idempotente: si el Pedido no existe o ya está cancelado, no hace nada.
     *
     * @param  string $carritoUuid  UUID del carrito cuyo Pedido debe cancelarse.
     */
    public function cancelarPedidoDeCarrito(string $carritoUuid): void;
}
