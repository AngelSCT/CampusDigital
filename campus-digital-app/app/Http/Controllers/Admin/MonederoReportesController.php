<?php

namespace App\Http\Controllers\Admin;

use App\Services\MonederoExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonederoReportesController extends Controller
{
    public function __construct(
        private MonederoExportService $exportService
    ) {}

    /**
     * Renderiza la página de reportes
     * GET /admin/monedero/reportes
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/Monedero/Reportes', [
            'usuarios' => \App\Models\Usuario::all(['id', 'nombre']),
        ]);
    }

    /**
     * GET /admin/monedero/reportes/estado-cuenta
     */
    public function estadoCuenta(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|integer',
            'desde' => 'date',
            'hasta' => 'date',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return Inertia::render('Admin/Monedero/Reportes', [
            'reporte' => $this->exportService->generarEstadoCuenta(
                $request->usuario_id,
                $desde,
                $hasta
            ),
            'tipo' => 'estado-cuenta',
        ]);
    }

    /**
     * GET /admin/monedero/reportes/movimientos
     */
    public function movimientos(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return Inertia::render('Admin/Monedero/Reportes', [
            'reporte' => $this->exportService->generarReporteMovimientos(
                $desde,
                $hasta,
                $request->modulo
            ),
            'tipo' => 'movimientos',
        ]);
    }

    /**
     * GET /admin/monedero/reportes/uso-categoria
     */
    public function usoCategoria(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return Inertia::render('Admin/Monedero/Reportes', [
            'reporte' => $this->exportService->generarReporteUsoPorCategoria($desde, $hasta),
            'tipo' => 'uso-categoria',
        ]);
    }

    /**
     * GET /admin/monedero/exportes/estado-cuenta/pdf
     */
    public function exportEstadoCuentaPDF(Request $request)
    {
        $request->validate(['usuario_id' => 'required']);
        
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        // TODO: Implementar generación de PDF
        return response()->json(['mensaje' => 'PDF generado']);
    }

    /**
     * GET /admin/monedero/exportes/estado-cuenta/csv
     */
    public function exportEstadoCuentaCSV(Request $request)
    {
        $request->validate(['usuario_id' => 'required']);
        
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        // TODO: Implementar generación de CSV
        return response()->json(['mensaje' => 'CSV generado']);
    }

    /**
     * GET /admin/monedero/exportes/movimientos/pdf
     */
    public function exportMovimientosPDF(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        // TODO: Implementar generación de PDF
        return response()->json(['mensaje' => 'PDF generado']);
    }

    /**
     * GET /admin/monedero/exportes/movimientos/csv
     */
    public function exportMovimientosCSV(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        // TODO: Implementar generación de CSV
        return response()->json(['mensaje' => 'CSV generado']);
    }

    /**
     * GET /admin/monedero/exportes/uso-categoria/pdf
     */
    public function exportUsoCategoriaaPDF(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        // TODO: Implementar generación de PDF
        return response()->json(['mensaje' => 'PDF generado']);
    }

    /**
     * GET /admin/monedero/exportes/uso-categoria/csv
     */
    public function exportUsoCategoriaCSV(Request $request)
    {
        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        // TODO: Implementar generación de CSV
        return response()->json(['mensaje' => 'CSV generado']);
    }
}
