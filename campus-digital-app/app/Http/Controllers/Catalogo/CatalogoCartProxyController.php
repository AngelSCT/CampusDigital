<?php

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Services\Catalogo\CatalogoCartResolver;
use App\Models\Catalogo\Catalogo;
use App\Models\Usuario;
use App\Exceptions\Catalogo\CategoryUnavailableException;
use App\Exceptions\Catalogo\PriceUnavailableException;
use App\Exceptions\Catalogo\OutOfStockException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * CatalogoCartProxyController — Capa A3
 *
 * Actúa como proxy seguro entre el catálogo y la Cart API.
 *
 * CONTRATO DE SEGURIDAD CRÍTICO:
 * Ningún método de este controlador lee precio, nombre ni categoria_slug
 * del $request. Todo dato de negocio proviene exclusivamente del catálogo
 * en BD (vía CatalogoCartResolver). El frontend solo envía la referencia
 * y la cantidad.
 */
class CatalogoCartProxyController extends Controller
{
    public function __construct(
        private readonly CatalogoCartResolver $resolver,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Helper privado: cartCall
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Ejecuta una llamada HTTP a la Cart API con manejo automático de:
     * - Refresh de token en caso de 401
     * - Retorno de JsonResponse 503 en caso de ConnectionException
     *
     * @param  string  $method  Método HTTP (get, post, delete, patch, put)
     * @param  string  $path    Path relativo (e.g. '/carritos/{uuid}/items')
     * @param  array   $data    Cuerpo de la petición (ignorado en GET/DELETE sin body)
     * @return \Illuminate\Http\Client\Response|\Illuminate\Http\JsonResponse
     */
    private function cartCall(string $method, string $path, array $data = []): mixed
    {
        $baseUrl = rtrim(config('cart.api.base_url'), '/');
        $url     = $baseUrl . $path;

        $token = Cache::get('cart_module_access_token', config('cart.client.module_token'));

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withToken($token)->$method($url, $data ?: null);
        } catch (ConnectionException $e) {
            Log::warning('[CatalogoCartProxy] Cart API unavailable', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error'  => 'CART_API_UNAVAILABLE',
                'mensaje' => 'El servicio de carrito no está disponible.',
            ], 503);
        }

        // Token expirado → refrescar y reintentar UNA vez
        if ($response->status() === 401) {
            $refreshToken = Cache::get('cart_module_refresh_token', config('cart.client.refresh_token'));

            try {
                $refreshResp = Http::withToken($refreshToken)
                    ->post($baseUrl . '/tokens/refresh', [
                        'refresh_token' => $refreshToken,
                    ]);
            } catch (ConnectionException $e) {
                Log::warning('[CatalogoCartProxy] Cart API unavailable during token refresh', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'error'  => 'CART_API_UNAVAILABLE',
                    'mensaje' => 'El servicio de carrito no está disponible.',
                ], 503);
            }

            if ($refreshResp->successful()) {
                Cache::put('cart_module_access_token', $refreshResp->json('access_token'), now()->addMinutes(55));
                Cache::put('cart_module_refresh_token', $refreshResp->json('refresh_token') ?? $refreshToken, now()->addDays(6));

                $newToken = Cache::get('cart_module_access_token');

                try {
                    $response = Http::withToken($newToken)->$method($url, $data ?: null);
                } catch (ConnectionException $e) {
                    Log::warning('[CatalogoCartProxy] Cart API unavailable after token refresh', [
                        'url'   => $url,
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'error'  => 'CART_API_UNAVAILABLE',
                        'mensaje' => 'El servicio de carrito no está disponible.',
                    ], 503);
                }
            }
        }

        return $response;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper privado: assertOwnership
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifica que el UUID del carrito pertenezca a la sesión del usuario actual.
     * Aborta con 403 si no coincide Y la sesión no es null.
     * Sesión null significa que el carrito expiró y fue limpiado; agregarItem
     * maneja ese caso con auto-recovery antes de llamar assertOwnership.
     */
    private function assertOwnership(string $uuid): void
    {
        $sessionUuid = session('cart_uuid');

        // Si hay un UUID en sesión y no coincide → 403
        if ($sessionUuid !== null && $sessionUuid !== $uuid) {
            abort(response()->json([
                'error'   => 'CART_OWNERSHIP_DENIED',
                'mensaje' => 'Este carrito no pertenece a tu sesión.',
            ], 403));
        }

        // $sessionUuid === null → sesión limpia (carrito expirado).
        // Se deja pasar; el método llamante es responsable de crear uno nuevo.
        // $sessionUuid === $uuid → propiedad confirmada.
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper privado: crearNuevoCarrito
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea un carrito nuevo en la Cart API y guarda el UUID en sesión.
     * TTL de 120 minutos para evitar expiraciones durante demos.
     *
     * @return \Illuminate\Http\Client\Response|\Illuminate\Http\JsonResponse
     */
    private function crearNuevoCarrito(): mixed
    {
        $resp = $this->cartCall('post', '/carritos', [
            'usuario_ref'       => strval(auth()->id()),
            'requiere_saldo'    => true,
            'expira_en_minutos' => 120,
            'metadata'          => [],
        ]);

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        if ($resp->successful()) {
            $uuid = $resp->json('carrito_uuid');
            session([
                'cart_uuid'             => $uuid,
                'catalogo_carrito_uuid' => $uuid,
            ]);
        }

        return $resp;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. crearCarrito
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /catalogo/cart-proxy/carritos
     *
     * Crea un nuevo carrito en la Cart API asociado al usuario autenticado.
     * Guarda el UUID retornado en sesión para la verificación de propiedad.
     */
    public function crearCarrito(Request $request): JsonResponse
    {
        $resp = $this->crearNuevoCarrito();

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. obtenerCarrito
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /catalogo/cart-proxy/carritos/{uuid}
     *
     * Retorna el estado actual del carrito. Requiere propiedad de sesión.
     * Si la Cart API devuelve 404 o 409 (carrito expirado/en estado terminal),
     * limpia la sesión y devuelve el error para que CartControl reinicie.
     */
    public function obtenerCarrito(Request $request, string $uuid): JsonResponse
    {
        $this->assertOwnership($uuid);

        $resp = $this->cartCall('get', "/carritos/{$uuid}");

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        // Carrito no encontrado o en estado terminal → limpiar sesión
        if ($resp->status() === 404 || $resp->status() === 409) {
            session()->forget(['cart_uuid', 'catalogo_carrito_uuid']);
        } elseif ($resp->successful()) {
            // Refrescar el UUID en sesión por si acaso
            session(['catalogo_carrito_uuid' => $uuid]);
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. agregarItem
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /catalogo/cart-proxy/carritos/{uuid}/items
     *
     * Agrega un ítem al carrito. El frontend SOLO envía referencia_externa
     * y cantidad. Precio, nombre y categoría se resuelven desde el catálogo
     * en BD — NUNCA desde el request.
     *
     * Auto-recovery: si el carrito expiró (CART_STATE_ERROR 409), crea uno
     * nuevo automáticamente y reintenta la operación. El cliente recibe
     * el resultado del reintento transparentemente.
     */
    public function agregarItem(Request $request, string $uuid): JsonResponse
    {
        // assertOwnership se hace DESPUÉS del posible auto-recovery.
        // Nota: si session('cart_uuid') === null (sesión limpia por expiración),
        // assertOwnership lo dejará pasar para que el auto-recovery funcione.
        $this->assertOwnership($uuid);

        // Solo acepta referencia y cantidad — NADA más
        $request->validate([
            'referencia_externa' => ['required', 'string', 'max:120'],
            'cantidad'           => ['nullable', 'integer', 'min:1'],
        ]);

        // Parsear el id de catálogo desde la referencia CAT-{id}
        $id = $this->resolver->parseReferencia($request->input('referencia_externa'));

        if ($id === null) {
            return response()->json([
                'error'   => 'INVALID_REFERENCE',
                'mensaje' => 'Formato inválido. Use CAT-{id}.',
            ], 422);
        }

        // Obtener el ítem del catálogo desde BD (nunca del request)
        $catalogo = Catalogo::with(['categoria', 'inventario', 'precios'])
            ->where('id_catalogo', $id)
            ->where('activo', true)
            ->firstOrFail();

        // Construir payload con datos exclusivamente del catálogo
        try {
            $payload = $this->resolver->construirPayloadItem(
                $catalogo,
                (int) $request->input('cantidad', 1)
            );
        } catch (CategoryUnavailableException) {
            return response()->json([
                'error'   => 'CATEGORY_UNAVAILABLE',
                'mensaje' => 'Categoría no disponible.',
            ], 422);
        } catch (PriceUnavailableException) {
            return response()->json([
                'error'   => 'PRICE_UNAVAILABLE',
                'mensaje' => 'Sin precio vigente.',
            ], 422);
        } catch (OutOfStockException) {
            return response()->json([
                'error'   => 'OUT_OF_STOCK',
                'mensaje' => 'Stock insuficiente.',
            ], 422);
        }

        $targetUuid = $uuid;
        $resp       = $this->cartCall('post', "/carritos/{$targetUuid}/items", $payload);

        // ── Auto-recovery: carrito expirado o en estado terminal ──────────────
        if (
            ! ($resp instanceof JsonResponse)
            && $resp->status() === 409
            && $resp->json('error') === 'CART_STATE_ERROR'
        ) {
            Log::info('[CatalogoCartProxy] Carrito expirado, creando uno nuevo.', [
                'uuid_anterior' => $targetUuid,
                'usuario'       => auth()->id(),
            ]);

            session()->forget('cart_uuid');

            $nuevoResp = $this->crearNuevoCarrito();

            if ($nuevoResp instanceof JsonResponse) {
                // Error al crear el nuevo carrito (503, etc.)
                return $nuevoResp;
            }

            if (! $nuevoResp->successful()) {
                return response()->json($nuevoResp->json(), $nuevoResp->status());
            }

            // Carrito nuevo creado — reintentar con el nuevo UUID
            $targetUuid = session('cart_uuid');
            $resp       = $this->cartCall('post', "/carritos/{$targetUuid}/items", $payload);

            if ($resp instanceof JsonResponse) {
                return $resp;
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. eliminarItem
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * DELETE /api/catalogo/cart/carritos/{uuid}/items/{item_id}
     *
     * Elimina un ítem del carrito. Requiere propiedad de sesión.
     */
    public function eliminarItem(Request $request, string $uuid, int $item_id): JsonResponse
    {
        $this->assertOwnership($uuid);

        $resp = $this->cartCall('delete', "/carritos/{$uuid}/items/{$item_id}");

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. checkout
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/catalogo/cart/carritos/{uuid}/checkout
     *
     * Revalida CADA ítem del carrito contra el catálogo antes de confirmar:
     * - Producto sigue activo
     * - Precio no cambió desde que se agregó al carrito
     * - Categoría sigue mapeada
     * - Stock suficiente
     *
     * Solo si todo pasa, se llama al endpoint de checkout de la Cart API.
     */
    public function checkout(Request $request, string $uuid): JsonResponse
    {
        $this->assertOwnership($uuid);

        // Paso 1 — Obtener carrito actual
        $cartResp = $this->cartCall('get', "/carritos/{$uuid}");

        if ($cartResp instanceof JsonResponse) {
            return $cartResp;
        }

        $items = $cartResp->json('items') ?? [];

        // Paso 2 — Revalidar cada ítem contra el catálogo
        foreach ($items as $item) {
            $id = $this->resolver->parseReferencia($item['referencia_externa'] ?? '');

            if ($id === null) {
                return response()->json([
                    'error'  => 'PRODUCT_UNAVAILABLE',
                    'mensaje' => 'Referencia inválida.',
                ], 409);
            }

            $catalogo = Catalogo::with(['categoria', 'inventario', 'precios'])
                ->where('id_catalogo', $id)
                ->where('activo', true)
                ->first();

            if (! $catalogo) {
                return response()->json([
                    'error'  => 'PRODUCT_UNAVAILABLE',
                    'mensaje' => 'Producto no disponible.',
                ], 409);
            }

            // Precio — comparar como strings normalizados, NUNCA como floats
            $precioActual   = number_format((float) $catalogo->precioVigenteValor(), 2, '.', '');
            $precioSnapshot = number_format((float) ($item['precio_unitario'] ?? 0), 2, '.', '');

            if ($precioActual !== $precioSnapshot) {
                return response()->json([
                    'error'  => 'PRICE_CHANGED',
                    'mensaje' => 'El precio cambió, elimina el producto y agrégalo de nuevo.',
                ], 409);
            }

            // Categoría — debe seguir mapeada
            $slug = $this->resolver->slugDesdeCategoria($catalogo->categoria?->nombre);

            if ($slug === null) {
                return response()->json([
                    'error' => 'CATEGORY_UNAVAILABLE',
                ], 409);
            }

            // Stock
            if (! $this->resolver->stockDisponible($catalogo, $item['cantidad'] ?? 1)) {
                return response()->json([
                    'error' => 'OUT_OF_STOCK',
                ], 409);
            }
        }

        // Paso 3 — Confirmar en la Cart API
        $resp = $this->cartCall('post', "/carritos/{$uuid}/checkout");

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        if ($resp->status() === 500) {
            $msg = $resp->json('message') ?? '';
            if (\Illuminate\Support\Str::contains($msg, ['BindingResolution', 'Target class', 'Interface'])) {
                return response()->json([
                    'error' => 'CHECKOUT_CONFIG_ERROR',
                    'mensaje' => 'Error de configuración en el servidor de pagos. Contacta al administrador.'
                ], 503);
            }
        }

        if ($resp->successful()) {
            session()->put('last_cart_uuid', $uuid);
            session()->forget('cart_uuid');
            // Nota: errores 402 y similares NO borran la sesión;
            // el carrito permanece abierto para que el usuario reintente.

            // ── Bridge de regalo: crear PedidoTienda en escrow por cada ítem regalo ──
            try {
                $carritoModel = \App\Models\Cart\Carrito::where('uuid', $uuid)->first();
                $itemsRegalo  = $carritoModel
                    ? $carritoModel->items()
                        ->whereRaw("(metadata->>'es_regalo')::text = 'true'")
                        ->get()
                    : collect();

                Log::info('[CatalogoCartProxy] Bridge regalo', [
                    'carrito'       => $uuid,
                    'items_regalo'  => $itemsRegalo->count(),
                ]);

                foreach ($itemsRegalo as $item) {
                    $meta           = $item->metadata ?? [];
                    $destinatarioId = $meta['destinatario_id'] ?? null;
                    if (! $destinatarioId) continue;

                    $total = round((float) $item->precio_unitario * $item->cantidad, 2);

                    $pedidoTienda = \App\Models\PedidoTienda::create([
                        'usuario_id'              => auth()->id(),
                        'destinatario_id'         => (int) $destinatarioId,
                        'total'                   => $total,
                        'estado'                  => 'en_escrow',
                        'metodo_pago'             => null,
                        'gracia_hasta'            => now()->addMinutes(2),
                        'notificado_destinatario' => false,
                    ]);

                    \App\Models\PedidoDetalle::create([
                        'pedido_id'       => $pedidoTienda->id,
                        'producto_id'     => null,
                        'nombre_producto' => $item->nombre,
                        'precio_unitario' => $item->precio_unitario,
                        'cantidad'        => $item->cantidad,
                        'subtotal'        => round((float) $item->precio_unitario * $item->cantidad, 2),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('[CatalogoCartProxy] Bridge regalo error: ' . $e->getMessage(), [
                    'uuid'  => $uuid,
                    'trace' => $e->getTraceAsString(),
                ]);
                // El checkout ya fue exitoso — no revertimos, solo logueamos.
            }
            // ─────────────────────────────────────────────────────────────────
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. cancelar
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/catalogo/cart/carritos/{uuid}/cancelar
     *
     * Cancela el carrito. Limpia la sesión si la Cart API confirma el éxito.
     */
    public function cancelar(Request $request, string $uuid): JsonResponse
    {
        $this->assertOwnership($uuid);

        $resp = $this->cartCall('post', "/carritos/{$uuid}/cancelar");

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        if ($resp->successful()) {
            session()->forget('cart_uuid');
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. historico
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /api/catalogo/cart/historico
     *
     * Retorna el histórico de carritos del usuario autenticado.
     * El usuario_ref se fuerza desde auth() — se ignora cualquier query del browser.
     */
    public function historico(Request $request): JsonResponse
    {
        $usuarioRef = auth()->user()->matricula ?? auth()->id();

        $resp = $this->cartCall('get', '/historico?usuario_ref=' . urlencode($usuarioRef));

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7b. obtenerComprobante
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /catalogo/cart-proxy/comprobante/{uuid}
     *
     * Proxea al endpoint de comprobantes del Cart API.
     * El frontend lo llama tras un checkout exitoso para mostrar folio e ítems.
     */
    public function obtenerComprobante(string $uuid): JsonResponse
    {
        $resp = $this->cartCall('get', "/comprobantes/{$uuid}");

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        return response()->json($resp->json(), $resp->status());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. validarDestinatario
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /catalogo/cart-proxy/validar-destinatario
     *
     * Verifica que la matrícula y primer nombre coincidan con un usuario
     * en la BD. No puede ser el mismo usuario autenticado.
     */
    public function validarDestinatario(Request $request): JsonResponse
    {
        $request->validate([
            'matricula'    => ['required', 'string'],
            'primer_nombre' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('matricula', $request->matricula)
            ->whereRaw('LOWER(nombre) LIKE ?', [strtolower($request->primer_nombre) . '%'])
            ->first();

        if (! $usuario) {
            return response()->json([
                'error'  => 'DESTINATARIO_NO_ENCONTRADO',
                'mensaje' => 'Los datos del destinatario no coinciden.',
            ], 422);
        }

        if ($usuario->id === auth()->id()) {
            return response()->json([
                'error'  => 'DESTINATARIO_ES_REMITENTE',
                'mensaje' => 'No puedes enviarte un regalo a ti mismo.',
            ], 422);
        }

        return response()->json([
            'valido'          => true,
            'destinatario_id' => $usuario->id,
            'nombre_completo' => trim("{$usuario->nombre} {$usuario->apellido}"),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. marcarRegalo
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * PATCH /catalogo/cart-proxy/carritos/{uuid}/items/{item_id}/regalo
     *
     * Actualiza el metadata del ítem para marcarlo (o desmarcar) como regalo.
     * La lógica persiste localmente en el catálogo; el Cart API no es involucrado.
     */
    public function marcarRegalo(Request $request, string $uuid, int $item_id): JsonResponse
    {
        $this->assertOwnership($uuid);

        $request->validate([
            'es_regalo' => ['required', 'boolean'],
        ]);

        if ($request->boolean('es_regalo')) {
            $request->validate([
                'destinatario_id'         => ['required', 'integer'],
                'destinatario_matricula'  => ['required', 'string'],
                'mensaje_dedicatorio'     => ['nullable', 'string', 'max:500'],
            ]);
        }

        // Actualizar metadata en Cart API via cartCall PATCH
        $payload = $request->boolean('es_regalo')
            ? [
                'metadata' => [
                    'es_regalo'               => true,
                    'destinatario_id'         => $request->integer('destinatario_id'),
                    'destinatario_matricula'  => $request->input('destinatario_matricula'),
                    'mensaje_dedicatorio'     => $request->input('mensaje_dedicatorio') ?? '',
                ],
            ]
            : [
                'metadata' => [
                    'es_regalo'               => false,
                    'destinatario_id'         => null,
                    'destinatario_matricula'  => null,
                    'mensaje_dedicatorio'     => null,
                ],
            ];

        $resp = $this->cartCall('patch', "/carritos/{$uuid}/items/{$item_id}", $payload);

        if ($resp instanceof JsonResponse) {
            return $resp;
        }

        // Si el Cart API no soporta PATCH de items, persiste el metadata localmente.
        if ($resp->status() === 404 || $resp->status() === 405) {
            $item    = \App\Models\Cart\ItemCarrito::findOrFail($item_id);
            $newMeta = array_merge($item->metadata ?? [], $payload['metadata']);
            $item->forceFill(['metadata' => $newMeta])->save();
            return response()->json([
                'metadata' => $newMeta,
                'item_id'  => $item_id,
            ]);
        }

        return response()->json($resp->json(), $resp->status());
    }
}
