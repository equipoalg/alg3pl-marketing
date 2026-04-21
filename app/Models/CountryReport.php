<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryReport extends Model
{
    protected $fillable = [
        'country_id', 'period', 'type', 'kpis', 'findings',
        'opportunities', 'ga4_data', 'gsc_data', 'summary', 'source_file',
    ];

    protected $casts = [
        'kpis' => 'array',
        'findings' => 'array',
        'opportunities' => 'array',
        'ga4_data' => 'array',
        'gsc_data' => 'array',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
