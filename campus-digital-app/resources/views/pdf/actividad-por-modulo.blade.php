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
        .modulo-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .modulo-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 5px;
        }
        .modulo-header h2 {
            font-size: 13px;
            margin-bottom: 3px;
        }
        .modulo-header p {
            font-size: 8px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
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
        .resumen-modulo {
            background: #eff6ff;
            padding: 6px;
            margin-top: 5px;
            border-radius: 5px;
            font-size: 8px;
            border-left: 3px solid #3b82f6;
        }
        .resumen-general {
            margin-top: 25px;
            padding: 12px;
            background: #f9fafb;
            border: 2px solid #1e40af;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        .resumen-general h3 {
            color: #1e40af;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: center;
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
        <p>Análisis detallado de actividades agrupadas por módulo del sistema</p>
    </div>

    <div class="info-box">
        <p><strong>Período:</strong> {{ $periodo }}</p>
        <p><strong>Total de módulos:</strong> {{ count($datosModulos) }}</p>
        <p><strong>Total de actividades:</strong> {{ $estadisticas['total'] }}</p>
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
        <p><strong>Generado por:</strong> {{ $generadoPor }}</p>
    </div>

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

    @foreach($datosModulos as $datos)
    <div class="modulo-section">
        <div class="modulo-header">
            <h2>MÓDULO: {{ strtoupper($datos['modulo']) }}</h2>
            <p>{{ $datos['actividades']->count() }} actividad(es) registrada(s)</p>
        </div>

        @if($datos['actividades']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">ID</th>
                    <th style="width: 15%;">Usuario</th>
                    <th style="width: 15%;">Acción</th>
                    <th style="width: 8%;">Estado</th>
                    <th style="width: 10%;">IP</th>
                    <th style="width: 33%;">Detalle</th>
                    <th style="width: 15%;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos['actividades'] as $actividad)
                <tr>
                    <td>{{ $actividad->id }}</td>
                    <td>{{ $actividad->usuario ? \Str::limit($actividad->usuario->email, 20) : 'N/A' }}</td>
                    <td>{{ \Str::limit($actividad->accion, 20) }}</td>
                    <td>
                        @if($actividad->exito)
                            <span class="badge badge-success">Éxito</span>
                        @else
                            <span class="badge badge-danger">Fallo</span>
                        @endif
                    </td>
                    <td>{{ $actividad->ip ?? 'N/A' }}</td>
                    <td>{{ \Str::limit($actividad->detalle ?? 'Sin detalles', 50) }}</td>
                    <td>{{ $actividad->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="resumen-modulo">
            <strong>Resumen del módulo:</strong> 
            Total: {{ $datos['total'] }} | 
            Exitosas: {{ $datos['exitosos'] }} | 
            Fallidas: {{ $datos['fallidos'] }} | 
            Tasa de éxito: {{ $datos['total'] > 0 ? round(($datos['exitosos'] / $datos['total']) * 100, 1) : 0 }}%
        </div>
        @else
        <p style="text-align: center; padding: 15px; color: #666; font-style: italic;">No hay actividades registradas en este módulo</p>
        @endif
    </div>
    @endforeach

    <div class="resumen-general">
        <h3>RESUMEN GENERAL POR MÓDULO</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Módulo</th>
                    <th style="width: 20%;">Total</th>
                    <th style="width: 20%;">Exitosas</th>
                    <th style="width: 20%;">Fallidas</th>
                    <th style="width: 10%;">% Éxito</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosModulos as $datos)
                <tr>
                    <td><strong>{{ $datos['modulo'] }}</strong></td>
                    <td>{{ $datos['total'] }}</td>
                    <td>{{ $datos['exitosos'] }}</td>
                    <td>{{ $datos['fallidos'] }}</td>
                    <td>{{ $datos['total'] > 0 ? round(($datos['exitosos'] / $datos['total']) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión de Actividades</p>
        <p>Este reporte contiene información confidencial y de auditoría - Uso exclusivo autorizado</p>
    </div>
</body>
</html>