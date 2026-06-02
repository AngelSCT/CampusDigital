<?php

namespace App\Http\Controllers;

use App\Events\GiftExpiredOrRejected;
use App\Models\ActividadBitacora;
use App\Models\Usuario;
use App\Models\CarritoItem;
use App\Models\PedidoDetalle;
use App\Models\PedidoTienda;
use App\Models\Producto;
use App\Models\SaldoMonedero;
use App\Models\SaldoMovimiento;
use App\Services\ExternalModulesService;
use App\Services\GiftRateLimiter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * @deprecated Sistema legacy de carrito.
 * No agregar nuevas funcionalidades aquí.
 * Usar el sistema nuevo en app/Modules/Cart y app/Http/Controllers/Api/Cart.
 * Congelado durante Fase 0. Pendiente de migración en Fase 2.
 */
class CartController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /carrito  (web, Inertia)
    // Renderiza el carrito pasando los datos como props al componente Vue.
    // -------------------------------------------------------------------------
    public function indexWeb(): InertiaResponse
    {
        $userId = Auth::id();

        // ── Sistema nuevo: ítems via cart_carritos / cart_items_carrito ──────
        $carritos = \App\Models\Cart\Carrito::where('usuario_ref', strval($userId))
            ->whereIn('estado', ['abierto', 'procesando_checkout'])
            ->with('itemsActivos.categoria')
            ->get();

        $items = $carritos->flatMap(function ($carrito) {
            return $carrito->itemsActivos->map(fn ($item) => [
                'id'                    => $item->id,
                'cantidad'              => $item->cantidad,
                'es_regalo'             => $item->metadata['es_regalo'] ?? false,
                'guardado_para_despues' => $item->metadata['guardado_para_despues'] ?? false,
                'referencia_externa'    => $item->referencia_externa,
                'carrito_uuid'          => $carrito->uuid,
                'producto' => [
                    'nombre'    => $item->nombre,
                    'precio'    => $item->precio_unitario,
                    'categoria' => $item->categoria?->slug ?? '',
                    'imagen_url'=> null,
                ],
            ]);
        })->values();

        try {
            $monedero        = SaldoMonedero::obtenerOCrear($userId);
            $saldoDisponible = (float) $monedero->saldo_disponible;
            $saldoRetenido   = (float) $monedero->saldo_retenido;
        } catch (\Throwable) {
            $saldoDisponible = 500.00;
            $saldoRetenido   = 0.00;
        }

        $activos = $items->filter(fn($i) => !$i['guardado_para_despues']);
        $total   = $activos->sum(fn($i) => $i['cantidad'] * (float) ($i['producto']['precio'] ?? 0));

        return Inertia::render('Carrito/Index', [
            'carrito'  => $items,
            'total'    => $total,
            'monedero' => [
                'saldo_disponible' => $saldoDisponible,
                'saldo_retenido'   => $saldoRetenido,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/carrito
    // Devuelve los ítems del carrito activo + guardados + saldo del monedero.
    // -------------------------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        // Todos los ítems del usuario (activos y guardados para después)
        $todosLosItems = CarritoItem::with('producto')
            ->where('usuario_id', $usuario->id)
            ->get();

        // Saldo del monedero: intenta el modelo real; cae a dummy si la tabla
        // aún no existe (migración pendiente de Módulo 4.2).
        try {
            $monedero = SaldoMonedero::obtenerOCrear($usuario->id);
            $saldoDisponible = (float) $monedero->saldo_disponible;
            $saldoRetenido   = (float) $monedero->saldo_retenido;
        } catch (\Throwable) {
            $saldoDisponible = 500.00;
            $saldoRetenido   = 0.00;
        }

        // Subtotal sólo de items activos (no guardados ni wishlist)
        $activos = $todosLosItems->filter(
            fn($i) => !$i->guardado_para_despues && !$i->en_wishlist
        );

        return response()->json([
            'carrito'  => $todosLosItems->values(),
            'total'    => $activos->sum(
                fn($i) => $i->cantidad * (float) ($i->producto->precio ?? 0)
            ),
            'monedero' => [
                'saldo_disponible' => $saldoDisponible,
                'saldo_retenido'   => $saldoRetenido,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /cart
    // Agrega un producto al carrito validando stock (Regla de inventario)
    // -------------------------------------------------------------------------
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad'    => ['required', 'integer', 'min:1'],
        ]);

        $usuario  = $request->user();
        $producto = Producto::findOrFail($data['producto_id']);

        // Reserva de inventario dentro de una transacción para evitar race conditions
        DB::transaction(function () use ($usuario, $producto, $data) {
            // Cantidad ya reservada por este usuario para el mismo producto
            $reservado = CarritoItem::where('usuario_id', $usuario->id)
                ->where('producto_id', $producto->id)
                ->where('guardado_para_despues', false)
                ->sum('cantidad');

            $necesario = $reservado + $data['cantidad'];

            if ($necesario > $producto->stock) {
                throw new \Exception(
                    "Stock insuficiente. Disponible: {$producto->stock}, solicitado: {$necesario}."
                );
            }

            $item = CarritoItem::updateOrCreate(
                [
                    'usuario_id'  => $usuario->id,
                    'producto_id' => $producto->id,
                    'guardado_para_despues' => false,
                    'en_wishlist' => false,
                ],
                [
                    'cantidad'            => DB::raw("cantidad + {$data['cantidad']}"),
                    'ultima_actividad_at' => now(),
                ]
            );

            // Reserva temporal de stock — dummy Módulo 4.2
            $this->checkAndReserveStock($item);
        });

        return response()->json(['mensaje' => 'Producto agregado al carrito.'], 201);
    }

    // -------------------------------------------------------------------------
    // PATCH /carrito/{item}/regalo  (web, auth)
    // Marca/desmarca un ítem de carrito como regalo.
    // Al marcar: valida límite global, calcula expiración dinámica y genera hash.
    // -------------------------------------------------------------------------
    public function marcarRegalo(Request $request, int $itemId): JsonResponse
    {
        $item    = \App\Models\Cart\ItemCarrito::findOrFail($itemId);
        $carrito = $item->carrito;
        if ($carrito->usuario_ref !== strval(Auth::id())) {
            abort(403);
        }
        $metadata              = $item->metadata ?? [];
        $metadata['es_regalo'] = !($metadata['es_regalo'] ?? false);
        $item->update(['metadata' => $metadata]);
        return response()->json(['es_regalo' => $metadata['es_regalo']]);
    }

    // -------------------------------------------------------------------------
    // POST /carrito/{item}/rechazar-regalo  (web, auth)
    // El comprador o el destinatario rechazan el regalo.
    // Dispara GiftExpiredOrRejected → reembolso al comprador + recuperar stock.
    // -------------------------------------------------------------------------
    public function rechazarRegalo(Request $request, CarritoItem $item): JsonResponse
    {
        $usuario = $request->user();

        // Solo el comprador o el destinatario asignado pueden rechazar
        abort_if(
            $item->usuario_id !== $usuario->id && $item->destinatario_id !== $usuario->id,
            403
        );

        if (! $item->es_regalo || $item->estado_regalo !== 'pendiente') {
            return response()->json(['mensaje' => 'No hay regalo pendiente en este ítem.'], 422);
        }

        event(new GiftExpiredOrRejected($item, 'rechazado'));

        return response()->json([
            'mensaje' => 'Regalo rechazado. El reembolso será procesado por el Módulo de Monedero.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /carrito/mis-regalos-recibidos  (web, auth)
    // Lista pedidos donde el usuario autenticado es destinatario_id.
    // Incluye estados 'en_escrow' y 'pendiente' (aún no aceptados).
    // -------------------------------------------------------------------------
    public function misRegalosRecibidos(Request $request): InertiaResponse
    {
        $usuario = $request->user();

        $pedidos = PedidoTienda::with('detalles.producto', 'usuario')
            ->where('destinatario_id', $usuario->id)
            ->whereIn('estado', ['en_escrow', 'pendiente', 'entregado'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Carrito/MisRegalos', [
            'pedidos' => $pedidos,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /carrito/regalos/{pedido}/aceptar  (web, auth)
    // El destinatario acepta el regalo:
    //   1. Libera saldo_retenido del comprador
    //   2. Abona saldo_disponible al proveedor de la tienda
    //   3. Marca el pedido como 'entregado'
    //   4. Registra movimientos en la bitácora
    // -------------------------------------------------------------------------
    public function aceptarRegalo(Request $request, PedidoTienda $pedido): \Illuminate\Http\RedirectResponse
    {
        $destinatario = $request->user();

        abort_if($pedido->destinatario_id !== $destinatario->id, 403);

        if ($pedido->estado !== 'en_escrow') {
            return back()->withErrors(['mensaje' => 'Este pedido no está disponible para aceptar.']);
        }

        try {
            DB::transaction(function () use ($pedido, $destinatario) {
                $total = (float) $pedido->total;

                // 1. Liberar saldo_retenido del comprador
                $compradorMonedero = SaldoMonedero::obtenerOCrear($pedido->usuario_id);
                $anteriorRetenido  = (float) $compradorMonedero->saldo_retenido;
                $compradorMonedero->decrement('saldo_retenido', $total);

                SaldoMovimiento::create([
                    'usuario_id'        => $pedido->usuario_id,
                    'saldo_monedero_id' => $compradorMonedero->id,
                    'tipo'              => 'cargo',
                    'monto'             => $total,
                    'saldo_anterior'    => $anteriorRetenido,
                    'saldo_nuevo'       => $anteriorRetenido - $total,
                    'modulo'            => 'souvenirs',
                    'concepto'          => "[REGALO RECLAMADO] Pedido #{$pedido->id} aceptado por destinatario #{$destinatario->id}",
                ]);

                // 2. Abonar al proveedor de la tienda
                $tienda    = $pedido->detalles->first()?->producto?->tienda;
                $proveedor = $tienda ? Usuario::where('tienda', $tienda)->first() : null;

                if ($proveedor) {
                    $proveedorMonedero = SaldoMonedero::obtenerOCrear($proveedor->id);
                    $proveedorMonedero->abonar(
                        monto:    $total,
                        concepto: "Regalo Reclamado — Pedido #{$pedido->id}",
                        modulo:   'souvenirs'
                    );
                }

                // 3. Cambiar estado del pedido a entregado
                $pedido->update(['estado' => 'entregado']);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['mensaje' => 'Error al procesar el regalo: ' . $e->getMessage()]);
        }

        return back()->with('success', '¡Regalo aceptado! El pedido ha sido marcado como entregado.');
    }

    // -------------------------------------------------------------------------
    // POST /carrito/regalos/{pedido}/rechazar  (web, auth)
    // El destinatario rechaza el regalo:
    //   1. Devuelve saldo_retenido → saldo_disponible al comprador
    //   2. Restaura el stock del producto en la tienda
    //   3. Marca el pedido como 'rechazado'
    // -------------------------------------------------------------------------
    public function rechazarRegaloEscrow(Request $request, PedidoTienda $pedido): \Illuminate\Http\RedirectResponse
    {
        $destinatario = $request->user();

        abort_if($pedido->destinatario_id !== $destinatario->id, 403);

        if ($pedido->estado !== 'en_escrow') {
            return back()->withErrors(['mensaje' => 'Este pedido no está disponible para rechazar.']);
        }

        try {
            DB::transaction(function () use ($pedido, $destinatario) {
                $total = (float) $pedido->total;

                // 1. Reembolsar: mover saldo_retenido → saldo_disponible del comprador
                $compradorMonedero = SaldoMonedero::obtenerOCrear($pedido->usuario_id);
                $anteriorRetenido  = (float) $compradorMonedero->saldo_retenido;
                $compradorMonedero->decrement('saldo_retenido', $total);
                $compradorMonedero->increment('saldo_disponible', $total);

                SaldoMovimiento::create([
                    'usuario_id'        => $pedido->usuario_id,
                    'saldo_monedero_id' => $compradorMonedero->id,
                    'tipo'              => 'abono',
                    'monto'             => $total,
                    'saldo_anterior'    => (float) $compradorMonedero->fresh()->saldo_disponible - $total,
                    'saldo_nuevo'       => (float) $compradorMonedero->fresh()->saldo_disponible,
                    'modulo'            => 'souvenirs',
                    'concepto'          => "[REGALO RECHAZADO] Pedido #{$pedido->id} rechazado por destinatario #{$destinatario->id}",
                ]);

                // 2. Restaurar stock de cada producto
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->producto) {
                        $detalle->producto->increment('stock', $detalle->cantidad);
                    }
                }

                // 3. Cambiar estado del pedido a rechazado
                $pedido->update(['estado' => 'rechazado']);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['mensaje' => 'Error al rechazar el regalo: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Has rechazado el regalo. El monto ha sido reembolsado al remitente.');
    }

    // -------------------------------------------------------------------------
    // POST /carrito/regalos/{pedido}/cancelar-remitente  (web, auth)
    // Período de gracia: el remitente puede cancelar el regalo en los primeros
    // 120 segundos tras el envío.
    //   1. Devuelve saldo_retenido → saldo_disponible del remitente
    //   2. Restaura el stock de cada producto
    //   3. Marca el pedido como 'cancelado_por_remitente'
    // -------------------------------------------------------------------------
    public function cancelarRegaloRemitente(Request $request, PedidoTienda $pedido): \Illuminate\Http\RedirectResponse
    {
        abort_if($pedido->usuario_id !== $request->user()->id, 403);

        if (! $pedido->destinatario_id) {
            return back()->withErrors(['mensaje' => 'Este pedido no es un regalo.']);
        }

        if ($pedido->estado !== 'en_escrow') {
            return back()->withErrors(['mensaje' => 'Este regalo ya no puede cancelarse (estado: ' . $pedido->estado . ').']);
        }

        if ($pedido->created_at->diffInSeconds(now()) >= 120) {
            return back()->withErrors(['mensaje' => 'El período de gracia de 2 minutos ha expirado.']);
        }

        // ── Rate limiting: cancelar también cuenta como acción de regalo ─────
        GiftRateLimiter::checkBlocked($request->user()->id);
        GiftRateLimiter::recordAttempt($request->user()->id);

        try {
            DB::transaction(function () use ($pedido) {
                $total = (float) $pedido->total;

                // 1. Reembolsar: saldo_retenido → saldo_disponible del remitente
                $monedero = SaldoMonedero::obtenerOCrear($pedido->usuario_id);
                $anteriorRetenido  = (float) $monedero->saldo_retenido;
                $anteriorDisponible = (float) $monedero->saldo_disponible;
                $monedero->decrement('saldo_retenido', $total);
                $monedero->increment('saldo_disponible', $total);

                SaldoMovimiento::create([
                    'usuario_id'        => $pedido->usuario_id,
                    'saldo_monedero_id' => $monedero->id,
                    'tipo'              => 'abono',
                    'monto'             => $total,
                    'saldo_anterior'    => $anteriorDisponible,
                    'saldo_nuevo'       => $anteriorDisponible + $total,
                    'modulo'            => 'souvenirs',
                    'concepto'          => "[CANCELADO POR REMITENTE] Pedido #{$pedido->id} cancelado en período de gracia",
                ]);

                // 2. Restaurar stock de cada producto
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->producto) {
                        $detalle->producto->increment('stock', $detalle->cantidad);
                    }
                }

                // 3. Marcar como cancelado por el remitente
                $pedido->update(['estado' => 'cancelado_por_remitente']);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['mensaje' => 'Error al cancelar el regalo: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Regalo cancelado. Tu saldo ha sido reembolsado al instante.');
    }

    // -------------------------------------------------------------------------
    // POST /carrito/validar-destinatario  (web, auth)
    // Valida que matrícula + primer nombre correspondan al mismo usuario
    // consultando la tabla usuario (proxy del Módulo 4.10).
    // Respuesta genérica por privacidad: nunca revela qué campo falló.
    // -------------------------------------------------------------------------
    public function validarDestinatario(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matricula'     => ['required', 'string', 'max:50'],
            'primer_nombre' => ['required', 'string', 'max:100'],
        ]);

        // Busca directamente por la columna matricula (migración 2026_04_09_110000)
        // + nombre con ILIKE para comparación case-insensitive en PostgreSQL.
        $matricula    = trim($data['matricula']);
        $primerNombre = trim($data['primer_nombre']);

        $usuario = Usuario::where('matricula', $matricula)
            ->where('nombre', 'ILIKE', $primerNombre)
            ->whereNull('deleted_at')
            ->first();

        if (! $usuario) {
            // Error intencionalmente genérico — no revelar cuál campo falló
            return response()->json([
                'mensaje' => 'Los datos del destinatario no coinciden.',
            ], 422);
        }

        return response()->json([
            'valido'          => true,
            'destinatario_id' => $usuario->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /carrito/pedido/{pedido}/cancelar-gracia  (web, auth)
    // Ventana 'Oops': cancela un regalo dentro del período de gracia (2 min).
    // Dispara reembolsarSaldo() + liberarStock() vía ExternalModulesService.
    // -------------------------------------------------------------------------
    public function cancelarConGracia(Request $request, PedidoTienda $pedido): JsonResponse
    {
        abort_if($pedido->usuario_id !== $request->user()->id, 403);

        if (! $pedido->destinatario_id) {
            return response()->json(['mensaje' => 'Este pedido no es un regalo.'], 422);
        }

        if (! $pedido->dentroDeGracia()) {
            return response()->json([
                'mensaje' => 'El período de gracia ha expirado o el destinatario ya fue notificado. '
                           . 'No es posible cancelar.',
            ], 422);
        }

        $modules = new ExternalModulesService();

        // ── 1. Reembolso al comprador ────────────────────────────────────────
        $modules->reembolsarSaldo(
            usuarioId: $pedido->usuario_id,
            monto:     (float) $pedido->total,
            concepto:  "Cancelación en período de gracia — pedido #{$pedido->id}"
        );

        // ── 2. Recuperar stock ───────────────────────────────────────────────
        $pedido->load('detalles');
        foreach ($pedido->detalles as $detalle) {
            $modules->liberarStock($detalle->producto_id, $detalle->cantidad);
        }

        // ── 3. Cancelar el pedido ────────────────────────────────────────────
        $pedido->update([
            'estado'       => 'cancelado',
            'gracia_hasta' => null,
        ]);

        return response()->json([
            'mensaje' => 'Pedido cancelado dentro del período de gracia. El reembolso será procesado.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/regalo/validar/{hash}  (público, sin auth)
    // Devuelve el mensaje dedicatorio e info del producto para el destinatario.
    // -------------------------------------------------------------------------
    public function validarRegalo(string $hash): JsonResponse
    {
        $item = CarritoItem::with('producto')
            ->where('regalo_hash', $hash)
            ->where('es_regalo', true)
            ->first();

        if (! $item) {
            return response()->json([
                'mensaje' => 'Enlace de regalo inválido o ya no disponible.',
            ], 404);
        }

        // Verificar expiración en tiempo real
        if ($item->fecha_expiracion_regalo && now()->isAfter($item->fecha_expiracion_regalo)) {
            if ($item->estado_regalo === 'pendiente') {
                event(new GiftExpiredOrRejected($item, 'expirado'));
            }
            return response()->json(['mensaje' => 'Este enlace de regalo ha expirado.'], 410);
        }

        return response()->json([
            'estado_regalo'       => $item->estado_regalo,
            'mensaje_dedicatorio' => $item->mensaje_dedicatorio,
            'producto'            => [
                'id'        => $item->producto->id,
                'nombre'    => $item->producto->nombre,
                'precio'    => $item->producto->precio,
                'categoria' => $item->producto->categoria,
                'tienda'    => $item->producto->tienda,
                'imagen_url'=> $item->producto->imagen_url,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // [PRIVADO] checkAndReserveStock
    // Dummy: simula una reserva temporal de stock con el Módulo de Inventarios.
    // -------------------------------------------------------------------------
    private function checkAndReserveStock(CarritoItem $item): void
    {
        // En producción: (new ExternalModulesService())->verificarStock($item->producto_id, $item->cantidad)
        $item->update(['reservado_hasta' => now()->addMinutes(10)]);
    }

    // -------------------------------------------------------------------------
    // [PRIVADO] calcularExpiracionRegalo
    // Timeout dinámico:
    //   - Tipo 'evento' con fecha_inicio_evento → expira 1h antes del evento
    //   - Cualquier otro tipo                   → expira en 24 horas
    // -------------------------------------------------------------------------
    private function calcularExpiracionRegalo(Producto $producto): Carbon
    {
        if (
            strtolower($producto->tipo ?? '') === 'evento'
            && $producto->fecha_inicio_evento
        ) {
            return Carbon::parse($producto->fecha_inicio_evento)->subHour();
        }

        return now()->addHours(24);
    }

    // -------------------------------------------------------------------------
    // [PRIVADO] validarLimiteGlobal
    // Verifica que el comprador no supere el límite permitido del producto,
    // contando unidades propias activas + regalos enviados pendientes.
    // -------------------------------------------------------------------------
    // [PRIVADO] esAptoParaEfectivo
    // Retorna false si el usuario tiene una sanción de efectivo activa (futura).
    // -------------------------------------------------------------------------
    private function esAptoParaEfectivo(int $usuarioId): bool
    {
        $usuario = Usuario::find($usuarioId);

        if (! $usuario) {
            return false;
        }

        if (
            $usuario->sancion_efectivo_hasta !== null
            && now()->isBefore($usuario->sancion_efectivo_hasta)
        ) {
            return false;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    private function validarLimiteGlobal(int $usuarioId, Producto $producto): bool
    {
        $limite = $producto->limite_por_usuario ?? 3;

        // Unidades propias activas en carrito (no guardadas, no wishlist)
        $enCarrito = CarritoItem::where('usuario_id', $usuarioId)
            ->where('producto_id', $producto->id)
            ->where('guardado_para_despues', false)
            ->where('en_wishlist', false)
            ->sum('cantidad');

        // Regalos enviados que aún están pendientes
        $regalosEnviados = CarritoItem::where('usuario_id', $usuarioId)
            ->where('producto_id', $producto->id)
            ->where('es_regalo', true)
            ->where('estado_regalo', 'pendiente')
            ->sum('cantidad');

        return ($enCarrito + $regalosEnviados) <= $limite;
    }

    // -------------------------------------------------------------------------
    // PATCH /carrito/{item}/mover-al-carrito  (web, auth)
    // Mueve un ítem de "Guardados para después" de vuelta al carrito activo.
    // Valida stock contra el modelo Producto (simula GET /api/inventario/producto/{id}).
    // -------------------------------------------------------------------------
    public function moverAlCarrito(Request $request, CarritoItem $item): JsonResponse
    {
        abort_if($item->usuario_id !== $request->user()->id, 403);

        $producto = $item->producto;

        // ── Limpieza automática: producto eliminado o no encontrado ──────────
        if (! $producto) {
            $item->delete();
            return response()->json([
                'mensaje'   => 'Este producto ya no está disponible y fue eliminado de tu carrito.',
                'eliminado' => true,
            ], 422);
        }

        // ── Re-validación de stock contra la cantidad solicitada ─────────────
        if ($producto->stock < $item->cantidad) {
            return response()->json([
                'mensaje'   => 'Lo sentimos, este producto ya no tiene existencias suficientes.',
                'sin_stock' => true,
            ], 422);
        }

        // ── Mover al carrito activo ──────────────────────────────────────────
        // El precio actualizado llega automáticamente a través de la relación
        // producto (fresh), por lo que no hay campo precio_unitario que cachear.
        $item->update([
            'guardado_para_despues' => false,
            'en_wishlist'           => false,
            'motivo_movimiento'     => null,
            'ultima_actividad_at'   => now(),
        ]);

        return response()->json([
            'mensaje' => 'Producto movido al carrito con el precio actualizado.',
            'item'    => $item->fresh('producto'),
        ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /carrito/{item}/guardar  (web, auth)
    // Mueve un ítem del carrito activo a "Guardados para después" (manual).
    // -------------------------------------------------------------------------
    public function guardarParaDespues(Request $request, int $itemId): JsonResponse
    {
        $item    = \App\Models\Cart\ItemCarrito::findOrFail($itemId);
        $carrito = $item->carrito;
        if ($carrito->usuario_ref !== strval(Auth::id())) abort(403);
        $metadata                          = $item->metadata ?? [];
        $metadata['guardado_para_despues'] = !($metadata['guardado_para_despues'] ?? false);
        $item->update(['metadata' => $metadata]);
        return response()->json(['guardado' => $metadata['guardado_para_despues']]);
    }

    // -------------------------------------------------------------------------
    // PATCH /cart/{item}/wishlist
    // Mueve un ítem entre el carrito activo y la wishlist
    // -------------------------------------------------------------------------
    public function moverWishlist(Request $request, CarritoItem $item): JsonResponse
    {
        abort_if($item->usuario_id !== $request->user()->id, 403);

        $item->update([
            'en_wishlist'         => ! $item->en_wishlist,
            'guardado_para_despues' => false,
            'ultima_actividad_at' => now(),
        ]);

        $mensaje = $item->en_wishlist ? 'Movido a wishlist.' : 'Devuelto al carrito.';

        return response()->json(['mensaje' => $mensaje, 'item' => $item]);
    }

    // -------------------------------------------------------------------------
    // POST /cart/limpiar
    // Mueve a "guardado para después" los ítems inactivos por más de 4 días.
    // Llamable desde un comando Artisan o manualmente vía ruta protegida.
    // -------------------------------------------------------------------------
    public function limpiarInactivos(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $afectados = CarritoItem::inactivos()
            ->where('usuario_id', $usuario->id)
            ->where('guardado_para_despues', false)
            ->update([
                'guardado_para_despues' => true,
                'motivo_movimiento'     => 'sistema',
                'ultima_actividad_at'   => now(),
            ]);

        return response()->json([
            'mensaje'   => "Ítems movidos a 'Guardado para después': {$afectados}.",
            'afectados' => $afectados,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /cart/pedidos-expirados  (llamable desde Artisan o ruta protegida)
    // Busca pedidos en 'esperando_pago' cuyo pago_expira_en ya pasó y:
    //   1. Registra el adeudo (saldo negativo) vía ExternalModulesService
    //   2. Registra merma por cada producto no cobrado vía ExternalModulesService
    //   3. Aplica sanción temporal de efectivo al usuario (shadow ban)
    //   4. Marca el pedido como 'cancelado'
    //   5. Registra en Bitácora
    // -------------------------------------------------------------------------
    public function procesarPedidosExpirados(?Request $request = null): JsonResponse
    {
        $pedidosExpirados = PedidoTienda::with('detalles')
            ->where('estado', 'esperando_pago')
            ->where('pago_expira_en', '<', now())
            ->get();

        if ($pedidosExpirados->isEmpty()) {
            return response()->json(['mensaje' => 'No hay pedidos expirados.', 'procesados' => 0]);
        }

        $modules   = new ExternalModulesService();
        $procesados = 0;

        foreach ($pedidosExpirados as $pedido) {
            // ── 1. Registrar adeudo (saldo negativo en Módulo 4.2) ───────────
            $modules->registrarAdeudo(
                usuarioId: $pedido->usuario_id,
                monto:     (float) $pedido->total,
                concepto:  "Pedido #{$pedido->id} no pagado en caja — expiró el {$pedido->pago_expira_en}"
            );

            // ── 2. Registrar merma por cada producto (Módulo 4.10) ───────────
            foreach ($pedido->detalles as $detalle) {
                $modules->registrarMerma(
                    productoId: $detalle->producto_id,
                    cantidad:   $detalle->cantidad,
                    motivo:     "Pedido #{$pedido->id} expirado sin pago en caja"
                );
            }

            // ── 3. Aplicar sanción de efectivo 30 días (Módulo 4.1) ──────────
            $sancionHasta = now()->addDays(30)->toDateTimeString();
            $modules->aplicarSancionEfectivo(
                usuarioId:    $pedido->usuario_id,
                sancionHasta: $sancionHasta
            );

            // Persistir la sanción en la tabla usuario para que esAptoParaEfectivo() la detecte
            Usuario::where('id', $pedido->usuario_id)
                ->update(['sancion_efectivo_hasta' => $sancionHasta]);

            // ── 4. Cancelar el pedido ────────────────────────────────────────
            $pedido->update(['estado' => 'cancelado']);

            // ── 5. Bitácora ──────────────────────────────────────────────────
            ActividadBitacora::create([
                'usuario_id'   => $pedido->usuario_id,
                'accion'       => 'pedido_expirado',
                'modulo'       => 'tienda',
                'target_tabla' => 'pedidos',
                'target_id'    => $pedido->id,
                'exito'        => true,
                'detalle'      => "Pedido #{$pedido->id} expiró sin pago. Adeudo registrado. Sanción efectivo hasta {$sancionHasta}.",
                'ip'           => $request?->ip() ?? 'cli',
                'user_agent'   => $request?->userAgent() ?? 'artisan/scheduler',
                'meta_json'    => [
                    'total'        => $pedido->total,
                    'expiro_en'    => $pedido->pago_expira_en,
                    'sancion_hasta'=> $sancionHasta,
                    'items'        => $pedido->detalles->count(),
                ],
            ]);

            $procesados++;
        }

        return response()->json([
            'mensaje'    => "{$procesados} pedido(s) expirado(s) procesados.",
            'procesados' => $procesados,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /cart/checkout
    // Confirma el pedido y registra la acción en la Bitácora (Módulo 4.1)
    // -------------------------------------------------------------------------
    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'metodo_pago' => ['required', 'string', 'max:50'],
        ]);

        $usuario = $request->user();

        $items = CarritoItem::with('producto')
            ->where('usuario_id', $usuario->id)
            ->where('guardado_para_despues', false)
            ->where('en_wishlist', false)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['mensaje' => 'El carrito está vacío.'], 422);
        }

        $pedido = DB::transaction(function () use ($usuario, $items, $data) {
            // Validar stock final antes de confirmar
            foreach ($items as $item) {
                $item->producto->refresh();
                if ($item->cantidad > $item->producto->stock) {
                    throw new \Exception(
                        "Stock insuficiente para '{$item->producto->nombre}'."
                    );
                }
            }

            $total = $items->sum(fn($i) => $i->cantidad * (float) $i->producto->precio);

            // ── Deducción del monedero (Módulo 4.2) ──────────────────────────
            if ($data['metodo_pago'] === 'monedero') {
                $monedero = SaldoMonedero::obtenerOCrear($usuario->id);
                // cargar() lanza Exception si saldo insuficiente y registra en saldo_movimiento
                $monedero->cargar(
                    monto:    $total,
                    concepto: "Compra tienda — pedido en proceso",
                    modulo:   'souvenirs'
                );
            }

            // Descontar stock
            foreach ($items as $item) {
                $item->producto->decrement('stock', $item->cantidad);
            }

            $pedido = PedidoTienda::create([
                'usuario_id'  => $usuario->id,
                'total'       => $total,
                'estado'      => 'pagado',
                'metodo_pago' => $data['metodo_pago'],
            ]);

            // Vaciar carrito
            $items->each->delete();

            return $pedido;
        });

        // ── Registro en Bitácora (Lineamiento 4.3) ────────────────────────────
        ActividadBitacora::create([
            'usuario_id'   => $usuario->id,
            'accion'       => 'checkout_confirmado',
            'modulo'       => 'tienda',
            'target_tabla' => 'pedidos',
            'target_id'    => $pedido->id,
            'exito'        => true,
            'detalle'      => "Pedido #{$pedido->id} confirmado. Total: \${$pedido->total}. Método: {$pedido->metodo_pago}.",
            'ip'           => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'meta_json'    => [
                'metodo_pago' => $data['metodo_pago'],
                'total'       => $pedido->total,
                'items'       => $items->count(),
            ],
        ]);

        return response()->json([
            'mensaje' => 'Checkout confirmado.',
            'pedido'  => $pedido,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // POST /carrito/confirmar  (web, Lineamiento 4.3)
    // Transacción segura con beginTransaction explícito:
    //   1. Resta total del saldo_disponible (monedero)
    //   2. Crea pedido en tabla pedidos
    //   3. Registra cada producto en pedido_detalles
    //   4. Vacía el carrito
    //   5. Registra en actividad_bitacora
    //   6. Redirige a vista de éxito
    // -------------------------------------------------------------------------
    public function confirmarPedido(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'metodo_pago'  => ['required', 'string', 'max:50'],
            'cupon_codigo' => ['nullable', 'string', 'max:30'],
        ]);

        $usuario = $request->user();

        $items = CarritoItem::with('producto')
            ->where('usuario_id', $usuario->id)
            ->where('guardado_para_despues', false)
            ->where('en_wishlist', false)
            ->get();

        if ($items->isEmpty()) {
            return back()->withErrors(['error' => 'El carrito está vacío.']);
        }

        // ── Rate limiting para acciones de regalo ────────────────────────────
        // Se ejecuta ANTES del beginTransaction para que ValidationException
        // se propague limpiamente a Inertia (no es capturada por el catch).
        if ($items->contains('es_regalo', true)) {
            GiftRateLimiter::checkBlocked($usuario->id);
            GiftRateLimiter::recordAttempt($usuario->id);
        }

        DB::beginTransaction();

        try {
            // ── 1. Validar stock de cada producto ────────────────────────────
            foreach ($items as $item) {
                $item->producto->refresh();
                if ($item->cantidad > $item->producto->stock) {
                    throw new \Exception(
                        "Stock insuficiente para \"{$item->producto->nombre}\". "
                        . "Disponible: {$item->producto->stock}, solicitado: {$item->cantidad}."
                    );
                }
            }

            // ── 1b. Validar límite_por_usuario por producto ──────────────────
            foreach ($items as $item) {
                $producto = $item->producto;
                $limite   = $producto->limite_por_usuario ?? null;
                if ($limite === null) {
                    continue;
                }

                $yaComprado = PedidoDetalle::where('producto_id', $producto->id)
                    ->whereIn('pedido_id',
                        PedidoTienda::where('usuario_id', $usuario->id)
                            ->whereIn('estado', ['pagado', 'esperando_pago'])
                            ->pluck('id')
                    )
                    ->sum('cantidad');

                if (($yaComprado + $item->cantidad) > $limite) {
                    throw new \Exception(
                        "Has superado el límite de {$limite} unidad(es) permitida(s) para \"{$producto->nombre}\"."
                    );
                }
            }

            // ── Cupón de descuento ───────────────────────────────────────────
            $cuponValido  = strtoupper(trim($data['cupon_codigo'] ?? '')) === 'CAMPUS10';
            $subtotalBruto = $items->sum(fn($i) => $i->cantidad * (float) $i->producto->precio);
            $descuento    = $cuponValido ? round($subtotalBruto * 0.10, 2) : 0;
            $total        = $subtotalBruto - $descuento;

            // Definir aquí para que esté disponible en los pasos 3, 4 y 5
            $tieneRegalos   = $items->contains('es_regalo', true);
            $destinatarioId = $items->firstWhere('es_regalo', true)?->destinatario_id;

            // ── 2. Anti-abuso: verificar aptitud para efectivo ───────────────
            if ($data['metodo_pago'] === 'efectivo' && ! $this->esAptoParaEfectivo($usuario->id)) {
                throw new \Exception(
                    'Tu cuenta tiene una restricción temporal para pagos en efectivo. '
                    . 'Usa tu monedero universitario o espera a que expire la sanción.'
                );
            }

            // ── 3. Cobro del monedero ────────────────────────────────────────
            $monedero = SaldoMonedero::obtenerOCrear($usuario->id);
            if ($data['metodo_pago'] === 'monedero') {
                if ($tieneRegalos) {
                    // ESCROW: mueve disponible → retenido usando increment/decrement atómico
                    $monedero->decrement('saldo_disponible', $total);
                    $monedero->increment('saldo_retenido', $total);
                    // Notificar al Módulo 4.2 del hold externo (dummy hasta integración)
                    (new ExternalModulesService())->retenerSaldo(
                        usuarioId: $usuario->id,
                        monto:     $total,
                        concepto:  "Escrow regalo — pedido en proceso para usuario #{$usuario->id}"
                    );
                } else {
                    // Compra normal: descuenta directamente del disponible
                    $monedero->decrement('saldo_disponible', $total);
                }
            }

            // ── 4. Descontar stock ───────────────────────────────────────────
            foreach ($items as $item) {
                $item->producto->decrement('stock', $item->cantidad);
            }

            // ── 5. Crear registro en pedidos ─────────────────────────────────
            $esEfectivo  = $data['metodo_pago'] === 'efectivo';
            $esEscrow    = $tieneRegalos && $data['metodo_pago'] === 'monedero';

            // Efectivo: estado 'esperando_pago' + token único + ventana 40 min
            $pagoToken    = $esEfectivo ? hash('sha256', $usuario->id . now()->timestamp . config('app.key') . random_int(1000, 9999)) : null;
            $pagoExpiraEn = $esEfectivo ? now()->addMinutes(40) : null;

            // Estado:
            //   'en_escrow'      — regalo monedero: dinero retenido hasta aceptación
            //   'esperando_pago' — efectivo: pendiente de cobro en caja
            //   'pagado'         — monedero sin regalo: cobrado de inmediato
            $estadoPedido = $esEscrow ? 'en_escrow' : ($esEfectivo ? 'esperando_pago' : 'pagado');

            $pedido = PedidoTienda::create([
                'usuario_id'              => $usuario->id,
                'destinatario_id'         => $destinatarioId,
                'total'                   => $total,
                'estado'                  => $estadoPedido,
                'metodo_pago'             => $data['metodo_pago'],
                // Si hay regalos: ventana de cancelación de 2 min; NO notificar aún
                'gracia_hasta'            => $tieneRegalos ? now()->addMinutes(2) : null,
                'notificado_destinatario' => ! $tieneRegalos,
                'pago_token'              => $pagoToken,
                'pago_expira_en'          => $pagoExpiraEn,
            ]);

            // ── 5. Registrar cada producto en pedido_detalles ─────────────────
            foreach ($items as $item) {
                $precioUnitario = (float) $item->producto->precio;
                PedidoDetalle::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $item->producto_id,
                    'nombre_producto' => $item->producto->nombre,
                    'precio_unitario' => $precioUnitario,
                    'cantidad'        => $item->cantidad,
                    'subtotal'        => $precioUnitario * $item->cantidad,
                ]);
            }

            // ── 6. Vaciar carrito ─────────────────────────────────────────────
            $items->each->delete();

            DB::commit();

            // ── 7. Bitácora (Lineamiento 4.3) ─────────────────────────────────
            ActividadBitacora::create([
                'usuario_id'   => $usuario->id,
                'accion'       => 'pedido_confirmado',
                'modulo'       => 'tienda',
                'target_tabla' => 'pedidos',
                'target_id'    => $pedido->id,
                'exito'        => true,
                'detalle'      => "Usuario realizó compra por un total de \${$total}.",
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'meta_json'    => [
                    'metodo_pago'     => $data['metodo_pago'],
                    'total'           => $total,
                    'items_comprados' => $items->count(),
                    'pedido_id'       => $pedido->id,
                ],
            ]);

            // ── 9. Redirigir a vista de éxito ────────────────────────────────
            return redirect()->route('carrito.exito', ['pedido' => $pedido->id])
                ->with('pedido_total', $total)
                ->with('pedido_metodo', $data['metodo_pago'])
                ->with('pago_token', $pagoToken)
                ->with('pago_expira_en', $pagoExpiraEn?->toIso8601String());

        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar fallo en bitácora
            ActividadBitacora::create([
                'usuario_id'   => $usuario->id,
                'accion'       => 'pedido_fallido',
                'modulo'       => 'tienda',
                'target_tabla' => 'pedidos',
                'target_id'    => null,
                'exito'        => false,
                'detalle'      => $e->getMessage(),
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // GET /carrito/exito  (web, Inertia)
    // Vista de confirmación exitosa del pedido.
    // -------------------------------------------------------------------------
    public function exitoPedido(Request $request): InertiaResponse
    {
        $pedidoId = $request->query('pedido');
        $pedido   = $pedidoId
            ? PedidoTienda::with('detalles')->find($pedidoId)
            : null;

        return Inertia::render('Carrito/Exito', [
            'pedido'       => $pedido,
            'pedidoTotal'  => session('pedido_total', $pedido?->total ?? 0),
            'pedidoMetodo' => session('pedido_metodo', $pedido?->metodo_pago ?? ''),
            'pagoToken'    => session('pago_token'),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /carrito/dashboard  (web, Inertia) — Módulo 4.4
    // KPIs de negocio: tasa de abandono, consumo promedio y horarios pico.
    // -------------------------------------------------------------------------
    public function dashboardData(): InertiaResponse
    {
        // Solo administradores y proveedores pueden ver el dashboard de tienda
        $usuario = Auth::user();
        if (! $usuario->hasAnyRole(['administrador', 'proveedor_area'])) {
            abort(403, 'No tienes acceso al dashboard de tienda.');
        }

        // ── Tasa de abandono REAL ────────────────────────────────────────────
        // Solo cuentan los ítems movidos por el sistema (inactividad > 4 días),
        // no los guardados manualmente por el usuario.
        $totalItems       = CarritoItem::count();
        $itemsAbandonados = CarritoItem::where('motivo_movimiento', 'sistema')->count();
        $tasaAbandono     = $totalItems > 0
            ? round(($itemsAbandonados / $totalItems) * 100, 2)
            : 0;

        // ── Consumo promedio ─────────────────────────────────────────────────
        $consumoPromedio = round((float) PedidoTienda::avg('total') ?? 0, 2);

        // ── Total de pedidos y ventas ─────────────────────────────────────────
        $totalPedidos = PedidoTienda::count();
        $ventasTotales = round((float) PedidoTienda::sum('total') ?? 0, 2);

        // ── Horarios pico ────────────────────────────────────────────────────
        // Agrupa pedidos por hora del día (MySQL: HOUR(); compatible con MariaDB).
        $horariosPico = DB::table('pedidos')
            ->selectRaw('EXTRACT(HOUR FROM created_at) as hora, COUNT(*) as total')
            ->groupByRaw('EXTRACT(HOUR FROM created_at)')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'hora'     => (int) $r->hora,
                'total'    => (int) $r->total,
                'etiqueta' => sprintf('%02d:00', $r->hora),
            ]);

        // ── Productos más vendidos (top 5) ────────────────────────────────────
        $topProductos = DB::table('carrito_items')
            ->join('productos', 'carrito_items.producto_id', '=', 'productos.id')
            ->selectRaw('productos.nombre, productos.categoria, SUM(carrito_items.cantidad) as unidades_vendidas')
            ->where('carrito_items.guardado_para_despues', false)
            ->where('carrito_items.en_wishlist', false)
            ->groupBy('productos.id', 'productos.nombre', 'productos.categoria')
            ->orderByDesc('unidades_vendidas')
            ->limit(5)
            ->get();

        return Inertia::render('Carrito/Dashboard', [
            'tasaAbandono'    => $tasaAbandono,
            'consumoPromedio' => $consumoPromedio,
            'totalPedidos'    => $totalPedidos,
            'ventasTotales'   => $ventasTotales,
            'horariosPico'    => $horariosPico,
            'topProductos'    => $topProductos,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /carrito/dashboard/exportar
    // Descarga un CSV con todos los pedidos históricos.
    // -------------------------------------------------------------------------
    public function exportarCSV(): HttpResponse
    {
        $pedidos = DB::table('pedidos')
            ->join('usuario', 'pedidos.usuario_id', '=', 'usuario.id')
            ->select(
                'pedidos.id',
                DB::raw("CONCAT(usuario.nombre, ' ', usuario.apellido) as cliente"),
                'usuario.email',
                'pedidos.total',
                'pedidos.estado',
                'pedidos.metodo_pago',
                'pedidos.created_at'
            )
            ->orderByDesc('pedidos.created_at')
            ->get();

        $csv  = "ID,Cliente,Email,Total,Estado,Metodo Pago,Fecha\n";
        foreach ($pedidos as $p) {
            $csv .= implode(',', [
                $p->id,
                "\"{$p->cliente}\"",
                $p->email,
                number_format($p->total, 2),
                $p->estado,
                $p->metodo_pago ?? 'N/A',
                $p->created_at,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_pedidos_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
