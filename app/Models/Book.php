<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

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
}
