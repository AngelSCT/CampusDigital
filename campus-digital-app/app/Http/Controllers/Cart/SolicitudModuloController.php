<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\SolicitudModuloRequest;
use App\Models\Cart\Bitacora;
use App\Models\Cart\SolicitudModulo;

class SolicitudModuloController extends Controller
{
    public function store(SolicitudModuloRequest $request)
    {
        // 409: ya existe solicitud pendiente o aprobada para el mismo tipo_modulo.
        $existe = SolicitudModulo::where('tipo_modulo', $request->tipo_modulo)
            ->whereIn('estado', [SolicitudModulo::ESTADO_PENDIENTE, SolicitudModulo::ESTADO_APROBADA])
            ->exists();

        if ($existe) {
            return response()->json([
                'error'   => 'SOLICITUD_EXISTENTE',
                'mensaje' => 'Ya existe una solicitud pendiente o aprobada para este tipo de módulo.',
            ], 409);
        }

        $folio = $this->generarFolioUnico($request->tipo_modulo);

        $solicitud = SolicitudModulo::create([
            'folio'                  => $folio,
            'nombre_modulo'          => $request->nombre_modulo,
            'tipo_modulo'            => $request->tipo_modulo,
            'categorias_solicitadas' => $request->categorias_solicitadas,
            'contacto_nombre'        => $request->contacto_nombre,
            'contacto_email'         => $request->contacto_email,
            'descripcion'            => $request->descripcion,
        ]);

        Bitacora::create([
            'accion'     => Bitacora::ACCION_MODULO_SOLICITUD,
            'ip_address' => $request->ip(),
            'payload'    => [
                'folio'          => $folio,
                'tipo_modulo'    => $request->tipo_modulo,
                'nombre_modulo'  => $request->nombre_modulo,
            ],
        ]);

        return response()->json([
            'folio'   => $folio,
            'mensaje' => 'Solicitud registrada. Recibirás notificación cuando sea revisada.',
            'estado'  => SolicitudModulo::ESTADO_PENDIENTE,
        ], 201);
    }

    public function estado(string $folio)
    {
        $solicitud = SolicitudModulo::where('folio', $folio)->first();

        if (!$solicitud) {
            return response()->json(['mensaje' => 'Folio no encontrado.'], 404);
        }

        $data = [
            'folio'      => $solicitud->folio,
            'estado'     => $solicitud->estado,
            'creada_at'  => $solicitud->created_at->toISOString(),
        ];

        // Solo exponer motivo_rechazo si el estado lo requiere; nunca datos sensibles.
        if ($solicitud->estado === SolicitudModulo::ESTADO_RECHAZADA && $solicitud->motivo_rechazo) {
            $data['motivo_rechazo'] = $solicitud->motivo_rechazo;
        }

        return response()->json($data);
    }

    private function generarFolioUnico(string $tipoModulo): string
    {
        // Prefijo: primeras 3 letras del tipo_modulo en mayúsculas, completado con X si es corto.
        $prefix = strtoupper(str_pad(substr($tipoModulo, 0, 3), 3, 'X'));

        do {
            $folio = $prefix . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (SolicitudModulo::where('folio', $folio)->exists());

        return $folio;
    }
}
