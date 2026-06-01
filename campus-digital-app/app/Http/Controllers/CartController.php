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

class CartController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /carrito  (web, Inertia)
    // Renderiza el carrito pasando los datos como props al componente Vue.
    // -------------------------------------------------------------------------
    public function indexWeb(): InertiaResponse
    {
        $userId = Auth::id();

        $items = CarritoItem::where('usuario_id', $userId)
            ->with('producto')
            ->get();

        try {
            $monedero        = SaldoMonedero::obtenerOCrear($userId);
            $saldoDisponible = (float) $monedero->saldo_disponible;
            $saldoRetenido   = (float) $monedero->saldo_retenido;
        } catch (\Throwable) {
            $saldoDisponible = 500.00;
            $saldoRetenido   = 0.00;
        }

        $activos = $items->filter(fn($i) => !$i->guardado_para_despues && !$i->en_wishlist);
        $total   = $activos->sum(fn($i) => $i->cantidad * (float) ($i->producto->precio ?? 0));

        return Inertia::render('Carrito/Index', [
            'carrito'  => $items->values(),
            'total'    => $total,
            'monedero' => [
                'saldo_disponible' => $saldoDisponible,
                'saldo_retenido'   => $saldoRetenido,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/carrito
    // Devuelve los ├¡tems del carrito activo + guardados + saldo del monedero.
    // -------------------------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        // Todos los ├¡tems del usuario (activos y guardados para despu├®s)
        $todosLosItems = CarritoItem::with('producto')
            ->where('usuario_id', $usuario->id)
            ->get();

        // Saldo del monedero: intenta el modelo real; cae a dummy si la tabla
        // a├║n no existe (migraci├│n pendiente de M├│dulo 4.2).
        try {
            $monedero = SaldoMonedero::obtenerOCrear($usuario->id);
            $saldoDisponible = (float) $monedero->saldo_disponible;
            $saldoRetenido   = (float) $monedero->saldo_retenido;
        } catch (\Throwable) {
            $saldoDisponible = 500.00;
            $saldoRetenido   = 0.00;
        }

        // Subtotal s├│lo de items activos (no guardados ni wishlist)
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

        // Reserva de inventario dentro de una transacci├│n para evitar race conditions
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

            // Reserva temporal de stock ÔÇö dummy M├│dulo 4.2
            $this->checkAndReserveStock($item);
        });

        return response()->json(['mensaje' => 'Producto agregado al carrito.'], 201);
    }

    // -------------------------------------------------------------------------
    // PATCH /carrito/{item}/regalo  (web, auth)
    // Marca/desmarca un ├¡tem de carrito como regalo.
    // Al marcar: valida l├¡mite global, calcula expiraci├│n din├ímica y genera hash.
    // -------------------------------------------------------------------------
    public function marcarRegalo(Request $request, CarritoItem $item): JsonResponse
    {
        abort_if($item->usuario_id !== $request->user()->id, 403);

        $data = $request->validate([
            'es_regalo'           => ['required', 'boolean'],
            'mensaje_dedicatorio' => ['nullable', 'string', 'max:200'],
            'destinatario_id'     => ['nullable', 'integer', 'exists:usuario,id'],
        ]);

        if ($data['es_regalo']) {
            // ÔöÇÔöÇ Validaci├│n de l├¡mite global ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $producto = $item->producto;
            if (! $this->validarLimiteGlobal($request->user()->id, $producto)) {
                $limite = $producto->limite_por_usuario ?? 3;
                return response()->json([
                    'mensaje' => "Has alcanzado el l├¡mite de {$limite} unidad(es) para este producto "
                               . "(suma de compras propias + regalos enviados pendientes).",
                ], 422);
            }

            $hash       = hash('sha256', $item->id . $item->usuario_id . now()->timestamp . config('app.key'));
            $expiracion = $this->calcularExpiracionRegalo($producto);

            $item->update([
                'es_regalo'               => true,
                'mensaje_dedicatorio'     => $data['mensaje_dedicatorio'] ?? null,
                'regalo_hash'             => $hash,
                'estado_regalo'           => 'pendiente',
                'fecha_expiracion_regalo' => $expiracion,
                'destinatario_id'         => $data['destinatario_id'] ?? null,
            ]);
        } else {
            $item->update([
                'es_regalo'               => false,
                'mensaje_dedicatorio'     => null,
                'regalo_hash'             => null,
                'estado_regalo'           => null,
                'fecha_expiracion_regalo' => null,
                'destinatario_id'         => null,
            ]);
        }

        return response()->json([
            'mensaje'                 => $data['es_regalo'] ? 'Marcado como regalo.' : 'Desmarcado como regalo.',
            'es_regalo'               => $item->es_regalo,
            'estado_regalo'           => $item->estado_regalo,
            'mensaje_dedicatorio'     => $item->mensaje_dedicatorio,
            'regalo_hash'             => $item->regalo_hash,
            'fecha_expiracion_regalo' => $item->fecha_expiracion_regalo,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /carrito/{item}/rechazar-regalo  (web, auth)
    // El comprador o el destinatario rechazan el regalo.
    // Dispara GiftExpiredOrRejected ÔåÆ reembolso al comprador + recuperar stock.
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
            return response()->json(['mensaje' => 'No hay regalo pendiente en este ├¡tem.'], 422);
        }

        event(new GiftExpiredOrRejected($item, 'rechazado'));

        return response()->json([
            'mensaje' => 'Regalo rechazado. El reembolso ser├í procesado por el M├│dulo de Monedero.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /carrito/mis-regalos-recibidos  (web, auth)
    // Lista pedidos donde el usuario autenticado es destinatario_id.
    // Incluye estados 'en_escrow' y 'pendiente' (a├║n no aceptados).
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
    //   4. Registra movimientos en la bit├ícora
    // -------------------------------------------------------------------------
    public function aceptarRegalo(Request $request, PedidoTienda $pedido): \Illuminate\Http\RedirectResponse
    {
        $destinatario = $request->user();

        abort_if($pedido->destinatario_id !== $destinatario->id, 403);

        if ($pedido->estado !== 'en_escrow') {
            return back()->withErrors(['mensaje' => 'Este pedido no est├í disponible para aceptar.']);
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
                        concepto: "Regalo Reclamado ÔÇö Pedido #{$pedido->id}",
                        modulo:   'souvenirs'
                    );
                }

                // 3. Cambiar estado del pedido a entregado
                $pedido->update(['estado' => 'entregado']);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['mensaje' => 'Error al procesar el regalo: ' . $e->getMessage()]);
        }

        return back()->with('success', '┬íRegalo aceptado! El pedido ha sido marcado como entregado.');
    }

    // -------------------------------------------------------------------------
    // POST /carrito/regalos/{pedido}/rechazar  (web, auth)
    // El destinatario rechaza el regalo:
    //   1. Devuelve saldo_retenido ÔåÆ saldo_disponible al comprador
    //   2. Restaura el stock del producto en la tienda
    //   3. Marca el pedido como 'rechazado'
    // -------------------------------------------------------------------------
    public function rechazarRegaloEscrow(Request $request, PedidoTienda $pedido): \Illuminate\Http\RedirectResponse
    {
        $destinatario = $request->user();

        abort_if($pedido->destinatario_id !== $destinatario->id, 403);

        if ($pedido->estado !== 'en_escrow') {
            return back()->withErrors(['mensaje' => 'Este pedido no est├í disponible para rechazar.']);
        }

        try {
            DB::transaction(function () use ($pedido, $destinatario) {
                $total = (float) $pedido->total;

                // 1. Reembolsar: mover saldo_retenido ÔåÆ saldo_disponible del comprador
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
    // Per├¡odo de gracia: el remitente puede cancelar el regalo en los primeros
    // 120 segundos tras el env├¡o.
    //   1. Devuelve saldo_retenido ÔåÆ saldo_disponible del remitente
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
            return back()->withErrors(['mensaje' => 'El per├¡odo de gracia de 2 minutos ha expirado.']);
        }

        // ÔöÇÔöÇ Rate limiting: cancelar tambi├®n cuenta como acci├│n de regalo ÔöÇÔöÇÔöÇÔöÇÔöÇ
        GiftRateLimiter::checkBlocked($request->user()->id);
        GiftRateLimiter::recordAttempt($request->user()->id);

        try {
            DB::transaction(function () use ($pedido) {
                $total = (float) $pedido->total;

                // 1. Reembolsar: saldo_retenido ÔåÆ saldo_disponible del remitente
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
                    'concepto'          => "[CANCELADO POR REMITENTE] Pedido #{$pedido->id} cancelado en per├¡odo de gracia",
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
    // Valida que matr├¡cula + primer nombre correspondan al mismo usuario
    // consultando la tabla usuario (proxy del M├│dulo 4.10).
    // Respuesta gen├®rica por privacidad: nunca revela qu├® campo fall├│.
    // -------------------------------------------------------------------------
    public function validarDestinatario(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matricula'     => ['required', 'string', 'max:50'],
            'primer_nombre' => ['required', 'string', 'max:100'],
        ]);

        // Busca directamente por la columna matricula (migraci├│n 2026_04_09_110000)
        // + nombre con ILIKE para comparaci├│n case-insensitive en PostgreSQL.
        $matricula    = trim($data['matricula']);
        $primerNombre = trim($data['primer_nombre']);

        $usuario = Usuario::where('matricula', $matricula)
            ->where('nombre', 'ILIKE', $primerNombre)
            ->whereNull('deleted_at')
            ->first();

        if (! $usuario) {
            // Error intencionalmente gen├®rico ÔÇö no revelar cu├íl campo fall├│
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
    // Ventana 'Oops': cancela un regalo dentro del per├¡odo de gracia (2 min).
    // Dispara reembolsarSaldo() + liberarStock() v├¡a ExternalModulesService.
    // -------------------------------------------------------------------------
    public function cancelarConGracia(Request $request, PedidoTienda $pedido): JsonResponse
    {
        abort_if($pedido->usuario_id !== $request->user()->id, 403);

        if (! $pedido->destinatario_id) {
            return response()->json(['mensaje' => 'Este pedido no es un regalo.'], 422);
        }

        if (! $pedido->dentroDeGracia()) {
            return response()->json([
                'mensaje' => 'El per├¡odo de gracia ha expirado o el destinatario ya fue notificado. '
                           . 'No es posible cancelar.',
            ], 422);
        }

        $modules = new ExternalModulesService();

        // ÔöÇÔöÇ 1. Reembolso al comprador ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        $modules->reembolsarSaldo(
            usuarioId: $pedido->usuario_id,
            monto:     (float) $pedido->total,
            concepto:  "Cancelaci├│n en per├¡odo de gracia ÔÇö pedido #{$pedido->id}"
        );

        // ÔöÇÔöÇ 2. Recuperar stock ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        $pedido->load('detalles');
        foreach ($pedido->detalles as $detalle) {
            $modules->liberarStock($detalle->producto_id, $detalle->cantidad);
        }

        // ÔöÇÔöÇ 3. Cancelar el pedido ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        $pedido->update([
            'estado'       => 'cancelado',
            'gracia_hasta' => null,
        ]);

        return response()->json([
            'mensaje' => 'Pedido cancelado dentro del per├¡odo de gracia. El reembolso ser├í procesado.',
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/regalo/validar/{hash}  (p├║blico, sin auth)
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
                'mensaje' => 'Enlace de regalo inv├ílido o ya no disponible.',
            ], 404);
        }

        // Verificar expiraci├│n en tiempo real
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
    // Dummy: simula una reserva temporal de stock con el M├│dulo de Inventarios.
    // -------------------------------------------------------------------------
    private function checkAndReserveStock(CarritoItem $item): void
    {
        // En producci├│n: (new ExternalModulesService())->verificarStock($item->producto_id, $item->cantidad)
        $item->update(['reservado_hasta' => now()->addMinutes(10)]);
    }

    // -------------------------------------------------------------------------
    // [PRIVADO] calcularExpiracionRegalo
    // Timeout din├ímico:
    //   - Tipo 'evento' con fecha_inicio_evento ÔåÆ expira 1h antes del evento
    //   - Cualquier otro tipo                   ÔåÆ expira en 24 horas
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
    // Verifica que el comprador no supere el l├¡mite permitido del producto,
    // contando unidades propias activas + regalos enviados pendientes.
    // -------------------------------------------------------------------------
    // [PRIVADO] esAptoParaEfectivo
    // Retorna false si el usuario tiene una sanci├│n de efectivo activa (futura).
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

        // Regalos enviados que a├║n est├ín pendientes
        $regalosEnviados = CarritoItem::where('usuario_id', $usuarioId)
            ->where('producto_id', $producto->id)
            ->where('es_regalo', true)
            ->where('estado_regalo', 'pendiente')
            ->sum('cantidad');

        return ($enCarrito + $regalosEnviados) <= $limite;
    }

    // -------------------------------------------------------------------------
    // PATCH /carrito/{item}/mover-al-carrito  (web, auth)
    // Mueve un ├¡tem de "Guardados para despu├®s" de vuelta al carrito activo.
    // Valida stock contra el modelo Producto (simula GET /api/inventario/producto/{id}).
    // -------------------------------------------------------------------------
    public function moverAlCarrito(Request $request, CarritoItem $item): JsonResponse
    {
        abort_if($item->usuario_id !== $request->user()->id, 403);

        $producto = $item->producto;

        // ÔöÇÔöÇ Limpieza autom├ítica: producto eliminado o no encontrado ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        if (! $producto) {
            $item->delete();
            return response()->json([
                'mensaje'   => 'Este producto ya no est├í disponible y fue eliminado de tu carrito.',
                'eliminado' => true,
            ], 422);
        }

        // ÔöÇÔöÇ Re-validaci├│n de stock contra la cantidad solicitada ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        if ($producto->stock < $item->cantidad) {
            return response()->json([
                'mensaje'   => 'Lo sentimos, este producto ya no tiene existencias suficientes.',
                'sin_stock' => true,
            ], 422);
        }

        // ÔöÇÔöÇ Mover al carrito activo ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        // El precio actualizado llega autom├íticamente a trav├®s de la relaci├│n
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
    // Mueve un ├¡tem del carrito activo a "Guardados para despu├®s" (manual).
    // -------------------------------------------------------------------------
    public function guardarParaDespues(Request $request, CarritoItem $item): JsonResponse
    {
        abort_if($item->usuario_id !== $request->user()->id, 403);

        $item->update([
            'guardado_para_despues' => true,
            'en_wishlist'           => false,
            'motivo_movimiento'     => 'manual',
            'ultima_actividad_at'   => now(),
        ]);

        return response()->json([
            'mensaje' => 'Producto guardado para despu├®s.',
            'item'    => $item->fresh('producto'),
        ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /cart/{item}/wishlist
    // Mueve un ├¡tem entre el carrito activo y la wishlist
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
    // Mueve a "guardado para despu├®s" los ├¡tems inactivos por m├ís de 4 d├¡as.
    // Llamable desde un comando Artisan o manualmente v├¡a ruta protegida.
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
            'mensaje'   => "├ìtems movidos a 'Guardado para despu├®s': {$afectados}.",
            'afectados' => $afectados,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /cart/pedidos-expirados  (llamable desde Artisan o ruta protegida)
    // Busca pedidos en 'esperando_pago' cuyo pago_expira_en ya pas├│ y:
    //   1. Registra el adeudo (saldo negativo) v├¡a ExternalModulesService
    //   2. Registra merma por cada producto no cobrado v├¡a ExternalModulesService
    //   3. Aplica sanci├│n temporal de efectivo al usuario (shadow ban)
    //   4. Marca el pedido como 'cancelado'
    //   5. Registra en Bit├ícora
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
            // ÔöÇÔöÇ 1. Registrar adeudo (saldo negativo en M├│dulo 4.2) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $modules->registrarAdeudo(
                usuarioId: $pedido->usuario_id,
                monto:     (float) $pedido->total,
                concepto:  "Pedido #{$pedido->id} no pagado en caja ÔÇö expir├│ el {$pedido->pago_expira_en}"
            );

            // ÔöÇÔöÇ 2. Registrar merma por cada producto (M├│dulo 4.10) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            foreach ($pedido->detalles as $detalle) {
                $modules->registrarMerma(
                    productoId: $detalle->producto_id,
                    cantidad:   $detalle->cantidad,
                    motivo:     "Pedido #{$pedido->id} expirado sin pago en caja"
                );
            }

            // ÔöÇÔöÇ 3. Aplicar sanci├│n de efectivo 30 d├¡as (M├│dulo 4.1) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $sancionHasta = now()->addDays(30)->toDateTimeString();
            $modules->aplicarSancionEfectivo(
                usuarioId:    $pedido->usuario_id,
                sancionHasta: $sancionHasta
            );

            // Persistir la sanci├│n en la tabla usuario para que esAptoParaEfectivo() la detecte
            Usuario::where('id', $pedido->usuario_id)
                ->update(['sancion_efectivo_hasta' => $sancionHasta]);

            // ÔöÇÔöÇ 4. Cancelar el pedido ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $pedido->update(['estado' => 'cancelado']);

            // ÔöÇÔöÇ 5. Bit├ícora ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            ActividadBitacora::create([
                'usuario_id'   => $pedido->usuario_id,
                'accion'       => 'pedido_expirado',
                'modulo'       => 'tienda',
                'target_tabla' => 'pedidos',
                'target_id'    => $pedido->id,
                'exito'        => true,
                'detalle'      => "Pedido #{$pedido->id} expir├│ sin pago. Adeudo registrado. Sanci├│n efectivo hasta {$sancionHasta}.",
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
    // Confirma el pedido y registra la acci├│n en la Bit├ícora (M├│dulo 4.1)
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
            return response()->json(['mensaje' => 'El carrito est├í vac├¡o.'], 422);
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

            // ÔöÇÔöÇ Deducci├│n del monedero (M├│dulo 4.2) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            if ($data['metodo_pago'] === 'monedero') {
                $monedero = SaldoMonedero::obtenerOCrear($usuario->id);
                // cargar() lanza Exception si saldo insuficiente y registra en saldo_movimiento
                $monedero->cargar(
                    monto:    $total,
                    concepto: "Compra tienda ÔÇö pedido en proceso",
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

        // ÔöÇÔöÇ Registro en Bit├ícora (Lineamiento 4.3) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        ActividadBitacora::create([
            'usuario_id'   => $usuario->id,
            'accion'       => 'checkout_confirmado',
            'modulo'       => 'tienda',
            'target_tabla' => 'pedidos',
            'target_id'    => $pedido->id,
            'exito'        => true,
            'detalle'      => "Pedido #{$pedido->id} confirmado. Total: \${$pedido->total}. M├®todo: {$pedido->metodo_pago}.",
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
    // Transacci├│n segura con beginTransaction expl├¡cito:
    //   1. Resta total del saldo_disponible (monedero)
    //   2. Crea pedido en tabla pedidos
    //   3. Registra cada producto en pedido_detalles
    //   4. Vac├¡a el carrito
    //   5. Registra en actividad_bitacora
    //   6. Redirige a vista de ├®xito
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
            return back()->withErrors(['error' => 'El carrito est├í vac├¡o.']);
        }

        // ÔöÇÔöÇ Rate limiting para acciones de regalo ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        // Se ejecuta ANTES del beginTransaction para que ValidationException
        // se propague limpiamente a Inertia (no es capturada por el catch).
        if ($items->contains('es_regalo', true)) {
            GiftRateLimiter::checkBlocked($usuario->id);
            GiftRateLimiter::recordAttempt($usuario->id);
        }

        DB::beginTransaction();

        try {
            // ÔöÇÔöÇ 1. Validar stock de cada producto ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            foreach ($items as $item) {
                $item->producto->refresh();
                if ($item->cantidad > $item->producto->stock) {
                    throw new \Exception(
                        "Stock insuficiente para \"{$item->producto->nombre}\". "
                        . "Disponible: {$item->producto->stock}, solicitado: {$item->cantidad}."
                    );
                }
            }

            // ÔöÇÔöÇ 1b. Validar l├¡mite_por_usuario por producto ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
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
                        "Has superado el l├¡mite de {$limite} unidad(es) permitida(s) para \"{$producto->nombre}\"."
                    );
                }
            }

            // ÔöÇÔöÇ Cup├│n de descuento ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $cuponValido  = strtoupper(trim($data['cupon_codigo'] ?? '')) === 'CAMPUS10';
            $subtotalBruto = $items->sum(fn($i) => $i->cantidad * (float) $i->producto->precio);
            $descuento    = $cuponValido ? round($subtotalBruto * 0.10, 2) : 0;
            $total        = $subtotalBruto - $descuento;

            // Definir aqu├¡ para que est├® disponible en los pasos 3, 4 y 5
            $tieneRegalos   = $items->contains('es_regalo', true);
            $destinatarioId = $items->firstWhere('es_regalo', true)?->destinatario_id;

            // ÔöÇÔöÇ 2. Anti-abuso: verificar aptitud para efectivo ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            if ($data['metodo_pago'] === 'efectivo' && ! $this->esAptoParaEfectivo($usuario->id)) {
                throw new \Exception(
                    'Tu cuenta tiene una restricci├│n temporal para pagos en efectivo. '
                    . 'Usa tu monedero universitario o espera a que expire la sanci├│n.'
                );
            }

            // ÔöÇÔöÇ 3. Cobro del monedero ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $monedero = SaldoMonedero::obtenerOCrear($usuario->id);
            if ($data['metodo_pago'] === 'monedero') {
                if ($tieneRegalos) {
                    // ESCROW: mueve disponible ÔåÆ retenido usando increment/decrement at├│mico
                    $monedero->decrement('saldo_disponible', $total);
                    $monedero->increment('saldo_retenido', $total);
                    // Notificar al M├│dulo 4.2 del hold externo (dummy hasta integraci├│n)
                    (new ExternalModulesService())->retenerSaldo(
                        usuarioId: $usuario->id,
                        monto:     $total,
                        concepto:  "Escrow regalo ÔÇö pedido en proceso para usuario #{$usuario->id}"
                    );
                } else {
                    // Compra normal: descuenta directamente del disponible
                    $monedero->decrement('saldo_disponible', $total);
                }
            }

            // ÔöÇÔöÇ 4. Descontar stock ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            foreach ($items as $item) {
                $item->producto->decrement('stock', $item->cantidad);
            }

            // ÔöÇÔöÇ 5. Crear registro en pedidos ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $esEfectivo  = $data['metodo_pago'] === 'efectivo';
            $esEscrow    = $tieneRegalos && $data['metodo_pago'] === 'monedero';

            // Efectivo: estado 'esperando_pago' + token ├║nico + ventana 40 min
            $pagoToken    = $esEfectivo ? hash('sha256', $usuario->id . now()->timestamp . config('app.key') . random_int(1000, 9999)) : null;
            $pagoExpiraEn = $esEfectivo ? now()->addMinutes(40) : null;

            // Estado:
            //   'en_escrow'      ÔÇö regalo monedero: dinero retenido hasta aceptaci├│n
            //   'esperando_pago' ÔÇö efectivo: pendiente de cobro en caja
            //   'pagado'         ÔÇö monedero sin regalo: cobrado de inmediato
            $estadoPedido = $esEscrow ? 'en_escrow' : ($esEfectivo ? 'esperando_pago' : 'pagado');

            $pedido = PedidoTienda::create([
                'usuario_id'              => $usuario->id,
                'destinatario_id'         => $destinatarioId,
                'total'                   => $total,
                'estado'                  => $estadoPedido,
                'metodo_pago'             => $data['metodo_pago'],
                // Si hay regalos: ventana de cancelaci├│n de 2 min; NO notificar a├║n
                'gracia_hasta'            => $tieneRegalos ? now()->addMinutes(2) : null,
                'notificado_destinatario' => ! $tieneRegalos,
                'pago_token'              => $pagoToken,
                'pago_expira_en'          => $pagoExpiraEn,
            ]);

            // ÔöÇÔöÇ 5. Registrar cada producto en pedido_detalles ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
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

            // ÔöÇÔöÇ 6. Vaciar carrito ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            $items->each->delete();

            DB::commit();

            // ÔöÇÔöÇ 7. Bit├ícora (Lineamiento 4.3) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            ActividadBitacora::create([
                'usuario_id'   => $usuario->id,
                'accion'       => 'pedido_confirmado',
                'modulo'       => 'tienda',
                'target_tabla' => 'pedidos',
                'target_id'    => $pedido->id,
                'exito'        => true,
                'detalle'      => "Usuario realiz├│ compra por un total de \${$total}.",
                'ip'           => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'meta_json'    => [
                    'metodo_pago'     => $data['metodo_pago'],
                    'total'           => $total,
                    'items_comprados' => $items->count(),
                    'pedido_id'       => $pedido->id,
                ],
            ]);

            // ÔöÇÔöÇ 9. Redirigir a vista de ├®xito ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            return redirect()->route('carrito.exito', ['pedido' => $pedido->id])
                ->with('pedido_total', $total)
                ->with('pedido_metodo', $data['metodo_pago'])
                ->with('pago_token', $pagoToken)
                ->with('pago_expira_en', $pagoExpiraEn?->toIso8601String());

        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar fallo en bit├ícora
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
    // Vista de confirmaci├│n exitosa del pedido.
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
    // GET /carrito/dashboard  (web, Inertia) ÔÇö M├│dulo 4.4
    // KPIs de negocio: tasa de abandono, consumo promedio y horarios pico.
    // -------------------------------------------------------------------------
    public function dashboardData(): InertiaResponse
    {
        // Solo administradores y proveedores pueden ver el dashboard de tienda
        $usuario = Auth::user();
        if (! $usuario->hasAnyRole(['administrador', 'proveedor_area'])) {
            abort(403, 'No tienes acceso al dashboard de tienda.');
        }

        // ÔöÇÔöÇ Tasa de abandono REAL ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        // Solo cuentan los ├¡tems movidos por el sistema (inactividad > 4 d├¡as),
        // no los guardados manualmente por el usuario.
        $totalItems       = CarritoItem::count();
        $itemsAbandonados = CarritoItem::where('motivo_movimiento', 'sistema')->count();
        $tasaAbandono     = $totalItems > 0
            ? round(($itemsAbandonados / $totalItems) * 100, 2)
            : 0;

        // ÔöÇÔöÇ Consumo promedio ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        $consumoPromedio = round((float) PedidoTienda::avg('total') ?? 0, 2);

        // ÔöÇÔöÇ Total de pedidos y ventas ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        $totalPedidos = PedidoTienda::count();
        $ventasTotales = round((float) PedidoTienda::sum('total') ?? 0, 2);

        // ÔöÇÔöÇ Horarios pico ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
        // Agrupa pedidos por hora del d├¡a (MySQL: HOUR(); compatible con MariaDB).
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

        // ÔöÇÔöÇ Productos m├ís vendidos (top 5) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
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
    // Descarga un CSV con todos los pedidos hist├│ricos.
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
