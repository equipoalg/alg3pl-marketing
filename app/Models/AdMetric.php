<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdMetric extends Model
{
    protected $fillable = [
        'country_id',
        'platform',
        'campaign_name',
        'period_start',
        'period_end',
        'impressions',
        'clicks',
        'spend',
        'leads_generated',
        'cost_per_lead',
        'roas',
        'notes',
        'synced_at',
    ];

    protected $casts = [
        'spend'         => 'decimal:2',
        'cost_per_lead' => 'decimal:2',
        'roas'          => 'decimal:4',
        'period_start'  => 'date',
        'period_end'    => 'date',
        'synced_at'     => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByCountry(Builder $query, int $id): Builder
    {
        return $query->where('country_id', $id);
    }

    public function scopeByPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('period_start', '>=', now()->subDays($days)->toDateString());
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Click-through rate as a percentage.
     */
    public function getCtrAttribute(): float
    {
        if (! $this->impressions || $this->impressions == 0) {
            return 0.0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }
}
