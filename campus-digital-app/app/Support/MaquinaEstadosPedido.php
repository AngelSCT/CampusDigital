<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Máquina de estados de Pedido.
 * 
 * Única fuente de verdad para saber qué transiciones son válidas.
 * Usada tanto por el controlador web como por el API.
 */
class MaquinaEstadosPedido
{
    /**
     * Mapa de transiciones permitidas.
     * clave = estado actual, valor = array de estados a los que puede pasar.
     */
    public const TRANSICIONES = [
        'creado'     => ['aceptado', 'cancelado'],
        'aceptado'   => ['en_proceso', 'cancelado'],
        'en_proceso' => ['listo', 'cancelado'],
        'listo'      => ['entregado', 'cancelado'],
        'entregado'  => [], // estado terminal
        'cancelado'  => [], // estado terminal
    ];

    /**
     * Estados desde los cuales se puede eliminar un pedido.
     */
    public const ESTADOS_ELIMINABLES = ['creado', 'cancelado'];

    /**
     * ¿Se puede pasar del estado X al estado Y?
     */
    public static function puedeTransicionar(string $desde, string $hacia): bool
    {
        return in_array($hacia, self::TRANSICIONES[$desde] ?? [], true);
    }

    /**
     * Lanza una excepción si la transición no es válida.
     * Útil para usar dentro de los controladores con try/catch.
     */
    public static function validarTransicion(string $desde, string $hacia): void
    {
        if (!self::puedeTransicionar($desde, $hacia)) {
            throw new InvalidArgumentException(
                "Transición no válida: no se puede pasar de '{$desde}' a '{$hacia}'."
            );
        }
    }

    /**
     * ¿El pedido se puede eliminar en su estado actual?
     */
    public static function puedeEliminarse(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_ELIMINABLES, true);
    }

    /**
     * ¿El estado es terminal (ya no puede cambiar)?
     */
    public static function esTerminal(string $estado): bool
    {
        return empty(self::TRANSICIONES[$estado] ?? []);
    }

    /**
     * Devuelve los estados a los que puede transicionar desde el estado actual.
     */
    public static function siguientesEstados(string $estadoActual): array
    {
        return self::TRANSICIONES[$estadoActual] ?? [];
    }
}