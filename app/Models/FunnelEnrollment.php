<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelEnrollment extends Model
{
    protected $fillable = [
        'funnel_id', 'lead_id', 'client_id', 'current_step',
        'status', 'enrolled_at', 'completed_at', 'step_history',
    ];

    protected function casts(): array
    {
        return [
            'step_history' => 'array',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function funnel(): BelongsTo { return $this->belongsTo(Funnel::class); }
    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
}
