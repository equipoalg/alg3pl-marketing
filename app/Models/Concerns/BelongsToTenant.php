<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Auto-scope queries to current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = app()->bound('current_tenant_id')
                ? app('current_tenant_id')
                : null;

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        // Auto-assign tenant_id on creation
        static::creating(function ($model) {
            if (!$model->tenant_id && app()->bound('current_tenant_id')) {
                $model->tenant_id = app('current_tenant_id');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
