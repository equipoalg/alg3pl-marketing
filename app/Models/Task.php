<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'country_id', 'title', 'description', 'category', 'priority',
        'effort', 'impact', 'status', 'assignee', 'due_date', 'notes', 'source_file',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'P0' => 'danger',
            'P1' => 'warning',
            'P2' => 'info',
            'P3' => 'gray',
            default => 'gray',
        };
    }
}
