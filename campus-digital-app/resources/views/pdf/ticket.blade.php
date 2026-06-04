<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $ticket->id_ticket }}</title>
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
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dbeafe;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            padding: 8px 10px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
            width: 50%;
        }
        .grid td .label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.05em;
            margin-bottom: 3px;
        }
        .grid td .value {
            font-size: 11px;
            color: #111827;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        /* Estado badges */
        .estado-abierto      { background: #dbeafe; color: #1e40af; }
        .estado-en-progreso  { background: #fef3c7; color: #92400e; }
        .estado-resuelto     { background: #d1fae5; color: #065f46; }
        .estado-cerrado      { background: #f3f4f6; color: #374151; }
        .estado-cancelado    { background: #fee2e2; color: #991b1b; }
        /* Prioridad badges */
        .prioridad-baja      { background: #f3f4f6; color: #374151; }
        .prioridad-media     { background: #dbeafe; color: #1e40af; }
        .prioridad-alta      { background: #ffedd5; color: #9a3412; }
        .prioridad-critica   { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ticket #{{ $ticket->id_ticket }}</h1>
        <p>Sistema de Gestión Campus Digital</p>
    </div>

    <div class="info-box">
        <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
        <p><strong>Generado por:</strong> {{ $generadoPor }}</p>
    </div>

    <div class="section-title">Información del Ticket</div>

    <table class="grid">
        <tr>
            <td>
                <div class="label">ID Ticket</div>
                <div class="value">#{{ $ticket->id_ticket }}</div>
            </td>
            <td>
                <div class="label">Solicitante</div>
                <div class="value">
                    @if($ticket->usuarioSolicitante)
                        {{ $ticket->usuarioSolicitante->nombre }} {{ $ticket->usuarioSolicitante->apellido }}
                    @else
                        —
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Categoría</div>
                <div class="value">{{ $ticket->categoria->nombre_categoria ?? '—' }}</div>
            </td>
            <td>
                <div class="label">Área</div>
                <div class="value">{{ $ticket->categoria->area->name_area ?? '—' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Equipo Relacionado</div>
                <div class="value">{{ $ticket->equipo->nombre_equipo ?? 'Sin equipo asignado' }}</div>
            </td>
            <td>
                <div class="label">Fecha de Creación</div>
                <div class="value">
                    @if($ticket->fecha_creacion)
                        {{ \Carbon\Carbon::parse($ticket->fecha_creacion)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                    @else
                        —
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Estado</div>
                <div class="value">
                    @php
                        $estadoClass = match($ticket->estado) {
                            'Abierto'     => 'estado-abierto',
                            'En progreso' => 'estado-en-progreso',
                            'Resuelto'    => 'estado-resuelto',
                            'Cerrado'     => 'estado-cerrado',
                            'Cancelado'   => 'estado-cancelado',
                            default       => 'estado-cerrado',
                        };
                    @endphp
                    <span class="badge {{ $estadoClass }}">{{ $ticket->estado }}</span>
                </div>
            </td>
            <td>
                <div class="label">Prioridad</div>
                <div class="value">
                    @php
                        $prioridadClass = match($ticket->prioridad) {
                            'Baja'    => 'prioridad-baja',
                            'Media'   => 'prioridad-media',
                            'Alta'    => 'prioridad-alta',
                            'Crítica' => 'prioridad-critica',
                            default   => 'prioridad-baja',
                        };
                    @endphp
                    <span class="badge {{ $prioridadClass }}">{{ $ticket->prioridad }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="label">Última Actualización</div>
                <div class="value">
                    {{ \Carbon\Carbon::parse($ticket->updated_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm') }}
                </div>
            </td>
        </tr>
    </table>

    @if($ticket->estado_pago === 'pagado')
    <div class="section-title" style="margin-top: 30px;">Comprobante de Pago</div>
    <div class="info-box" style="background: #ecfdf5; border-left-color: #10b981;">
        <p><strong>Estado de Pago:</strong> Pagado</p>
        <p><strong>Fecha de Pago:</strong> {{ $ticket->fecha_pago ? \Carbon\Carbon::parse($ticket->fecha_pago)->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm') : '—' }}</p>
        <p><strong>Total Cobrado:</strong> ${{ number_format($ticket->costo_total, 2) }}</p>
        <p><strong>UUID Carrito:</strong> {{ $ticket->carrito_uuid }}</p>
    </div>

    <table class="grid" style="margin-top: 10px;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 5px; border-bottom: 1px solid #ccc; font-size: 9px;">Concepto</th>
                <th style="text-align: center; padding: 5px; border-bottom: 1px solid #ccc; font-size: 9px;">Cant.</th>
                <th style="text-align: right; padding: 5px; border-bottom: 1px solid #ccc; font-size: 9px;">P.U.</th>
                <th style="text-align: right; padding: 5px; border-bottom: 1px solid #ccc; font-size: 9px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ticket->gastos as $gasto)
            <tr>
                <td style="padding: 5px; border-bottom: 1px solid #eee; font-size: 10px;">{{ $gasto->insumo->nombre_insumo ?? 'Servicio/Insumo' }}</td>
                <td style="text-align: center; padding: 5px; border-bottom: 1px solid #eee; font-size: 10px;">{{ $gasto->cantidad }}</td>
                <td style="text-align: right; padding: 5px; border-bottom: 1px solid #eee; font-size: 10px;">${{ number_format($gasto->insumo->precio_unitario ?? 0, 2) }}</td>
                <td style="text-align: right; padding: 5px; border-bottom: 1px solid #eee; font-size: 10px;">${{ number_format($gasto->cantidad * ($gasto->insumo->precio_unitario ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right; padding: 5px; font-size: 11px;">Total:</th>
                <th style="text-align: right; padding: 5px; font-size: 11px;">${{ number_format($ticket->costo_total, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Gestión Campus Digital</p>
    </div>
</body>
</html>
