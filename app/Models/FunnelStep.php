<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelStep extends Model
{
    protected $fillable = [
        'funnel_id', 'order', 'name', 'action_type', 'action_config',
        'delay_hours', 'condition', 'entries_count', 'completions_count',
    ];

    protected function casts(): array
    {
        return [
            'action_config' => 'array',
            'condition' => 'array',
        ];
    }

    public function funnel(): BelongsTo { return $this->belongsTo(Funnel::class); }

    public function getDropoffRateAttribute(): float
    {
        return $this->entries_count > 0
            ? round((1 - $this->completions_count / $this->entries_count) * 100, 1)
            : 0;
    }
}
