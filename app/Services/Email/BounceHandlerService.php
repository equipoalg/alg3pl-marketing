<?php

namespace App\Services\Email;

use App\Models\EmailBounce;
use App\Models\Lead;

class BounceHandlerService
{
    /**
     * Record a bounce event.
     */
    public function recordBounce(string $email, string $type = 'soft', ?string $reason = null, ?string $diagnosticCode = null): EmailBounce
    {
        $bounce = EmailBounce::where('email', $email)->first();

        if ($bounce) {
            $bounce->update([
                'bounce_type' => $type === 'hard' ? 'hard' : $bounce->bounce_type,
                'bounce_count' => $bounce->bounce_count + 1,
                'reason' => $reason ?? $bounce->reason,
                'diagnostic_code' => $diagnosticCode ?? $bounce->diagnostic_code,
                'last_bounced_at' => now(),
            ]);
        } else {
            $lead = Lead::where('email', $email)->first();
            $bounce = EmailBounce::create([
                'lead_id' => $lead?->id,
                'email' => $email,
                'bounce_type' => $type,
                'reason' => $reason,
                'diagnostic_code' => $diagnosticCode,
                'bounce_count' => 1,
                'first_bounced_at' => now(),
                'last_bounced_at' => now(),
            ]);
        }

        // Apply score penalty
        if ($bounce->shouldSuppress()) {
            $this->suppressEmail($email);
        } elseif ($type === 'soft') {
            $this->applyScorePenalty($email, 5);
        }

        return $bounce;
    }

    /**
     * Check if an email is suppressed (should not receive emails).
     */
    public function isSuppressed(string $email): bool
    {
        $bounce = EmailBounce::where('email', $email)->first();
        return $bounce && $bounce->shouldSuppress();
    }

    /**
     * Get all suppressed emails.
     */
    public function getSuppressedEmails(): array
    {
        return EmailBounce::where(function ($q) {
            $q->where('bounce_type', 'hard')
              ->orWhere('bounce_count', '>=', 3);
        })->pluck('email')->toArray();
    }

    /**
     * Suppress an email — unsubscribe the lead.
     */
    private function suppressEmail(string $email): void
    {
        Lead::where('email', $email)
            ->whereNull('unsubscribed_at')
            ->update(['unsubscribed_at' => now()]);
    }

    /**
     * Apply score penalty to a lead.
     */
    private function applyScorePenalty(string $email, int $penalty): void
    {
        $lead = Lead::where('email', $email)->first();
        if ($lead) {
            $lead->score = max(0, $lead->score - $penalty);
            $lead->save();
        }
    }

    /**
     * Record a spam complaint.
     */
    public function recordComplaint(string $email): EmailBounce
    {
        return $this->recordBounce($email, 'complaint', 'Spam complaint received');
    }

    /**
     * Clean up: remove bounces older than N days for soft bounces.
     */
    public function cleanup(int $days = 90): int
    {
        return EmailBounce::where('bounce_type', 'soft')
            ->where('last_bounced_at', '<', now()->subDays($days))
            ->where('bounce_count', '<', 3)
            ->delete();
    }
}
