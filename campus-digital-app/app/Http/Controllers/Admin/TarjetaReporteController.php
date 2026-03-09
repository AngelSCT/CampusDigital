<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarjetaLectura;
use App\Models\TarjetaUniversitaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TarjetaReporteController extends Controller
{
    public function index(Request $request)
    {
        // ── Reporte 1: Lecturas por usuario ─────────────────
        $queryLecturas = TarjetaLectura::with(['tarjeta.usuario:id,nombre,apellido,email', 'operador:id,nombre,apellido'])
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('modulo'), fn($q) => $q->where('modulo', $request->modulo))
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo_lectura', $request->tipo))
            ->when($request->filled('exito'), fn($q) => $q->where('exito', $request->exito === 'true'))
            ->latest();

        $lecturas = $queryLecturas->paginate(20)->withQueryString();

        // ── Reporte 2: Uso por módulo ────────────────────────
        $usoModulo = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN exito=true THEN 1 ELSE 0 END) as exitosas'))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        // ── Reporte 3: Incidentes (tarjetas bloqueadas + lecturas fallidas) ──
        $incidentes = TarjetaUniversitaria::with(['usuario:id,nombre,apellido,email', 'bloqueadoPor:id,nombre,apellido'])
            ->whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])
            ->latest('bloqueado_at')
            ->paginate(10, ['*'], 'pageIncidentes')
            ->withQueryString();

        return Inertia::render('Admin/Tarjetas/Reportes', [
            'lecturas'   => $lecturas,
            'usoModulo'  => $usoModulo,
            'incidentes' => $incidentes,
            'filters'    => $request->only(['desde', 'hasta', 'modulo', 'tipo', 'exito']),
            'modulos'    => ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'acceso', 'otro'],
            'tipos'      => ['acceso', 'consumo', 'consulta_saldo', 'confirmacion_entrega'],
        ]);
    }

    /* ─── Exportar CSV lecturas ─────────────────────────── */

    public function exportCsv(Request $request)
    {
        $lecturas = TarjetaLectura::with(['tarjeta.usuario', 'operador'])
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('modulo'), fn($q) => $q->where('modulo', $request->modulo))
            ->latest()
            ->get();

        $csv = "ID,UID Leído,Usuario,Módulo,Tipo,Resultado,Detalle,IP,Operador,Fecha\n";

        foreach ($lecturas as $l) {
            $usuario  = $l->tarjeta?->usuario
                ? "{$l->tarjeta->usuario->nombre} {$l->tarjeta->usuario->apellido}"
                : 'Desconocido';
            $operador = $l->operador
                ? "{$l->operador->nombre} {$l->operador->apellido}"
                : '-';

            $csv .= implode(',', [
                $l->id,
                $l->uid_leido,
                '"' . $usuario . '"',
                $l->modulo,
                $l->tipo_lectura,
                $l->exito ? 'Exitoso' : 'Fallido',
                '"' . str_replace('"', '""', $l->detalle) . '"',
                $l->ip ?? '-',
                '"' . $operador . '"',
                $l->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="lecturas_tarjeta_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    /* ─── Exportar PDF lecturas ─────────────────────────── */

    public function exportLecturasPdf(Request $request)
    {
        $lecturas = TarjetaLectura::with(['tarjeta.usuario:id,nombre,apellido,email', 'operador:id,nombre,apellido'])
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('modulo'), fn($q) => $q->where('modulo', $request->modulo))
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo_lectura', $request->tipo))
            ->when($request->filled('exito'), fn($q) => $q->where('exito', $request->exito === 'true'))
            ->latest()
            ->limit(500)
            ->get();

        $pdf = Pdf::loadView('pdf.reportes.lecturas', [
            'lecturas' => $lecturas,
            'filtros'  => $request->only(['desde', 'hasta', 'modulo', 'tipo', 'exito']),
            'fecha'    => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('lecturas_tarjeta_' . now()->format('Ymd_His') . '.pdf');
    }

    /* ─── Exportar CSV módulos ──────────────────────────── */

    public function exportModuloCsv(Request $request)
    {
        $usoModulo = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN exito=true THEN 1 ELSE 0 END) as exitosas'))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        $csv = "Módulo,Total,Exitosas,Fallidas\n";

        foreach ($usoModulo as $m) {
            $fallidas = $m->total - $m->exitosas;
            $csv .= implode(',', [
                $m->modulo,
                $m->total,
                $m->exitosas,
                $fallidas,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="uso_modulo_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    /* ─── Exportar PDF módulos ──────────────────────────── */

    public function exportModuloPdf(Request $request)
    {
        $usoModulo = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN exito=true THEN 1 ELSE 0 END) as exitosas'))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        $pdf = Pdf::loadView('pdf.reportes.modulo', [
            'usoModulo' => $usoModulo,
            'filtros'   => $request->only(['desde', 'hasta']),
            'fecha'     => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('uso_modulo_' . now()->format('Ymd_His') . '.pdf');
    }

    /* ─── Exportar CSV incidentes ───────────────────────── */

    public function exportIncidentesCsv(Request $request)
    {
        $incidentes = TarjetaUniversitaria::with(['usuario', 'bloqueadoPor'])
            ->whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])
            ->latest('bloqueado_at')
            ->get();

        $csv = "ID,UID,Usuario,Estado,Motivo,Bloqueado Por,Fecha Bloqueo\n";

        foreach ($incidentes as $t) {
            $usuario = $t->usuario ? "{$t->usuario->nombre} {$t->usuario->apellido}" : '-';
            $por     = $t->bloqueadoPor ? "{$t->bloqueadoPor->nombre} {$t->bloqueadoPor->apellido}" : '-';

            $csv .= implode(',', [
                $t->id,
                $t->uid,
                '"' . $usuario . '"',
                $t->estado,
                '"' . str_replace('"', '""', $t->motivo_bloqueo ?? '') . '"',
                '"' . $por . '"',
                $t->bloqueado_at?->format('Y-m-d H:i:s') ?? '-',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="incidentes_tarjeta_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    /* ─── Exportar PDF incidentes ───────────────────────── */

    public function exportIncidentesPdf(Request $request)
    {
        $incidentes = TarjetaUniversitaria::with(['usuario:id,nombre,apellido,email', 'bloqueadoPor:id,nombre,apellido'])
            ->whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])
            ->latest('bloqueado_at')
            ->limit(500)
            ->get();

        $pdf = Pdf::loadView('pdf.reportes.incidentes', [
            'incidentes' => $incidentes,
            'fecha'      => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('incidentes_tarjeta_' . now()->format('Ymd_His') . '.pdf');
    }
}