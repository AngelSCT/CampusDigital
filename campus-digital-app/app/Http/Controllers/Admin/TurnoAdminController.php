<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\Recurso;
use App\Modules\Reservas\Services\TurnoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TurnoAdminController extends Controller
{
    protected TurnoService $turnoService;

    public function __construct(TurnoService $turnoService)
    {
        $this->turnoService = $turnoService;
    }

    public function index(Request $request)
    {
        $query = Turno::with(['usuario', 'recurso']);

        if ($request->filled('tipo_turno')) {
            $query->where('tipo_turno', $request->tipo_turno);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        } else {
            $query->whereDate('created_at', today());
        }

        $turnos = $query->orderBy('posicion')->orderBy('id_turno')->paginate(20)->withQueryString();

        $turnoModel = new Turno();
        $tipos = $turnoModel->getTiposDisponibles();
        $estados = $turnoModel->getEstadosDisponibles();

        $estadisticasPorTipo = [];
        foreach ($tipos as $key => $label) {
            $estadisticasPorTipo[$key] = $this->turnoService->estadisticasHoy($key);
        }

        return Inertia::render('Admin/Reservas/TurnosIndex', [
            'turnos'              => $turnos,
            'tipos'               => $tipos,
            'estados'             => $estados,
            'estadisticasPorTipo' => $estadisticasPorTipo,
            'filters'             => $request->only(['tipo_turno', 'estado', 'fecha']),
        ]);
    }

    public function llamar(Turno $turno)
    {
        $this->turnoService->llamar($turno);
        return back()->with('success', "Turno {$turno->numero_turno} llamado.");
    }

    public function atender(Turno $turno)
    {
        $this->turnoService->atender($turno);
        $this->turnoService->recalcularPosiciones($turno->tipo_turno);
        return back()->with('success', "Turno {$turno->numero_turno} atendido.");
    }

    public function noShow(Turno $turno)
    {
        $this->turnoService->marcarNoShow($turno);
        $this->turnoService->recalcularPosiciones($turno->tipo_turno);
        return back()->with('success', "Turno {$turno->numero_turno} marcado como no-show.");
    }

    public function destroy(Turno $turno)
    {
        $tipoTurno = $turno->tipo_turno;
        $this->turnoService->cancelar($turno);
        $this->turnoService->recalcularPosiciones($tipoTurno);
        return back()->with('success', 'Turno cancelado.');
    }
}
