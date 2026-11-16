<?php

namespace App\Models;

use Database\Factories\ListeningSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Appareil en train d'écouter : ouvert au lancement de la lecture, coupé quand un troisième appareil prend la place.
 */
class ListeningSession extends Model
{
    /** @use HasFactory<ListeningSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_agent',
        'ip_address',
        'started_at',
        'last_seen_at',
        'ended_at',
        'end_reason',
        'stream_credit_seconds',
        'credit_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'ended_at' => 'datetime',
            'credit_updated_at' => 'datetime',
            'stream_credit_seconds' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEnded(): bool
    {
        return $this->ended_at !== null;
    }
}
