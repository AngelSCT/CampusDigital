<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\Saldo;
use App\Models\Movimiento;
use App\Services\SimulacionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * RecargaController
 *
 * Gestiona las recargas de saldo al monedero universitario.
 * Maneja estados de transacción (pendiente, exitosa, fallida) y permite
 * reintentar pagos fallidos con historial completo.
 */
class RecargaController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly SimulacionService $simulacionService
    ) {}

    /**
     * Mostrar formulario de recarga con saldo actual, historial de recargas,
     * historial de movimientos y módulos disponibles para simular.
     */
    public function mostrarFormulario()
    {
        $usuario = Auth::user();

        // Saldo actual en tiempo real
        $saldo    = Saldo::where('usuario_id', $usuario->id)->first();
        $monedero = $saldo ? (float) $saldo->saldo : 0;

        // Últimas 20 recargas del usuario (ordenadas por fecha)
        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Últimos 30 movimientos del usuario (gastos y recargas)
        $movimientos = Movimiento::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Módulos disponibles para simular
        $modulos = $this->simulacionService->obtenerModulos();

        // Límites de recarga configurables
        $limites = [
            'monto_minimo'      => 1,
            'monto_maximo'      => 5000,
            'max_recargas_dia'  => 3,
            'intervalo_minutos' => 5,
        ];

        // Flash messages de simulación previa
        $simulacionOk  = session('simulacion_ok');
        $simulacionErr = session('errors') ? session('errors')->first('simulacion') : null;

        return Inertia::render('Monedero/Recargar', [
            'monedero'     => $monedero,
            'recargas'     => $recargas,
            'movimientos'  => $movimientos,
            'modulos'      => $modulos,
            'limites'      => $limites,
            'simulacionOk' => $simulacionOk,
        ]);
    }

    /**
     * Procesar recarga.
     * Estados: pendiente → exitosa | fallida
     * Solo los pagos exitosos incrementan el saldo disponible.
     */
    public function procesarRecarga(Request $request)
    {
        $usuario = Auth::user();

        $validated = $request->validate([
            'monto'       => 'required|numeric|min:1|max:5000',
            'metodo_pago' => 'required|in:tarjeta,transferencia,efectivo,billetera_digital',
        ]);

        // Validar límites antes de procesar
        $validacion = $this->validarLimites($usuario);
        if ($validacion['error']) {
            return back()->withErrors(['recarga' => $validacion['mensaje']]);
        }

        try {
            $recarga = DB::transaction(function () use ($usuario, $validated) {

                // Crear registro en estado pendiente
                $recarga = Recarga::create([
                    'usuario_id'  => $usuario->id,
                    'monto'       => $validated['monto'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado'      => 'pendiente',
                    'referencia'  => 'WEB-' . strtoupper(uniqid()),
                ]);

                // Simular procesamiento de pago (80% de éxito)
                // En producción: integración con pasarela de pago real
                $pagoExitoso = $this->procesarPago($validated['metodo_pago']);

                if ($pagoExitoso) {
                    $this->procesarAbonoExitoso($recarga, $usuario);
                    $recarga->update(['estado' => 'exitosa']);
                } else {
                    $recarga->update([
                        'estado'      => 'fallida',
                        'razon_fallo' => 'Pago rechazado por la entidad financiera',
                    ]);
                }

                return $recarga;
            });

            if ($recarga->estado === 'exitosa') {
                return redirect()->route('modulo_8.recargar.form')
                    ->with('success', "Recarga de \${$validated['monto']} realizada exitosamente. Folio: {$recarga->referencia}");
            } else {
                return redirect()->route('modulo_8.recargar.form')
                    ->with('error', "La recarga falló. Puedes reintentar. Folio: {$recarga->referencia}");
            }

        } catch (\Exception $e) {
            return back()->withErrors([
                'recarga' => 'Error al procesar la recarga: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Reintentar un pago fallido.
     * Solo aplicable a recargas en estado 'fallida' del usuario autenticado.
     */
    public function reintentar($id)
    {
        $recarga = Recarga::findOrFail($id);

        if ($recarga->usuario_id !== Auth::id() || $recarga->estado !== 'fallida') {
            abort(403, 'No puedes reintentar esta recarga');
        }

        try {
            DB::transaction(function () use ($recarga) {
                $recarga->update(['estado' => 'pendiente']);

                $pagoExitoso = $this->procesarPago($recarga->metodo_pago);

                if ($pagoExitoso) {
                    $this->procesarAbonoExitoso($recarga, $recarga->usuario);
                    $recarga->update(['estado' => 'exitosa']);
                } else {
                    $recarga->update([
                        'estado'      => 'fallida',
                        'razon_fallo' => 'Reintento fallido. Pago rechazado nuevamente.',
                    ]);
                }
            });

            $mensaje = $recarga->estado === 'exitosa'
                ? "Reintento exitoso. Se acreditaron \${$recarga->monto}"
                : 'El reintento falló nuevamente. Intenta más tarde.';

            return redirect()->route('modulo_8.recargar.form')
                ->with($recarga->estado === 'exitosa' ? 'success' : 'error', $mensaje);

        } catch (\Exception $e) {
            return back()->withErrors(['recarga' => $e->getMessage()]);
        }
    }

    /**
     * Descargar comprobante HTML de una recarga exitosa.
     */
    public function descargarComprobante($id)
    {
        $recarga = Recarga::findOrFail($id);

        if ($recarga->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permiso');
        }

        if ($recarga->estado !== 'exitosa') {
            abort(404, 'No hay comprobante disponible');
        }

        $usuario = $recarga->usuario;
        $html    = $this->generarHTML($recarga, $usuario);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"comprobante-{$recarga->referencia}.html\"");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Métodos privados
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Procesa el abono al saldo cuando una recarga es exitosa.
     * Usa lockForUpdate() para garantizar atomicidad.
     */
    private function procesarAbonoExitoso(Recarga $recarga, $usuario): void
    {
        $saldo = Saldo::where('usuario_id', $usuario->id)
            ->lockForUpdate()
            ->firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['saldo' => 0]
            );

        $saldoAnterior = (float) $saldo->saldo;
        $saldo->saldo += $recarga->monto;
        $saldo->save();

        // Registrar movimiento de tipo recarga
        $movimiento = Movimiento::create([
            'usuario_id'      => $usuario->id,
            'tipo'            => 'recarga',
            'monto'           => $recarga->monto,
            'estado'          => 'exitosa',
            'modulo'          => 'recarga',
            'concepto'        => "Recarga de saldo vía {$recarga->metodo_pago}",
            'saldo_anterior'  => $saldoAnterior,
            'saldo_nuevo'     => (float) $saldo->saldo,
            'referencia_type' => Recarga::class,
            'referencia_id'   => $recarga->id,
        ]);

        $recarga->update(['saldo_movimiento_id' => $movimiento->id]);
    }

    /**
     * Simula el procesamiento del pago con 80% de probabilidad de éxito.
     * En producción, se reemplaza por integración real con pasarela de pago.
     */
    private function procesarPago(string $metodo): bool
    {
        return rand(1, 100) <= 80;
    }

    /**
     * Valida los límites de recarga del usuario (max por día, intervalo mínimo).
     */
    private function validarLimites($usuario): array
    {
        $recargasHoy = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->whereDate('created_at', today())
            ->count();

        if ($recargasHoy >= 3) {
            return [
                'error'   => true,
                'mensaje' => 'Has alcanzado el límite de 3 recargas por día.',
            ];
        }

        $ultimaRecarga = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->latest('created_at')
            ->first();

        if ($ultimaRecarga && $ultimaRecarga->created_at->diffInMinutes(now()) < 5) {
            $minutosRestantes = 5 - $ultimaRecarga->created_at->diffInMinutes(now());
            return [
                'error'   => true,
                'mensaje' => "Espera {$minutosRestantes} minuto(s) antes de recargar nuevamente.",
            ];
        }

        return ['error' => false];
    }

    /**
     * Genera el HTML del comprobante de recarga.
     */
    private function generarHTML(Recarga $recarga, $usuario): string
    {
        $metodoLabel = match ($recarga->metodo_pago) {
            'tarjeta'           => 'Tarjeta de Crédito/Débito',
            'transferencia'     => 'Transferencia Bancaria',
            'efectivo'          => 'Efectivo',
            'billetera_digital' => 'Billetera Digital',
            default             => $recarga->metodo_pago,
        };

        $fecha = $recarga->created_at->format('d/m/Y H:i:s');

        return "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <title>Comprobante</title>
                <style>
                    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
                    .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
                    .header { background: linear-gradient(135deg, #06b6d4, #0ea5e9); color: white; padding: 30px; text-align: center; }
                    .content { padding: 30px; }
                    .row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                    .label { color: #666; font-weight: 500; }
                    .value { color: #333; font-weight: 600; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>✓ Comprobante de Recarga</h1>
                        <p>Folio: {$recarga->referencia}</p>
                    </div>
                    <div class='content'>
                        <div class='row'>
                            <span class='label'>Nombre</span>
                            <span class='value'>{$usuario->nombre} {$usuario->apellido}</span>
                        </div>
                        <div class='row'>
                            <span class='label'>Monto</span>
                            <span class='value'>\${$recarga->monto}</span>
                        </div>
                        <div class='row'>
                            <span class='label'>Método</span>
                            <span class='value'>{$metodoLabel}</span>
                        </div>
                        <div class='row'>
                            <span class='label'>Fecha</span>
                            <span class='value'>{$fecha}</span>
                        </div>
                        <div class='row'>
                            <span class='label'>Estado</span>
                            <span class='value'>EXITOSO</span>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}

