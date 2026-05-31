<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'category',
        'amount',
        'reference',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────
    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    // ─── Scopes ────────────────────────────────────────────────
    public function scopeCargos($query)
    {
        return $query->where('type', 'cargo');
    }

    public function scopeAbonos($query)
    {
        return $query->where('type', 'abono');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ─── Accessors ─────────────────────────────────────────────
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'abono' ? '+' : '-';
        return $prefix . '$' . number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute(): string
    {
        $date = $this->created_at;
        if ($date->isToday())    return 'Hoy ' . $date->format('H:i');
        if ($date->isYesterday()) return 'Ayer ' . $date->format('H:i');
        return $date->format('d/m/Y H:i');
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'Cafetería'   => '☕',
            'Librería'    => '📚',
            'Impresiones' => '🖨️',
            'Recarga'     => '💳',
            'Laboratorio' => '🔬',
            'Deportes'    => '⚽',
            default       => '💰',
        };
    }
}
