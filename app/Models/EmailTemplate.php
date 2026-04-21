<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_id', 'name', 'subject', 'body_html',
        'body_text', 'category', 'variables', 'is_active', 'usage_count',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Render template replacing variables with actual values.
     */
    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $body = $this->body_html;

        foreach ($data as $key => $value) {
            $subject = str_replace("{{$key}}", $value, $subject);
            $body = str_replace("{{$key}}", $value, $body);
        }

        // Replace any remaining variables with defaults
        if ($this->variables) {
            foreach ($this->variables as $var) {
                $subject = str_replace("{{$var['key']}}", $var['default'] ?? '', $subject);
                $body = str_replace("{{$var['key']}}", $var['default'] ?? '', $body);
            }
        }

        return ['subject' => $subject, 'body' => $body];
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
