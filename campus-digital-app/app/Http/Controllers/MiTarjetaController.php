<?php

namespace App\Http\Controllers;

use App\Models\TarjetaUniversitaria;
use App\Models\TarjetaLectura;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;


class MiTarjetaController extends Controller
{

    public function show()
    {
        $usuario = Auth::user();

        $tarjeta = TarjetaUniversitaria::with(['registradoPor'])
            ->where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->first();

        $monedero = SaldoMonedero::obtenerOCrear($usuario->id);

        $lecturas = $tarjeta
            ? $tarjeta->lecturas()
                ->latest()
                ->take(20)
                ->get()
                ->map(fn($l) => [
                    'id'           => $l->id,
                    'modulo'       => $l->modulo,
                    'tipo_lectura' => $l->tipo_lectura,
                    'exito'        => $l->exito,
                    'detalle'      => $l->detalle,
                    'created_at'   => $l->created_at,
                ])
            : [];

        $movimientos = SaldoMovimiento::where('usuario_id', $usuario->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'tipo'        => $m->tipo,
                'monto'       => $m->monto,
                'saldo_nuevo' => $m->saldo_nuevo,
                'modulo'      => $m->modulo,
                'concepto'    => $m->concepto,
                'created_at'  => $m->created_at,
            ]);

        return Inertia::render('Tarjetas/MiTarjeta', [
            'tarjeta'     => $tarjeta ? [
                'id'             => $tarjeta->id,
                'uid'            => $tarjeta->uid,
                'estado'         => $tarjeta->estado,
                'motivo_bloqueo' => $tarjeta->motivo_bloqueo,
                'tiene_pin'      => (bool) $tarjeta->pin_hash,
                'created_at'     => $tarjeta->created_at,
                'updated_at'     => $tarjeta->updated_at,
            ] : null,
            'monedero'    => [
                'saldo_disponible' => $monedero->saldo_disponible,
                'saldo_retenido'   => $monedero->saldo_retenido,
            ],
            'lecturas'    => $lecturas,
            'movimientos' => $movimientos,
            'usuario'     => [
                'id'       => $usuario->id,
                'nombre'   => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email'    => $usuario->email,
            ],
        ]);
    }


    public function simularEscaneo(Request $request)
    {
        $data = $request->validate([
            'modulo'       => 'required|string|in:' . implode(',', TarjetaLectura::MODULOS),
            'tipo_lectura' => 'required|string|in:' . implode(',', TarjetaLectura::TIPOS),
        ]);

        $usuario = Auth::user();
        $tarjeta = TarjetaUniversitaria::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$tarjeta) {
            return back()->with('error', 'No tienes una tarjeta registrada.');
        }

        if (!$tarjeta->estaActiva()) {
            return back()->with('error', 'Tu tarjeta no está activa.');
        }

        TarjetaLectura::create([
            'tarjeta_id'          => $tarjeta->id,
            'uid_leido'           => $tarjeta->uid,
            'modulo'              => $data['modulo'],
            'tipo_lectura'        => $data['tipo_lectura'],
            'exito'               => true,
            'detalle'             => 'Simulación desde Mi Tarjeta. Módulo: ' . $data['modulo'],
            'ip'                  => $request->ip(),
            'user_agent'          => $request->userAgent() ?? '',
            'operador_usuario_id' => $usuario->id,
            'meta_json'           => [],
        ]);

        return back()->with('success', 'Escaneo simulado en módulo: ' . $data['modulo']);
    }


    public function showPin()
    {
        $tarjeta = TarjetaUniversitaria::where('usuario_id', Auth::id())
            ->whereNull('deleted_at')
            ->first();

        return Inertia::render('Tarjetas/ConfigurarPin', [
            'tiene_pin'     => (bool) $tarjeta?->pin_hash,
            'tiene_tarjeta' => (bool) $tarjeta,
        ]);
    }

    public function updatePin(Request $request)
    {
        $usuario = Auth::user();
        $tarjeta = TarjetaUniversitaria::where('usuario_id', $usuario->id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $rules = [
            'pin_nuevo'     => 'required|digits:4',
            'pin_confirmar' => 'required|digits:4|same:pin_nuevo',
        ];

        if ($tarjeta->pin_hash) {
            $rules['pin_actual'] = 'required|digits:4';
        }

        $request->validate($rules, [
            'pin_nuevo.digits'     => 'El PIN debe tener exactamente 4 dígitos.',
            'pin_confirmar.same'   => 'Los PINs no coinciden.',
            'pin_actual.required'  => 'Debes ingresar tu PIN actual.',
        ]);

        if ($tarjeta->pin_hash && !Hash::check($request->pin_actual, $tarjeta->pin_hash)) {
            return back()->withErrors(['pin_actual' => 'El PIN actual es incorrecto.']);
        }

        $tarjeta->update(['pin_hash' => Hash::make($request->pin_nuevo)]);

        return back()->with('success', $tarjeta->pin_hash
            ? 'PIN actualizado correctamente.'
            : 'PIN configurado. Ya puedes usar tu tarjeta para iniciar sesión.'
        );
    }
}