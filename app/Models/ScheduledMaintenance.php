<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledMaintenance extends Model
{
    use BelongsToTenant;

    protected $table = 'scheduled_maintenances';

    protected $fillable = [
        'tenant_id', 'client_id', 'assigned_to', 'type', 'title',
        'description', 'due_date', 'status', 'priority', 'completed_at',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'date',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function scopeOverdue($q) { return $q->where('due_date', '<', now())->where('status', '!=', 'completed'); }
    public function scopeUpcoming($q) { return $q->whereBetween('due_date', [now(), now()->addDays(7)]); }
}
