<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Rôle du back-office et ses permissions (liste dans config/permissions.php).
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    public const SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (empty($role->slug)) {
                $slug = Str::slug($role->name, '_') ?: 'role';
                $role->slug = static::where('slug', $slug)->exists() ? $slug.'_'.Str::lower(Str::random(4)) : $slug;
            }
        });
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }

    /**
     * Rôles que l'on peut attribuer et modifier (tous sauf le super administrateur).
     */
    public function scopeAssignable(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    public function grants(string $permission): bool
    {
        return $this->slug === self::SUPER_ADMIN || in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Libellés des permissions accordées, dans l'ordre de la configuration.
     *
     * @return array<int, string>
     */
    public function permissionLabels(): array
    {
        return collect(config('permissions'))
            ->filter(fn (array $permission, string $key) => $this->grants($key))
            ->pluck('label')
            ->all();
    }
}
