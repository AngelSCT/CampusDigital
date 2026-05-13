<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
        .header { background: #0f172a; color: #fff; padding: 16px 20px; margin-bottom: 16px; border-bottom: 3px solid #dc2626; }
        .header h1 { font-size: 16px; font-weight: bold; color: #fca5a5; }
        .header p  { font-size: 9px; color: #94a3b8; margin-top: 3px; }
        .meta { margin: 0 20px 12px; font-size: 9px; color: #64748b; }
        .meta strong { color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead { background: #0f172a; }
        thead th { padding: 7px 10px; text-align: left; font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
        tbody tr:nth-child(even) { background: #fff5f5; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        .badge { display:inline-block; padding: 2px 7px; border-radius: 20px; font-size: 8px; font-weight: bold; }
        .badge-bloqueada { background: #fee2e2; color: #991b1b; }
        .badge-perdida   { background: #fef9c3; color: #854d0e; }
        .badge-cancelada { background: #f1f5f9; color: #475569; }
        .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Campus Digital — Incidentes y Auditoría de Bloqueos</h1>
        <p>Generado el {{ $fecha }}</p>
    </div>

    <div class="meta">
        Total incidentes: <strong>{{ $incidentes->count() }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>UID</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Motivo</th>
                <th>Bloqueado por</th>
                <th>Fecha bloqueo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidentes as $t)
            <tr>
                <td style="font-family:monospace; font-size:8px;">{{ $t->uid }}</td>
                <td>
                    @if($t->usuario)
                        {{ $t->usuario->nombre }} {{ $t->usuario->apellido }}<br>
                        <span style="color:#64748b; font-size:8px;">{{ $t->usuario->email }}</span>
                    @else
                        <span style="color:#94a3b8; font-style:italic;">Sin usuario</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $t->estado }}">{{ ucfirst($t->estado) }}</span>
                </td>
                <td style="max-width:180px;">{{ $t->motivo_bloqueo ?? '-' }}</td>
                <td>
                    @if($t->bloqueadoPor)
                        {{ $t->bloqueadoPor->nombre }} {{ $t->bloqueadoPor->apellido }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $t->bloqueado_at?->format('d/m/Y H:i') ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px; color:#94a3b8;">Sin incidentes registrados</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Campus Digital &mdash; Reporte generado automáticamente &mdash; {{ $fecha }}</div>
</body>
</html>