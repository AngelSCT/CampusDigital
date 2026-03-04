<?php

namespace App\Http\Controllers;

use App\Models\TarjetaLectura;
use App\Models\TarjetaUniversitaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TarjetaLecturaController extends Controller
{
    /* ─── Panel del lector simulado ─────────────────────── */

    public function index()
    {
        $lecturasRecientes = TarjetaLectura::with(['tarjeta.usuario:id,nombre,apellido,email', 'operador:id,nombre,apellido'])
            ->where('operador_usuario_id', Auth::id())
            ->latest()
            ->take(15)
            ->get();

        return Inertia::render('Lector/Index', [
            'lecturasRecientes' => $lecturasRecientes,
            'modulos' => [
                ['value' => 'cafeteria',            'label' => 'Cafetería'],
                ['value' => 'copias',               'label' => 'Copias / Impresiones'],
                ['value' => 'souvenirs',            'label' => 'Souvenirs'],
                ['value' => 'biblioteca',           'label' => 'Biblioteca'],
                ['value' => 'acceso',               'label' => 'Control de Acceso'],
                ['value' => 'otro',                 'label' => 'Otro'],
            ],
            'tipos' => [
                ['value' => 'acceso',               'label' => 'Acceso / Entrada'],
                ['value' => 'consumo',              'label' => 'Confirmar Consumo'],
                ['value' => 'consulta_saldo',       'label' => 'Consulta de Saldo'],
                ['value' => 'confirmacion_entrega', 'label' => 'Confirmar Entrega'],
            ],
        ]);
    }

    /* ─── Procesar lectura (simulación) ─────────────────── */

    public function leer(Request $request)
    {
        $request->validate([
            'uid'          => 'required|string|max:64',
            'modulo'       => 'required|string|max:50',
            'tipo_lectura' => 'required|string|max:50',
        ]);

        $uid = strtoupper(trim($request->uid));

        // Buscar tarjeta por UID
        $tarjeta = TarjetaUniversitaria::with('usuario:id,nombre,apellido,email')
            ->where('uid', $uid)
            ->whereNull('deleted_at')
            ->first();

        // Determinar resultado
        if (!$tarjeta) {
            $exito   = false;
            $detalle = 'Tarjeta no registrada en el sistema.';
            $tarjetaId = null;
        } elseif ($tarjeta->estaBloqueada()) {
            $exito   = false;
            $detalle = "Tarjeta {$tarjeta->estado}. Motivo: " . ($tarjeta->motivo_bloqueo ?? 'Sin motivo registrado');
            $tarjetaId = $tarjeta->id;
        } else {
            $exito   = true;
            $detalle = "Lectura exitosa en módulo: {$request->modulo}. Tipo: {$request->tipo_lectura}.";
            $tarjetaId = $tarjeta->id;
        }

        // Registrar lectura en bitácora
        $lectura = TarjetaLectura::create([
            'tarjeta_id'         => $tarjetaId,
            'uid_leido'          => $uid,
            'modulo'             => $request->modulo,
            'tipo_lectura'       => $request->tipo_lectura,
            'exito'              => $exito,
            'detalle'            => $detalle,
            'ip'                 => $request->ip(),
            'user_agent'         => $request->userAgent() ?? '',
            'operador_usuario_id' => Auth::id(),
            'meta_json'          => [],
        ]);

        return back()->with('resultado', [
            'exito'   => $exito,
            'detalle' => $detalle,
            'uid'     => $uid,
            'usuario' => $tarjeta?->usuario ? [
                'nombre'   => $tarjeta->usuario->nombre,
                'apellido' => $tarjeta->usuario->apellido,
                'email'    => $tarjeta->usuario->email,
            ] : null,
            'tarjeta' => $tarjeta ? [
                'id'     => $tarjeta->id,
                'estado' => $tarjeta->estado,
            ] : null,
            'lectura_id' => $lectura->id,
        ]);
    }
}