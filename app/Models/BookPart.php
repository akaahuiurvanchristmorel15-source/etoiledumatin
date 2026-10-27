<?php

namespace App\Models;

use Database\Factories\BookPartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookPart extends Model
{
    /** @use HasFactory<BookPartFactory> */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'position',
        'title',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(AudioTrack::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AudioSubscription::class);
    }

    /**
     * Libellé court, ex. « Partie 2 · Le retour ».
     */
    public function label(): string
    {
        return 'Partie '.$this->position.' · '.$this->title;
    }

    /**
     * Langues dans lesquelles une piste est disponible, dans l'ordre de la configuration.
     *
     * @return array<string, string>
     */
    public function availableLanguages(): array
    {
        $codes = $this->tracks->pluck('language')->all();

        return array_filter(
            config('audio.languages'),
            fn (string $code) => in_array($code, $codes, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
