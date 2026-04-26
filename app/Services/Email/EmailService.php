<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Renders an EmailCampaign for a single Lead and sends it via the configured
 * mailer. Used by SendCampaignEmailJob for per-recipient delivery.
 */
class EmailService
{
    public function __construct(
        protected ?BounceHandlerService $bounceHandler = null,
    ) {}

    /**
     * Send one campaign email to one lead. Returns true on send, false on
     * skip/failure. Updates EmailCampaign.sent_count + Lead activity on success.
     */
    public function sendCampaignToLead(EmailCampaign $emailCampaign, Lead $lead): bool
    {
        // Recipient must have an email + be allowed to receive
        if (! $lead->email || ! $this->canReceive($lead)) {
            return false;
        }

        // Suppression list (bounces, unsubscribes)
        if ($this->bounceHandler && $this->bounceHandler->isSuppressed($lead->email)) {
            return false;
        }

        // Render subject + body. Prefer the linked template when present so
        // its variables ({nombre}, {empresa}, ...) get expanded; fall back to
        // EmailCampaign's own subject/body otherwise.
        [$subject, $body] = $this->render($emailCampaign, $lead);

        try {
            Mail::html($body, function ($message) use ($lead, $subject, $emailCampaign) {
                $message->to($lead->email, $lead->name ?? null)
                        ->subject($subject);

                if ($emailCampaign->from_email) {
                    $message->from($emailCampaign->from_email, $emailCampaign->from_name);
                }
            });

            $emailCampaign->increment('sent_count');
            $lead->activities()->create([
                'user_id'     => null,
                'type'        => 'email_sent',
                'description' => "Email enviado: {$subject}",
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('EmailService send failed', [
                'lead_id'      => $lead->id,
                'campaign_id'  => $emailCampaign->id,
                'email'        => $lead->email,
                'error'        => $e->getMessage(),
            ]);
            $emailCampaign->increment('bounce_count');
            return false;
        }
    }

    /**
     * Resolve final subject + body for a lead, preferring template render.
     */
    private function render(EmailCampaign $ec, Lead $lead): array
    {
        $vars = [
            'nombre'  => $lead->name ?? '',
            'empresa' => $lead->company ?? '',
            'email'   => $lead->email ?? '',
            'pais'    => $lead->country?->name ?? '',
        ];

        if ($ec->template) {
            $rendered = $ec->template->render($vars);
            return [$rendered['subject'] ?? $ec->subject, $rendered['body'] ?? $ec->body];
        }

        $subject = $ec->subject;
        $body    = $ec->body;
        foreach ($vars as $k => $v) {
            $subject = str_replace("{{$k}}", $v, $subject);
            $body    = str_replace("{{$k}}", $v, $body);
        }
        return [$subject, $body];
    }

    private function canReceive(Lead $lead): bool
    {
        // Lead model exposes canReceiveEmail() in this codebase; fall back to
        // a basic unsubscribe check if not present.
        if (method_exists($lead, 'canReceiveEmail')) {
            return $lead->canReceiveEmail();
        }
        return ! $lead->unsubscribed_at;
    }
}
