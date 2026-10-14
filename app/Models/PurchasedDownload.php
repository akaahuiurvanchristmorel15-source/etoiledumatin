<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PurchasedDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'book_id',
        'format',
        'customer_name',
        'customer_email',
        'download_token',
        'download_count',
        'max_downloads',
        'last_downloaded_at',
        'last_ip_address',
    ];

    protected function casts(): array
    {
        return [
            'download_count' => 'integer',
            'max_downloads' => 'integer',
            'last_downloaded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchasedDownload $download) {
            if (empty($download->download_token)) {
                $download->download_token = Str::random(40);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function canDownload(): bool
    {
        return $this->download_count < $this->max_downloads;
    }

    public function isPdf(): bool
    {
        return ($this->format ?? 'pdf') === 'pdf';
    }

    public function isAudio(): bool
    {
        return ($this->format ?? 'pdf') === 'audio';
    }

    public function recordDownload(string $ip): void
    {
        $this->increment('download_count');
        $this->update([
            'last_downloaded_at' => now(),
            'last_ip_address' => $ip,
        ]);
    }
}
