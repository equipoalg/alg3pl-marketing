<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $fillable = [
        'campaign_id', 'template_id', 'ab_test_id', 'variant',
        'subject', 'body', 'from_name', 'from_email',
        'sent_count', 'open_count', 'click_count',
        'bounce_count', 'unsubscribe_count', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    public function getOpenRateAttribute(): float
    {
        return $this->sent_count > 0 ? round(($this->open_count / $this->sent_count) * 100, 2) : 0;
    }

    public function getClickRateAttribute(): float
    {
        return $this->sent_count > 0 ? round(($this->click_count / $this->sent_count) * 100, 2) : 0;
    }
}
