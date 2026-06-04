<?php

namespace App\Events;

use App\Models\CarritoItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se dispara cuando un regalo es Rechazado manualmente
 * o cuando el sistema detecta que ha Expirado.
 *
 * El Listener HandleGiftExpiredOrRejected se encarga de:
 *   1. Reembolsar al comprador (Módulo 4.5 Monedero)
 *   2. Recuperar el stock (Módulo 4.2 Inventario)
 *   3. Marcar el ítem con el estado final
 */
class GiftExpiredOrRejected
{
    use Dispatchable, SerializesModels;

    /**
     * @param CarritoItem $item   El ítem de carrito marcado como regalo
     * @param string      $motivo 'rechazado' | 'expirado'
     */
    public function __construct(
        public readonly CarritoItem $item,
        public readonly string      $motivo,
    ) {}
}
