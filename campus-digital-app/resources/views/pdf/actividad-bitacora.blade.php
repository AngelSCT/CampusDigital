<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #333;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1e40af;
        }
        .header h1 {
            color: #1e40af;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 10px;
        }
        .info-box {
            background: #f3f4f6;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #1e40af;
        }
        .info-box p {
            margin: 2px 0;
            font-size: 9px;
        }
        .info-box strong {
            color: #1e40af;
        }
        .filtros {
            background: #eff6ff;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #3b82f6;
        }
        .filtros p {
            font-size: 8px;
            color: #1e40af;
            margin: 2px 0;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        .stat-label {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead {
            background: #1e40af;
            color: white;
        }
        th {
            padding: 5px 3px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
        }
        td {
            padding: 3px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 7px;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: bold;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-modulo {
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 6px;
        }
        .stats-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .stats-section h3 {
            color: #1e40af;
            font-size: 12px;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .two-column-stats {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .column {
            display: table-cell;
            width: 50%;
            padding: 0 5px;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Registro y Auditoría de Actividades del Sistema</p>
    </div>

    <div class="info-box">
        <p><strong>Período:</strong> {{ $periodo }}</p>
        <p><strong>Total de registros:</strong> {{ $actividades->count() }}</p>
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
        <p><strong>Generado por:</strong> {{ $generadoPor }}</p>
    </div>

    @if(count($filtrosAplicados) > 0)
    <div class="filtros">
        <p><strong>Filtros aplicados:</strong></p>
        @foreach($filtrosAplicados as $filtro)
        <p>• {{ $filtro }}</p>
        @endforeach
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value">{{ $estadisticas['total'] }}</div>
            <div class="stat-label">Total Actividades</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $estadisticas['exitosos'] }}</div>
            <div class="stat-label">Exitosas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $estadisticas['fallidos'] }}</div>
            <div class="stat-label">Fallidas</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">ID</th>
                <th style="width: 12%;">Usuario</th>
                <th style="width: 8%;">Módulo</th>
                <th style="width: 12%;">Acción</th>
                <th style="width: 6%;">Estado</th>
                <th style="width: 8%;">IP</th>
                <th style="width: 8%;">Tabla</th>
                <th style="width: 28%;">Detalle</th>
                <th style="width: 10%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($actividades as $actividad)
            <tr>
                <td>{{ $actividad->id }}</td>
                <td>{{ $actividad->usuario ? \Str::limit($actividad->usuario->email, 20) : 'N/A' }}</td>
                <td><span class="badge-modulo">{{ $actividad->modulo }}</span></td>
                <td>{{ \Str::limit($actividad->accion, 20) }}</td>
                <td>
                    @if($actividad->exito)
                        <span class="badge badge-success">Éxito</span>
                    @else
                        <span class="badge badge-danger">Fallo</span>
                    @endif
                </td>
                <td>{{ $actividad->ip ?? 'N/A' }}</td>
                <td>{{ $actividad->target_tabla ?? 'N/A' }}</td>
                <td>{{ \Str::limit($actividad->detalle ?? 'Sin detalles', 50) }}</td>
                <td>{{ $actividad->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="two-column-stats">
        <div class="column">
            <div class="stats-section">
                <h3>ESTADÍSTICAS POR MÓDULO</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">Módulo</th>
                            <th style="width: 20%;">Total</th>
                            <th style="width: 20%;">Éxito</th>
                            <th style="width: 20%;">Fallo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moduloStats as $stat)
                        <tr>
                            <td>{{ $stat['modulo'] }}</td>
                            <td>{{ $stat['total'] }}</td>
                            <td>{{ $stat['exitosos'] }}</td>
                            <td>{{ $stat['fallidos'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="column">
            <div class="stats-section">
                <h3>TOP 10 ACCIONES MÁS FRECUENTES</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 70%;">Acción</th>
                            <th style="width: 30%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accionStats as $stat)
                        <tr>
                            <td>{{ \Str::limit($stat['accion'], 30) }}</td>
                            <td>{{ $stat['total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión de Actividades</p>
        <p>Este reporte contiene información confidencial y de auditoría - Uso exclusivo autorizado</p>
    </div>
</body>
</html>