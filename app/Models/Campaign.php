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
}
