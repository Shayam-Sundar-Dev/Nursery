<?php

namespace App\Models;

use App\Enums\AdminRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'phone', 'street_address', 'city', 'state', 'postal_code', 'cart', 'wishlist', 'is_admin', 'role', 'is_active', 'provider', 'provider_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
            'is_admin' => 'boolean',
            'role' => AdminRole::class,
            'is_active' => 'boolean',
            'cart' => 'array',
            'wishlist' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin && (bool) $this->is_active;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasRole(string|array|AdminRole ...$roles): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        // Super admins have access to all areas
        if ($this->role === AdminRole::SuperAdmin) {
            return true;
        }

        $flattened = [];
        foreach ($roles as $r) {
            if (is_array($r)) {
                $flattened = array_merge($flattened, $r);
            } else {
                $flattened[] = $r;
            }
        }

        foreach ($flattened as $role) {
            $roleValue = $role instanceof AdminRole ? $role->value : $role;
            if ($this->role?->value === $roleValue) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return in_array($permission, $this->role?->permissions() ?? [], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === AdminRole::SuperAdmin;
    }

    public function isBotanist(): bool
    {
        return $this->role === AdminRole::Botanist;
    }

    public function isFulfillment(): bool
    {
        return $this->role === AdminRole::Fulfillment;
    }

    public function isSupport(): bool
    {
        return $this->role === AdminRole::Support;
    }

    public function roleLabel(): string
    {
        return $this->role?->label() ?? 'Gardener';
    }

    public function roleBadge(): string
    {
        return $this->role?->badgeClasses() ?? 'bg-stone-100 text-stone-700';
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function userPlants(): HasMany
    {
        return $this->hasMany(UserPlant::class);
    }
}
