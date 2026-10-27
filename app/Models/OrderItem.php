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
        'book_part_id',
        'format',
        'access_days',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'access_days' => 'integer',
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
        return $this->belongsTo(Book::class)->withTrashed();
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(BookPart::class, 'book_part_id');
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

    /**
     * Abonnement d'écoute à une partie du livre (par opposition à l'ancien achat MP3 définitif).
     */
    public function isAudioSubscription(): bool
    {
        return $this->isAudio() && $this->access_days !== null;
    }

    /**
     * Libellé du format affiché dans les récapitulatifs de commande.
     */
    public function formatLabel(): string
    {
        if ($this->isAudioSubscription()) {
            return collect([
                'Écoute audio',
                $this->part ? 'Partie '.$this->part->position : null,
                config('audio.plans.'.$this->access_days, $this->access_days.' jours'),
            ])->filter()->implode(' · ');
        }

        return match ($this->format) {
            'pdf' => 'e-Book PDF',
            'audio' => 'Livre audio',
            default => 'Livre papier',
        };
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
