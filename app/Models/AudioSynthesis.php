<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Suivi de la génération d'une piste par synthèse vocale (une par partie et par langue).
 */
class AudioSynthesis extends Model
{
    protected $fillable = [
        'book_part_id',
        'language',
        'status',
        'error',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(BookPart::class, 'book_part_id');
    }

    public function isRunning(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'En attente de génération',
            'processing' => 'Génération en cours…',
            'failed' => 'Échec de la génération',
            default => 'Voix générée',
        };
    }
}
