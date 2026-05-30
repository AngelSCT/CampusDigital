<?php

namespace App\DTOs;

use InvalidArgumentException;

/**
 * DTO: datos necesarios para crear un pedido desde un checkout.
 *
 * Este objeto es la "carta de presentación" que cualquier módulo
 * (M4.4 Carrito, u otro consumidor) debe pasarle al Service
 * CrearPedidoDesdeCheckout para crear un pedido en el M4.5.
 *
 * No contiene total: el M4.5 lo calcula leyendo precios del M4.3
 * para evitar inconsistencias.
 */
class CheckoutPedidoDTO
{
    /**
     * @param int $usuarioId         ID del usuario que hace el pedido
     * @param string $modulo         Módulo destino: cafeteria, copias, souvenirs, biblioteca, otro
     * @param array $items           Array de items: cada uno debe tener producto_id y cantidad
     * @param string|null $descripcion  Descripción opcional del pedido
     * @param array|null $metaJson   Metadata libre (origen, referencias, etc.)
     */
    public function __construct(
        public readonly int $usuarioId,
        public readonly string $modulo,
        public readonly array $items,
        public readonly ?string $descripcion = null,
        public readonly ?array $metaJson = null,
        public readonly ?string $carritoUuid = null,
    ) {
        $this->validar();
    }

    /**
     * Validaciones básicas del DTO. Lanza excepciones si los datos
     * son inválidos antes de que el Service haga cualquier cosa.
     */
    private function validar(): void
    {
        // Módulos válidos según el M4.5
        $modulosValidos = ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'otro'];
        if (!in_array($this->modulo, $modulosValidos, true)) {
            throw new InvalidArgumentException(
                "Módulo inválido: '{$this->modulo}'. Permitidos: " . implode(', ', $modulosValidos)
            );
        }

        // Items no vacíos
        if (empty($this->items)) {
            throw new InvalidArgumentException("El pedido debe tener al menos un item.");
        }

        // Cada item debe tener producto_id y cantidad
        foreach ($this->items as $i => $item) {
            if (!isset($item['producto_id']) || !is_int($item['producto_id'])) {
                throw new InvalidArgumentException(
                    "Item #{$i}: falta 'producto_id' o no es un entero."
                );
            }
            if (!isset($item['cantidad']) || !is_int($item['cantidad']) || $item['cantidad'] < 1) {
                throw new InvalidArgumentException(
                    "Item #{$i}: 'cantidad' debe ser un entero mayor a 0."
                );
            }
        }
    }

    /**
     * Factory método para construir el DTO desde un array crudo.
     * Útil cuando los datos vienen de un request HTTP o de otro módulo.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            usuarioId:   (int) ($data['usuario_id'] ?? 0),
            modulo:      (string) ($data['modulo'] ?? ''),
            items:       $data['items'] ?? [],
            descripcion: $data['descripcion'] ?? null,
            metaJson:    $data['meta_json'] ?? null,
            carritoUuid: $data['carrito_uuid'] ?? null,
        );
    }
}