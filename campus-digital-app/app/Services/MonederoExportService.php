<?php

namespace App\Services;

use App\Models\SaldoMovimiento;
use App\Models\SaldoMonedero;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonederoExportService
{
    /**
     * Genera datos para exportar reporte de estado de cuenta
     */
    public function generarEstadoCuenta(int $usuarioId, ?Carbon $desde = null, ?Carbon $hasta = null): array
    {
        $desde = $desde ?? now()->subDays(30);
        $hasta = $hasta ?? now();

        $usuario = Usuario::find($usuarioId);
        $movimientos = SaldoMovimiento::where('usuario_id', $usuarioId)
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderByDesc('created_at')
            ->get();

        $saldoMonedero = SaldoMonedero::where('usuario_id', $usuarioId)->first();

        return [
            'usuario' => $usuario,
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'movimientos' => $movimientos,
            'saldo_actual' => $saldoMonedero?->saldo_disponible ?? 0,
            'total_cargos' => $movimientos->where('tipo', 'cargo')->sum('monto'),
            'total_abonos' => $movimientos->where('tipo', 'abono')->sum('monto'),
            'cantidad_movimientos' => $movimientos->count(),
        ];
    }

    /**
     * Genera datos para exportar reporte de movimientos
     */
    public function generarReporteMovimientos(?Carbon $desde = null, ?Carbon $hasta = null, ?string $modulo = null): array
    {
        $desde = $desde ?? now()->subDays(30);
        $hasta = $hasta ?? now();

        $query = SaldoMovimiento::with('usuario')
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderByDesc('created_at');

        if ($modulo) {
            $query->where('modulo', $modulo);
        }

        $movimientos = $query->get();

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'modulo' => $modulo,
            'movimientos' => $movimientos,
            'resumen' => [
                'total_movimientos' => $movimientos->count(),
                'total_cargos' => $movimientos->where('tipo', 'cargo')->sum('monto'),
                'total_abonos' => $movimientos->where('tipo', 'abono')->sum('monto'),
                'saldo_neto' => $movimientos->where('tipo', 'abono')->sum('monto') - $movimientos->where('tipo', 'cargo')->sum('monto'),
            ],
        ];
    }

    /**
     * Genera datos para exportar reporte de uso por categoría
     */
    public function generarReporteUsoPorCategoria(?Carbon $desde = null, ?Carbon $hasta = null): array
    {
        $desde = $desde ?? now()->subDays(30);
        $hasta = $hasta ?? now();

        $categorias = SaldoMovimiento::query()
            ->select(
                'modulo',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(CASE WHEN tipo = \'cargo\' THEN monto ELSE 0 END) as total_cargo'),
                DB::raw('SUM(CASE WHEN tipo = \'abono\' THEN monto ELSE 0 END) as total_abono'),
                DB::raw('COUNT(DISTINCT usuario_id) as usuarios_unicos')
            )
            ->whereBetween('created_at', [$desde, $hasta])
            ->groupBy('modulo')
            ->orderByDesc('total_cargo')
            ->get();

        $totalGeneral = $categorias->sum('total_cargo');

        $categorias = $categorias->map(function ($cat) use ($totalGeneral) {
            $cat->porcentaje = $totalGeneral > 0 ? round(($cat->total_cargo / $totalGeneral) * 100, 2) : 0;
            return $cat;
        });

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'categorias' => $categorias,
            'total_general' => $totalGeneral,
        ];
    }

    /**
     * Formatea movimientos para CSV
     */
    public function formatearParaCSV(array $movimientos): array
    {
        return array_map(function ($mov) {
            return [
                'Fecha' => $mov['created_at'] ?? '',
                'Usuario' => $mov['usuario']['nombre'] ?? '',
                'Tipo' => $mov['tipo'] ?? '',
                'Módulo' => $mov['modulo'] ?? '',
                'Concepto' => $mov['concepto'] ?? '',
                'Monto' => $mov['monto'] ?? 0,
                'Saldo Nuevo' => $mov['saldo_nuevo'] ?? 0,
            ];
        }, $movimientos);
    }

    /**
     * Genera encabezados CSV
     */
    public function generarEncabezadosCSV(): array
    {
        return [
            'Fecha',
            'Usuario',
            'Tipo',
            'Módulo',
            'Concepto',
            'Monto',
            'Saldo Nuevo',
        ];
    }
}
