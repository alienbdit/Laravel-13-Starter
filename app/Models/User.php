<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'profile_photo', 'two_factor_type', 'two_factor_secret', 'two_factor_phone', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
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
            'email_verified_at'        => 'datetime',
            'password'                 => 'hashed',
            'two_factor_confirmed_at'  => 'datetime',
        ];
    }

    public function twoFactorEnabled(): bool
    {
        return $this->two_factor_type !== null && $this->two_factor_confirmed_at !== null;
    }

    public function photoUrl(): ?string
    {
        return $this->profile_photo ? route('profile.avatar', $this->id) : null;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string|array $roles): bool
    {
        return $this->roles->whereIn('name', (array) $roles)->isNotEmpty();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->some(fn (Role $role) => $role->hasPermission($permission));
    }
}
