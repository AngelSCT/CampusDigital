<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 11px; background: #0f0f1a; color: #e2e8f0; }
    .header { background: linear-gradient(135deg, #1e1e3f, #6c3fc5); padding: 20px 24px; margin-bottom: 16px; }
    .header h1 { font-size: 20px; color: #fff; font-weight: bold; }
    .header p { font-size: 11px; color: #a78bfa; margin-top: 4px; }
    .meta { display: flex; gap: 16px; padding: 0 24px 12px; }
    .meta-item { background: #1e1e2e; border: 1px solid #2d2d3f; border-radius: 6px; padding: 8px 14px; }
    .meta-item .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
    .meta-item .value { font-size: 13px; font-weight: bold; color: #a78bfa; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
    thead tr { background: #6c3fc5; }
    thead th { padding: 8px 10px; text-align: left; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    tbody tr:nth-child(even) { background: #1e1e2e; }
    tbody tr:nth-child(odd)  { background: #16213e; }
    tbody td { padding: 7px 10px; color: #e2e8f0; border-bottom: 1px solid #2d2d3f; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: bold; }
    .badge-creado     { background: #1d4ed8; color: #bfdbfe; }
    .badge-aceptado   { background: #92400e; color: #fde68a; }
    .badge-en_proceso { background: #9a3412; color: #fed7aa; }
    .badge-listo      { background: #581c87; color: #e9d5ff; }
    .badge-entregado  { background: #14532d; color: #bbf7d0; }
    .badge-cancelado  { background: #7f1d1d; color: #fecaca; }
    .footer { text-align: center; color: #4b5563; font-size: 9px; padding: 8px 24px; border-top: 1px solid #2d2d3f; }
    .total-row { font-weight: bold; text-align: right; color: #a78bfa; }
</style>
</head>
<body>
<div class="header">
    <h1>📦 Reporte de Pedidos — Campus Digital</h1>
    <p>Módulo 4.5: Pedidos y Seguimiento &nbsp;·&nbsp; Generado: {{ $fecha }}</p>
</div>

<div class="meta">
    <div class="meta-item">
        <div class="label">Total registros</div>
        <div class="value">{{ $pedidos->count() }}</div>
    </div>
    <div class="meta-item">
        <div class="label">Monto total</div>
        <div class="value">${{ number_format($pedidos->sum('total'), 2) }}</div>
    </div>
    @if($filtros['estado'] ?? null)
    <div class="meta-item">
        <div class="label">Filtro estado</div>
        <div class="value">{{ $filtros['estado'] }}</div>
    </div>
    @endif
    @if($filtros['modulo'] ?? null)
    <div class="meta-item">
        <div class="label">Filtro módulo</div>
        <div class="value">{{ $filtros['modulo'] }}</div>
    </div>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Folio</th>
            <th>Usuario</th>
            <th>Módulo</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Operador</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedidos as $i => $p)
        <tr>
            <td style="color:#6b7280">{{ $i + 1 }}</td>
            <td style="font-family:monospace; color:#a78bfa; font-size:10px">{{ $p->numero_folio }}</td>
            <td>{{ $p->usuario?->nombre_completo ?? '-' }}</td>
            <td style="text-transform:capitalize">{{ $p->modulo }}</td>
            <td><span class="badge badge-{{ $p->estado }}">{{ strtoupper(str_replace('_',' ',$p->estado)) }}</span></td>
            <td style="text-align:right; font-weight:bold">${{ number_format($p->total, 2) }}</td>
            <td>{{ $p->operador?->nombre_completo ?? '-' }}</td>
            <td style="color:#6b7280">{{ $p->created_at?->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Campus Digital · Módulo 4.5 Pedidos y Seguimiento · {{ $fecha }} · Documento generado automáticamente
</div>
</body>
</html>