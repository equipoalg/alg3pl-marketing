<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'ga4_property_id', 'gsc_property_url',
        'website_url', 'timezone', 'currency', 'phone_prefix',
        'google_ads_account', 'is_active', 'is_regional',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_regional' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function analyticsSnapshots(): HasMany
    {
        return $this->hasMany(AnalyticsSnapshot::class);
    }

    public function searchConsoleData(): HasMany
    {
        return $this->hasMany(SearchConsoleData::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function config(): HasOne
    {
        return $this->hasOne(CountryConfig::class);
    }

    /**
     * Return this country's config, creating a default if it doesn't exist.
     */
    public function getConfig(): CountryConfig
    {
        return CountryConfig::forCountry($this->id);
    }

    /**
     * Multi-tenant relationship — dormant until TenantScope middleware is activated.
     * See database/migrations/2026_04_15_000003_add_tenant_to_countries_table.php
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasGoogleAds(): bool
    {
        return !empty($this->google_ads_account);
    }

    public function getFlagEmojiAttribute(): string
    {
        $flags = [
            'sv' => "\u{1F1F8}\u{1F1FB}", 'gt' => "\u{1F1EC}\u{1F1F9}",
            'hn' => "\u{1F1ED}\u{1F1F3}", 'ni' => "\u{1F1F3}\u{1F1EE}",
            'cr' => "\u{1F1E8}\u{1F1F7}", 'pa' => "\u{1F1F5}\u{1F1E6}",
            'us' => "\u{1F1FA}\u{1F1F8}",
        ];
        return $flags[$this->code] ?? '';
    }
}
