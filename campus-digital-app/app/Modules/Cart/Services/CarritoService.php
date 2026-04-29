<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Exceptions\CartOwnershipException;
use App\Modules\Cart\Exceptions\CartStateException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CarritoService
{
    public function crear(ModuloCliente $modulo, array $data): Carrito
    {
        $uuid = (string) Str::uuid();

        $carrito = Carrito::create([
            'uuid'           => $uuid,
            'modulo_id'      => $modulo->id,
            'usuario_ref'    => $data['usuario_ref'],
            'estado'         => Carrito::ESTADO_ABIERTO,
            'requiere_saldo' => $data['requiere_saldo'] ?? false,
            'total'          => '0.00',
            'metadata'       => $data['metadata'] ?? null,
            'expira_at'      => isset($data['expira_en_minutos'])
                ? now()->addMinutes((int) $data['expira_en_minutos'])
                : null,
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CREADO,
            'modulo_id'    => $modulo->id,
            'carrito_uuid' => $uuid,
        ]);

        return $carrito->load('modulo');
    }

    /**
     * Busca el carrito por UUID y verifica que pertenezca al módulo autenticado.
     *
     * @throws CartOwnershipException  Si el carrito pertenece a otro módulo.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  Si no existe.
     */
    public function obtenerPorUuid(string $uuid, ModuloCliente $modulo): Carrito
    {
        $carrito = Carrito::where('uuid', $uuid)
            ->with(['modulo', 'itemsActivos.categoria'])
            ->first();

        if (!$carrito) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Carrito {$uuid} no encontrado.");
        }

        if ($carrito->modulo_id !== $modulo->id) {
            throw new CartOwnershipException("El carrito no pertenece al módulo autenticado.");
        }

        return $carrito;
    }

    /** @throws CartStateException */
    public function cancelar(Carrito $carrito): Carrito
    {
        $this->assertOperable($carrito);

        $carrito->update([
            'estado'       => Carrito::ESTADO_CANCELADO,
            'cancelled_at' => now(),
        ]);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_CARRITO_CANCELADO,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
        ]);

        return $carrito->fresh();
    }

    public function historial(ModuloCliente $modulo, array $filters): LengthAwarePaginator
    {
        $query = Carrito::where('modulo_id', $modulo->id)
            ->with('itemsActivos');

        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }
        if (isset($filters['usuario_ref'])) {
            $query->where('usuario_ref', $filters['usuario_ref']);
        }
        if (isset($filters['desde'])) {
            $query->where('created_at', '>=', $filters['desde']);
        }
        if (isset($filters['hasta'])) {
            $query->where('created_at', '<=', $filters['hasta']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function toArray(Carrito $carrito): array
    {
        $carrito->loadMissing(['modulo', 'itemsActivos.categoria']);

        return [
            'carrito_uuid' => $carrito->uuid,
            'estado'       => $carrito->estado,
            'modulo'       => $carrito->modulo->slug,
            'total'        => $carrito->total,
            'requiere_saldo' => $carrito->requiere_saldo,
            'items'        => $carrito->itemsActivos->map(fn($i) => [
                'id'                 => $i->id,
                'categoria_slug'     => $i->categoria->slug,
                'referencia_externa' => $i->referencia_externa,
                'nombre'             => $i->nombre,
                'precio_unitario'    => $i->precio_unitario,
                'cantidad'           => $i->cantidad,
                'duracion_horas'     => $i->duracion_horas,
                'estado_item'        => $i->estado_item,
                'metadata'           => $i->metadata,
                'added_at'           => $i->added_at?->toISOString(),
            ])->values()->toArray(),
            'metadata'     => $carrito->metadata,
            'expira_at'    => $carrito->expira_at?->toISOString(),
            'confirmed_at' => $carrito->confirmed_at?->toISOString(),
            'cancelled_at' => $carrito->cancelled_at?->toISOString(),
        ];
    }

    /** @throws CartStateException */
    public function assertOperable(Carrito $carrito): void
    {
        if ($carrito->expira_at && $carrito->expira_at->isPast()) {
            throw new CartStateException('El carrito ha expirado.');
        }
        if ($carrito->estaEnEstadoTerminal()) {
            throw new CartStateException("El carrito está en estado '{$carrito->estado}'.");
        }
    }
}
