<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivo';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'carpeta_id',
        'nombre_original',
        'nombre_almacenado',
        'ruta',
        'mime_type',
        'extension',
        'tamanio',
        'visto_admin',
        'visto_admin_at',
        'visto_por',
        'notas_admin',
    ];

    protected $casts = [
        'visto_admin'    => 'boolean',
        'visto_admin_at' => 'datetime',
        'tamanio'        => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function carpeta()
    {
        return $this->belongsTo(ArchivosCarpeta::class, 'carpeta_id');
    }

    public function vistoBy()
    {
        return $this->belongsTo(Usuario::class, 'visto_por');
    }

    public function getTamanioHumanoAttribute(): string
    {
        $b = $this->tamanio;
        if ($b < 1024)        return "{$b} B";
        if ($b < 1_048_576)   return round($b / 1024, 1) . ' KB';
        if ($b < 1_073_741_824) return round($b / 1_048_576, 1) . ' MB';
        return round($b / 1_073_741_824, 1) . ' GB';
    }

    public function getIconoAttribute(): string
    {
        return match(strtolower($this->extension)) {
            'pdf'                     => 'pdf',
            'doc', 'docx'             => 'word',
            'xls', 'xlsx'             => 'excel',
            'ppt', 'pptx'             => 'powerpoint',
            'txt', 'csv'              => 'text',
            'png', 'jpg', 'jpeg',
            'gif', 'webp', 'svg'      => 'image',
            'zip', 'rar', '7z', 'tar' => 'archive',
            default                   => 'generic',
        };
    }

    public function getEsPrevisualizableAttribute(): bool
    {
        return in_array(strtolower($this->extension), [
            'pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'txt',
        ]);
    }
}