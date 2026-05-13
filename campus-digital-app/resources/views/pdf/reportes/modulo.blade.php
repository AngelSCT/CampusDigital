<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }
        .header { background: #0f172a; color: #fff; padding: 16px 20px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #22d3ee; }
        .header p  { font-size: 9px; color: #94a3b8; margin-top: 3px; }
        .meta { margin: 0 20px 16px; font-size: 9px; color: #64748b; }
        .meta strong { color: #1e293b; }
        table { width: 90%; margin: 0 auto 20px; border-collapse: collapse; }
        thead { background: #0f172a; }
        thead th { padding: 9px 14px; text-align: left; font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 8px 14px; border-bottom: 1px solid #e2e8f0; }
        .bar-wrap { background: #e2e8f0; border-radius: 4px; height: 10px; width: 200px; display:inline-block; vertical-align: middle; }
        .bar-fill  { background: linear-gradient(to right, #0284c7, #22d3ee); border-radius: 4px; height: 10px; display:block; }
        .num { font-weight: bold; }
        .green { color: #16a34a; }
        .red   { color: #dc2626; }
        .footer { text-align: center; font-size: 8px; color: #94a3b8; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Campus Digital — Uso por Módulo RFID/NFC</h1>
        <p>Generado el {{ $fecha }}</p>
    </div>

    <div class="meta">
        @if(!empty($filtros['desde'])) Desde: <strong>{{ $filtros['desde'] }}</strong> &nbsp;|&nbsp; @endif
        @if(!empty($filtros['hasta'])) Hasta: <strong>{{ $filtros['hasta'] }}</strong> &nbsp;|&nbsp; @endif
        Total módulos: <strong>{{ $usoModulo->count() }}</strong>
    </div>

    @php $maxTotal = $usoModulo->max('total') ?: 1; @endphp

    <table>
        <thead>
            <tr>
                <th>Módulo</th>
                <th>Total lecturas</th>
                <th>Exitosas</th>
                <th>Fallidas</th>
                <th>% Éxito</th>
                <th>Distribución</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usoModulo as $m)
            @php
                $fallidas  = $m->total - $m->exitosas;
                $pct       = $m->total > 0 ? round($m->exitosas / $m->total * 100, 1) : 0;
                $barWidth  = round($m->total / $maxTotal * 100);
            @endphp
            <tr>
                <td style="text-transform:capitalize; font-weight:bold;">{{ str_replace('_', ' ', $m->modulo) }}</td>
                <td><span class="num">{{ $m->total }}</span></td>
                <td><span class="green num">{{ $m->exitosas }}</span></td>
                <td><span class="red num">{{ $fallidas }}</span></td>
                <td><span class="num">{{ $pct }}%</span></td>
                <td>
                    <div class="bar-wrap">
                        <span class="bar-fill" style="width:{{ $barWidth }}%;"></span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px; color:#94a3b8;">Sin datos</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Campus Digital &mdash; Reporte generado automáticamente &mdash; {{ $fecha }}</div>
</body>
</html>