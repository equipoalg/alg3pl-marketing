<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ScoringRule extends Model
{
    protected $fillable = [
        'factor',
        'label',
        'weight',
        'category',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Cache scoring rules for 1 hour to avoid repeated DB hits during scoring.
     */
    public static function getCached(): Collection
    {
        return Cache::remember('scoring_rules', 3600, fn () => static::where('is_active', true)->get());
    }

    /**
     * Flush the scoring rules cache (call after saving/updating rules).
     */
    public static function flushCache(): void
    {
        Cache::forget('scoring_rules');
    }

    protected static function booted(): void
    {
        // Flush cache whenever a rule changes so scoring stays current
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the weight for a specific factor from the cached collection.
     */
    public static function weightFor(string $factor, int $default = 0): int
    {
        return static::getCached()->firstWhere('factor', $factor)?->weight ?? $default;
    }
}
