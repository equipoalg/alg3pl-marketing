<?php

namespace App\Services\Notifications;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Posts notifications to a Slack channel via Incoming Webhook.
 *
 * Configure: SLACK_WEBHOOK_URL=https://hooks.slack.com/services/T../B../...
 *
 * The notifier degrades gracefully — without the env var set, every send()
 * returns false and logs a debug line. No exceptions thrown.
 */
class SlackNotifier
{
    public const HOT_LEAD_SCORE = 80;
    private const TIMEOUT = 8;

    public function __construct(
        private readonly ?string $webhookUrl = null,
    ) {}

    /** Returns true on successful POST, false on skip/failure. */
    public function send(string $text, array $blocks = []): bool
    {
        $url = $this->webhookUrl ?? config('services.slack.webhook_url', env('SLACK_WEBHOOK_URL'));

        if (empty($url)) {
            Log::debug('SlackNotifier: SLACK_WEBHOOK_URL not configured, skipping.');
            return false;
        }

        $payload = ['text' => $text];
        if (! empty($blocks)) {
            $payload['blocks'] = $blocks;
        }

        try {
            $response = Http::timeout(self::TIMEOUT)->post($url, $payload);
            if ($response->successful()) {
                return true;
            }
            Log::warning('SlackNotifier: webhook returned non-2xx', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::warning('SlackNotifier: send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * High-signal alert when a lead crosses the hot threshold (score >= 80).
     * Renders a rich Slack message block with country, company, score, and
     * a deep link to the lead in Filament.
     */
    public function notifyHotLead(Lead $lead): bool
    {
        $countryFlag = match (strtolower($lead->country?->code ?? '')) {
            'sv' => '🇸🇻', 'gt' => '🇬🇹', 'hn' => '🇭🇳', 'ni' => '🇳🇮',
            'cr' => '🇨🇷', 'pa' => '🇵🇦', 'mx' => '🇲🇽', default => '🌐',
        };

        $url = url("/admin/leads/{$lead->id}/edit");
        $assignedTo = $lead->assignedUser?->email ?? 'sin asignar';

        $text = "🔥 *Hot lead detectado* — {$lead->name} (score {$lead->score})";
        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "🔥 *Hot lead* — score *{$lead->score}*\n*{$lead->name}* @ {$lead->company} {$countryFlag} {$lead->country?->code}",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    ['type' => 'mrkdwn', 'text' => "*Email:*\n{$lead->email}"],
                    ['type' => 'mrkdwn', 'text' => "*Asignado:*\n{$assignedTo}"],
                    ['type' => 'mrkdwn', 'text' => "*Servicio:*\n" . ($lead->service_interest ?: '—')],
                    ['type' => 'mrkdwn', 'text' => "*Source:*\n" . ($lead->source ?: '—')],
                ],
            ],
            [
                'type' => 'actions',
                'elements' => [[
                    'type' => 'button',
                    'text' => ['type' => 'plain_text', 'text' => 'Abrir lead'],
                    'url'  => $url,
                    'style' => 'primary',
                ]],
            ],
        ];

        return $this->send($text, $blocks);
    }
}
