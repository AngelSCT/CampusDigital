<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $pedido->numero_folio }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #1F3864; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #1F3864; font-size: 20px; margin: 0; }
        .header p { color: #666; margin: 5px 0 0; }
        .folio { background: #1F3864; color: white; padding: 8px 15px; display: inline-block; border-radius: 5px; font-size: 16px; font-weight: bold; }
        .info-grid { display: table; width: 100%; margin: 15px 0; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; font-weight: bold; padding: 5px 10px 5px 0; width: 140px; color: #1F3864; }
        .info-value { display: table-cell; padding: 5px 0; }
        table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table.items th { background: #1F3864; color: white; padding: 8px; text-align: left; font-size: 11px; }
        table.items td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table.items tr:nth-child(even) { background: #f9f9f9; }
        .total-row { background: #e8f0fe !important; font-weight: bold; }
        .total-row td { border-top: 2px solid #1F3864; font-size: 13px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .estado { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; color: white; }
        .estado-creado { background: #3B82F6; }
        .estado-aceptado { background: #8B5CF6; }
        .estado-en_proceso { background: #F59E0B; }
        .estado-listo { background: #10B981; }
        .estado-entregado { background: #059669; }
        .estado-cancelado { background: #EF4444; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Campus Digital</h1>
        <p>Sistema de Gestión Escolar — Ticket de Pedido</p>
    </div>

    <div style="text-align: center; margin: 15px 0;">
        <span class="folio">{{ $pedido->numero_folio }}</span>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Estado:</span>
            <span class="info-value">
                <span class="estado estado-{{ $pedido->estado }}">{{ ucfirst($pedido->estado) }}</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Módulo:</span>
            <span class="info-value">{{ ucfirst($pedido->modulo) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Usuario:</span>
            <span class="info-value">{{ $pedido->usuario->nombre ?? 'N/A' }} {{ $pedido->usuario->apellido ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha:</span>
            <span class="info-value">{{ $pedido->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Descripción:</span>
            <span class="info-value">{{ $pedido->descripcion ?: '—' }}</span>
        </div>
    </div>

    @if($pedido->items->count() > 0)
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Cant.</th>
                <th>P. Unit.</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->nombre_producto }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>${{ number_format($item->precio_unitario, 2) }}</td>
                <td>${{ number_format($item->subtotal, 2) }}</td>
                <td>${{ number_format($item->iva_monto, 2) }}</td>
                <td><strong>${{ number_format($item->total_linea, 2) }}</strong></td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">TOTAL:</td>
                <td>${{ number_format($pedido->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Ticket generado el {{ now()->format('d/m/Y H:i:s') }} — Campus Digital M4.5</p>
        <p>Este documento es un comprobante de pedido. Consérvelo para cualquier aclaración.</p>
    </div>
</body>
</html>