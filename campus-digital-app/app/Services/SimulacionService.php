<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\Saldo;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * SimulacionService
 *
 * Servicio dedicado para simular transacciones de saldo en los distintos módulos
 * del campus universitario. Implementa montos realistas por módulo, validación
 * de saldo disponible y actualización atómica con bloqueo para evitar
 * condiciones de carrera.
 */
class SimulacionService
{
    /**
     * Configuración de módulos: nombre legible, rango de montos y si genera cargo.
     * Se centraliza aquí para evitar hardcoding en controladores.
     *
     * Formato: 'clave' => ['label', min, max, es_cargo]
     */
    private const MODULOS = [
        'cafeteria'  => ['label' => 'Cafetería',           'min' => 50,  'max' => 150, 'cargo' => true],
        'copias'     => ['label' => 'Copias / Impresiones', 'min' => 10,  'max' => 50,  'cargo' => true],
        'souvenirs'  => ['label' => 'Souvenirs',            'min' => 30,  'max' => 100, 'cargo' => true],
        'biblioteca' => ['label' => 'Biblioteca',           'min' => 5,   'max' => 20,  'cargo' => true],
        'acceso'     => ['label' => 'Control de Acceso',    'min' => 0,   'max' => 0,   'cargo' => false],
    ];

    /**
     * Retorna la configuración completa de todos los módulos disponibles.
     * Útil para enviar al frontend sin hardcoding.
     */
    public function obtenerModulos(): array
    {
        $resultado = [];
        foreach (self::MODULOS as $clave => $config) {
            $resultado[] = [
                'clave' => $clave,
                'label' => $config['label'],
                'rango' => $config['cargo']
                    ? "\${$config['min']} - \${$config['max']}"
                    : 'Sin costo',
                'cargo' => $config['cargo'],
            ];
        }
        return $resultado;
    }

    /**
     * Simula una transacción en el módulo indicado.
     *
     * - Módulos con cargo: descuenta un monto aleatorio del rango configurado.
     *   Lanza excepción si el saldo es insuficiente.
     * - Módulo de acceso: solo registra la consulta sin modificar el saldo.
     *
     * Usa lockForUpdate() para garantizar atomicidad y evitar condiciones de carrera
     * en entornos con múltiples peticiones concurrentes.
     *
     * @param  Usuario $usuario  Usuario autenticado
     * @param  string  $modulo   Clave del módulo (cafeteria, copias, etc.)
     * @return array             Resultado con movimiento, monto y saldo actualizado
     *
     * @throws \InvalidArgumentException  Si el módulo no existe
     * @throws \RuntimeException          Si el saldo es insuficiente
     */
    public function simular(Usuario $usuario, string $modulo): array
    {
        if (!array_key_exists($modulo, self::MODULOS)) {
            throw new \InvalidArgumentException("Módulo '{$modulo}' no reconocido.");
        }

        $config = self::MODULOS[$modulo];

        // Módulo de acceso: solo consulta, sin cargo
        if (!$config['cargo']) {
            return $this->registrarAcceso($usuario, $config);
        }

        // Generar monto aleatorio con centavos realistas
        $montoEntero   = rand($config['min'], $config['max']);
        $centavos      = rand(0, 99) / 100;
        $monto         = round($montoEntero + $centavos, 2);
        $concepto      = "Consumo en {$config['label']}";

        return DB::transaction(function () use ($usuario, $modulo, $config, $monto, $concepto) {

            // Obtener saldo con bloqueo exclusivo para evitar race conditions
            $saldo = Saldo::where('usuario_id', $usuario->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['usuario_id' => $usuario->id],
                    ['saldo' => 0]
                );

            if ($saldo->saldo < $monto) {
                throw new \RuntimeException(
                    "Saldo insuficiente. Disponible: \${$saldo->saldo}, requerido: \${$monto}."
                );
            }

            $saldoAnterior       = (float) $saldo->saldo;
            $saldo->saldo        -= $monto;
            $saldo->save();

            // Registrar movimiento detallado
            $movimiento = Movimiento::create([
                'usuario_id'     => $usuario->id,
                'tipo'           => 'pago',
                'monto'          => $monto,
                'estado'         => 'exitosa',
                'modulo'         => $modulo,
                'concepto'       => $concepto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo'    => (float) $saldo->saldo,
                'referencia_type' => null,
                'referencia_id'   => null,
            ]);

            return [
                'movimiento'  => $movimiento,
                'monto'       => $monto,
                'modulo'      => $config['label'],
                'saldo_nuevo' => (float) $saldo->saldo,
                'concepto'    => $concepto,
            ];
        });
    }

    /**
     * Registra una consulta de acceso (sin cargo al saldo).
     * Se guarda como movimiento de tipo 'pago' con monto $0 para tener historial completo.
     */
    private function registrarAcceso(Usuario $usuario, array $config): array
    {
        $saldo = Saldo::where('usuario_id', $usuario->id)
            ->firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['saldo' => 0]
            );

        $movimiento = Movimiento::create([
            'usuario_id'      => $usuario->id,
            'tipo'            => 'pago',
            'monto'           => 0,
            'estado'          => 'exitosa',
            'modulo'          => 'acceso',
            'concepto'        => 'Consulta de acceso al campus',
            'saldo_anterior'  => (float) $saldo->saldo,
            'saldo_nuevo'     => (float) $saldo->saldo,
            'referencia_type' => null,
            'referencia_id'   => null,
        ]);

        return [
            'movimiento'  => $movimiento,
            'monto'       => 0,
            'modulo'      => $config['label'],
            'saldo_nuevo' => (float) $saldo->saldo,
            'concepto'    => 'Consulta de acceso al campus',
        ];
    }

    /**
     * Obtiene el historial de movimientos de un usuario con soporte de filtros y paginación.
     *
     * @param  Usuario $usuario
     * @param  array   $filtros  ['tipo' => 'pago|recarga', 'modulo' => 'cafeteria|...', 'per_page' => 15]
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function historial(Usuario $usuario, array $filtros = [])
    {
        $query = Movimiento::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at');

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['modulo'])) {
            $query->where('modulo', $filtros['modulo']);
        }

        $porPagina = (int) ($filtros['per_page'] ?? 15);

        return $query->paginate($porPagina);
    }
}
