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
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1e40af;
        }
        .header h1 {
            color: #1e40af;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
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
            font-size: 10px;
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
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        tbody tr:hover {
            background: #f3f4f6;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
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
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .roles-list {
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Sistema de Gestión de Usuarios</p>
    </div>

    <div class="info-box">
        <p><strong>Total de usuarios:</strong> {{ $usuarios->count() }}</p>
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
        <p><strong>Generado por:</strong> {{ $generadoPor }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Nombre Completo</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 12%;">Teléfono</th>
                <th style="width: 18%;">Roles</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 15%;">Verificado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->id }}</td>
                <td>{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                <td>{{ $usuario->email }}</td>
                <td>{{ $usuario->telefono ?? 'N/A' }}</td>
                <td>
                    <div class="roles-list">
                        @foreach($usuario->roles as $rol)
                            <span class="badge badge-info">{{ $rol->nombre }}</span>
                        @endforeach
                    </div>
                </td>
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

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión</p>
        <p>Página {PAGENO} de {nb}</p>
    </div>
</body>
</html>