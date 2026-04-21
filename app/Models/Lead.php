<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'country_id', 'name', 'email', 'phone', 'company',
        'service_interest', 'route_interest', 'source', 'source_detail',
        'landing_page', 'utm_source', 'utm_medium', 'utm_campaign',
        'score', 'status', 'assigned_to', 'notes', 'estimated_value',
        'email_verified_at', 'verification_token', 'unsubscribed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'estimated_value' => 'decimal:2',
        'email_verified_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('created_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('source')->withTimestamps();
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class, 'segment_lead')->withTimestamps();
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    public function canReceiveEmail(): bool
    {
        return $this->email && !$this->isUnsubscribed();
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeHot($query)
    {
        return $query->where('score', '>=', 70);
    }

    public function scopeWarm($query)
    {
        return $query->whereBetween('score', [40, 69]);
    }

    public function scopeCold($query)
    {
        return $query->where('score', '<', 40);
    }

    public function isHot(): bool
    {
        return $this->score >= 70;
    }

    public function getTemperatureAttribute(): string
    {
        if ($this->score >= 70) return 'hot';
        if ($this->score >= 40) return 'warm';
        return 'cold';
    }
}
