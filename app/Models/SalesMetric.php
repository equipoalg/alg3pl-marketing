<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesMetric extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_id', 'period_date', 'period_type',
        'new_leads', 'qualified_leads', 'proposals_sent', 'deals_won', 'deals_lost',
        'pipeline_value', 'closed_value', 'conversion_rate',
        'active_clients', 'churned_clients', 'churn_rate', 'nps_score',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'pipeline_value' => 'decimal:2',
            'closed_value' => 'decimal:2',
        ];
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
}
