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
            font-size: 10px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1e40af;
        }
        .header h1 {
            color: #1e40af;
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 10px;
        }
        .info-box {
            background: #f3f4f6;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #1e40af;
        }
        .info-box p {
            margin: 3px 0;
            font-size: 9px;
        }
        .rol-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .rol-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .rol-header h2 {
            font-size: 14px;
            margin-bottom: 3px;
        }
        .rol-header p {
            font-size: 9px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        thead {
            background: #1e40af;
            color: white;
        }
        th {
            padding: 6px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        td {
            padding: 5px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
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
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .resumen-rol {
            background: #eff6ff;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            font-size: 9px;
        }
        .resumen-general {
            margin-top: 30px;
            padding: 15px;
            background: #f9fafb;
            border: 2px solid #1e40af;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        .resumen-general h3 {
            color: #1e40af;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
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
        .footer {
            margin-top: 20px;
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
        <p>Análisis detallado de usuarios por rol</p>
    </div>

    <div class="info-box">
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
        <p><strong>Generado por:</strong> {{ $generadoPor }}</p>
        <p><strong>Total de roles activos:</strong> {{ count($datosRoles) }}</p>
    </div>

    @foreach($datosRoles as $datos)
    <div class="rol-section">
        <div class="rol-header">
            <h2>{{ strtoupper($datos['rol']->nombre) }}</h2>
            <p>{{ $datos['usuarios']->count() }} usuario(s) asignado(s)</p>
        </div>

        @if($datos['usuarios']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 25%;">Nombre Completo</th>
                    <th style="width: 25%;">Email</th>
                    <th style="width: 15%;">Teléfono</th>
                    <th style="width: 15%;">Estado</th>
                    <th style="width: 15%;">Verificado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos['usuarios'] as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->telefono ?? 'N/A' }}</td>
                    <td>
                        @if($usuario->bloqueado)
                            <span class="badge badge-danger">Bloqueado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                    <td>
                        @if($usuario->email_verificado)
                            <span class="badge badge-success">Sí</span>
                        @else
                            <span class="badge badge-warning">No</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="resumen-rol">
            <strong>Resumen:</strong> 
            Total: {{ $datos['total'] }} | 
            Activos: {{ $datos['activos'] }} | 
            Bloqueados: {{ $datos['bloqueados'] }} | 
            Verificados: {{ $datos['verificados'] }}
        </div>
        @else
        <p style="text-align: center; padding: 15px; color: #666; font-style: italic;">No hay usuarios asignados a este rol</p>
        @endif
    </div>
    @endforeach

    <div class="resumen-general">
        <h3>RESUMEN GENERAL DEL SISTEMA</h3>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $estadisticas['total'] }}</div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $estadisticas['activos'] }}</div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $estadisticas['bloqueados'] }}</div>
                <div class="stat-label">Bloqueados</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $estadisticas['verificados'] }}</div>
                <div class="stat-label">Verificados</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión</p>
        <p>Este reporte contiene información confidencial - Uso exclusivo autorizado</p>
    </div>
</body>
</html>