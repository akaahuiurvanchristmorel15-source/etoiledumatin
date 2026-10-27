<?php

namespace App\Models;

use Database\Factories\AudioTrackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AudioTrack extends Model
{
    /** @use HasFactory<AudioTrackFactory> */
    use HasFactory;

    protected $fillable = [
        'book_part_id',
        'language',
        'path',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (AudioTrack $track) {
            Storage::disk(config('audio.disk'))->delete($track->path);
        });
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(BookPart::class, 'book_part_id');
    }

    public function languageLabel(): string
    {
        return config('audio.languages.'.$this->language, $this->language);
    }
}
