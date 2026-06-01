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
            'usuario_id' => 'integer|exists:usuario,id|nullable',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteMovimientos(
            $request->usuario_id,
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
            'usuario_id' => 'integer|exists:usuario,id|nullable',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $datos = $this->exportService->generarReporteUsoPorCategoria(
            $request->usuario_id,
            $desde,
            $hasta,
            $request->modulo
        );

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

        $reporte = $this->exportService->generarEstadoCuenta(
            $request->usuario_id,
            $desde,
            $hasta
        );

        $pdf = \PDF::loadView('pdf.monedero.estado-cuenta', [
            'reporte'      => $reporte,
            'titulo'       => 'Estado de Cuenta - Monedero Digital',
            'fecha'        => now()->format('d/m/Y H:i:s'),
            'generadoPor'  => 'API',
        ]);

        return $pdf->download('estado-cuenta_' . now()->format('Y-m-d_His') . '.pdf');
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

        $reporte = $this->exportService->generarEstadoCuenta(
            $request->usuario_id,
            $desde,
            $hasta
        );

        $csvData = "Fecha,Tipo,Módulo,Concepto,Monto,Saldo Nuevo\n";
        foreach ($reporte['movimientos'] as $mov) {
            $tipo = $mov->tipo === 'abono' ? 'Abono' : 'Cargo';
            $fecha = $mov->created_at instanceof \Carbon\Carbon
                ? $mov->created_at->format('d/m/Y H:i')
                : $mov->created_at;
            $csvData .= "{$fecha},{$tipo},{$mov->modulo},\"{$mov->concepto}\",{$mov->monto},{$mov->saldo_nuevo}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="estado-cuenta_' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * GET /api/admin/monedero/exportes/movimientos/pdf
     */
    public function exportMovimientosPDF(Request $request)
    {
        $request->validate([
            'usuario_id' => 'integer|exists:usuario,id|nullable',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $reporte = $this->exportService->generarReporteMovimientos(
            $request->usuario_id,
            $desde,
            $hasta,
            $request->modulo
        );

        $pdf = \PDF::loadView('pdf.monedero.movimientos', [
            'reporte'      => $reporte,
            'titulo'       => 'Reporte de Movimientos - Monedero Digital',
            'fecha'        => now()->format('d/m/Y H:i:s'),
            'generadoPor'  => 'API',
        ]);

        return $pdf->download('movimientos_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * GET /api/admin/monedero/exportes/movimientos/csv
     */
    public function exportMovimientosCSV(Request $request)
    {
        $request->validate([
            'usuario_id' => 'integer|exists:usuario,id|nullable',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $reporte = $this->exportService->generarReporteMovimientos(
            $request->usuario_id,
            $desde,
            $hasta,
            $request->modulo
        );

        $csvData = "Fecha,Usuario,Tipo,Módulo,Concepto,Monto\n";
        foreach ($reporte['movimientos'] as $mov) {
            $tipo = $mov->tipo === 'abono' ? 'Abono' : 'Cargo';
            $usuario = $mov->usuario->nombre ?? 'N/A';
            $fecha = $mov->created_at instanceof \Carbon\Carbon
                ? $mov->created_at->format('d/m/Y H:i')
                : $mov->created_at;
            $csvData .= "{$fecha},{$usuario},{$tipo},{$mov->modulo},\"{$mov->concepto}\",{$mov->monto}\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="movimientos_' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * GET /api/admin/monedero/exportes/uso-categoria/pdf
     */
    public function exportUsoCategoriaPDF(Request $request)
    {
        $request->validate([
            'usuario_id' => 'integer|exists:usuario,id|nullable',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $reporte = $this->exportService->generarReporteUsoPorCategoria(
            $request->usuario_id,
            $desde,
            $hasta,
            $request->modulo
        );

        $pdf = \PDF::loadView('pdf.monedero.uso-categoria', [
            'reporte'      => $reporte,
            'titulo'       => 'Uso de Saldo por Categoría - Monedero Digital',
            'fecha'        => now()->format('d/m/Y H:i:s'),
            'generadoPor'  => 'API',
        ]);

        return $pdf->download('uso-categoria_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * GET /api/admin/monedero/exportes/uso-categoria/csv
     */
    public function exportUsoCategoriaCSV(Request $request)
    {
        $request->validate([
            'usuario_id' => 'integer|exists:usuario,id|nullable',
            'desde' => 'date',
            'hasta' => 'date|after_or_equal:desde',
            'modulo' => 'string|nullable',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        $reporte = $this->exportService->generarReporteUsoPorCategoria(
            $request->usuario_id,
            $desde,
            $hasta,
            $request->modulo
        );

        $csvData = "Categoría,Total Cargo,Total Abono,Transacciones,Usuarios Únicos,Porcentaje\n";
        foreach ($reporte['categorias'] as $cat) {
            $csvData .= "{$cat->modulo},{$cat->total_cargo},{$cat->total_abono},{$cat->cantidad},{$cat->usuarios_unicos},{$cat->porcentaje}%\n";
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="uso-categoria_' . now()->format('Y-m-d') . '.csv"');
    }
}
