<?php

namespace App\Http\Controllers;

use App\Models\PedidoTienda;
use App\Models\Usuario;
use App\Models\AccesoBitacora;
use App\Models\SaldoMonedero;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        if ($user->hasRole('administrador')) {
            return $this->dashboardAdministrador($user);
        } elseif ($user->hasRole('proveedor_area')) {
            return $this->dashboardProveedor($user);
        } else {
            return $this->dashboardEstudiante($user);
        }
    }

    private function dashboardAdministrador($user)
    {
        $hoy     = now();
        $ayer    = now()->subDay();
        $hace7d  = now()->subDays(7);
        $hace14d = now()->subDays(14);
        $hace30d = now()->subDays(30);

        $usuariosActivos    = Usuario::where('bloqueado', false)->whereNull('deleted_at')->count();
        $usuariosBloqueados = Usuario::where('bloqueado', true)->whereNull('deleted_at')->count();
        $usuariosTotal      = Usuario::whereNull('deleted_at')->count();
        $usuariosNuevosHoy  = Usuario::whereNull('deleted_at')->whereDate('created_at', $hoy)->count();
        $emailVerificados   = Usuario::whereNull('deleted_at')->where('email_verificado', true)->count();

        $accesos           = AccesoBitacora::where('created_at', '>=', $hace7d)->get();
        $accesosExitosos   = $accesos->where('exito', true)->count();
        $accesosFallidos   = $accesos->where('exito', false)->count();
        $actividadReciente = AccesoBitacora::orderBy('created_at', 'desc')->limit(10)->get();

        $sesionesActivas = DB::table('usuario_sesion')
            ->whereNull('deleted_at')->where('activa', true)->whereNull('termina_at')->count();

        $usuariosPorRol = DB::table('rol as r')
            ->leftJoin('usuario_rol as ur', fn($j) => $j->on('ur.rol_id', '=', 'r.id')->whereNull('ur.deleted_at'))
            ->leftJoin('usuario as u', fn($j) => $j->on('u.id', '=', 'ur.usuario_id')->whereNull('u.deleted_at'))
            ->whereNull('r.deleted_at')
            ->select('r.nombre', DB::raw('COUNT(DISTINCT u.id) as total'))
            ->groupBy('r.id', 'r.nombre')->orderByDesc('total')->get();

        $accesosPorDia = DB::table('acceso_bitacora')
            ->select(
                DB::raw("TO_CHAR(created_at AT TIME ZONE 'America/Mexico_City', 'DD/MM') as dia"),
                DB::raw('SUM(CASE WHEN exito THEN 1 ELSE 0 END) as exitosos'),
                DB::raw('SUM(CASE WHEN NOT exito THEN 1 ELSE 0 END) as fallidos')
            )
            ->where('created_at', '>=', $hace7d)
            ->groupBy(DB::raw("TO_CHAR(created_at AT TIME ZONE 'America/Mexico_City', 'DD/MM')"))
            ->orderBy(DB::raw('MIN(created_at)'))->get();

        $actividadPorModulo = DB::table('actividad_bitacora')
            ->select('modulo', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $hace30d)->whereNull('deleted_at')
            ->groupBy('modulo')->orderByDesc('total')->limit(8)->get();

        $tarjetasActivas    = DB::table('tarjeta_universitaria')->whereNull('deleted_at')->where('estado', 'activa')->count();
        $tarjetasBloqueadas = DB::table('tarjeta_universitaria')->whereNull('deleted_at')->where('estado', 'bloqueada')->count();
        $tarjetasPerdidas   = DB::table('tarjeta_universitaria')->whereNull('deleted_at')->where('estado', 'perdida')->count();
        $lecturasHoy        = DB::table('tarjeta_lectura')->whereNull('deleted_at')->whereDate('created_at', $hoy)->count();

        $lecturasPorModulo = DB::table('tarjeta_lectura')
            ->select('modulo', DB::raw('COUNT(*) as total'))
            ->whereNull('deleted_at')->whereDate('created_at', $hoy)
            ->groupBy('modulo')->orderByDesc('total')->get();

        $saldoTotal     = DB::table('saldo_monedero')->whereNull('deleted_at')->sum('saldo_disponible');
        $movimientosHoy = DB::table('saldo_movimiento')->whereNull('deleted_at')->whereDate('created_at', $hoy)->count();

        $pedidosPorEstado = DB::table('pedido')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->whereNull('deleted_at')->where('created_at', '>=', $hace30d)
            ->groupBy('estado')->get()->keyBy('estado');


        $crecimientoUsuarios = DB::table('usuario')
            ->select(
                DB::raw("TO_CHAR(created_at AT TIME ZONE 'America/Mexico_City', 'DD/MM') as dia"),
                DB::raw('COUNT(*) as total')
            )
            ->whereNull('deleted_at')->where('created_at', '>=', $hace14d)
            ->groupBy(DB::raw("TO_CHAR(created_at AT TIME ZONE 'America/Mexico_City', 'DD/MM')"))
            ->orderBy(DB::raw('MIN(created_at)'))->get();

        $lecturasPorTipo = DB::table('tarjeta_lectura')
            ->select('tipo_lectura', DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN exito THEN 1 ELSE 0 END) as exitosas'))
            ->whereNull('deleted_at')->where('created_at', '>=', $hace7d)
            ->groupBy('tipo_lectura')->orderByDesc('total')->get();

        $topUsuariosActivos = DB::table('actividad_bitacora as ab')
            ->join('usuario as u', 'u.id', '=', 'ab.usuario_id')
            ->select('u.nombre', 'u.apellido', 'u.email', DB::raw('COUNT(*) as acciones'))
            ->whereNull('ab.deleted_at')->where('ab.created_at', '>=', $hace30d)->whereNull('u.deleted_at')
            ->groupBy('u.id', 'u.nombre', 'u.apellido', 'u.email')
            ->orderByDesc('acciones')->limit(5)->get();

        $topIpsFallidas = DB::table('acceso_bitacora')
            ->select('ip', DB::raw('COUNT(*) as intentos'))
            ->where('exito', false)->where('created_at', '>=', $hace7d)->whereNotNull('ip')
            ->groupBy('ip')->orderByDesc('intentos')->limit(5)->get();

        $movimientosPorTipo = DB::table('saldo_movimiento')
            ->select('tipo', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(monto) as total_monto'))
            ->whereNull('deleted_at')->where('created_at', '>=', $hace30d)
            ->groupBy('tipo')->get()->keyBy('tipo');

        $movimientosPorModulo = DB::table('saldo_movimiento')
            ->select('modulo', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(monto) as monto'))
            ->whereNull('deleted_at')->where('created_at', '>=', $hace30d)
            ->groupBy('modulo')->orderByDesc('monto')->limit(6)->get();

        $saldoPromedio = round(DB::table('saldo_monedero')->whereNull('deleted_at')->avg('saldo_disponible') ?? 0, 2);

        $pedidosPorModulo = DB::table('pedido')
            ->select('modulo', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as monto'))
            ->whereNull('deleted_at')->where('created_at', '>=', $hace30d)
            ->groupBy('modulo')->orderByDesc('total')->get();

        $pedidosConTarjeta = DB::table('pedido')->whereNull('deleted_at')
            ->where('created_at', '>=', $hace30d)->where('confirmado_con_tarjeta', true)->count();
        $pedidosSinTarjeta = DB::table('pedido')->whereNull('deleted_at')
            ->where('created_at', '>=', $hace30d)->where('confirmado_con_tarjeta', false)->count();

        $ingresosEntregados = DB::table('pedido')->whereNull('deleted_at')
            ->where('estado', 'entregado')->where('created_at', '>=', $hace30d)->sum('total');

        $usuariosSinTarjeta = DB::table('usuario as u')
            ->leftJoin('tarjeta_universitaria as t', fn($j) =>
                $j->on('t.usuario_id', '=', 'u.id')->whereNull('t.deleted_at'))
            ->whereNull('u.deleted_at')->whereNull('t.id')->count();

        $tarjetasEsteMes = DB::table('tarjeta_universitaria')->whereNull('deleted_at')
            ->whereMonth('created_at', $hoy->month)->whereYear('created_at', $hoy->year)->count();

        $eventosPorTipo = DB::table('acceso_bitacora')
            ->select('evento', DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN exito THEN 1 ELSE 0 END) as exitosos'))
            ->where('created_at', '>=', $hace7d)
            ->groupBy('evento')->orderByDesc('total')->get();

        $actividadPorHoraRaw = DB::table('actividad_bitacora')
            ->select(
                DB::raw("EXTRACT(HOUR FROM created_at AT TIME ZONE 'America/Mexico_City')::int as hora"),
                DB::raw('COUNT(*) as total')
            )
            ->whereNull('deleted_at')->where('created_at', '>=', $hace30d)
            ->groupBy(DB::raw("EXTRACT(HOUR FROM created_at AT TIME ZONE 'America/Mexico_City')::int"))
            ->orderBy('hora')->get()->keyBy('hora');

        $actividadPorHora = collect(range(0, 23))->map(fn($h) => [
            'hora'  => $h,
            'total' => $actividadPorHoraRaw->get($h)?->total ?? 0,
        ]);

        $usuariosNuevosAyer = Usuario::whereNull('deleted_at')->whereDate('created_at', $ayer)->count();

        $resumen24h = [
            'accesos'         => DB::table('acceso_bitacora')->where('created_at', '>=', now()->subHours(24))->count(),
            'fallidos'        => DB::table('acceso_bitacora')->where('created_at', '>=', now()->subHours(24))->where('exito', false)->count(),
            'lecturas'        => DB::table('tarjeta_lectura')->whereNull('deleted_at')->where('created_at', '>=', now()->subHours(24))->count(),
            'movimientos'     => DB::table('saldo_movimiento')->whereNull('deleted_at')->where('created_at', '>=', now()->subHours(24))->count(),
            'pedidos'         => DB::table('pedido')->whereNull('deleted_at')->where('created_at', '>=', now()->subHours(24))->count(),
            'nuevos_usuarios' => Usuario::whereNull('deleted_at')->where('created_at', '>=', now()->subHours(24))->count(),
        ];

        return Inertia::render('Dashboard/Admin', [
            'stats' => [
                'usuarios_activos'       => $usuariosActivos,
                'usuarios_por_rol'       => $usuariosPorRol,
                'actividad_reciente'     => $actividadReciente,
                'accesos_exitosos'       => $accesosExitosos,
                'accesos_fallidos'       => $accesosFallidos,
                'usuarios_bloqueados'    => $usuariosBloqueados,
                'usuarios_total'         => $usuariosTotal,
                'usuarios_nuevos_hoy'    => $usuariosNuevosHoy,
                'email_verificados'      => $emailVerificados,
                'sesiones_activas'       => $sesionesActivas,
                'tarjetas_activas'       => $tarjetasActivas,
                'tarjetas_bloqueadas'    => $tarjetasBloqueadas,
                'tarjetas_perdidas'      => $tarjetasPerdidas,
                'lecturas_hoy'           => $lecturasHoy,
                'saldo_total'            => $saldoTotal,
                'movimientos_hoy'        => $movimientosHoy,
                'pedidos_por_estado'     => $pedidosPorEstado,
                'accesos_por_dia'        => $accesosPorDia,
                'actividad_por_modulo'   => $actividadPorModulo,
                'lecturas_por_modulo'    => $lecturasPorModulo,
                'crecimiento_usuarios'   => $crecimientoUsuarios,
                'lecturas_por_tipo'      => $lecturasPorTipo,
                'top_usuarios_activos'   => $topUsuariosActivos,
                'top_ips_fallidas'       => $topIpsFallidas,
                'movimientos_por_tipo'   => $movimientosPorTipo,
                'movimientos_por_modulo' => $movimientosPorModulo,
                'saldo_promedio'         => $saldoPromedio,
                'pedidos_por_modulo'     => $pedidosPorModulo,
                'pedidos_con_tarjeta'    => $pedidosConTarjeta,
                'pedidos_sin_tarjeta'    => $pedidosSinTarjeta,
                'ingresos_entregados'    => $ingresosEntregados,
                'usuarios_sin_tarjeta'   => $usuariosSinTarjeta,
                'tarjetas_este_mes'      => $tarjetasEsteMes,
                'eventos_por_tipo'       => $eventosPorTipo,
                'actividad_por_hora'     => $actividadPorHora,
                'usuarios_nuevos_ayer'   => $usuariosNuevosAyer,
                'resumen_24h'            => $resumen24h,
            ],
        ]);
    }

    private function dashboardProveedor($user)
    {
        return Inertia::render('Dashboard/Proveedor', []);
    }

    private function dashboardEstudiante($user)
    {
        $monedero = SaldoMonedero::where('usuario_id', $user->id)->first();

        // Gasto promedio del estudiante en pedidos confirmados
        $gastoPromedio = round(
            (float) PedidoTienda::where('usuario_id', $user->id)->avg('total') ?? 0,
            2
        );

        // Producto más comprado por el estudiante
        $topProducto = DB::table('carrito_items')
            ->join('productos', 'carrito_items.producto_id', '=', 'productos.id')
            ->where('carrito_items.usuario_id', $user->id)
            ->where('carrito_items.guardado_para_despues', false)
            ->where('carrito_items.en_wishlist', false)
            ->selectRaw('productos.nombre, productos.categoria, SUM(carrito_items.cantidad) as total_unidades')
            ->groupBy('productos.id', 'productos.nombre', 'productos.categoria')
            ->orderByDesc('total_unidades')
            ->first();

        $totalPedidos = PedidoTienda::where('usuario_id', $user->id)->count();

        return Inertia::render('Dashboard/Estudiante', [
            'monedero' => $monedero,
            'resumenConsumo' => [
                'gasto_promedio'  => $gastoPromedio,
                'total_pedidos'   => $totalPedidos,
                'top_producto'    => $topProducto ? [
                    'nombre'         => $topProducto->nombre,
                    'categoria'      => $topProducto->categoria,
                    'total_unidades' => (int) $topProducto->total_unidades,
                ] : null,
            ],
        ]);
    }
}