<?php

namespace App\Services;

use App\Models\Comprobante;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CartComprobanteService — Módulo 8
 *
 * Consume la API del Módulo 4 (Carrito) para obtener los datos
 * del pedido y generar el comprobante/ticket de venta.
 *
 * Configuración necesaria en config/services.php:
 *   'modulo4_cart' => [
 *       'base_url'      => env('MODULO4_CART_URL'),
 *       'access_token'  => env('MODULO4_CART_TOKEN'),
 *       'refresh_token' => env('MODULO4_CART_REFRESH_TOKEN'),
 *   ]
 */
class CartComprobanteService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.modulo4_cart.base_url', ''), '/');
        $this->token   = config('services.modulo4_cart.access_token', '');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP BASE
    // ─────────────────────────────────────────────────────────────────────────

    private function http()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OBTENER COMPROBANTE DEL MÓDULO 4
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Consulta el comprobante del carrito al Módulo 4 y lo guarda localmente.
     *
     * @param  string $carritoUuid
     * @return array  ['ok' => bool, 'comprobante' => Comprobante|null, 'data' => array]
     */
    public function obtenerYGuardar(string $carritoUuid): array
    {
        try {
            $response = $this->http()->get("/api/cart/comprobantes/{$carritoUuid}");

            if ($response->successful()) {
                $data = $response->json();

                // Guardar o actualizar comprobante local
                $comprobante = Comprobante::updateOrCreate(
                    ['carrito_uuid' => $carritoUuid],
                    [
                        'folio'              => $data['folio'] ?? $this->generarFolio(),
                        'usuario_ref'        => $data['usuario_ref'] ?? $data['usuario_id'] ?? null,
                        'modulo_origen'      => $data['modulo'] ?? 'catalogo',
                        'items'              => $data['items'] ?? [],
                        'total'              => $data['total'] ?? 0,
                        'estado'             => $data['estado'] ?? 'confirmado',
                        'fecha_confirmacion' => $data['fecha_confirmacion'] ?? now(),
                        'data_raw'           => $data,
                    ]
                );

                return [
                    'ok'          => true,
                    'comprobante' => $comprobante,
                    'data'        => $data,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'ok'          => false,
                    'comprobante' => null,
                    'error'       => 'El carrito no existe o aún no está confirmado.',
                    'code'        => 404,
                ];
            }

            Log::warning("CartComprobanteService - Módulo 4 respondió {$response->status()}: " . $response->body());

            return [
                'ok'    => false,
                'error' => 'Error al obtener comprobante del sistema de carrito.',
                'code'  => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error("CartComprobanteService::obtenerYGuardar - " . $e->getMessage());
            return [
                'ok'    => false,
                'error' => 'Servicio de carrito no disponible. Intenta más tarde.',
                'code'  => 503,
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERAR TICKET PARA MÓDULO 4
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera el ticket completo que el Módulo 4 espera recibir.
     * Formato exacto según especificación del equipo Módulo 4.
     *
     * @param  string $carritoUuid
     * @return array
     */
    public function generarTicket(string $carritoUuid): array
    {
        $resultado = $this->obtenerYGuardar($carritoUuid);

        if (!$resultado['ok']) {
            return $resultado;
        }

        $comprobante = $resultado['comprobante'];
        $data        = $resultado['data'];

        // Generar QR en base64 (texto simple como QR placeholder)
        $qrData   = "TICKET:{$comprobante->folio}|UUID:{$carritoUuid}|TOTAL:{$comprobante->total}";
        $qrBase64 = base64_encode($qrData);

        $ticket = [
            'ticket_id' => 'TKT-' . strtoupper(substr($carritoUuid, 0, 8)),
            'folio'     => $comprobante->folio,
            'fecha'     => $comprobante->fecha_confirmacion instanceof \Carbon\Carbon
                ? $comprobante->fecha_confirmacion->toIso8601String()
                : now()->toIso8601String(),
            'items'     => collect($data['items'] ?? [])->map(fn($i) => [
                'referencia_externa' => $i['referencia_externa'] ?? null,
                'nombre'             => $i['nombre'] ?? 'Producto',
                'precio_unitario'    => floatval($i['precio_unitario'] ?? 0),
                'cantidad'           => intval($i['cantidad'] ?? 1),
                'subtotal'           => floatval($i['subtotal'] ?? 0),
                'categoria_slug'     => $i['categoria_slug'] ?? null,
            ])->toArray(),
            'total'    => floatval($comprobante->total),
            'qr_code'  => $qrBase64,
        ];

        // Guardar ticket_id en el comprobante
        $comprobante->update(['ticket_id' => $ticket['ticket_id']]);

        return [
            'ok'     => true,
            'ticket' => $ticket,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function generarFolio(): string
    {
        $fecha = now()->format('Ymd');
        $seq   = str_pad(Comprobante::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        return "PED-{$fecha}-{$seq}";
    }
}
