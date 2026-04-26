<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\WhatsAppMessage;
use App\Models\SocialPost;

class Campaign extends Model
{
    protected $fillable = [
        'country_id', 'created_by', 'name', 'type', 'status',
        'audience_filter', 'start_date', 'end_date', 'budget', 'description',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(\App\Models\EmailCampaign::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    /**
     * Resolve this campaign's email audience based on country + audience_filter.
     * Returns a Lead query so the caller can ->count() or ->cursor() over it.
     */
    public function resolveEmailAudience(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Lead::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('unsubscribed_at')
            ->where('status', '!=', 'lost');

        // Country scope (always applied if campaign has one)
        if ($this->country_id) {
            $query->where('country_id', $this->country_id);
        }

        // audience_filter is a KeyValue cast to array — apply each k=>v as a where
        foreach (($this->audience_filter ?? []) as $field => $value) {
            if ($value === null || $value === '') continue;
            // Allow comma-separated values for "in" semantics
            if (is_string($value) && str_contains($value, ',')) {
                $query->whereIn($field, array_map('trim', explode(',', $value)));
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Dispatch this campaign's email to its audience.
     * Returns ['email_campaign_id' => int, 'queued' => int].
     *
     * Pass an EmailCampaign to use a specific variant; otherwise the latest
     * linked EmailCampaign is used (or null if none — caller should handle).
     */
    public function dispatchEmail(?\App\Models\EmailCampaign $emailCampaign = null): array
    {
        $emailCampaign ??= $this->emailCampaigns()->latest()->first();

        if (! $emailCampaign) {
            throw new \RuntimeException(
                'Esta campaña no tiene un EmailCampaign asociado todavía. ' .
                'Crea uno (con subject + body o template) antes de enviar.'
            );
        }

        $queued = 0;
        // cursor() avoids loading the entire result set into memory
        foreach ($this->resolveEmailAudience()->cursor() as $lead) {
            \App\Jobs\SendCampaignEmailJob::dispatch($emailCampaign, $lead);
            $queued++;
        }

        // Mark first send time on the EmailCampaign if this is its first run
        if (! $emailCampaign->sent_at) {
            $emailCampaign->update(['sent_at' => now()]);
        }

        return ['email_campaign_id' => $emailCampaign->id, 'queued' => $queued];
    }
}
