<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\Usuario;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RecargaController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Mostrar formulario de recarga
     */
    public function mostrarFormulario()
    {
        $usuario = Auth::user();

        // Obtener saldo del usuario - ACTUALIZADO EN TIEMPO REAL
        $saldo = \App\Models\Saldo::where('usuario_id', $usuario->id)->first();
        $monedero = $saldo ? $saldo->saldo : 0;

        // Últimas 20 recargas del usuario
        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Límites configurables
        $limites = [
            'monto_minimo' => 1,
            'monto_maximo' => 5000,
            'max_recargas_dia' => 3,
            'intervalo_minutos' => 5
        ];

        return Inertia::render('Monedero/Recargar', [
            'monedero' => $monedero,  // ← CAMBIO: pasar el saldo directo
            'recargas' => $recargas,
            'limites' => $limites,
        ]);
    }

    /**
     * Procesar recarga
     * CRITERIO 2.1: Registra estados: pendiente, exitoso, fallido
     * CRITERIO 2.4: Guarda fecha, hora y método de pago
     */
    public function procesarRecarga(Request $request)
    {
        $usuario = Auth::user();

        // Validar entrada
        $validated = $request->validate([
            'monto' => 'required|numeric|min:1|max:5000',
            'metodo_pago' => 'required|in:tarjeta,efectivo',
        ]);

        // Validar límites
        $validacion = $this->validarLimites($usuario);
        if ($validacion['error']) {
            return back()->withErrors(['recarga' => $validacion['mensaje']]);
        }

        try {
            $recarga = DB::transaction(function () use ($usuario, $validated, $request) {

                // PASO 1: Crear registro de recarga en estado PENDIENTE
                $recarga = Recarga::create([
                    'usuario_id' => $usuario->id,
                    'monto' => $validated['monto'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'estado' => 'pendiente', // CRITERIO 2.1: Estado pendiente
                    'referencia' => 'WEB-' . strtoupper(uniqid()),
                ]);

                // PASO 2: Simular procesamiento de pago
                // En producción aquí iría la integración con la pasarela de pago
                $pagoExitoso = $this->procesarPago($validated['metodo_pago']);

                if ($pagoExitoso) {
                    // CRITERIO 2.2: Solo los exitosos generan abono
                    $this->procesarAbonoExitoso($recarga, $usuario);

                    $recarga->update(['estado' => 'exitosa']); // CRITERIO 2.1: Estado exitoso
                } else {
                    // CRITERIO 2.3: Los fallidos permiten reintento
                    $recarga->update([
                        'estado' => 'fallida', // CRITERIO 2.1: Estado fallido
                        'razon_fallo' => 'Pago rechazado por la entidad financiera'
                    ]);
                }

                return $recarga;
            });

            if ($recarga->estado === 'exitosa') {
                return redirect()->route('modulo_8.recargar.form')
                    ->with('success', "Recarga de \${$validated['monto']} realizada exitosamente. Folio: {$recarga->referencia}")
                    ->with('saldo_actualizado', true);  // ← AGREGAR ESTO
            } else {
                return redirect()->route('modulo_8.recargar.form')
                    ->with('error', "La recarga falló. Puedes reintentar. Folio: {$recarga->referencia}");
            }

        } catch (\Exception $e) {
            return back()->withErrors([
                'recarga' => 'Error al procesar la recarga: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reintentar un pago fallido
     * CRITERIO 2.3: Los pagos fallidos permiten reintento
     */
    public function reintentar($id)
    {
        $recarga = Recarga::findOrFail($id);

        // Solo se puede reintentar si es fallida y pertenece al usuario
        if ($recarga->usuario_id !== Auth::id() || $recarga->estado !== 'fallida') {
            abort(403, 'No puedes reintentar esta recarga');
        }

        try {
            DB::transaction(function () use ($recarga) {
                // Cambiar a pendiente y reintentar
                $recarga->update(['estado' => 'pendiente']);

                // Simular pago nuevamente
                $pagoExitoso = $this->procesarPago($recarga->metodo_pago);

                if ($pagoExitoso) {
                    // CRITERIO 2.2: Procesar abono solo si es exitoso
                    $this->procesarAbonoExitoso($recarga, $recarga->usuario);
                    $recarga->update(['estado' => 'exitosa']);
                } else {
                    $recarga->update([
                        'estado' => 'fallida',
                        'razon_fallo' => 'Reintento fallido. Pago rechazado nuevamente.'
                    ]);
                }
            });

            $mensaje = $recarga->estado === 'exitosa'
                ? "Reintento exitoso. Se acreditó ${$recarga->monto}"
                : "El reintento falló nuevamente. Intenta más tarde.";

            return redirect()->route('modulo_8.recargar.form')
                ->with($recarga->estado === 'exitosa' ? 'success' : 'error', $mensaje)
                ->with('saldo_actualizado', $recarga->estado === 'exitosa');  // ← AGREGAR ESTO

        } catch (\Exception $e) {
            return back()->withErrors(['recarga' => $e->getMessage()]);
        }
    }

    /**
     * Procesar abono exitoso
     * CRITERIO 2.2: Solo los pagos exitosos generan abono
     */
    private function procesarAbonoExitoso($recarga, $usuario)
    {
        // Obtener o crear saldo
        $saldo = \App\Models\Saldo::where('usuario_id', $usuario->id)->first();

        if (!$saldo) {
            $saldo = \App\Models\Saldo::create([
                'usuario_id' => $usuario->id,
                'saldo' => 0,
            ]);
        }

        // Incrementar saldo
        $saldo->saldo += $recarga->monto;
        $saldo->save();

        // Crear movimiento con todos los datos necesarios
        $movimiento = \App\Models\Movimiento::create([
            'usuario_id' => $usuario->id,
            'tipo' => 'recarga',
            'monto' => $recarga->monto,
            'estado' => 'exitosa',
            'referencia_type' => 'App\\Models\\Recarga',
            'referencia_id' => $recarga->id,
        ]);

        // Vincular la recarga al movimiento
        $recarga->update([
            'saldo_movimiento_id' => $movimiento->id,
        ]);
    }

    /**
     * Simular procesamiento de pago
     * En producción, aquí iría la integración con pasarela de pago
     */
    private function procesarPago($metodo)
    {
        // 80% de probabilidad de éxito (simula pagos reales)
        return rand(1, 100) <= 80;
    }

    /**
     * Descargar comprobante
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
        $html = $this->generarHTML($recarga, $usuario);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"comprobante-{$recarga->referencia}.html\"");
    }

    /**
     * Validar límites
     */
    private function validarLimites($usuario)
    {
        // Máximo 3 recargas por día
        $recargasHoy = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->whereDate('created_at', today())
            ->count();

        if ($recargasHoy >= 3) {
            return [
                'error' => true,
                'mensaje' => 'Has alcanzado el límite de 3 recargas por día.'
            ];
        }

        // Mínimo 5 minutos entre recargas
        $ultimaRecarga = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->latest('created_at')
            ->first();

        if ($ultimaRecarga && $ultimaRecarga->created_at->diffInMinutes(now()) < 5) {
            $minutosRestantes = 5 - $ultimaRecarga->created_at->diffInMinutes(now());
            return [
                'error' => true,
                'mensaje' => "Espera {$minutosRestantes} minuto(s) antes de recargar nuevamente."
            ];
        }

        return ['error' => false];
    }

    /**
     * Generar HTML comprobante
     */
    private function generarHTML($recarga, $usuario)
    {
        $metodoLabel = match ($recarga->metodo_pago) {
            'tarjeta' => 'Tarjeta de Crédito/Débito',
            'efectivo' => 'Efectivo',
            default => $recarga->metodo_pago
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