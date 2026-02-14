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
            font-size: 9px;
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
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        td {
            padding: 4px;
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
            font-size: 7px;
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
        .evento-stats {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .evento-stats h3 {
            color: #1e40af;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .evento-stats table {
            margin-top: 5px;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #666;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Control y Monitoreo de Accesos</p>
    </div>

    <div class="info-box">
        <p><strong>Período:</strong> {{ $periodo }}</p>
        <p><strong>Total de registros:</strong> {{ $accesos->count() }}</p>
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
        <p><strong>Generado por:</strong> {{ $generadoPor }}</p>
    </div>

    @if(isset($filtrosAplicados) && count($filtrosAplicados) > 0)
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
            <div class="stat-label">Total Accesos</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $estadisticas['exitosos'] }}</div>
            <div class="stat-label">Exitosos</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $estadisticas['fallidos'] }}</div>
            <div class="stat-label">Fallidos</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">ID</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 10%;">Evento</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 10%;">IP</th>
                <th style="width: 25%;">User Agent</th>
                <th style="width: 18%;">Detalle</th>
                <th style="width: 10%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accesos as $acceso)
            <tr>
                <td>{{ $acceso->id }}</td>
                <td>{{ $acceso->email_intentado ?? 'N/A' }}</td>
                <td>{{ $acceso->evento }}</td>
                <td>
                    @if($acceso->exito)
                        <span class="badge badge-success">Exitoso</span>
                    @else
                        <span class="badge badge-danger">Fallido</span>
                    @endif
                </td>
                <td>{{ $acceso->ip ?? 'N/A' }}</td>
                <td>{{ \Str::limit($acceso->user_agent ?? 'N/A', 40) }}</td>
                <td>{{ \Str::limit($acceso->detalle ?? 'Sin detalles', 30) }}</td>
                <td>{{ $acceso->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="evento-stats">
        <h3>ESTADÍSTICAS POR TIPO DE EVENTO</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Evento</th>
                    <th style="width: 20%;">Total</th>
                    <th style="width: 20%;">Exitosos</th>
                    <th style="width: 20%;">Fallidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eventoStats as $stat)
                <tr>
                    <td>{{ $stat['evento'] }}</td>
                    <td>{{ $stat['total'] }}</td>
                    <td>{{ $stat['exitosos'] }}</td>
                    <td>{{ $stat['fallidos'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión de Accesos</p>
        <p>Este reporte contiene información confidencial - Uso exclusivo autorizado</p>
    </div>
</body>
</html>