<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'card_number',
        'monthly_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance'       => 'decimal:2',
            'monthly_limit' => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ─── Helpers ───────────────────────────────────────────────
    public function getFormattedBalanceAttribute(): string
    {
        return '$' . number_format($this->balance, 2);
    }

    public function getFormattedCardAttribute(): string
    {
        return '**** **** **** ' . substr($this->card_number, -4);
    }

    public function getMonthlySpendsAttribute(): float
    {
        return $this->transactions()
            ->where('type', 'cargo')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
    }

    public function deposit(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    public function charge(float $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }
        $this->decrement('balance', $amount);
        return true;
    }
}
