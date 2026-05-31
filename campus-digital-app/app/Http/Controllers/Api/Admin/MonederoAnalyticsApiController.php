<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\MonederoAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonederoAnalyticsApiController extends Controller
{
    public function __construct(
        private MonederoAnalyticsService $analytics
    ) {}

    /**
     * GET /api/admin/monedero/analytics/dashboard
     * Obtiene datos completos del dashboard
     */
    public function dashboard(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'in:cafeteria,copias,souvenirs,biblioteca,recarga,otro',
        ]);

        $dateRange = [
            'desde' => $request->desde ? Carbon::parse($request->desde)->startOfDay() : now()->subDays(30),
            'hasta' => $request->hasta ? Carbon::parse($request->hasta)->endOfDay() : now(),
        ];

        $modulo = $request->modulo;

        // Obtener KPIs
        $kpis = $this->analytics->getKPIs($dateRange, $modulo);

        // Top usuarios
        $topUsuarios = $this->analytics->getTopUsuarios(10, $dateRange, $modulo);

        // Consumo por módulo
        $consumoModulos = $this->analytics->getConsumoModulos($dateRange);

        // Últimos movimientos
        $ultimosMovimientos = $this->analytics->getUltimosMovimientos(20, $dateRange);

        // Data para gráficos
        $chartsData = $this->analytics->getChartsData($dateRange, $modulo);

        return response()->json([
            'kpis' => $kpis,
            'topUsuarios' => $topUsuarios,
            'consumoModulos' => $consumoModulos,
            'ultimosMovimientos' => $ultimosMovimientos,
            'chartsData' => $chartsData,
        ]);
    }

    /**
     * GET /api/admin/monedero/analytics/top-usuarios
     * Obtiene top N usuarios por consumo
     */
    public function topUsuarios(Request $request)
    {
        $limit = $request->limit ?? 10;
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return response()->json(
            $this->analytics->getTopUsuarios($limit, compact('desde', 'hasta'))
        );
    }

    /**
     * GET /api/admin/monedero/analytics/movimientos-modulo
     * Obtiene consumo por módulo
     */
    public function movimientosPorModulo(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return response()->json(
            $this->analytics->getConsumoModulos(compact('desde', 'hasta'))
        );
    }

    /**
     * GET /api/admin/monedero/analytics/timeseries
     * Obtiene datos de serie temporal
     */
    public function timeseriesData(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return response()->json(
            $this->analytics->getTimeSeriesData(compact('desde', 'hasta'))
        );
    }
}
