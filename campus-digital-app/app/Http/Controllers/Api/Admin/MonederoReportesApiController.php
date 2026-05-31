<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\MonederoExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonederoReportesApiController extends Controller
{
    public function __construct(
        private MonederoExportService $exportService
    ) {}

    /**
     * GET /api/admin/monedero/reportes/estado-cuenta
     */
    public function estadoCuenta(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarEstadoCuenta(
            $request->usuario_id,
            $desde,
            $hasta
        );

        return response()->json($datos);
    }

    /**
     * GET /api/admin/monedero/reportes/movimientos
     */
    public function movimientos(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteMovimientos(
            $desde,
            $hasta,
            $request->modulo
        );

        return response()->json($datos);
    }

    /**
     * GET /api/admin/monedero/reportes/uso-categoria
     */
    public function usoCategoria(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteUsoPorCategoria($desde, $hasta);

        return response()->json($datos);
    }

    /**
     * GET /api/admin/monedero/exportes/estado-cuenta/pdf
     */
    public function exportEstadoCuentaPDF(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarEstadoCuenta(
            $request->usuario_id,
            $desde,
            $hasta
        );

        // TODO: Implementar generación de PDF con DomPDF
        return response()->json(['mensaje' => 'PDF generado', 'datos' => $datos]);
    }

    /**
     * GET /api/admin/monedero/exportes/estado-cuenta/csv
     */
    public function exportEstadoCuentaCSV(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:usuario,id',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarEstadoCuenta(
            $request->usuario_id,
            $desde,
            $hasta
        );

        // TODO: Implementar generación de CSV
        return response()->json(['mensaje' => 'CSV generado', 'datos' => $datos]);
    }

    /**
     * GET /api/admin/monedero/exportes/movimientos/pdf
     */
    public function exportMovimientosPDF(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteMovimientos(
            $desde,
            $hasta,
            $request->modulo
        );

        // TODO: Implementar generación de PDF
        return response()->json(['mensaje' => 'PDF generado', 'datos' => $datos]);
    }

    /**
     * GET /api/admin/monedero/exportes/movimientos/csv
     */
    public function exportMovimientosCSV(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteMovimientos(
            $desde,
            $hasta,
            $request->modulo
        );

        // TODO: Implementar generación de CSV
        return response()->json(['mensaje' => 'CSV generado', 'datos' => $datos]);
    }

    /**
     * GET /api/admin/monedero/exportes/uso-categoria/pdf
     */
    public function exportUsoCategoriaaPDF(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteUsoPorCategoria($desde, $hasta);

        // TODO: Implementar generación de PDF
        return response()->json(['mensaje' => 'PDF generado', 'datos' => $datos]);
    }

    /**
     * GET /api/admin/monedero/exportes/uso-categoria/csv
     */
    public function exportUsoCategoriaCSV(Request $request)
    {
        $request->validate([
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteUsoPorCategoria($desde, $hasta);

        // TODO: Implementar generación de CSV
        return response()->json(['mensaje' => 'CSV generado', 'datos' => $datos]);
    }
}
