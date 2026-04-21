<?php

namespace App\Services\Email;

use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DoubleOptInService
{
    /**
     * Send verification email to a lead.
     */
    public function sendVerification(Lead $lead): void
    {
        if (!$lead->email || $lead->isVerified()) return;

        $token = Str::random(64);
        $lead->update(['verification_token' => $token]);

        $verifyUrl = url("/verify-email/{$token}");

        Mail::raw(
            "Please confirm your email by clicking this link: {$verifyUrl}",
            function ($message) use ($lead) {
                $message->to($lead->email, $lead->name)
                        ->subject('Confirm your email — ALG3PL');
            }
        );
    }

    /**
     * Verify a lead's email with token.
     */
    public function verify(string $token): ?Lead
    {
        $lead = Lead::where('verification_token', $token)
            ->whereNull('email_verified_at')
            ->first();

        if (!$lead) return null;

        $lead->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        return $lead;
    }

    /**
     * Unsubscribe a lead.
     */
    public function unsubscribe(string $email): bool
    {
        return Lead::where('email', $email)
            ->whereNull('unsubscribed_at')
            ->update(['unsubscribed_at' => now()]) > 0;
    }

    /**
     * Resubscribe a lead.
     */
    public function resubscribe(string $email): bool
    {
        return Lead::where('email', $email)
            ->whereNotNull('unsubscribed_at')
            ->update(['unsubscribed_at' => null]) > 0;
    }
}
