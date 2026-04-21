<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryConfig extends Model
{
    protected $fillable = [
        'country_id',
        'monthly_lead_goal',
        'primary_manager',
        'webhook_assignees',
        'active_services',
        'monthly_fee',
        'notes',
    ];

    protected $casts = [
        'monthly_lead_goal' => 'integer',
        'webhook_assignees' => 'array',
        'active_services' => 'array',
        'monthly_fee' => 'decimal:2',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Return the config for a given country, creating a default one if it doesn't exist.
     */
    public static function forCountry(int $countryId): static
    {
        return static::firstOrCreate(
            ['country_id' => $countryId],
            [
                'monthly_lead_goal' => 50,
                'primary_manager' => null,
                'webhook_assignees' => [],
                'active_services' => [],
                'monthly_fee' => 150.00,
                'notes' => null,
            ]
        );
    }
}
