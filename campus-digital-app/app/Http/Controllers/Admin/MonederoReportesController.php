<?php

namespace App\Http\Controllers\Admin;

use App\Services\MonederoExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

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
        // Si no se proporcionó usuario_id, renderizamos la vista de reportes
        // para que el admin pueda seleccionar usuario y rango en la UI.
        if (! $request->has('usuario_id')) {
            return Inertia::render('Admin/Monedero/Reportes', [
                'usuarios' => \App\Models\Usuario::all(['id', 'nombre']),
                'reporte' => null,
                'tipo' => 'estado-cuenta',
            ]);
        }

        $request->validate([
            'usuario_id' => 'required|integer',
            'desde' => 'date',
            'hasta' => 'date',
        ]);

        $desde = $request->desde ? Carbon::parse($request->desde) : now()->subDays(30);
        $hasta = $request->hasta ? Carbon::parse($request->hasta) : now();

        return Inertia::render('Admin/Monedero/Reportes', [
            'usuarios' => \App\Models\Usuario::all(['id', 'nombre']),
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
            'usuarios' => \App\Models\Usuario::all(['id', 'nombre']),
            'reporte' => $this->exportService->generarReporteMovimientos(
                $request->usuario_id,
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
            'usuarios' => \App\Models\Usuario::all(['id', 'nombre']),
            'reporte' => $this->exportService->generarReporteUsoPorCategoria(
                $request->usuario_id,
                $desde,
                $hasta,
                $request->modulo
            ),
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

        $reporte = $this->exportService->generarEstadoCuenta(
            $request->usuario_id,
            $desde,
            $hasta
        );

        $pdf = \PDF::loadView('pdf.monedero.estado-cuenta', [
            'reporte'      => $reporte,
            'titulo'       => 'Estado de Cuenta - Monedero Digital',
            'fecha'        => now()->format('d/m/Y H:i:s'),
            'generadoPor'  => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        ]);

        return $pdf->download('estado-cuenta_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * GET /admin/monedero/exportes/estado-cuenta/csv
     */
    public function exportEstadoCuentaCSV(Request $request)
    {
        $request->validate(['usuario_id' => 'required']);
        
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
     * GET /admin/monedero/exportes/movimientos/pdf
     */
    public function exportMovimientosPDF(Request $request)
    {
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
            'generadoPor'  => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        ]);

        return $pdf->download('movimientos_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * GET /admin/monedero/exportes/movimientos/csv
     */
    public function exportMovimientosCSV(Request $request)
    {
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
     * GET /admin/monedero/exportes/uso-categoria/pdf
     */
    public function exportUsoCategoriaPDF(Request $request)
    {
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
            'generadoPor'  => auth()->user()->nombre . ' ' . auth()->user()->apellido,
        ]);

        return $pdf->download('uso-categoria_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * GET /admin/monedero/exportes/uso-categoria/csv
     */
    public function exportUsoCategoriaCSV(Request $request)
    {
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
