<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'address', 'city', 'country'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Rôle du back-office (users.role contient son identifiant), absent pour un client.
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SUPER_ADMIN;
    }

    /**
     * Membre de l'équipe : tout compte rattaché à un rôle du back-office.
     */
    public function isStaff(): bool
    {
        return $this->role !== 'customer' && $this->roleModel !== null;
    }

    public function hasPermission(string $permission): bool
    {
        return (bool) $this->roleModel?->grants($permission);
    }

    public function roleLabel(): string
    {
        return $this->roleModel?->name ?? 'Client';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function purchasedDownloads(): HasMany
    {
        return $this->hasMany(PurchasedDownload::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function audioSubscriptions(): HasMany
    {
        return $this->hasMany(AudioSubscription::class);
    }

    public function canListenTo(BookPart $part): bool
    {
        return $this->audioSubscriptions()->active()->where('book_part_id', $part->id)->exists();
    }
}
