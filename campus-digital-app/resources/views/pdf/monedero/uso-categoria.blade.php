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
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #1e40af; color: white; }
        th { padding: 8px; text-align: left; font-size: 9px; font-weight: bold; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .progress-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; }
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
        @if($reporte['usuario_id'])
        <p><strong>Usuario ID:</strong> {{ $reporte['usuario_id'] }}</p>
        @endif
        @if($reporte['modulo'] ?? false)
        <p><strong>Módulo filtrado:</strong> {{ $reporte['modulo'] }}</p>
        @endif
        <p><strong>Total General Consumido:</strong> ${{ number_format($reporte['total_general'], 2) }}</p>
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Categoría</th>
                <th class="text-right">Total Cargo</th>
                <th class="text-right">Total Abono</th>
                <th class="text-right">Transacciones</th>
                <th class="text-right">Usuarios Únicos</th>
                <th class="text-right">%</th>
                <th>Distribución</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reporte['categorias'] as $cat)
            <tr>
                <td><strong>{{ $cat->modulo }}</strong></td>
                <td class="text-right">${{ number_format($cat->total_cargo, 2) }}</td>
                <td class="text-right">${{ number_format($cat->total_abono, 2) }}</td>
                <td class="text-right">{{ $cat->cantidad }}</td>
                <td class="text-right">{{ $cat->usuarios_unicos }}</td>
                <td class="text-right">{{ number_format($cat->porcentaje, 2) }}%</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min($cat->porcentaje, 100) }}%; background: 
                            @if($loop->index % 5 == 0) #3b82f6
                            @elseif($loop->index % 5 == 1) #10b981
                            @elseif($loop->index % 5 == 2) #f59e0b
                            @elseif($loop->index % 5 == 3) #ef4444
                            @else #8b5cf6
                            @endif;">
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align: center; padding: 20px; color: #999;">No se encontraron categorías</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente por Campus Digital - Módulo Monedero</p>
        <p>Página {PAGENO} de {nb}</p>
    </div>
</body>
</html>
