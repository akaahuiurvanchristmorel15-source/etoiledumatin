<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'book_id',
        'format',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isPdf(): bool
    {
        return $this->format === 'pdf';
    }

    public function isPhysical(): bool
    {
        return $this->format === 'physical';
    }

    public function isAudio(): bool
    {
        return $this->format === 'audio';
    }

    public function getBookTitleAttribute(): string
    {
        return $this->book?->title ?? 'Ouvrage inconnu';
    }

    public function getBookAuthorAttribute(): string
    {
        return $this->book?->author ?? 'Auteur inconnu';
    }

    public function getSubtotalAttribute(): int
    {
        return $this->total_price ?? ($this->unit_price * $this->quantity);
    }
}
