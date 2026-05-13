<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TarjetaUniversitaria;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use App\Models\Pedido;
use App\Models\TarjetaLectura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RfidApiController extends Controller
{

    public function auth(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:64',
            'pin' => 'required|digits:4',
        ]);

        $resultado = $this->resolverTarjeta(
            strtoupper($request->uid),
            $request->pin
        );

        if ($resultado['error']) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $resultado['mensaje'],
            ], 401);
        }

        $usuario = $resultado['tarjeta']->usuario;
        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();

        return response()->json([
            'ok'      => true,
            'usuario' => [
                'id'               => $usuario->id,
                'nombre'           => $usuario->nombre,
                'apellido'         => $usuario->apellido,
                'email'            => $usuario->email,
                'email_verificado' => $usuario->email_verificado,
                'roles'            => $usuario->roles->pluck('nombre'),
            ],
            'tarjeta' => [
                'uid'    => $resultado['tarjeta']->uid,
                'estado' => $resultado['tarjeta']->estado,
            ],
            'saldo' => $monedero
                ? number_format($monedero->saldo_disponible, 2)
                : '0.00',
        ]);
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:64',
            'pin' => 'required|digits:4',
        ]);

        $resultado = $this->resolverTarjeta(
            strtoupper($request->uid),
            $request->pin
        );

        if ($resultado['error']) {
            return response()->json([
                'ok'      => false,
                'valido'  => false,
                'mensaje' => $resultado['mensaje'],
            ], 422);
        }

        return response()->json([
            'ok'     => true,
            'valido' => true,
            'uid'    => $resultado['tarjeta']->uid,
            'estado' => $resultado['tarjeta']->estado,
        ]);
    }

    public function datosUsuario(string $uid)
    {
        $tarjeta = $this->buscarTarjeta($uid);
        if (!$tarjeta) {
            return response()->json(['ok' => false, 'mensaje' => 'Tarjeta no encontrada.'], 404);
        }

        $usuario = $tarjeta->usuario()->with('perfil', 'roles')->first();

        return response()->json([
            'ok'      => true,
            'usuario' => [
                'id'               => $usuario->id,
                'nombre'           => $usuario->nombre,
                'apellido'         => $usuario->apellido,
                'nombre_completo'  => $usuario->nombre . ' ' . $usuario->apellido,
                'email'            => $usuario->email,
                'telefono'         => $usuario->telefono,
                'foto_url'         => $usuario->foto_url ? '/storage/' . $usuario->foto_url : null,
                'email_verificado' => $usuario->email_verificado,
                'bloqueado'        => $usuario->bloqueado,
                'roles'            => $usuario->roles->pluck('nombre'),
                'perfil'           => $usuario->perfil ? [
                    'fecha_nacimiento' => $usuario->perfil->fecha_nacimiento,
                    'genero'           => $usuario->perfil->genero,
                    'direccion'        => $usuario->perfil->direccion,
                ] : null,
                'miembro_desde'    => $usuario->created_at?->format('Y-m-d'),
            ],
            'tarjeta' => [
                'uid'        => $tarjeta->uid,
                'estado'     => $tarjeta->estado,
                'registrada' => $tarjeta->created_at?->format('Y-m-d'),
            ],
        ]);
    }

    public function saldo(string $uid)
    {
        $tarjeta = $this->buscarTarjeta($uid);
        if (!$tarjeta) {
            return response()->json(['ok' => false, 'mensaje' => 'Tarjeta no encontrada.'], 404);
        }

        $usuario  = $tarjeta->usuario;
        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();

        return response()->json([
            'ok'             => true,
            'uid'            => $tarjeta->uid,
            'nombre'         => $usuario->nombre . ' ' . $usuario->apellido,
            'saldo_disponible' => $monedero
                ? number_format($monedero->saldo_disponible, 2)
                : '0.00',
            'saldo_retenido' => $monedero
                ? number_format($monedero->saldo_retenido, 2)
                : '0.00',
            'monedero_id'    => $monedero?->id,
        ]);
    }

    public function historial(Request $request, string $uid)
    {
        $tarjeta = $this->buscarTarjeta($uid);
        if (!$tarjeta) {
            return response()->json(['ok' => false, 'mensaje' => 'Tarjeta no encontrada.'], 404);
        }

        $query = SaldoMovimiento::where('usuario_id', $tarjeta->usuario_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $perPage     = min((int) ($request->per_page ?? 15), 50);
        $movimientos = $query->paginate($perPage);

        return response()->json([
            'ok'      => true,
            'uid'     => $uid,
            'usuario' => $tarjeta->usuario->nombre . ' ' . $tarjeta->usuario->apellido,
            'data'    => $movimientos->map(fn($m) => [
                'id'             => $m->id,
                'tipo'           => $m->tipo,
                'monto'          => number_format($m->monto, 2),
                'saldo_anterior' => number_format($m->saldo_anterior, 2),
                'saldo_nuevo'    => number_format($m->saldo_nuevo, 2),
                'modulo'         => $m->modulo,
                'concepto'       => $m->concepto,
                'fecha'          => $m->created_at?->format('Y-m-d H:i:s'),
            ]),
            'total'        => $movimientos->total(),
            'por_pagina'   => $movimientos->perPage(),
            'pagina_actual'=> $movimientos->currentPage(),
            'ultima_pagina'=> $movimientos->lastPage(),
        ]);
    }

    public function pedidosPendientes(Request $request, string $uid)
    {
        $tarjeta = $this->buscarTarjeta($uid);
        if (!$tarjeta) {
            return response()->json(['ok' => false, 'mensaje' => 'Tarjeta no encontrada.'], 404);
        }

        $query = Pedido::where('usuario_id', $tarjeta->usuario_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        $estadosFiltro = $request->filled('estado')
            ? [$request->estado]
            : ['creado', 'aceptado', 'en_proceso', 'listo'];

        $query->whereIn('estado', $estadosFiltro);

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $pedidos = $query->get();

        return response()->json([
            'ok'      => true,
            'uid'     => $uid,
            'usuario' => $tarjeta->usuario->nombre . ' ' . $tarjeta->usuario->apellido,
            'total'   => $pedidos->count(),
            'pedidos' => $pedidos->map(fn($p) => [
                'id'          => $p->id,
                'folio'       => $p->numero_folio,
                'estado'      => $p->estado,
                'modulo'      => $p->modulo,
                'total'       => number_format($p->total, 2),
                'descripcion' => $p->descripcion,
                'notas'       => $p->notas,
                'creado'      => $p->created_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    public function lecturas(Request $request, string $uid)
    {
        $tarjeta = $this->buscarTarjeta($uid);
        if (!$tarjeta) {
            return response()->json(['ok' => false, 'mensaje' => 'Tarjeta no encontrada.'], 404);
        }

        $query = TarjetaLectura::where('tarjeta_id', $tarjeta->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }
        if ($request->filled('tipo_lectura')) {
            $query->where('tipo_lectura', $request->tipo_lectura);
        }

        $perPage  = min((int) ($request->per_page ?? 15), 50);
        $lecturas = $query->paginate($perPage);

        return response()->json([
            'ok'      => true,
            'uid'     => $uid,
            'usuario' => $tarjeta->usuario->nombre . ' ' . $tarjeta->usuario->apellido,
            'data'    => $lecturas->map(fn($l) => [
                'id'           => $l->id,
                'modulo'       => $l->modulo,
                'tipo_lectura' => $l->tipo_lectura,
                'exito'        => $l->exito,
                'detalle'      => $l->detalle,
                'fecha'        => $l->created_at?->format('Y-m-d H:i:s'),
            ]),
            'total'        => $lecturas->total(),
            'por_pagina'   => $lecturas->perPage(),
            'pagina_actual'=> $lecturas->currentPage(),
            'ultima_pagina'=> $lecturas->lastPage(),
        ]);
    }

    private function buscarTarjeta(string $uid): ?TarjetaUniversitaria
    {
        return TarjetaUniversitaria::where('uid', strtoupper($uid))
            ->whereNull('deleted_at')
            ->with('usuario')
            ->first();
    }

    private function resolverTarjeta(string $uid, string $pin): array
    {
        $tarjeta = $this->buscarTarjeta($uid);

        if (!$tarjeta) {
            return ['error' => true, 'mensaje' => 'Tarjeta no registrada.', 'tarjeta' => null];
        }
        if (!$tarjeta->pin_hash) {
            return ['error' => true, 'mensaje' => 'PIN no configurado en esta tarjeta.', 'tarjeta' => null];
        }
        if (!Hash::check($pin, $tarjeta->pin_hash)) {
            return ['error' => true, 'mensaje' => 'PIN incorrecto.', 'tarjeta' => null];
        }
        if ($tarjeta->estaBloqueada()) {
            return ['error' => true, 'mensaje' => 'Tarjeta ' . $tarjeta->estado . '.', 'tarjeta' => null];
        }
        if (!$tarjeta->usuario || $tarjeta->usuario->bloqueado) {
            return ['error' => true, 'mensaje' => 'Usuario no disponible.', 'tarjeta' => null];
        }

        return ['error' => false, 'mensaje' => 'OK', 'tarjeta' => $tarjeta];
    }
}