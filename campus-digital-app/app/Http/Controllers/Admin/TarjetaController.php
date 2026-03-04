<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarjetaUniversitaria;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TarjetaController extends Controller
{
    /* ─── INDEX ──────────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = TarjetaUniversitaria::with(['usuario', 'registradoPor'])
            ->withCount('lecturas');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uid', 'ilike', "%{$search}%")
                  ->orWhereHas('usuario', fn($u) => $u->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('apellido', 'ilike', "%{$search}%")
                      ->orWhere('email', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $tarjetas = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Tarjetas/Index', [
            'tarjetas' => $tarjetas,
            'filters'  => $request->only(['search', 'estado']),
            'stats'    => [
                'total'     => TarjetaUniversitaria::count(),
                'activas'   => TarjetaUniversitaria::where('estado', 'activa')->count(),
                'bloqueadas' => TarjetaUniversitaria::where('estado', 'bloqueada')->count(),
                'perdidas'  => TarjetaUniversitaria::where('estado', 'perdida')->count(),
            ],
        ]);
    }

    /* ─── CREATE ─────────────────────────────────────────── */

    public function create()
    {
        $usuarios = Usuario::whereDoesntHave('tarjeta', fn($q) => $q->whereNull('deleted_at'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'email']);

        return Inertia::render('Admin/Tarjetas/Create', [
            'usuarios' => $usuarios,
        ]);
    }

    /* ─── STORE ──────────────────────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuario,id',
            'uid'        => 'required|string|max:64|unique:tarjeta_universitaria,uid',
        ]);

        // Verificar que el usuario no tenga ya una tarjeta activa
        $existente = TarjetaUniversitaria::where('usuario_id', $request->usuario_id)
            ->whereNull('deleted_at')
            ->first();

        if ($existente) {
            return back()->withErrors(['usuario_id' => 'Este usuario ya tiene una tarjeta registrada.']);
        }

        TarjetaUniversitaria::create([
            'usuario_id'                 => $request->usuario_id,
            'uid'                        => strtoupper(trim($request->uid)),
            'estado'                     => 'activa',
            'registrado_por_usuario_id'  => Auth::id(),
        ]);

        return redirect()->route('admin.tarjetas.index')
            ->with('success', 'Tarjeta registrada exitosamente.');
    }

    /* ─── SHOW ───────────────────────────────────────────── */

    public function show(TarjetaUniversitaria $tarjeta)
    {
        $tarjeta->load(['usuario.perfil', 'registradoPor', 'bloqueadoPor']);

        $lecturas = $tarjeta->lecturas()
            ->with('operador')
            ->latest()
            ->paginate(20);

        $statsLecturas = [
            'total'    => $tarjeta->lecturas()->count(),
            'exitosas' => $tarjeta->lecturas()->where('exito', true)->count(),
            'fallidas' => $tarjeta->lecturas()->where('exito', false)->count(),
            'hoy'      => $tarjeta->lecturas()->whereDate('created_at', today())->count(),
        ];

        return Inertia::render('Admin/Tarjetas/Show', [
            'tarjeta'       => $tarjeta,
            'lecturas'      => $lecturas,
            'statsLecturas' => $statsLecturas,
        ]);
    }

    /* ─── EDIT ───────────────────────────────────────────── */

    public function edit(TarjetaUniversitaria $tarjeta)
    {
        $tarjeta->load('usuario');

        return Inertia::render('Admin/Tarjetas/Edit', [
            'tarjeta' => $tarjeta,
        ]);
    }

    /* ─── UPDATE ─────────────────────────────────────────── */

    public function update(Request $request, TarjetaUniversitaria $tarjeta)
    {
        $request->validate([
            'uid' => "required|string|max:64|unique:tarjeta_universitaria,uid,{$tarjeta->id}",
        ]);

        $tarjeta->update([
            'uid' => strtoupper(trim($request->uid)),
        ]);

        return redirect()->route('admin.tarjetas.show', $tarjeta)
            ->with('success', 'Tarjeta actualizada.');
    }

    /* ─── DESTROY ────────────────────────────────────────── */

    public function destroy(TarjetaUniversitaria $tarjeta)
    {
        $tarjeta->delete();

        return redirect()->route('admin.tarjetas.index')
            ->with('success', 'Tarjeta eliminada.');
    }

    /* ─── TOGGLE BLOCK ───────────────────────────────────── */

    public function toggleBlock(Request $request, TarjetaUniversitaria $tarjeta)
    {
        if ($tarjeta->estaActiva()) {
            $request->validate([
                'motivo'  => 'required|string|max:255',
                'estado'  => 'required|in:bloqueada,perdida,cancelada',
            ]);

            $tarjeta->update([
                'estado'                  => $request->estado,
                'motivo_bloqueo'          => $request->motivo,
                'bloqueado_por_usuario_id' => Auth::id(),
                'bloqueado_at'            => now(),
            ]);

            $mensaje = 'Tarjeta bloqueada exitosamente.';
        } else {
            $tarjeta->update([
                'estado'                  => 'activa',
                'motivo_bloqueo'          => null,
                'bloqueado_por_usuario_id' => null,
                'bloqueado_at'            => null,
            ]);

            $mensaje = 'Tarjeta reactivada exitosamente.';
        }

        return back()->with('success', $mensaje);
    }

    /* ─── MI TARJETA (Vista estudiante / usuario autenticado) */

    public function miTarjeta()
    {
        $usuario = Auth::user();

        $tarjeta = TarjetaUniversitaria::with(['registradoPor'])
            ->where('usuario_id', $usuario->id)
            ->first();

        $lecturas = [];
        if ($tarjeta) {
            $lecturas = $tarjeta->lecturas()->latest()->take(10)->get();
        }

        return Inertia::render('Tarjetas/MiTarjeta', [
            'tarjeta'  => $tarjeta,
            'lecturas' => $lecturas,
            'usuario'  => $usuario->only(['id', 'nombre', 'apellido', 'email']),
        ]);
    }
}