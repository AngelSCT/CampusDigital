<?php

namespace App\Http\Controllers\Archivos;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\ArchivosCarpeta;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArchivoController extends Controller
{
    private const EXTENSIONES_PERMITIDAS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg',
        'zip', 'rar', '7z',
    ];

    private const MAX_TAMANIO_MB = 50;

    public function index(Request $request)
    {
        $usuario   = auth()->user();
        $esAdmin   = $usuario->hasRole('administrador');
        $carpetaId = $request->get('carpeta') ? (int) $request->get('carpeta') : null;

        $usuarioId = $esAdmin
            ? ($request->get('usuario_id') ? (int) $request->get('usuario_id') : $usuario->id)
            : $usuario->id;

        $usuarioVisto = $usuarioId !== $usuario->id
            ? Usuario::findOrFail($usuarioId)
            : $usuario;

        $carpetas = ArchivosCarpeta::where('usuario_id', $usuarioId)
            ->where('padre_id', $carpetaId)
            ->whereNull('deleted_at')
            ->withCount(['archivos'])
            ->orderBy('nombre')
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'nombre'        => $c->nombre,
                'padre_id'      => $c->padre_id,
                'archivos_count'=> $c->archivos_count,
                'created_at'    => $c->created_at,
            ]);

        $archivos = Archivo::where('usuario_id', $usuarioId)
            ->where('carpeta_id', $carpetaId)
            ->whereNull('deleted_at')
            ->with(['vistoBy:id,nombre,apellido'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'                => $a->id,
                'nombre_original'   => $a->nombre_original,
                'extension'         => $a->extension,
                'mime_type'         => $a->mime_type,
                'tamanio'           => $a->tamanio,
                'tamanio_humano'    => $a->tamanio_humano,
                'icono'             => $a->icono,
                'es_previsualizable'=> $a->es_previsualizable,
                'visto_admin'       => $a->visto_admin,
                'visto_admin_at'    => $a->visto_admin_at,
                'notas_admin'       => $a->notas_admin,
                'visto_por'         => $a->vistoBy ? $a->vistoBy->nombre . ' ' . $a->vistoBy->apellido : null,
                'carpeta_id'        => $a->carpeta_id,
                'created_at'        => $a->created_at,
            ]);

        $migajas = $this->buildBreadcrumb($carpetaId);

        $stats = [
            'total_archivos'  => Archivo::where('usuario_id', $usuarioId)->whereNull('deleted_at')->count(),
            'total_carpetas'  => ArchivosCarpeta::where('usuario_id', $usuarioId)->whereNull('deleted_at')->count(),
            'tamanio_total'   => $this->formatBytes(
                Archivo::where('usuario_id', $usuarioId)->whereNull('deleted_at')->sum('tamanio')
            ),
            'sin_ver_admin'   => $esAdmin
                ? Archivo::whereNull('deleted_at')->where('visto_admin', false)->count()
                : null,
        ];

        $usuariosConArchivos = $esAdmin
            ? Usuario::whereHas('archivos', fn($q) => $q->whereNull('deleted_at'))
                ->select('id', 'nombre', 'apellido', 'email')
                ->orderBy('nombre')
                ->get()
                ->map(fn($u) => [
                    'id'         => $u->id,
                    'nombre'     => $u->nombre,
                    'apellido'   => $u->apellido,
                    'email'      => $u->email,
                    'archivos'   => Archivo::where('usuario_id', $u->id)->whereNull('deleted_at')->count(),
                ])
            : null;

        return Inertia::render('Archivos/Index', [
            'carpetas'            => $carpetas,
            'archivos'            => $archivos,
            'carpetaActual'       => $carpetaId ? ArchivosCarpeta::find($carpetaId) : null,
            'migajas'             => $migajas,
            'esAdmin'             => $esAdmin,
            'usuarioVisto'        => [
                'id'      => $usuarioVisto->id,
                'nombre'  => $usuarioVisto->nombre,
                'apellido'=> $usuarioVisto->apellido,
                'email'   => $usuarioVisto->email,
            ],
            'usuariosConArchivos' => $usuariosConArchivos,
            'stats'               => $stats,
            'usuarioActualId'     => $usuario->id,
        ]);
    }

    public function crearCarpeta(Request $request)
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'max:200', 'regex:/^[^\/\\\\<>:"|?*]+$/'],
            'padre_id' => ['nullable', 'integer', 'exists:archivo_carpeta,id'],
        ]);

        $usuario = auth()->user();

        if ($request->padre_id) {
            $padre = ArchivosCarpeta::findOrFail($request->padre_id);
            if ($padre->usuario_id !== $usuario->id) {
                abort(403);
            }
        }

        $existe = ArchivosCarpeta::where('usuario_id', $usuario->id)
            ->where('padre_id', $request->padre_id)
            ->where('nombre', $request->nombre)
            ->whereNull('deleted_at')
            ->exists();

        if ($existe) {
            return back()->withErrors(['nombre' => 'Ya existe una carpeta con ese nombre en esta ubicación.']);
        }

        $ruta = $this->buildRuta($request->padre_id, $request->nombre);

        ArchivosCarpeta::create([
            'usuario_id' => $usuario->id,
            'nombre'     => $request->nombre,
            'padre_id'   => $request->padre_id,
            'ruta'       => $ruta,
        ]);

        return back()->with('success', "Carpeta \"{$request->nombre}\" creada exitosamente.");
    }

    public function subir(Request $request)
    {
        $request->validate([
            'archivo'    => [
                'required',
                'file',
                'max:' . (self::MAX_TAMANIO_MB * 1024),
                'mimes:' . implode(',', self::EXTENSIONES_PERMITIDAS),
            ],
            'carpeta_id' => ['nullable', 'integer', 'exists:archivo_carpeta,id'],
        ]);

        $usuario   = auth()->user();
        $file      = $request->file('archivo');
        $ext       = strtolower($file->getClientOriginalExtension());
        $uuid      = Str::uuid();
        $nombre    = $uuid . '.' . $ext;
        $carpetaId = $request->carpeta_id ? (int) $request->carpeta_id : null;

        if ($carpetaId) {
            $carpeta = ArchivosCarpeta::findOrFail($carpetaId);
            if ($carpeta->usuario_id !== $usuario->id) {
                abort(403);
            }
        }

        $subdir = "archivos/usuario_{$usuario->id}/" . ($carpetaId ? "carpeta_{$carpetaId}" : 'raiz');
        $ruta   = $file->storeAs($subdir, $nombre, 'local');

        Archivo::create([
            'usuario_id'        => $usuario->id,
            'carpeta_id'        => $carpetaId,
            'nombre_original'   => $file->getClientOriginalName(),
            'nombre_almacenado' => $nombre,
            'ruta'              => $ruta,
            'mime_type'         => $file->getMimeType() ?? 'application/octet-stream',
            'extension'         => $ext,
            'tamanio'           => $file->getSize(),
        ]);

        return back()->with('success', "Archivo \"{$file->getClientOriginalName()}\" subido exitosamente.");
    }

    public function previsualizar(Archivo $archivo)
    {
        $this->autorizarAcceso($archivo);

        if (!Storage::disk('local')->exists($archivo->ruta)) {
            abort(404, 'Archivo no encontrado en el servidor.');
        }

        return Storage::disk('local')->response($archivo->ruta, $archivo->nombre_original, [
            'Content-Type'        => $archivo->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes($archivo->nombre_original) . '"',
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }

    public function descargar(Archivo $archivo)
    {
        $this->autorizarAcceso($archivo);

        if (!Storage::disk('local')->exists($archivo->ruta)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('local')->download($archivo->ruta, $archivo->nombre_original);
    }

    public function eliminarArchivo(Archivo $archivo)
    {
        $this->autorizarAcceso($archivo);

        Storage::disk('local')->delete($archivo->ruta);
        $archivo->delete();

        return back()->with('success', "Archivo \"{$archivo->nombre_original}\" eliminado.");
    }

    public function eliminarCarpeta(ArchivosCarpeta $carpeta)
    {
        $usuario = auth()->user();

        if (!$usuario->hasRole('administrador') && $carpeta->usuario_id !== $usuario->id) {
            abort(403);
        }

        $totalArchivos = $this->contarArchivosRecursivo($carpeta);
        $this->eliminarCarpetaRecursiva($carpeta);

        return back()->with('success', "Carpeta \"{$carpeta->nombre}\" eliminada ({$totalArchivos} archivos borrados).");
    }

    public function renombrarCarpeta(Request $request, ArchivosCarpeta $carpeta)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:200', 'regex:/^[^\/\\\\<>:"|?*]+$/'],
        ]);

        $usuario = auth()->user();
        if (!$usuario->hasRole('administrador') && $carpeta->usuario_id !== $usuario->id) {
            abort(403);
        }

        $carpeta->update(['nombre' => $request->nombre]);
        return back()->with('success', 'Carpeta renombrada.');
    }

    public function marcarVisto(Archivo $archivo)
    {
        $archivo->update([
            'visto_admin'    => true,
            'visto_admin_at' => now(),
            'visto_por'      => auth()->id(),
        ]);

        return back()->with('success', 'Archivo marcado como revisado.');
    }

    public function desmarcarVisto(Archivo $archivo)
    {
        $archivo->update([
            'visto_admin'    => false,
            'visto_admin_at' => null,
            'visto_por'      => null,
        ]);

        return back()->with('success', 'Marcado de revisión eliminado.');
    }

    public function agregarNota(Request $request, Archivo $archivo)
    {
        $request->validate([
            'notas_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $archivo->update(['notas_admin' => $request->notas_admin ?? '']);

        return back()->with('success', 'Nota guardada exitosamente.');
    }

    private function autorizarAcceso(Archivo $archivo): void
    {
        $usuario = auth()->user();
        if (!$usuario->hasRole('administrador') && $archivo->usuario_id !== $usuario->id) {
            abort(403, 'No tienes permiso para acceder a este archivo.');
        }
    }

    private function buildBreadcrumb(?int $carpetaId): array
    {
        if (!$carpetaId) return [];

        $migajas = [];
        $carpeta  = ArchivosCarpeta::find($carpetaId);

        while ($carpeta) {
            array_unshift($migajas, [
                'id'     => $carpeta->id,
                'nombre' => $carpeta->nombre,
            ]);
            $carpeta = $carpeta->padre_id
                ? ArchivosCarpeta::find($carpeta->padre_id)
                : null;
        }

        return $migajas;
    }

    private function buildRuta(?int $padreId, string $nombre): string
    {
        if (!$padreId) return "/{$nombre}";
        $padre = ArchivosCarpeta::find($padreId);
        return $padre ? rtrim($padre->ruta, '/') . '/' . $nombre : "/{$nombre}";
    }

    private function eliminarCarpetaRecursiva(ArchivosCarpeta $carpeta): void
    {
        foreach ($carpeta->archivos as $archivo) {
            Storage::disk('local')->delete($archivo->ruta);
            $archivo->delete();
        }

        foreach ($carpeta->hijos as $hijo) {
            $this->eliminarCarpetaRecursiva($hijo);
        }

        $carpeta->delete();
    }

    private function contarArchivosRecursivo(ArchivosCarpeta $carpeta): int
    {
        $count = $carpeta->archivos->count();
        foreach ($carpeta->hijos as $hijo) {
            $count += $this->contarArchivosRecursivo($hijo);
        }
        return $count;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024)          return "{$bytes} B";
        if ($bytes < 1_048_576)     return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1_073_741_824) return round($bytes / 1_048_576, 1) . ' MB';
        return round($bytes / 1_073_741_824, 2) . ' GB';
    }
}