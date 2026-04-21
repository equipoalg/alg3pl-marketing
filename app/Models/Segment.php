<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Segment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'type', 'rules',
        'cached_count', 'last_calculated_at',
    ];

    protected $casts = [
        'rules' => 'array',
        'last_calculated_at' => 'datetime',
    ];

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'segment_lead')->withTimestamps();
    }

    /**
     * Build a query for dynamic segments based on rules.
     */
    public function buildQuery(): Builder
    {
        $query = Lead::query();
        $rules = $this->rules ?? [];
        $logic = $rules['logic'] ?? 'and';
        $conditions = $rules['conditions'] ?? [];

        $method = $logic === 'or' ? 'orWhere' : 'where';

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $op = $condition['op'] ?? '=';
            $value = $condition['value'] ?? null;

            if (!$field) continue;

            match ($op) {
                'in' => $query->$method(fn ($q) => $q->whereIn($field, (array) $value)),
                'not_in' => $query->$method(fn ($q) => $q->whereNotIn($field, (array) $value)),
                'between' => $query->$method(fn ($q) => $q->whereBetween($field, (array) $value)),
                'is_null' => $query->$method(fn ($q) => $q->whereNull($field)),
                'is_not_null' => $query->$method(fn ($q) => $q->whereNotNull($field)),
                'has_tag' => $query->$method(fn ($q) => $q->whereHas('tags', fn ($t) => $t->whereIn('tags.id', (array) $value))),
                'days_ago' => $query->$method(fn ($q) => $q->where($field, '>=', now()->subDays((int) $value))),
                default => $query->$method($field, $op, $value),
            };
        }

        return $query;
    }

    /**
     * Get matching leads — dynamic builds query, static uses pivot.
     */
    public function getLeads(): Builder
    {
        if ($this->type === 'static') {
            return Lead::whereIn('id', $this->leads()->pluck('leads.id'));
        }
        return $this->buildQuery();
    }

    /**
     * Recalculate cached count.
     */
    public function recalculate(): void
    {
        $this->update([
            'cached_count' => $this->getLeads()->count(),
            'last_calculated_at' => now(),
        ]);
    }
}
