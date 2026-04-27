<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'name', 'email', 'password', 'tenant_id',
        'country_id', 'role', 'is_super_admin',
        'preferences',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'preferences' => 'array',
        ];
    }

    /**
     * Read a single preference key with a default fallback.
     * Example: $user->pref('variant', 'b') returns the saved layout variant or 'b'.
     */
    public function pref(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Merge new preferences into the JSON column without losing existing keys.
     * Example: $user->setPrefs(['variant' => 'b', 'theme' => 'light']);
     */
    public function setPrefs(array $values): self
    {
        $this->preferences = array_merge($this->preferences ?? [], $values);
        return $this;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class); }
    public function leadActivities(): HasMany { return $this->hasMany(LeadActivity::class); }
    public function apiTokens(): HasMany { return $this->hasMany(ApiToken::class); }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) return true;

        return $this->roles->contains(fn (Role $role) => $role->hasPermission($permission));
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $p) {
            if ($this->hasPermission($p)) return true;
        }
        return false;
    }

    public function isAdmin(): bool
    {
        return $this->is_super_admin || $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }
}
