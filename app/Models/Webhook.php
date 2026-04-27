<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_id', 'name', 'direction', 'source', 'url', 'secret',
        'events', 'field_mapping', 'is_active', 'success_count', 'failure_count', 'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'field_mapping' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class)->orderByDesc('created_at');
    }

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    public function recordSuccess(): void
    {
        $this->increment('success_count');
        $this->update(['last_triggered_at' => now()]);
    }

    public function recordFailure(): void
    {
        $this->increment('failure_count');
        $this->update(['last_triggered_at' => now()]);
    }
}
