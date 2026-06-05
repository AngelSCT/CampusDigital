<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservaAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Reserva::with(['recurso', 'usuario']);

        if ($request->filled('search')) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('nombre', 'ilike', "%{$request->search}%")
                  ->orWhere('apellido', 'ilike', "%{$request->search}%");
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('recurso')) {
            $query->where('id_recurso', $request->recurso);
        }
        if ($request->filled('desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->hasta);
        }

        $reservas = $query->orderBy('fecha_inicio', 'desc')->paginate(15)->withQueryString();

        $recursos = Recurso::orderBy('nombre')->get(['id_recurso', 'nombre', 'tipo']);

        return Inertia::render('Admin/Reservas/Index', [
            'reservas' => $reservas,
            'recursos' => $recursos,
            'filters'  => $request->only(['search', 'estado', 'recurso', 'desde', 'hasta']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_recurso'   => ['required', 'integer', 'exists:recursos,id_recurso'],
            'id_usuario'   => ['required', 'integer', 'exists:usuario,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
            'proposito'    => ['nullable', 'string', 'max:500'],
        ]);

        Reserva::create($validated + ['estado' => Reserva::ESTADO_CONFIRMADA]);

        return redirect()->route('admin.reservas.index')->with('success', 'Reserva creada.');
    }

    public function show(Reserva $reserva)
    {
        $reserva->load(['recurso.ubicacion', 'usuario', 'usuarioCancela']);

        return Inertia::render('Admin/Reservas/Show', [
            'reserva' => $reserva,
        ]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $validated = $request->validate([
            'estado'                => ['required', 'string', 'in:pendiente,confirmada,cancelada,no_show,completada'],
            'motivo_cancelacion'    => ['nullable', 'string', 'max:500'],
        ]);

        $data = ['estado' => $validated['estado']];

        if ($validated['estado'] === Reserva::ESTADO_CANCELADA) {
            $data['id_usuario_cancelacion'] = auth()->id();
            $data['cancelada_at'] = now();
            $data['motivo_cancelacion'] = $validated['motivo_cancelacion'] ?? 'Cancelada por administrador';
        }

        if ($validated['estado'] === Reserva::ESTADO_COMPLETADA && !$reserva->check_in_at) {
            $data['check_in_at'] = now();
        }

        $reserva->update($data);

        return redirect()->route('admin.reservas.show', $reserva->id_reserva)->with('success', 'Reserva actualizada.');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return redirect()->route('admin.reservas.index')->with('success', 'Reserva eliminada.');
    }
}
