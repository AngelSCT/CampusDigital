<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarjetaLectura;
use App\Models\TarjetaUniversitaria;
use App\Models\SaldoMovimiento;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TarjetaDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tarjetas'  => TarjetaUniversitaria::count(),
            'activas'         => TarjetaUniversitaria::where('estado', 'activa')->count(),
            'bloqueadas'      => TarjetaUniversitaria::whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])->count(),
            'lecturas_hoy'    => TarjetaLectura::whereDate('created_at', today())->count(),
            'lecturas_semana' => TarjetaLectura::where('created_at', '>=', now()->subDays(7))->count(),

            'perdidas'             => TarjetaUniversitaria::where('estado', 'perdida')->count(),
            'canceladas'           => TarjetaUniversitaria::where('estado', 'cancelada')->count(),
            'con_pin'              => TarjetaUniversitaria::whereNotNull('pin_hash')->count(),
            'sin_pin'              => TarjetaUniversitaria::whereNull('pin_hash')->where('estado', 'activa')->count(),
            'lecturas_mes'         => TarjetaLectura::where('created_at', '>=', now()->subDays(30))->count(),
            'lecturas_exitosas_hoy'=> TarjetaLectura::whereDate('created_at', today())->where('exito', true)->count(),
            'lecturas_fallidas_hoy'=> TarjetaLectura::whereDate('created_at', today())->where('exito', false)->count(),
            'lecturas_ayer'        => TarjetaLectura::whereDate('created_at', today()->subDay())->count(),
            'tasa_exito_mes'       => $this->tasaExito(),
            'tarjetas_sin_uso_30d' => $this->tarjetasSinUso(),
            'nuevas_esta_semana'   => TarjetaUniversitaria::where('created_at', '>=', now()->subDays(7))->count(),
            'logins_rfid_hoy'      => DB::table('acceso_bitacora')
                ->where('evento', 'rfid_login_success')
                ->whereDate('created_at', today())
                ->count(),
            'logins_rfid_semana'   => DB::table('acceso_bitacora')
                ->where('evento', 'rfid_login_success')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'confirmaciones_hoy'   => TarjetaLectura::whereDate('created_at', today())
                ->where('tipo_lectura', 'confirmacion_entrega')
                ->where('exito', true)
                ->count(),
            'consultas_saldo_hoy'  => TarjetaLectura::whereDate('created_at', today())
                ->where('tipo_lectura', 'consulta_saldo')
                ->count(),
        ];

        $lecturasPorDia = TarjetaLectura::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN exito = true THEN 1 ELSE 0 END) as exitosas'),
            DB::raw('SUM(CASE WHEN exito = false THEN 1 ELSE 0 END) as fallidas')
        )
        ->where('created_at', '>=', now()->subDays(13))
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();

        $diasCompletos = [];
        for ($i = 13; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->toDateString();
            $dia   = $lecturasPorDia->firstWhere('fecha', $fecha);
            $diasCompletos[] = [
                'fecha'    => $fecha,
                'total'    => $dia ? (int)$dia->total    : 0,
                'exitosas' => $dia ? (int)$dia->exitosas : 0,
                'fallidas' => $dia ? (int)$dia->fallidas : 0,
            ];
        }

        $lecturasPorModulo = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        $usuariosActivos = TarjetaUniversitaria::select(
            'tarjeta_universitaria.id',
            'tarjeta_universitaria.uid',
            'tarjeta_universitaria.estado',
            DB::raw('COUNT(tarjeta_lectura.id) as total_lecturas')
        )
        ->leftJoin('tarjeta_lectura', function ($join) {
            $join->on('tarjeta_lectura.tarjeta_id', '=', 'tarjeta_universitaria.id')
                 ->where('tarjeta_lectura.created_at', '>=', now()->subDays(30))
                 ->whereNull('tarjeta_lectura.deleted_at');
        })
        ->with('usuario:id,nombre,apellido,email')
        ->groupBy('tarjeta_universitaria.id', 'tarjeta_universitaria.uid', 'tarjeta_universitaria.estado')
        ->orderByDesc('total_lecturas')
        ->take(10)
        ->get();

        $tarjetasBloqueadas = TarjetaUniversitaria::with([
            'usuario:id,nombre,apellido,email',
            'bloqueadoPor:id,nombre,apellido',
        ])
        ->whereIn('estado', ['bloqueada', 'perdida', 'cancelada'])
        ->latest('bloqueado_at')
        ->take(5)
        ->get();

        $lecturasRecientes = TarjetaLectura::with([
            'tarjeta.usuario:id,nombre,apellido',
            'operador:id,nombre,apellido',
        ])
        ->latest()
        ->take(10)
        ->get();

        $lecturasPorTipo = TarjetaLectura::select(
            'tipo_lectura',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN exito = true THEN 1 ELSE 0 END) as exitosas')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('tipo_lectura')
        ->orderByDesc('total')
        ->get();

        $lecturasPorHora = TarjetaLectura::select(
            DB::raw('EXTRACT(HOUR FROM created_at) as hora'),
            DB::raw('COUNT(*) as total')
        )
        ->where('created_at', '>=', now()->subDays(7))
        ->groupBy('hora')
        ->orderBy('hora')
        ->get()
        ->keyBy('hora');

        $horasCompletas = [];
        for ($h = 0; $h < 24; $h++) {
            $horasCompletas[] = [
                'hora'  => $h,
                'label' => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => isset($lecturasPorHora[$h]) ? (int)$lecturasPorHora[$h]->total : 0,
            ];
        }

        $exitoFalloPorModulo = TarjetaLectura::select(
            'modulo',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN exito = true THEN 1 ELSE 0 END) as exitosas'),
            DB::raw('SUM(CASE WHEN exito = false THEN 1 ELSE 0 END) as fallidas')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('modulo')
        ->orderByDesc('total')
        ->get()
        ->map(fn($m) => [
            'modulo'   => $m->modulo,
            'total'    => (int)$m->total,
            'exitosas' => (int)$m->exitosas,
            'fallidas' => (int)$m->fallidas,
            'tasa'     => $m->total > 0 ? round($m->exitosas / $m->total * 100, 1) : 0,
        ]);

        $evolucionSemanal = [];
        for ($w = 7; $w >= 0; $w--) {
            $inicio = now()->startOfWeek()->subWeeks($w);
            $fin    = (clone $inicio)->endOfWeek();
            $total  = TarjetaLectura::whereBetween('created_at', [$inicio, $fin])->count();
            $evolucionSemanal[] = [
                'semana' => 'S' . $inicio->format('W'),
                'inicio' => $inicio->toDateString(),
                'total'  => $total,
            ];
        }

        $tarjetasPorDia = TarjetaUniversitaria::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as total')
        )
        ->where('created_at', '>=', now()->subDays(27))
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get()
        ->keyBy('fecha');

        $registrosTarjetas = [];
        for ($i = 27; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->toDateString();
            $registrosTarjetas[] = [
                'fecha' => $fecha,
                'total' => isset($tarjetasPorDia[$fecha]) ? (int)$tarjetasPorDia[$fecha]->total : 0,
            ];
        }

        $loginsPorDia = DB::table('acceso_bitacora')
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN exito = true THEN 1 ELSE 0 END) as exitosos"),
                DB::raw("SUM(CASE WHEN exito = false THEN 1 ELSE 0 END) as fallidos")
            )
            ->whereIn('evento', ['rfid_login_success', 'rfid_login_failed'])
            ->where('created_at', '>=', now()->subDays(13))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        $loginsRfidDias = [];
        for ($i = 13; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->toDateString();
            $d     = $loginsPorDia[$fecha] ?? null;
            $loginsRfidDias[] = [
                'fecha'    => $fecha,
                'total'    => $d ? (int)$d->total    : 0,
                'exitosos' => $d ? (int)$d->exitosos : 0,
                'fallidos' => $d ? (int)$d->fallidos : 0,
            ];
        }

        $topConfirmaciones = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'))
            ->where('tipo_lectura', 'confirmacion_entrega')
            ->where('exito', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        $topConsultas = TarjetaLectura::select('modulo', DB::raw('COUNT(*) as total'))
            ->where('tipo_lectura', 'consulta_saldo')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('modulo')
            ->orderByDesc('total')
            ->get();

        $bloqueosHistorico = TarjetaUniversitaria::select(
            DB::raw("TO_CHAR(bloqueado_at, 'YYYY-MM') as mes"),
            DB::raw('COUNT(*) as total')
        )
        ->whereNotNull('bloqueado_at')
        ->where('bloqueado_at', '>=', now()->subMonths(6))
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        $ultimosLoginsRfid = DB::table('acceso_bitacora')
            ->join('usuario', 'acceso_bitacora.usuario_id', '=', 'usuario.id')
            ->select(
                'acceso_bitacora.id',
                'acceso_bitacora.evento',
                'acceso_bitacora.exito',
                'acceso_bitacora.ip',
                'acceso_bitacora.created_at',
                'usuario.nombre',
                'usuario.apellido',
                'usuario.email'
            )
            ->whereIn('acceso_bitacora.evento', ['rfid_login_success', 'rfid_login_failed'])
            ->orderByDesc('acceso_bitacora.created_at')
            ->take(8)
            ->get();

        $distribucionEstados = TarjetaUniversitaria::select(
            'estado',
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('estado')
        ->get()
        ->map(fn($e) => ['estado' => $e->estado, 'total' => (int)$e->total]);

        return Inertia::render('Admin/Tarjetas/Dashboard', [
            'stats'              => $stats,
            'lecturasPorDia'     => $diasCompletos,
            'lecturasPorModulo'  => $lecturasPorModulo,
            'usuariosActivos'    => $usuariosActivos,
            'tarjetasBloqueadas' => $tarjetasBloqueadas,
            'lecturasRecientes'  => $lecturasRecientes,

            'lecturasPorTipo'    => $lecturasPorTipo,
            'lecturasPorHora'    => $horasCompletas,
            'exitoFalloPorModulo'=> $exitoFalloPorModulo,
            'evolucionSemanal'   => $evolucionSemanal,
            'registrosTarjetas'  => $registrosTarjetas,
            'loginsRfidDias'     => $loginsRfidDias,
            'topConfirmaciones'  => $topConfirmaciones,
            'topConsultas'       => $topConsultas,
            'bloqueosHistorico'  => $bloqueosHistorico,
            'ultimosLoginsRfid'  => $ultimosLoginsRfid,
            'distribucionEstados'=> $distribucionEstados,
        ]);
    }

    private function tasaExito(): float
    {
        $total    = TarjetaLectura::where('created_at', '>=', now()->subDays(30))->count();
        $exitosas = TarjetaLectura::where('created_at', '>=', now()->subDays(30))->where('exito', true)->count();
        return $total > 0 ? round($exitosas / $total * 100, 1) : 0;
    }

    private function tarjetasSinUso(): int
    {
        $conUso = TarjetaLectura::where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('tarjeta_id')
            ->distinct('tarjeta_id')
            ->count('tarjeta_id');
        $total  = TarjetaUniversitaria::where('estado', 'activa')->count();
        return max(0, $total - $conUso);
    }
}