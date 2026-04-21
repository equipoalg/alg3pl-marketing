<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funnel extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_id', 'name', 'description', 'status',
        'trigger_type', 'trigger_config', 'audience_rules',
        'total_entries', 'total_conversions',
    ];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'audience_rules' => 'array',
        ];
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function steps(): HasMany { return $this->hasMany(FunnelStep::class)->orderBy('order'); }
    public function enrollments(): HasMany { return $this->hasMany(FunnelEnrollment::class); }

    public function getConversionRateAttribute(): float
    {
        return $this->total_entries > 0
            ? round(($this->total_conversions / $this->total_entries) * 100, 1)
            : 0;
    }
}
