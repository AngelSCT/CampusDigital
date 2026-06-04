<?php

namespace App\Http\Controllers\Admin;

use App\Services\MonederoAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class MonederoAnalyticsController extends Controller
{
    public function __construct(
        private MonederoAnalyticsService $analytics
    ) {}

    /**
     * Renderiza el dashboard del monedero
     * GET /admin/monedero/dashboard
     */
    public function index(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();
        $modulo = $request->modulo;

        $dateRange = [
            'desde' => $desde->startOfDay(),
            'hasta' => $hasta->endOfDay(),
        ];

        // Obtener datos
        $kpis = $this->analytics->getKPIs($dateRange, $modulo);
        $topUsuarios = $this->analytics->getTopUsuarios(10, $dateRange, $modulo);
        $consumoModulos = $this->analytics->getConsumoModulos($dateRange);
        $ultimosMovimientos = $this->analytics->getUltimosMovimientos(20, $dateRange);
        $chartsData = $this->analytics->getChartsData($dateRange, $modulo);

        return Inertia::render('Admin/Monedero/Dashboard', [
            'kpis' => $kpis,
            'topUsuarios' => $topUsuarios,
            'consumoModulos' => $consumoModulos,
            'ultimosMovimientos' => $ultimosMovimientos,
            'chartsData' => $chartsData,
            'filtros' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
                'modulo' => $modulo,
            ],
        ]);
    }
}
