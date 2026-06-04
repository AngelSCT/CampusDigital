<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoReporteController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario->hasAnyRole(['administrador'])) {
            return redirect()->route('sin-permiso');
        }

        $query = Pedido::with(['usuario', 'operador'])
            ->when($request->estado,      fn($q, $v) => $q->where('estado', $v))
            ->when($request->modulo,      fn($q, $v) => $q->where('modulo', $v))
            ->when($request->fecha_desde, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pedidos/Reportes', [
            'pedidos' => $query,
            'estados' => Pedido::ESTADOS,
            'modulos' => Pedido::MODULOS,
            'filtros' => $request->only(['estado', 'modulo', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $this->checkAdmin();
        $pedidos  = $this->getPedidosFiltrados($request);
        $filename = 'pedidos_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($pedidos) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel
            fputcsv($h, ['Folio','Usuario','Módulo','Estado','Total','Operador','Fecha','Notas']);
            foreach ($pedidos as $p) {
                fputcsv($h, [
                    $p->numero_folio,
                    $p->usuario?->nombre_completo ?? '-',
                    $p->modulo,
                    $p->estado,
                    number_format($p->total, 2),
                    $p->operador?->nombre_completo ?? '-',
                    $p->created_at?->format('d/m/Y H:i'),
                    $p->notas,
                ]);
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->checkAdmin();
        $pedidos  = $this->getPedidosFiltrados($request);
        $filename = 'pedidos_' . now()->format('Ymd_His') . '.xls';

        // Generamos XLSX manualmente con XML (sin dependencia extra)
        $rows = [];
        $rows[] = ['Folio','Usuario','Módulo','Estado','Total','Operador','Fecha','Notas'];
        foreach ($pedidos as $p) {
            $rows[] = [
                $p->numero_folio,
                $p->usuario?->nombre_completo ?? '-',
                $p->modulo,
                $p->estado,
                number_format($p->total, 2),
                $p->operador?->nombre_completo ?? '-',
                $p->created_at?->format('d/m/Y H:i'),
                $p->notas,
            ];
        }

        $xml = $this->buildXlsx($rows);

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $this->checkAdmin();
        $pedidos = $this->getPedidosFiltrados($request);

        $pdf = Pdf::loadView('exports.pedidos-pdf', [
            'pedidos'     => $pedidos,
            'fecha'       => now()->format('d/m/Y H:i'),
            'filtros'     => $request->only(['estado', 'modulo', 'fecha_desde', 'fecha_hasta']),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('pedidos_' . now()->format('Ymd_His') . '.pdf');
    }

    // ── Helpers ─────────────────────────────────────────────────
    private function checkAdmin()
    {
        if (!Auth::user()->hasAnyRole(['administrador'])) abort(403);
    }

    private function getPedidosFiltrados(Request $request)
    {
        return Pedido::with(['usuario', 'operador'])
            ->when($request->estado,      fn($q, $v) => $q->where('estado', $v))
            ->when($request->modulo,      fn($q, $v) => $q->where('modulo', $v))
            ->when($request->fecha_desde, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->get();
    }

    private function buildXlsx(array $rows): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Styles>
            <Style ss:ID="header">
                <Font ss:Bold="1" ss:Color="#FFFFFF"/>
                <Interior ss:Color="#6C3FC5" ss:Pattern="Solid"/>
            </Style>
            <Style ss:ID="even">
                <Interior ss:Color="#1E1E2E" ss:Pattern="Solid"/>
                <Font ss:Color="#FFFFFF"/>
            </Style>
            <Style ss:ID="odd">
                <Interior ss:Color="#16213E" ss:Pattern="Solid"/>
                <Font ss:Color="#CCCCCC"/>
            </Style>
        </Styles>' . "\n";
        $xml .= '<Worksheet ss:Name="Pedidos"><Table>' . "\n";

        foreach ($rows as $i => $row) {
            $style = $i === 0 ? 'header' : ($i % 2 === 0 ? 'even' : 'odd');
            $xml  .= "<Row>\n";
            foreach ($row as $cell) {
                $cell = htmlspecialchars((string)$cell, ENT_XML1);
                $xml .= "<Cell ss:StyleID=\"$style\"><Data ss:Type=\"String\">$cell</Data></Cell>\n";
            }
            $xml .= "</Row>\n";
        }

        $xml .= '</Table></Worksheet></Workbook>';
        return $xml;
    }
}