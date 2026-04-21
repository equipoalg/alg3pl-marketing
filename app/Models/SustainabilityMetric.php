<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SustainabilityMetric extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_id', 'period_date', 'period_type',
        'co2_emissions_kg', 'co2_per_shipment', 'co2_per_ton_km',
        'ocean_emissions_kg', 'air_emissions_kg', 'ground_emissions_kg',
        'total_shipments', 'consolidated_shipments', 'consolidation_rate',
        'avg_container_utilization', 'packaging_waste_kg', 'recycled_packaging_pct',
        'extra_metrics',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'extra_metrics' => 'array',
        ];
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }

    public function getCo2SavedByConsolidationAttribute(): float
    {
        $avgPerShipment = $this->co2_per_shipment;
        $saved = $this->consolidated_shipments * $avgPerShipment * 0.35;
        return round($saved, 2);
    }
}
