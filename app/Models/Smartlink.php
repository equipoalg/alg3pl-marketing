<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Smartlink extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'slug', 'destination_url', 'name',
        'tags_to_apply', 'score_adjustment', 'click_count', 'is_active',
    ];

    protected $casts = [
        'tags_to_apply' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Smartlink $link) {
            if (empty($link->slug)) {
                $link->slug = Str::random(8);
            }
        });
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(SmartlinkClick::class);
    }

    /**
     * Process a click: apply tags, adjust score, track.
     */
    public function processClick(?Lead $lead, ?string $ip = null, ?string $userAgent = null, ?string $referrer = null): void
    {
        $this->clicks()->create([
            'lead_id' => $lead?->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
        ]);

        $this->increment('click_count');

        if ($lead) {
            // Apply tags
            if ($this->tags_to_apply) {
                foreach ($this->tags_to_apply as $tagId) {
                    $lead->tags()->syncWithoutDetaching([
                        $tagId => ['source' => 'smartlink'],
                    ]);
                }
            }

            // Adjust score
            if ($this->score_adjustment !== 0) {
                $lead->score = min(100, max(0, $lead->score + $this->score_adjustment));
                $lead->save();
            }
        }
    }

    public function getFullUrlAttribute(): string
    {
        return url("/sl/{$this->slug}");
    }
}
