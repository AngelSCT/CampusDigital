<?php

namespace App\Exceptions\Pedidos;

use RuntimeException;

class ProductoNoDisponibleException extends RuntimeException
{
    public static function noEncontrado(int $productoId): self
    {
        return new self("El producto ID {$productoId} no existe en el catálogo.");
    }

    public static function inactivo(int $productoId): self
    {
        return new self("El producto ID {$productoId} no está disponible.");
    }
}