<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'name', 'token', 'scopes',
        'last_used_at', 'expires_at', 'is_active',
    ];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function hasScope(string $scope): bool
    {
        return in_array('*', $this->scopes ?? []) || in_array($scope, $this->scopes ?? []);
    }

    public function isValid(): bool
    {
        return $this->is_active && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
