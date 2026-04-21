<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'client_id', 'user_id', 'lead_id', 'type',
        'subject', 'body', 'outcome', 'scheduled_at', 'duration_minutes',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'scheduled_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
}
