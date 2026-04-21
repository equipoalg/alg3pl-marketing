<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbTest extends Model
{
    protected $fillable = [
        'campaign_id', 'test_percentage', 'test_variable',
        'winning_metric', 'status', 'winning_variant', 'test_end_at',
    ];

    protected $casts = [
        'test_end_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(AbTestVariant::class);
    }

    public function determineWinner(): ?int
    {
        $variants = $this->variants;
        if ($variants->count() < 2) return null;

        $metric = $this->winning_metric;

        $rates = $variants->map(function ($v) use ($metric) {
            if ($v->sent_count === 0) return 0;
            return $metric === 'open_rate'
                ? ($v->open_count / $v->sent_count) * 100
                : ($v->click_count / $v->sent_count) * 100;
        });

        $winner = $rates->keys()->sortByDesc(fn ($k) => $rates[$k])->first();

        $this->update([
            'winning_variant' => $winner,
            'status' => 'winner_selected',
        ]);

        return $winner;
    }
}
