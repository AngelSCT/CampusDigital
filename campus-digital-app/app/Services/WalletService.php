<?php

namespace App\Services;

use App\Models\Recarga;
use App\Models\Movimiento;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.modulo2_api.base_url', ''), '/');
        $this->apiKey  = config('services.modulo2_api.api_key', '');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP BASE
    // ─────────────────────────────────────────────────────────────────────────

    private function http()
    {
        return Http::withHeaders([
            'X-API-KEY' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->baseUrl($this->baseUrl . '/api');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SALDO
    // ─────────────────────────────────────────────────────────────────────────

    public function obtenerSaldo($usuario): array
    {
        try {
            $response = $this->http()
                ->get("/saldo-monederos/usuario/{$usuario->id}");

            if ($response->successful()) {
                $data = $response->json();

                SaldoMonedero::updateOrCreate(
                    ['usuario_id' => $usuario->id],
                    [
                        'saldo_disponible' => $data['saldo_disponible'] ?? 0,
                        'saldo_retenido'   => $data['saldo_retenido']   ?? 0,
                    ]
                );

                return [
                    'id'               => $data['id']               ?? null,
                    'saldo_disponible' => floatval($data['saldo_disponible'] ?? 0),
                    'saldo_retenido'   => floatval($data['saldo_retenido']   ?? 0),
                    'fuente'           => 'api',
                ];
            }

            return $this->saldoLocal($usuario);

        } catch (\Exception $e) {
            Log::warning("WalletService::obtenerSaldo - API no disponible: " . $e->getMessage());
            return $this->saldoLocal($usuario);
        }
    }

    private function saldoLocal($usuario): array
    {
        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();
        return [
            'id'               => $monedero?->id,
            'saldo_disponible' => floatval($monedero?->saldo_disponible ?? 0),
            'saldo_retenido'   => floatval($monedero?->saldo_retenido   ?? 0),
            'fuente'           => 'local',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECARGAS
    // ─────────────────────────────────────────────────────────────────────────

    public function recargar($usuario, array $datos): array
    {
        $referencia = 'MOD8-' . strtoupper(uniqid());

        $recargaLocal = Recarga::create([
            'usuario_id'  => $usuario->id,
            'monto'       => $datos['monto'],
            'metodo_pago' => $datos['metodo_pago'],
            'estado'      => 'pendiente',
            'referencia'  => $referencia,
        ]);

        try {
            $response = $this->http()->post('/recargas', [
                'usuario_id'      => $usuario->id,
                'monto'           => $datos['monto'],
                'metodo_pago'     => $datos['metodo_pago'],
                'referencia_pago' => $referencia,
                'resultado_pago'  => 'exitoso',
                'meta_json'       => [
                    'modulo_origen' => 'modulo_8',
                    'ip'            => request()->ip(),
                ],
            ]);

            if ($response->successful()) {
                $apiData   = $response->json();
                $estadoApi = $apiData['estado'] ?? 'exitoso';

                if (in_array($estadoApi, ['exitoso', 'exitosa'])) {
                    $movimiento = $this->registrarMovimientoLocal(
                        usuario:      $usuario,
                        monto:        $datos['monto'],
                        tipo:         'recarga',
                        referenciaId: $recargaLocal->id,
                    );

                    $recargaLocal->update([
                        'estado' => Recarga::ESTADO_EXITOSO,
                    ]);

                    return [
                        'exitosa' => true,
                        'recarga' => $recargaLocal->fresh(),
                        'mensaje' => "Recarga de \${$datos['monto']} realizada exitosamente. Folio: {$referencia}",
                    ];

                } else {
                    $recargaLocal->update([
                        'estado'      => 'fallida',
                        'razon_fallo' => $apiData['razon_fallo'] ?? 'Pago rechazado por la entidad financiera.',
                    ]);

                    return [
                        'exitosa' => false,
                        'recarga' => $recargaLocal->fresh(),
                        'mensaje' => 'La recarga falló. Puedes reintentar. Folio: ' . $referencia,
                    ];
                }

            } elseif ($response->status() >= 500) {
                $recargaLocal->update([
                    'estado'      => 'fallida',
                    'razon_fallo' => 'Error interno del servidor de pagos.',
                ]);
                return [
                    'exitosa' => false,
                    'recarga' => $recargaLocal->fresh(),
                    'mensaje' => 'El servidor de pagos no está disponible. Intenta más tarde.',
                ];

            } else {
                $error = $response->json('message') ?? 'Error al procesar el pago.';
                $recargaLocal->update(['estado' => 'fallida', 'razon_fallo' => $error]);
                return [
                    'exitosa' => false,
                    'recarga' => $recargaLocal->fresh(),
                    'mensaje' => $error,
                ];
            }

        } catch (\Exception $e) {
            Log::warning("WalletService::recargar - API no disponible, modo local: " . $e->getMessage());

            $movimiento = $this->registrarMovimientoLocal(
                usuario:      $usuario,
                monto:        $datos['monto'],
                tipo:         'recarga',
                referenciaId: $recargaLocal->id,
            );

            $recargaLocal->update([
                'estado' => Recarga::ESTADO_EXITOSO,
            ]);

            return [
                'exitosa' => true,
                'recarga' => $recargaLocal->fresh(),
                'mensaje' => "Recarga de \${$datos['monto']} realizada exitosamente. Folio: {$referencia}",
            ];
        }
    }

    public function reintentar(Recarga $recarga, $usuario): array
    {
        $recarga->update(['estado' => 'pendiente', 'razon_fallo' => null]);

        return $this->recargar($usuario, [
            'monto'       => $recarga->monto,
            'metodo_pago' => $recarga->metodo_pago,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MOVIMIENTOS
    // ─────────────────────────────────────────────────────────────────────────

    public function movimientos($usuario): array
    {
        try {
            $response = $this->http()->get('/saldo-movimientos', [
                'usuario_id' => $usuario->id,
            ]);

            if ($response->successful()) {
                return $response->json('data') ?? $response->json() ?? [];
            }

        } catch (\Exception $e) {
            Log::warning("WalletService::movimientos - API no disponible: " . $e->getMessage());
        }

        return SaldoMovimiento::where('usuario_id', $usuario->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAGOS
    // ─────────────────────────────────────────────────────────────────────────

    public function pagar($usuario, array $datos): array
    {
        $saldo = $this->obtenerSaldo($usuario);
        if ($saldo['saldo_disponible'] < $datos['monto']) {
            throw new \Exception("Saldo insuficiente. Disponible: \${$saldo['saldo_disponible']}");
        }

        try {
            if (!empty($datos['pedido_id'])) {
                $response = $this->http()->post("/pedidos/{$datos['pedido_id']}/estado", [
                    'estado'     => 'pagado',
                    'usuario_id' => $usuario->id,
                ]);
            } else {
                $response = $this->http()->post('/saldo-movimientos', [
                    'usuario_id'       => $usuario->id,
                    'tipo'             => 'cargo',
                    'monto'            => $datos['monto'],
                    'modulo'           => 'modulo_8',
                    'concepto'         => $datos['concepto'] ?? 'Pago desde Módulo 8',
                    'referencia_tabla' => $datos['referencia_tabla'] ?? null,
                    'referencia_id'    => $datos['referencia_id']    ?? null,
                ]);
            }

            if ($response->successful()) {
                $movimiento = $this->registrarMovimientoLocal(
                    usuario:      $usuario,
                    monto:        $datos['monto'],
                    tipo:         'pago',
                    referenciaId: $datos['pedido_id'] ?? null,
                );

                return [
                    'exitoso'    => true,
                    'movimiento' => $movimiento,
                    'data'       => $response->json(),
                ];
            }

            throw new \Exception($response->json('message') ?? 'Error al procesar el pago.');

        } catch (\Exception $e) {
            Log::error("WalletService::pagar - " . $e->getMessage());
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COMPROBANTES
    // ─────────────────────────────────────────────────────────────────────────

    public function comprobantes($usuario)
    {
        try {
            $response = $this->http()->get("/recargas/usuario/{$usuario->id}");

            if ($response->successful()) {
                $data = collect($response->json('data') ?? $response->json() ?? []);
                return $data->filter(fn($r) => in_array($r['estado'] ?? '', ['exitoso', 'exitosa']));
            }

        } catch (\Exception $e) {
            Log::warning("WalletService::comprobantes - API no disponible: " . $e->getMessage());
        }

        return Recarga::where('usuario_id', $usuario->id)
            ->where('estado', 'exitosa')
            ->orderByDesc('created_at')
            ->get();
    }

    public function detalleRecarga(int $recargaId): ?array
    {
        try {
            $response = $this->http()->get("/recargas/{$recargaId}");
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::warning("WalletService::detalleRecarga - " . $e->getMessage());
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    private function registrarMovimientoLocal(
        $usuario,
        float $monto,
        string $tipo,
        ?int $referenciaId = null
    ): Movimiento {
        $monedero = SaldoMonedero::where('usuario_id', $usuario->id)->first();
        if ($monedero) {
            $monedero->saldo_disponible = $tipo === 'recarga'
                ? $monedero->saldo_disponible + $monto
                : $monedero->saldo_disponible - $monto;
            $monedero->save();
        }

        return Movimiento::create([
            'usuario_id'      => $usuario->id,
            'tipo'            => $tipo,
            'monto'           => $monto,
            'estado'          => 'exitosa',
            'referencia_type' => 'App\\Models\\Recarga',
            'referencia_id'   => $referenciaId,
        ]);
    }
}
