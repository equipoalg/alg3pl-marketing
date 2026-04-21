<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchConsoleData extends Model
{
    protected $fillable = [
        'country_id', 'date', 'query', 'page',
        'clicks', 'impressions', 'ctr', 'position',
    ];

    protected $casts = [
        'date' => 'date',
        'ctr' => 'decimal:2',
        'position' => 'decimal:1',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeTopQueries($query, $countryId, $limit = 20)
    {
        return $query->where('country_id', $countryId)
            ->selectRaw('query, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(position) as avg_position, AVG(ctr) as avg_ctr')
            ->groupBy('query')
            ->orderByDesc('total_clicks')
            ->limit($limit);
    }

    public function scopeTopPages($query, $countryId, $limit = 20)
    {
        return $query->where('country_id', $countryId)
            ->whereNotNull('page')
            ->selectRaw('page, SUM(clicks) as total_clicks, SUM(impressions) as total_impressions, AVG(position) as avg_position')
            ->groupBy('page')
            ->orderByDesc('total_clicks')
            ->limit($limit);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
