<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentMetric extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_id', 'client_id', 'period_date', 'period_type',
        'total_shipments', 'on_time_shipments', 'in_full_shipments', 'otif_shipments',
        'otif_percentage', 'total_revenue', 'total_cost', 'cost_to_serve', 'gross_margin',
        'total_weight_kg', 'total_cbm', 'total_teus', 'mode_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'mode_breakdown' => 'array',
            'total_revenue' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
