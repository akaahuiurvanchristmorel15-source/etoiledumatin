<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    public function isLogistics(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'logistics']);
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isLogistics();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super administrateur',
            'admin' => 'Administrateur',
            'manager' => 'Gestionnaire catalogue',
            'logistics' => 'Logistique',
            default => 'Client',
        };
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
