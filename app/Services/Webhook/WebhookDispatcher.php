<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;

class WebhookDispatcher
{
    /**
     * Dispatch an event to all matching outbound webhooks.
     */
    public function dispatch(string $event, array $payload, ?int $tenantId = null): void
    {
        $webhooks = Webhook::where('direction', 'outbound')
            ->where('is_active', true)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->get()
            ->filter(fn (Webhook $w) => $w->listensTo($event));

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $event, $payload);
        }
    }

    /**
     * Send payload to a specific webhook endpoint.
     */
    public function send(Webhook $webhook, string $event, array $payload): WebhookLog
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);
            $headers['X-Webhook-Signature'] = $signature;
        }

        $headers['X-Webhook-Event'] = $event;

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($webhook->url, $payload);

            $success = $response->successful();

            if ($success) {
                $webhook->recordSuccess();
            } else {
                $webhook->recordFailure();
            }

            return $webhook->logs()->create([
                'direction' => 'outbound',
                'event' => $event,
                'payload' => $payload,
                'response' => $response->json() ?? ['body' => $response->body()],
                'response_code' => $response->status(),
                'success' => $success,
                'error_message' => $success ? null : "HTTP {$response->status()}",
            ]);
        } catch (\Exception $e) {
            $webhook->recordFailure();

            return $webhook->logs()->create([
                'direction' => 'outbound',
                'event' => $event,
                'payload' => $payload,
                'response' => null,
                'response_code' => null,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process an inbound webhook request.
     */
    public function processInbound(Webhook $webhook, string $event, array $payload, ?string $signature = null): WebhookLog
    {
        // Verify signature if secret is set
        if ($webhook->secret && $signature) {
            $expected = hash_hmac('sha256', json_encode($payload), $webhook->secret);
            if (!hash_equals($expected, $signature)) {
                return $webhook->logs()->create([
                    'direction' => 'inbound',
                    'event' => $event,
                    'payload' => $payload,
                    'success' => false,
                    'error_message' => 'Invalid signature',
                ]);
            }
        }

        $webhook->recordSuccess();

        return $webhook->logs()->create([
            'direction' => 'inbound',
            'event' => $event,
            'payload' => $payload,
            'success' => true,
        ]);
    }

    /**
     * Available events that can trigger webhooks.
     */
    public static function availableEvents(): array
    {
        return [
            'lead.created',
            'lead.updated',
            'lead.status_changed',
            'lead.score_changed',
            'lead.assigned',
            'client.created',
            'client.status_changed',
            'client.health_changed',
            'campaign.started',
            'campaign.completed',
            'funnel.enrollment',
            'funnel.completed',
            'quote.generated',
            'email.bounced',
            'whatsapp.received',
        ];
    }
}
