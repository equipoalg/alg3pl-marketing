<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'country_id', 'assigned_to', 'company_name', 'trade_name',
        'tax_id', 'industry', 'tier', 'status', 'primary_contact_name',
        'primary_contact_email', 'primary_contact_phone', 'address', 'city',
        'annual_revenue', 'monthly_volume', 'services_contracted', 'lanes',
        'contract_start', 'contract_end', 'health_score', 'notes', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'services_contracted' => 'array',
            'lanes' => 'array',
            'metadata' => 'array',
            'contract_start' => 'date',
            'contract_end' => 'date',
            'annual_revenue' => 'decimal:2',
            'monthly_volume' => 'decimal:2',
        ];
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function interactions(): HasMany { return $this->hasMany(Interaction::class); }
    public function maintenances(): HasMany { return $this->hasMany(ScheduledMaintenance::class); }

    public function scopeEnterprise($q) { return $q->where('tier', 'enterprise'); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeAtRisk($q) { return $q->where('health_score', '<', 40); }

    public function isContractExpiringSoon(): bool
    {
        return $this->contract_end?->between(now(), now()->addDays(30));
    }
}
