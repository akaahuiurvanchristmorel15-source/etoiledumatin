<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'recipient_name',
        'phone',
        'delivery_zone',
        'address_line',
        'city',
        'country',
        'instructions',
        'status',
        'carrier_name',
        'tracking_number',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'À préparer',
            'processing' => 'En cours de préparation',
            'shipped' => 'Colis expédié',
            'delivered' => 'Colis livré',
            'cancelled' => 'Annulé',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'processing' => 'bg-blue-100 text-blue-800 border-blue-300',
            'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getZoneLabelAttribute(): string
    {
        $zones = config('shipping.zones');

        return $zones[$this->delivery_zone]['name'] ?? ucfirst($this->delivery_zone);
    }

    public function getAddressAttribute(): string
    {
        return $this->address_line ?? '';
    }
}
