<?php

namespace App\Http\Controllers\Recargas;

use App\Http\Controllers\Controller;
use App\Models\Recarga;
use App\Models\SaldoMonedero;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RecargaController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    // ─────────────────────────────────────────────────────────────────────
    // FORMULARIO
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Muestra el formulario de recarga con saldo actual e historial.
     */
    public function mostrarFormulario()
    {
        $usuario = Auth::user();

        // Saldo desde la API del Módulo 2 (con fallback local)
        $saldoData = $this->walletService->obtenerSaldo($usuario);

        // Historial local (las 20 más recientes)
        $recargas = Recarga::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'monto'       => floatval($r->monto),
                'metodo_pago' => $r->metodo_pago,
                'estado'      => $r->estado,
                'referencia'  => $r->referencia_pago,
                'razon_fallo' => $r->razon_fallo,
                'created_at'  => $r->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Monedero/Recargar', [
            'monedero' => $saldoData['saldo_disponible'],
            'recargas' => $recargas,
            'limites'  => [
                'monto_minimo'      => 1,
                'monto_maximo'      => 5000,
                'max_recargas_dia'  => 3,
                'intervalo_minutos' => 5,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PROCESAR RECARGA
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Valida la solicitud y delega el procesamiento al WalletService.
     */
    public function procesarRecarga(Request $request)
    {
        $usuario = Auth::user();

        $validated = $request->validate([
            'monto'       => 'required|numeric|min:1|max:5000',
            'metodo_pago' => 'required|in:tarjeta,transferencia,efectivo,billetera_digital',
        ]);

        // Validar límites locales antes de llamar a la API
        $limite = $this->validarLimites($usuario);
        if ($limite['error']) {
            return back()->withErrors(['recarga' => $limite['mensaje']]);
        }

        try {
            $resultado = $this->walletService->recargar($usuario, $validated);

            $tipo = $resultado['exitosa'] ? 'success' : 'error';
            return redirect()
                ->route('modulo_8.recargar.form')
                ->with($tipo, $resultado['mensaje']);

        } catch (\Exception $e) {
            return back()->withErrors(['recarga' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // REINTENTAR
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Reintenta un pago previamente fallido.
     */
    public function reintentar($id)
    {
        $recarga = Recarga::findOrFail($id);

        if ($recarga->usuario_id !== Auth::id() || $recarga->estado !== 'fallida') {
            abort(403, 'No puedes reintentar esta recarga.');
        }

        try {
            $resultado = $this->walletService->reintentar($recarga, Auth::user());

            $tipo = $resultado['exitosa'] ? 'success' : 'error';
            return redirect()
                ->route('modulo_8.recargar.form')
                ->with($tipo, $resultado['mensaje']);

        } catch (\Exception $e) {
            return back()->withErrors(['recarga' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // COMPROBANTE
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Descarga el comprobante HTML de una recarga exitosa.
     */
    public function descargarComprobante($id)
    {
        $recarga = Recarga::findOrFail($id);

        if ($recarga->usuario_id !== Auth::id()) {
            abort(403, 'Sin permiso.');
        }

        if ($recarga->estado !== 'exitosa') {
            abort(404, 'No hay comprobante disponible para esta recarga.');
        }

        $html = $this->generarHTML($recarga, Auth::user());

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"comprobante-{$recarga->referencia_pago}.html\"");
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────

    private function validarLimites($usuario): array
    {
        $hoy = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->whereDate('created_at', today())
            ->count();

        if ($hoy >= 3) {
            return ['error' => true, 'mensaje' => 'Has alcanzado el límite de 3 recargas por día.'];
        }

        $ultima = Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->latest('created_at')
            ->first();

        if ($ultima && $ultima->created_at->diffInMinutes(now()) < 5) {
            $restantes = 5 - $ultima->created_at->diffInMinutes(now());
            return ['error' => true, 'mensaje' => "Espera {$restantes} minuto(s) antes de recargar nuevamente."];
        }

        return ['error' => false];
    }

    private function generarHTML(Recarga $recarga, $usuario): string
    {
        $metodoLabel = match ($recarga->metodo_pago) {
            'tarjeta'           => 'Tarjeta de Crédito/Débito',
            'transferencia'     => 'Transferencia Bancaria',
            'efectivo'          => 'Efectivo',
            'billetera_digital' => 'Billetera Digital',
            default             => $recarga->metodo_pago,
        };

        $fecha  = $recarga->created_at->format('d/m/Y H:i:s');
        $nombre = "{$usuario->nombre} {$usuario->apellido}";

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Comprobante de Recarga</title>
            <style>
                body { font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; }
                .container { max-width:600px; margin:0 auto; background:white; border-radius:8px;
                             box-shadow:0 2px 10px rgba(0,0,0,.1); overflow:hidden; }
                .header { background:linear-gradient(135deg,#06b6d4,#0ea5e9); color:white;
                          padding:30px; text-align:center; }
                .header h1 { margin:0 0 8px; font-size:22px; }
                .content { padding:30px; }
                .row { display:flex; justify-content:space-between; padding:10px 0;
                       border-bottom:1px solid #eee; font-size:14px; }
                .label { color:#666; font-weight:500; }
                .value { color:#333; font-weight:600; }
                .footer { text-align:center; padding:16px; font-size:12px; color:#999; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✓ Comprobante de Recarga</h1>
                    <p>Folio: {$recarga->referencia_pago}</p>
                </div>
                <div class="content">
                    <div class="row"><span class="label">Nombre</span><span class="value">{$nombre}</span></div>
                    <div class="row"><span class="label">Email</span><span class="value">{$usuario->email}</span></div>
                    <div class="row"><span class="label">Monto</span><span class="value">\${$recarga->monto}</span></div>
                    <div class="row"><span class="label">Método</span><span class="value">{$metodoLabel}</span></div>
                    <div class="row"><span class="label">Fecha</span><span class="value">{$fecha}</span></div>
                    <div class="row"><span class="label">Estado</span><span class="value">✓ EXITOSO</span></div>
                </div>
                <div class="footer">Campus Digital — Módulo 8 Recargas</div>
            </div>
        </body>
        </html>
        HTML;
    }
}
