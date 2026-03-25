<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Recarga;
use Illuminate\Http\Request;

class ComprobanteController extends Controller
{
    /**
     * Listar todos los comprobantes
     */
    public function index()
    {
        $comprobantes = Comprobante::with('referencia')->paginate(15);
        
        return response()->json([
            'mensaje' => 'Comprobantes obtenidos correctamente',
            'data' => $comprobantes
        ]);
    }

    /**
     * Ver un comprobante específico
     */
    public function show($id)
    {
        $comprobante = Comprobante::with('referencia')->findOrFail($id);
        
        return response()->json([
            'mensaje' => 'Comprobante obtenido correctamente',
            'data' => $comprobante
        ]);
    }

    /**
     * Crear un nuevo comprobante
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:usuario,id',
            'referencia_id' => 'required|integer',
            'referencia_type' => 'required|string', // ej: "App\Models\Recarga"
            'total' => 'required|numeric|min:0.01'
        ]);

        try {
            $comprobante = Comprobante::create($validated);

            return response()->json([
                'mensaje' => 'Comprobante creado exitosamente',
                'data' => $comprobante
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el comprobante',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar comprobante
     */
    public function update(Request $request, $id)
    {
        $comprobante = Comprobante::findOrFail($id);

        $validated = $request->validate([
            'total' => 'sometimes|numeric|min:0.01'
        ]);

        try {
            $comprobante->update($validated);

            return response()->json([
                'mensaje' => 'Comprobante actualizado correctamente',
                'data' => $comprobante
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar comprobante
     */
    public function destroy($id)
    {
        $comprobante = Comprobante::findOrFail($id);

        try {
            $comprobante->delete();

            return response()->json([
                'mensaje' => 'Comprobante eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar comprobante en PDF
     */
    public function generarPDF($id)
    {
        $comprobante = Comprobante::with(['usuario', 'referencia'])->findOrFail($id);

        // Aquí irá la lógica para generar PDF
        // Por ahora, devolvemos los datos
        return response()->json([
            'mensaje' => 'PDF generado (función pendiente)',
            'data' => $comprobante
        ]);
    }
}