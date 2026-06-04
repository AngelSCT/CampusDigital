<?php

namespace App\Services;

use App\Models\SaldoMovimiento;
use App\Models\SaldoMonedero;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonederoAnalyticsService
{
    /**
     * Obtiene los KPIs principales del monedero
     */
    public function getKPIs(array $dateRange, ?string $modulo = null): array
    {
        $query = SaldoMovimiento::whereBetween('created_at', [
            $dateRange['desde'],
            $dateRange['hasta']
        ]);

        if ($modulo) {
            $query->where('modulo', $modulo);
        }

        $totalAbonos = $query->clone()->where('tipo', 'abono')->sum('monto');
        $totalCargos = $query->clone()->where('tipo', 'cargo')->sum('monto');
        $saldoTotal = SaldoMonedero::whereNull('deleted_at')->sum('saldo_disponible');
        $usuariosActivos = $query->clone()->distinct('usuario_id')->count('usuario_id');

        return [
            'saldoTotal' => $saldoTotal,
            'totalAbonos' => $totalAbonos,
            'totalCargos' => $totalCargos,
            'usuariosActivos' => $usuariosActivos,
        ];
    }

    /**
     * Top N usuarios por consumo
     */
    public function getTopUsuarios(int $limit = 10, array $dateRange = [], ?string $modulo = null)
    {
        $query = SaldoMovimiento::query()
            ->select(
                'usuario_id',
                DB::raw('usuario.nombre'),
                DB::raw('SUM(CASE WHEN tipo = \'cargo\' THEN monto ELSE 0 END) as total_consumo'),
                DB::raw('COUNT(*) as cantidad_movimientos')
            )
            ->join('usuario', 'usuario.id', '=', 'saldo_movimiento.usuario_id')
            ->where('saldo_movimiento.tipo', 'cargo')
            ->groupBy('usuario_id', 'usuario.nombre')
            ->orderByDesc('total_consumo')
            ->limit($limit);

        if (!empty($dateRange)) {
            $query->whereBetween('saldo_movimiento.created_at', [
                $dateRange['desde'],
                $dateRange['hasta']
            ]);
        }

        if ($modulo) {
            $query->where('saldo_movimiento.modulo', $modulo);
        }

        return $query->get()->toArray();
    }

    /**
     * Consumo por categoría/módulo
     */
    public function getConsumoModulos(array $dateRange = []): array
    {
        $query = SaldoMovimiento::query()
            ->select(
                'modulo',
                DB::raw('SUM(CASE WHEN tipo = \'cargo\' THEN monto ELSE 0 END) as monto'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->where('tipo', 'cargo')
            ->groupBy('modulo');

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', [
                $dateRange['desde'],
                $dateRange['hasta']
            ]);
        }

        $resultados = $query->get()->toArray();
        $total = array_sum(array_column($resultados, 'monto'));

        return array_map(function ($item) use ($total) {
            $item['porcentaje'] = $total > 0 ? round(($item['monto'] / $total) * 100, 2) : 0;
            return $item;
        }, $resultados);
    }

    /**
     * Últimos N movimientos
     */
    public function getUltimosMovimientos(int $limit = 20, array $dateRange = [])
    {
        $query = SaldoMovimiento::with('usuario')
            ->orderByDesc('created_at')
            ->limit($limit);

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', [
                $dateRange['desde'],
                $dateRange['hasta']
            ]);
        }

        return $query->get();
    }

    /**
     * Data para gráficos (Charts.js)
     */
    public function getChartsData(array $dateRange, ?string $modulo = null): array
    {
        // Datos de pie chart (cargos vs abonos)
        $totalCargos = SaldoMovimiento::whereBetween('created_at', [
            $dateRange['desde'],
            $dateRange['hasta']
        ])->where('tipo', 'cargo')
        ->sum('monto');

        $totalAbonos = SaldoMovimiento::whereBetween('created_at', [
            $dateRange['desde'],
            $dateRange['hasta']
        ])->where('tipo', 'abono')
        ->sum('monto');

        // Datos de línea temporal (saldo diario)
        $timeseriesData = $this->getTimeSeriesData($dateRange);

        return [
            'totalCargos' => $totalCargos,
            'totalAbonos' => $totalAbonos,
            'timeseriesFechas' => $timeseriesData['fechas'],
            'timeseriesSaldos' => $timeseriesData['saldos'],
        ];
    }

    /**
     * Series temporal de saldo (para gráfico de línea)
     */
    public function getTimeSeriesData(array $dateRange = []): array
    {
        $desde = $dateRange['desde'] ?? now()->subDays(30);
        $hasta = $dateRange['hasta'] ?? now();

        // Generar fechas diarias
        $fechas = [];
        $current = clone $desde;
        while ($current <= $hasta) {
            $fechas[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Obtener saldo total por día (acumulativo)
        $saldosPorDia = DB::select(
            'SELECT DATE(created_at) as fecha, SUM(monto) as total_movimientos 
             FROM saldo_movimiento 
             WHERE created_at BETWEEN ? AND ?
             AND tipo = \'cargo\'
             GROUP BY DATE(created_at)
             ORDER BY fecha ASC',
            [$desde, $hasta]
        );

        $saldoMap = [];
        $saldoActual = SaldoMonedero::sum('saldo_disponible');

        foreach ($saldosPorDia as $item) {
            $saldoMap[$item->fecha] = $item->total_movimientos;
        }

        // Construir serie
        $saldos = [];
        $saldoAcumulado = $saldoActual;
        foreach ($fechas as $fecha) {
            if (isset($saldoMap[$fecha])) {
                $saldoAcumulado -= $saldoMap[$fecha];
            }
            $saldos[] = max(0, $saldoAcumulado);
        }

        return [
            'fechas' => $fechas,
            'saldos' => $saldos,
        ];
    }
}
