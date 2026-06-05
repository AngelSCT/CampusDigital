<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Modules\Reservas\Services\TurnoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TurnoController extends Controller
{
    protected TurnoService $turnoService;

    public function __construct(TurnoService $turnoService)
    {
        $this->turnoService = $turnoService;
    }

    public function index(Request $request)
    {
        $miTurnoActivo = Turno::with('recurso')
            ->where('id_usuario', auth()->id())
            ->whereIn('estado', [Turno::ESTADO_ESPERANDO, Turno::ESTADO_LLAMADO])
            ->whereDate('created_at', today())
            ->orderBy('id_turno', 'desc')
            ->first();

        $tipos = collect((new Turno())->getTiposDisponibles());

        $estadisticasPorTipo = [];
        foreach ($tipos as $key => $label) {
            $estadisticasPorTipo[$key] = $this->turnoService->estadisticasHoy($key);
        }

        return Inertia::render('Turnos/Index', [
            'miTurno'             => $miTurnoActivo,
            'tipos'               => $tipos,
            'estadisticasPorTipo' => $estadisticasPorTipo,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_turno' => ['required', 'string', 'in:atencion,recoleccion,cafeteria,biblioteca,general'],
            'id_recurso' => ['nullable', 'integer', 'exists:recursos,id_recurso'],
            'notas'      => ['nullable', 'string', 'max:500'],
        ]);

        $existente = Turno::where('id_usuario', auth()->id())
            ->whereIn('estado', [Turno::ESTADO_ESPERANDO, Turno::ESTADO_LLAMADO])
            ->whereDate('created_at', today())
            ->first();

        if ($existente) {
            return back()->withErrors(['error' => 'Ya tienes un turno activo hoy. Cancélalo primero si quieres uno nuevo.']);
        }

        $turno = $this->turnoService->crearTurno(
            auth()->id(),
            $validated['tipo_turno'],
            $validated['id_recurso'] ?? null,
            null,
            $validated['notas'] ?? null
        );

        return redirect()->route('turnos.index')->with('success', "Turno {$turno->numero_turno} generado correctamente.");
    }

    public function miTurno()
    {
        $turno = Turno::with('recurso')
            ->where('id_usuario', auth()->id())
            ->whereIn('estado', [Turno::ESTADO_ESPERANDO, Turno::ESTADO_LLAMADO])
            ->whereDate('created_at', today())
            ->orderBy('id_turno', 'desc')
            ->first();

        if (!$turno) {
            return response()->json(['error' => 'No tienes un turno activo hoy.'], 404);
        }

        $estadisticas = $this->turnoService->estadisticasHoy($turno->tipo_turno);

        $turnoActual = Turno::where('tipo_turno', $turno->tipo_turno)
            ->where('estado', Turno::ESTADO_LLAMADO)
            ->orderBy('id_turno', 'desc')
            ->first();

        return response()->json([
            'turno'        => $turno,
            'turno_actual' => $turnoActual,
            'estadisticas' => $estadisticas,
        ]);
    }

    public function destroy(Turno $turno)
    {
        if ($turno->id_usuario !== auth()->id() && !auth()->user()->hasAnyRole(['administrador'])) {
            abort(403);
        }

        if (!in_array($turno->estado, [Turno::ESTADO_ESPERANDO, Turno::ESTADO_LLAMADO], true)) {
            return back()->withErrors(['error' => 'El turno no se puede cancelar.']);
        }

        $this->turnoService->cancelar($turno);
        $this->turnoService->recalcularPosiciones($turno->tipo_turno);

        return back()->with('success', 'Turno cancelado.');
    }
}
