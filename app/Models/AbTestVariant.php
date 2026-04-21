<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbTestVariant extends Model
{
    protected $fillable = [
        'ab_test_id', 'variant_index', 'subject',
        'body_html', 'sent_count', 'open_count', 'click_count',
    ];

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

    public function getVariantLabelAttribute(): string
    {
        return $this->variant_index === 0 ? 'A' : 'B';
    }
}
