<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Seuil en dessous duquel le stock papier est considéré comme faible.
     */
    public const LOW_STOCK_THRESHOLD = 5;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'publisher',
        'language',
        'pages_count',
        'published_year',
        'isbn',
        'description',
        'excerpt',
        'cover_image',
        'pdf_path',
        'weight_grams',
        'price_pdf',
        'price_physical',
        'price_audio',
        'stock_physical',
        'is_pdf_available',
        'is_physical_available',
        'is_audio_available',
        'audio_path',
        'audio_sample_path',
        'audio_duration',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_pdf' => 'integer',
            'price_physical' => 'integer',
            'price_audio' => 'integer',
            'stock_physical' => 'integer',
            'weight_grams' => 'integer',
            'pages_count' => 'integer',
            'published_year' => 'integer',
            'is_pdf_available' => 'boolean',
            'is_physical_available' => 'boolean',
            'is_audio_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function purchasedDownloads(): HasMany
    {
        return $this->hasMany(PurchasedDownload::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getFormattedPricePdfAttribute(): string
    {
        return number_format($this->price_pdf, 0, ',', ' ').' FCFA';
    }

    public function getFormattedPricePhysicalAttribute(): string
    {
        return number_format($this->price_physical, 0, ',', ' ').' FCFA';
    }

    public function getFormattedPriceAudioAttribute(): string
    {
        return number_format($this->price_audio, 0, ',', ' ').' FCFA';
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->approvedReviews()->avg('rating') ?? 5), 1);
    }

    public function hasStock(): bool
    {
        return $this->is_physical_available && $this->stock_physical > 0;
    }

    /**
     * Niveau de stock papier : « out » (épuisé), « low » (faible) ou « ok ».
     */
    public function stockLevel(): string
    {
        return match (true) {
            $this->stock_physical <= 0 => 'out',
            $this->stock_physical <= self::LOW_STOCK_THRESHOLD => 'low',
            default => 'ok',
        };
    }

    /**
     * Libellé affiché aux lecteurs, avec la quantité restante exacte.
     */
    public function stockLabel(): string
    {
        return match ($this->stockLevel()) {
            'out' => 'Épuisé',
            'low' => 'Plus que '.$this->stock_physical.' '.($this->stock_physical > 1 ? 'exemplaires' : 'exemplaire'),
            default => $this->stock_physical.' exemplaires en stock',
        };
    }
}
