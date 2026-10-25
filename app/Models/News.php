<?php

namespace App\Models;

use App\Enums\NewsType;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'title',
        'slug',
        'type',
        'excerpt',
        'content',
        'cover_image',
        'event_date',
        'location',
        'published_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'type' => NewsType::class,
            'event_date' => 'datetime',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (News $news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Actualités visibles par les lecteurs : publiées et dont la date de publication est passée.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isVisible(): bool
    {
        return $this->is_published && $this->published_at?->isPast();
    }

    /**
     * Image de partage et de vignette : la couverture, sinon celle du livre lié, sinon le logo.
     */
    public function coverUrl(): string
    {
        return match (true) {
            (bool) $this->cover_image => asset($this->cover_image),
            (bool) $this->book?->cover_image => asset($this->book->cover_image),
            default => asset('images/brand/nsoromma-logo.png'),
        };
    }
}
