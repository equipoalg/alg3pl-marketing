<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSnapshot extends Model
{
    protected $fillable = [
        'country_id', 'date', 'users', 'new_users', 'sessions',
        'page_views', 'avg_session_duration', 'bounce_rate',
        'organic_users', 'direct_users', 'referral_users',
        'social_users', 'paid_users', 'conversions',
    ];

    protected $casts = [
        'date' => 'date',
        'bounce_rate' => 'decimal:2',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getOrganicPercentAttribute(): float
    {
        return $this->users > 0
            ? round(($this->organic_users / $this->users) * 100, 1)
            : 0;
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }
}
