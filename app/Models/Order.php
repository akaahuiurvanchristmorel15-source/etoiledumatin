<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal_pdf',
        'subtotal_physical',
        'subtotal_audio',
        'shipping_cost',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_pdf' => 'integer',
            'subtotal_physical' => 'integer',
            'subtotal_audio' => 'integer',
            'shipping_cost' => 'integer',
            'total_amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'EDM-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(PurchasedDownload::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function hasPhysicalItems(): bool
    {
        return $this->items()->where('format', 'physical')->exists();
    }

    public function hasPdfItems(): bool
    {
        return $this->items()->where('format', 'pdf')->exists();
    }

    public function hasAudioItems(): bool
    {
        return $this->items()->where('format', 'audio')->exists();
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', ' ').' '.$this->currency;
    }
}
