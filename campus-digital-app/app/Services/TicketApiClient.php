<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP para los endpoints del API legacy de tickets.
 *
 * Autenticación mediante header X-API-KEY.
 * Configuración en config('tickets.api.*').
 */
class TicketApiClient
{
    private string $baseUrl;
    private int    $timeout;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('tickets.api.base_url', 'http://localhost'), '/');
        $this->timeout = (int) config('tickets.api.timeout', 5);
        $this->apiKey  = (string) config('tickets.api.api_key', '');
    }

    // ──────────────────────────────────────────────────────────────
    //  Áreas
    // ──────────────────────────────────────────────────────────────

    /**
     * GET /api/areas — Obtiene todas las áreas.
     *
     * @return array Lista de áreas o array vacío si falla.
     */
    public function obtenerAreas(): array
    {
        try {
            $response = $this->http()
                ->get($this->baseUrl . '/api/areas');

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('TicketApiClient::obtenerAreas — respuesta no exitosa', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('TicketApiClient::obtenerAreas — excepción', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * GET /api/areas/{id} — Obtiene un área por ID.
     *
     * @param  int  $id  Identificador del área.
     * @return array|null Datos del área o null si no existe.
     */
    public function obtenerArea(int $id): ?array
    {
        try {
            $response = $this->http()
                ->get($this->baseUrl . '/api/areas/' . $id);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('TicketApiClient::obtenerArea — respuesta no exitosa', [
                'id'     => $id,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('TicketApiClient::obtenerArea — excepción', [
                'id'      => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Saldo / Monederos
    // ──────────────────────────────────────────────────────────────

    /**
     * GET /api/saldo-monederos/usuario/{usuario_id} — Consulta el saldo del monedero de un usuario.
     *
     * @param  int  $usuarioId  ID del usuario.
     * @return array|null {saldo_disponible, saldo_retenido, ...} o null si no tiene monedero.
     */
    public function consultarSaldo(int $usuarioId): ?array
    {
        try {
            $response = $this->http()
                ->get($this->baseUrl . '/api/saldo-monederos/usuario/' . $usuarioId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('TicketApiClient::consultarSaldo — respuesta no exitosa', [
                'usuario_id' => $usuarioId,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('TicketApiClient::consultarSaldo — excepción', [
                'usuario_id' => $usuarioId,
                'message'    => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * POST /api/saldo-movimientos — Registra un movimiento de saldo (cargo o abono).
     *
     * @param  array  $data  {usuario_id, tipo, monto, modulo, concepto, ...}
     * @return array|null Respuesta con el nuevo saldo o null si falla.
     */
    public function registrarMovimiento(array $data): ?array
    {
        try {
            $response = $this->http()
                ->post($this->baseUrl . '/api/saldo-movimientos', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('TicketApiClient::registrarMovimiento — respuesta no exitosa', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('TicketApiClient::registrarMovimiento — excepción', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  HTTP base
    // ──────────────────────────────────────────────────────────────

    /**
     * Retorna un PendingRequest configurado con timeout, API key y retry.
     */
    private function http()
    {
        return Http::timeout($this->timeout)
            ->withHeaders(['X-API-KEY' => $this->apiKey])
            ->retry(2, 200, fn ($e) => $e instanceof ConnectionException);
    }
}
