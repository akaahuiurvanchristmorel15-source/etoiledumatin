<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'book_part_id',
        'order_id',
        'access_days',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'access_days' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(BookPart::class, 'book_part_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Abonnements en cours de validité à cet instant.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('starts_at', '<=', now())->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->starts_at->isPast() && $this->expires_at->isFuture();
    }
}
