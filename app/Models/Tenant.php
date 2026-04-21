<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'domain', 'logo_url', 'branding',
        'default_locale', 'default_currency', 'timezone',
        'plan', 'status', 'trial_ends_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'settings' => 'array',
            'trial_ends_at' => 'date',
        ];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function countries(): HasMany { return $this->hasMany(Country::class); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function clients(): HasMany { return $this->hasMany(Client::class); }
    public function campaigns(): HasMany { return $this->hasMany(Campaign::class); }
    public function roles(): HasMany { return $this->hasMany(Role::class); }
    public function auditLogs(): HasMany { return $this->hasMany(AuditLog::class); }
    public function funnels(): HasMany { return $this->hasMany(Funnel::class); }
    public function apiTokens(): HasMany { return $this->hasMany(ApiToken::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' ||
            ($this->status === 'trial' && $this->trial_ends_at?->isFuture());
    }

    public function getPrimaryColorAttribute(): string
    {
        return $this->branding['primary_color'] ?? '#3B82F6';
    }
}
