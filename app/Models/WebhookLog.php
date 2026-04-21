<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'webhook_id', 'direction', 'event', 'payload',
        'response', 'response_code', 'success', 'error_message', 'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
