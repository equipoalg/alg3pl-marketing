<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailBounce extends Model
{
    protected $fillable = [
        'lead_id', 'email', 'bounce_type', 'reason',
        'diagnostic_code', 'bounce_count', 'first_bounced_at', 'last_bounced_at',
    ];

    protected $casts = [
        'first_bounced_at' => 'datetime',
        'last_bounced_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isHardBounce(): bool
    {
        return $this->bounce_type === 'hard';
    }

    public function shouldSuppress(): bool
    {
        return $this->bounce_type === 'hard' || $this->bounce_count >= 3;
    }
}
