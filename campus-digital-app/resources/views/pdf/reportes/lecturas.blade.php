<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
        .header { background: #0f172a; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #22d3ee; }
        .header p  { font-size: 9px; color: #94a3b8; margin-top: 3px; }
        .meta { display: flex; gap: 20px; margin: 0 20px 12px; }
        .meta span { font-size: 9px; color: #64748b; }
        .meta strong { color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin: 0 0 20px; }
        thead { background: #0f172a; }
        thead th { padding: 7px 10px; text-align: left; font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .badge-ok  { background: #dcfce7; color: #166534; }
        .badge-err { background: #fee2e2; color: #991b1b; }
        .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Campus Digital — Reporte de Lecturas RFID/NFC</h1>
        <p>Generado el {{ $fecha }}</p>
    </div>

    <div class="meta">
        @if(!empty($filtros['desde'])) <span>Desde: <strong>{{ $filtros['desde'] }}</strong></span> @endif
        @if(!empty($filtros['hasta'])) <span>Hasta: <strong>{{ $filtros['hasta'] }}</strong></span> @endif
        @if(!empty($filtros['modulo'])) <span>Módulo: <strong>{{ $filtros['modulo'] }}</strong></span> @endif
        @if(!empty($filtros['tipo'])) <span>Tipo: <strong>{{ str_replace('_', ' ', $filtros['tipo']) }}</strong></span> @endif
        <span>Total registros: <strong>{{ $lecturas->count() }}</strong></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>UID</th>
                <th>Usuario</th>
                <th>Módulo</th>
                <th>Tipo</th>
                <th>Resultado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lecturas as $l)
            <tr>
                <td>{{ $l->created_at->format('d/m/Y H:i') }}</td>
                <td style="font-family: monospace; font-size:8px;">{{ $l->uid_leido }}</td>
                <td>
                    @if($l->tarjeta?->usuario)
                        {{ $l->tarjeta->usuario->nombre }} {{ $l->tarjeta->usuario->apellido }}<br>
                        <span style="color:#64748b; font-size:8px;">{{ $l->tarjeta->usuario->email }}</span>
                    @else
                        <span style="color:#94a3b8; font-style:italic;">No registrado</span>
                    @endif
                </td>
                <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $l->modulo) }}</td>
                <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $l->tipo_lectura) }}</td>
                <td>
                    <span class="badge {{ $l->exito ? 'badge-ok' : 'badge-err' }}">
                        {{ $l->exito ? 'Exitoso' : 'Fallido' }}
                    </span>
                </td>
                <td style="max-width:200px; overflow:hidden;">{{ Str::limit($l->detalle, 60) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:20px; color:#94a3b8;">Sin registros</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Campus Digital &mdash; Reporte generado automáticamente &mdash; {{ $fecha }}</div>
</body>
</html>