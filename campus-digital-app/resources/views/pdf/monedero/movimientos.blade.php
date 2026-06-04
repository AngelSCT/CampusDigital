<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #1e40af; }
        .header h1 { color: #1e40af; font-size: 22px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 11px; }
        .info-box { background: #f3f4f6; padding: 10px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #1e40af; }
        .info-box p { margin: 3px 0; font-size: 10px; }
        .summary-grid { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .summary-card { flex: 1; min-width: 120px; padding: 10px; border-radius: 5px; text-align: center; }
        .summary-card h4 { font-size: 9px; text-transform: uppercase; margin-bottom: 5px; }
        .summary-card .value { font-size: 16px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #1e40af; color: white; }
        th { padding: 8px; text-align: left; font-size: 9px; font-weight: bold; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 2px solid #e5e7eb; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Módulo de Saldo Digital Universitario</p>
    </div>

    <div class="info-box">
        <p><strong>Período:</strong> {{ $reporte['periodo']['desde'] }} a {{ $reporte['periodo']['hasta'] }}</p>
        @if($reporte['usuario_id'] && count($reporte['movimientos']) > 0)
        <p><strong>Usuario:</strong> {{ $reporte['movimientos'][0]->usuario->nombre ?? 'N/A' }}</p>
        @endif
        @if($reporte['modulo'])
        <p><strong>Módulo filtrado:</strong> {{ $reporte['modulo'] }}</p>
        @endif
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card" style="background: #f3f4f6;">
            <h4>Total Movimientos</h4>
            <div class="value" style="color: #374151;">{{ $reporte['resumen']['total_movimientos'] }}</div>
        </div>
        <div class="summary-card" style="background: #d1fae5;">
            <h4>Total Abonos</h4>
            <div class="value" style="color: #065f46;">${{ number_format($reporte['resumen']['total_abonos'], 2) }}</div>
        </div>
        <div class="summary-card" style="background: #fee2e2;">
            <h4>Total Cargos</h4>
            <div class="value" style="color: #991b1b;">${{ number_format($reporte['resumen']['total_cargos'], 2) }}</div>
        </div>
        <div class="summary-card" style="background: #dbeafe;">
            <h4>Saldo Neto</h4>
            <div class="value" style="color: #1e40af;">${{ number_format($reporte['resumen']['saldo_neto'], 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Módulo</th>
                <th>Concepto</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reporte['movimientos'] as $mov)
            <tr>
                <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $mov->usuario->nombre ?? 'N/A' }}</td>
                <td>
                    @if($mov->tipo === 'abono')
                        <span class="badge badge-success">Abono</span>
                    @else
                        <span class="badge badge-danger">Cargo</span>
                    @endif
                </td>
                <td>{{ $mov->modulo }}</td>
                <td>{{ $mov->concepto }}</td>
                <td class="text-right">${{ number_format($mov->monto, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align: center; padding: 20px; color: #999;">No se encontraron movimientos</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente por Campus Digital - Módulo Monedero</p>
        <p>Página {PAGENO} de {nb}</p>
    </div>
</body>
</html>
