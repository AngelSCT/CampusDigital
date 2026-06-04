<?php

namespace App\Http\Controllers\Admin\Cart;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Services\CartReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartReportController extends Controller
{
    public function __construct(private readonly CartReportService $service) {}

    // ─── Consumos por período ─────────────────────────────────────────────────

    public function consumosPorPeriodo(Request $request): Response|StreamedResponse
    {
        $validated = $request->validate([
            'desde'       => ['nullable', 'date'],
            'hasta'       => ['nullable', 'date'],
            'modulo_slug' => ['nullable', 'string'],
        ]);

        $desde = isset($validated['desde']) ? Carbon::parse($validated['desde']) : now()->subDays(30);
        $hasta = isset($validated['hasta']) ? Carbon::parse($validated['hasta']) : now();

        $datos = $this->service->consumosPorPeriodo($desde, $hasta, $validated['modulo_slug'] ?? null);

        if ($request->get('format') === 'csv') {
            return $this->csvResponse('consumos_periodo', ['carrito_uuid', 'estado', 'total', 'usuario_ref', 'modulo', 'confirmed_at'], $datos['detalle']);
        }

        return Inertia::render('Admin/Cart/Reportes/ConsumosPorPeriodo', [
            'datos'   => $datos,
            'filtros' => ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'modulo_slug' => $validated['modulo_slug'] ?? null],
        ]);
    }

    // ─── Carritos abandonados ─────────────────────────────────────────────────

    public function carritosAbandonados(Request $request): Response|StreamedResponse
    {
        $validated = $request->validate([
            'desde'       => ['nullable', 'date'],
            'hasta'       => ['nullable', 'date'],
            'modulo_slug' => ['nullable', 'string'],
        ]);

        $desde = isset($validated['desde']) ? Carbon::parse($validated['desde']) : now()->subDays(30);
        $hasta = isset($validated['hasta']) ? Carbon::parse($validated['hasta']) : now();

        $datos = $this->service->carritosAbandonados($desde, $hasta, $validated['modulo_slug'] ?? null);

        if ($request->get('format') === 'csv') {
            $filas = array_merge(
                $datos['lista'],
                array_map(fn($r) => array_merge($r, ['estado' => 'abierto_vencido']), $datos['abiertos_vencidos'])
            );
            return $this->csvResponse('carritos_abandonados', ['carrito_uuid', 'usuario_ref', 'modulo', 'estado', 'total', 'created_at', 'expira_at'], $filas);
        }

        return Inertia::render('Admin/Cart/Reportes/CarritosAbandonados', [
            'datos'   => $datos,
            'filtros' => ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString()],
        ]);
    }

    // ─── Consumo por categoría ────────────────────────────────────────────────

    public function consumoPorCategoria(Request $request): Response|StreamedResponse
    {
        $validated = $request->validate([
            'desde'          => ['nullable', 'date'],
            'hasta'          => ['nullable', 'date'],
            'categoria_slug' => ['nullable', 'string'],
        ]);

        $desde = isset($validated['desde']) ? Carbon::parse($validated['desde']) : now()->subDays(30);
        $hasta = isset($validated['hasta']) ? Carbon::parse($validated['hasta']) : now();

        $datos = $this->service->consumoPorCategoria($desde, $hasta, $validated['categoria_slug'] ?? null);

        if ($request->get('format') === 'csv') {
            return $this->csvResponse('consumo_categoria', ['categoria_slug', 'categoria_nombre', 'cantidad_items', 'total_unidades', 'total_consumido'], $datos);
        }

        return Inertia::render('Admin/Cart/Reportes/ConsumoPorCategoria', [
            'datos'   => $datos,
            'filtros' => ['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'categoria_slug' => $validated['categoria_slug'] ?? null],
        ]);
    }

    // ─── Helper CSV ───────────────────────────────────────────────────────────

    private function csvResponse(string $nombre, array $columnas, array $filas): StreamedResponse
    {
        $filename = "{$nombre}_" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($columnas, $filas) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columnas);
            foreach ($filas as $fila) {
                fputcsv($out, array_values($fila));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
