<?php

namespace App\Services;

use App\Models\SaldoMovimiento;
use App\Models\SaldoMonedero;
use App\Models\SaldoRegla;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonederoValidationService
{
    /**
     * Valida si un usuario tiene saldo suficiente
     */
    public function validarSaldo(int $usuarioId, float $monto): bool
    {
        $monedero = SaldoMonedero::where('usuario_id', $usuarioId)->first();
        
        if (!$monedero) {
            return false;
        }

        return $monedero->saldo_disponible >= $monto;
    }

    /**
     * Valida todas las reglas aplicables a un movimiento
     */
    public function validarReglas(int $usuarioId, float $monto, ?string $modulo = null, string $tipo = 'cargo'): array
    {
        $resultado = [
            'valido' => true,
            'errores' => [],
            'advertencias' => [],
        ];

        if ($tipo !== 'cargo') {
            return $resultado;
        }

        // Obtener reglas aplicables
        $reglas = SaldoRegla::activas()
            ->porUsuario($usuarioId)
            ->where(function ($q) use ($modulo) {
                $q->whereNull('modulo')
                  ->orWhere('modulo', $modulo);
            })
            ->get();

        foreach ($reglas as $regla) {
            switch ($regla->tipo_limite) {
                case 'diario':
                    $validacion = $this->verificarLimiteDiario($usuarioId, $monto, $modulo, $regla);
                    break;
                case 'semanal':
                    $validacion = $this->verificarLimiteSemanal($usuarioId, $monto, $modulo, $regla);
                    break;
                case 'mensual':
                    $validacion = $this->verificarLimiteMensual($usuarioId, $monto, $modulo, $regla);
                    break;
                default:
                    continue;
            }

            if (!$validacion['valido']) {
                $resultado['valido'] = false;
                $resultado['errores'][] = $validacion['mensaje'];
            }
        }

        // Validar saldo suficiente
        if (!$this->validarSaldo($usuarioId, $monto)) {
            $resultado['valido'] = false;
            $resultado['errores'][] = 'Saldo insuficiente para realizar la transacción.';
        }

        return $resultado;
    }

    /**
     * Verifica límite diario
     */
    public function verificarLimiteDiario(int $usuarioId, float $monto, ?string $modulo, SaldoRegla $regla): array
    {
        $hoy = now()->startOfDay();
        $consumidoHoy = SaldoMovimiento::where('usuario_id', $usuarioId)
            ->where('tipo', 'cargo')
            ->where('created_at', '>=', $hoy)
            ->when($modulo, fn($q) => $q->where('modulo', $modulo))
            ->sum('monto');

        $totalSiConsume = $consumidoHoy + $monto;

        if ($totalSiConsume > $regla->monto_limite) {
            return [
                'valido' => false,
                'mensaje' => "Límite diario excedido. Limit: {$regla->monto_limite}, Actual: {$consumidoHoy}, Solicitud: {$monto}",
            ];
        }

        return ['valido' => true];
    }

    /**
     * Verifica límite semanal
     */
    public function verificarLimiteSemanal(int $usuarioId, float $monto, ?string $modulo, SaldoRegla $regla): array
    {
        $inicioSemana = now()->startOfWeek();
        $finSemana = now()->endOfWeek();

        $consumidoSemana = SaldoMovimiento::where('usuario_id', $usuarioId)
            ->where('tipo', 'cargo')
            ->whereBetween('created_at', [$inicioSemana, $finSemana])
            ->when($modulo, fn($q) => $q->where('modulo', $modulo))
            ->sum('monto');

        $totalSiConsume = $consumidoSemana + $monto;

        if ($totalSiConsume > $regla->monto_limite) {
            return [
                'valido' => false,
                'mensaje' => "Límite semanal excedido. Límite: {$regla->monto_limite}, Actual: {$consumidoSemana}, Solicitud: {$monto}",
            ];
        }

        return ['valido' => true];
    }

    /**
     * Verifica límite mensual
     */
    public function verificarLimiteMensual(int $usuarioId, float $monto, ?string $modulo, SaldoRegla $regla): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $consumidoMes = SaldoMovimiento::where('usuario_id', $usuarioId)
            ->where('tipo', 'cargo')
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->when($modulo, fn($q) => $q->where('modulo', $modulo))
            ->sum('monto');

        $totalSiConsume = $consumidoMes + $monto;

        if ($totalSiConsume > $regla->monto_limite) {
            return [
                'valido' => false,
                'mensaje' => "Límite mensual excedido. Límite: {$regla->monto_limite}, Actual: {$consumidoMes}, Solicitud: {$monto}",
            ];
        }

        return ['valido' => true];
    }

    /**
     * Obtiene consumo actual del usuario
     */
    public function obtenerConsumoActual(int $usuarioId, string $periodo = 'diario'): float
    {
        $query = SaldoMovimiento::where('usuario_id', $usuarioId)
            ->where('tipo', 'cargo');

        switch ($periodo) {
            case 'diario':
                $query->where('created_at', '>=', now()->startOfDay());
                break;
            case 'semanal':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'mensual':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
        }

        return $query->sum('monto');
    }

    /**
     * Obtiene límites aplicables a un usuario
     */
    public function obtenerLimitesAplicables(int $usuarioId, ?string $modulo = null): array
    {
        $reglas = SaldoRegla::activas()
            ->porUsuario($usuarioId)
            ->when($modulo, fn($q) => $q->where(function ($q2) use ($modulo) {
                $q2->whereNull('modulo')->orWhere('modulo', $modulo);
            }))
            ->get();

        $limites = [];
        foreach ($reglas as $regla) {
            $consumo = $this->obtenerConsumoActual($usuarioId, str_replace(['diario', 'semanal', 'mensual'], ['daily', 'weekly', 'monthly'], $regla->tipo_limite));
            
            $limites[] = [
                'tipo' => $regla->tipo_limite,
                'limite' => $regla->monto_limite,
                'consumido' => $consumo,
                'disponible' => $regla->monto_limite - $consumo,
                'porcentaje_uso' => round(($consumo / $regla->monto_limite) * 100, 2),
            ];
        }

        return $limites;
    }
}
