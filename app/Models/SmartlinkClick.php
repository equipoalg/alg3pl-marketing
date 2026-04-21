<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartlinkClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'smartlink_id', 'lead_id', 'ip_address', 'user_agent', 'referrer', 'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function smartlink(): BelongsTo
    {
        return $this->belongsTo(Smartlink::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
