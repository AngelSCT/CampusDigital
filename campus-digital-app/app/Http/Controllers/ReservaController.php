<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\SaldoMonedero;
use App\Modules\Reservas\Services\ConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReservaController extends Controller
{
    protected ConflictDetectionService $conflictService;

    public function __construct(ConflictDetectionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    public function index(Request $request)
    {
        $query = Recurso::with('ubicacion')
            ->where('estado', Recurso::ESTADO_DISPONIBLE);

        if ($request->filled('search')) {
            $query->where('nombre', 'ilike', "%{$request->search}%");
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $recursos = $query->orderBy('nombre')->paginate(12)->withQueryString();

        $misReservas = Reserva::with('recurso')
            ->where('id_usuario', auth()->id())
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_CONFIRMADA])
            ->where('fecha_fin', '>=', now())
            ->orderBy('fecha_inicio')
            ->limit(5)
            ->get();

        return Inertia::render('Reservas/Index', [
            'recursos'    => $recursos,
            'misReservas' => $misReservas,
            'filters'     => $request->only(['search', 'tipo']),
        ]);
    }

    public function create(Request $request, Recurso $recurso)
    {
        $fecha = $request->filled('fecha') ? Carbon::parse($request->fecha) : Carbon::tomorrow();

        $ocupados = Reserva::where('id_recurso', $recurso->id_recurso)
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_CONFIRMADA])
            ->where('fecha_inicio', '>=', $fecha->copy()->startOfDay())
            ->where('fecha_inicio', '<=', $fecha->copy()->endOfDay())
            ->orderBy('fecha_inicio')
            ->get(['id_reserva', 'fecha_inicio', 'fecha_fin', 'estado']);

        return Inertia::render('Reservas/Create', [
            'recurso'  => $recurso->load('ubicacion'),
            'fecha'    => $fecha->toDateString(),
            'ocupados' => $ocupados,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_recurso'   => ['required', 'integer', 'exists:recursos,id_recurso'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
            'proposito'    => ['nullable', 'string', 'max:500'],
        ]);

        $recurso = Recurso::findOrFail($validated['id_recurso']);
        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaFin    = Carbon::parse($validated['fecha_fin']);

        $errores = $this->conflictService->validarHorario($recurso, $fechaInicio, $fechaFin);
        if (!empty($errores)) {
            return back()->withErrors($errores)->withInput();
        }

        $conflictos = $this->conflictService->detectarConflictos($recurso->id_recurso, $fechaInicio, $fechaFin);
        if (!empty($conflictos)) {
            return back()->withErrors(['conflicto' => 'El recurso ya está reservado en ese horario.'])->withInput();
        }

        $costo = $this->conflictService->calcularCosto($recurso, $fechaInicio, $fechaFin);
        $cobro = $costo > 0;

        if ($cobro) {
            $monedero = SaldoMonedero::obtenerOCrear(auth()->id());
            if (!$monedero->tieneSaldo($costo)) {
                return back()->withErrors(['saldo' => "Saldo insuficiente. Necesitas \${$costo} para esta reserva."])->withInput();
            }
        }

        $reserva = DB::transaction(function () use ($recurso, $fechaInicio, $fechaFin, $validated, $cobro, $costo) {
            $reserva = Reserva::create([
                'id_recurso'   => $recurso->id_recurso,
                'id_usuario'   => auth()->id(),
                'fecha_inicio' => $fechaInicio,
                'fecha_fin'    => $fechaFin,
                'estado'       => Reserva::ESTADO_CONFIRMADA,
                'proposito'    => $validated['proposito'] ?? null,
                'cobro_saldo'  => $cobro,
                'monto_cobrado'=> $cobro ? $costo : null,
            ]);

            if ($cobro) {
                $monedero = SaldoMonedero::obtenerOCrear(auth()->id());
                $monedero->cargar(
                    $costo,
                    "Reserva #{$reserva->id_reserva}: {$recurso->nombre}",
                    'reservas'
                );
            }

            return $reserva;
        });

        return redirect()->route('reservas.show', $reserva->id_reserva)
            ->with('success', 'Reserva creada correctamente.');
    }

    public function show(Reserva $reserva)
    {
        if ($reserva->id_usuario !== auth()->id() && !auth()->user()->hasAnyRole(['administrador'])) {
            abort(403);
        }

        $reserva->load(['recurso.ubicacion', 'usuario']);

        return Inertia::render('Reservas/Show', [
            'reserva' => $reserva,
        ]);
    }

    public function destroy(Reserva $reserva)
    {
        if ($reserva->id_usuario !== auth()->id() && !auth()->user()->hasAnyRole(['administrador'])) {
            abort(403);
        }

        if (!$reserva->estaActiva()) {
            return back()->withErrors(['error' => 'La reserva no se puede cancelar en su estado actual.']);
        }

        $reserva->update([
            'estado'                 => Reserva::ESTADO_CANCELADA,
            'id_usuario_cancelacion' => auth()->id(),
            'cancelada_at'           => now(),
            'motivo_cancelacion'     => 'Cancelada por el usuario',
        ]);

        return redirect()->route('reservas.index')->with('success', 'Reserva cancelada.');
    }
}
