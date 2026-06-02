<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Carrito;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;

/**
 * Implementación stub de PedidoCreatorInterface.
 *
 * No crea ni cancela ningún Pedido real. Se usa como binding por defecto
 * mientras el módulo de Pedidos no haya implementado EloquentPedidoCreator
 * o equivalente, para no bloquear el desarrollo del módulo Checkout.
 *
 * REEMPLAZAR en AppServiceProvider cuando el módulo de Pedidos esté listo:
 *   $this->app->bind(PedidoCreatorInterface::class, EloquentPedidoCreator::class);
 */
final class NullPedidoCreator implements PedidoCreatorInterface
{
    public function crearDesdeCarrito(Carrito $carrito): void
    {
        // No-op: stub no bloqueante
    }

    public function cancelarPedidoDeCarrito(string $carritoUuid): void
    {
        // No-op: stub no bloqueante
    }
}
