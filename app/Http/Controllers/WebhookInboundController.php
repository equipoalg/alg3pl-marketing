<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Webhook;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class WebhookInboundController extends Controller
{
    public function handle(Request $request, int $webhookId)
    {
        $rateLimiterKey = 'webhook:' . $request->ip();

        // Allow 30 attempts per minute per IP
        if (RateLimiter::tooManyAttempts($rateLimiterKey, 30)) {
            $retryAfter = RateLimiter::availableIn($rateLimiterKey);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again in ' . $retryAfter . ' seconds.',
            ], 429, ['Retry-After' => $retryAfter]);
        }

        RateLimiter::hit($rateLimiterKey, 60); // decay: 60 seconds

        $webhook = Webhook::where('id', $webhookId)
            ->where('direction', 'inbound')
            ->where('is_active', true)
            ->firstOrFail();

        $payload = $request->all();

        // Fluent Forms (or any "form_to_lead" source) — parse payload into a Lead
        // and stamp it with the webhook's country_id. Skips the generic dispatcher.
        if ($webhook->source === 'fluent_forms' || $webhook->source === 'form') {
            $lead = $this->parseAsLead($webhook, $payload);
            $webhook->recordSuccess();

            return response()->json([
                'success' => true,
                'message' => 'Lead created',
                'lead_id' => $lead->id,
            ], 200);
        }

        // Generic webhook flow (existing behavior)
        $dispatcher = app(WebhookDispatcher::class);
        $event     = $request->header('X-Webhook-Event', 'unknown');
        $signature = $request->header('X-Webhook-Signature');

        $log = $dispatcher->processInbound($webhook, $event, $payload, $signature);

        return response()->json([
            'success' => $log->success,
            'message' => $log->success ? 'Webhook received' : $log->error_message,
        ], $log->success ? 200 : 401);
    }

    /**
     * Map a Fluent Forms (or generic form) payload to a Lead.
     *
     * Fluent Forms POSTs the form data as a flat key/value map where keys are the
     * field "names" set in the form builder. We honor the webhook's `field_mapping`
     * JSON when present (e.g. {"name_field": "names", "email_field": "email"}),
     * and fall back to common default field names otherwise.
     */
    private function parseAsLead(Webhook $webhook, array $payload): Lead
    {
        // Fluent Forms typically wraps fields under "data" in some integrations.
        $data = $payload['data'] ?? $payload['fields'] ?? $payload;

        $mapping = array_merge([
            'name_field'    => null,
            'email_field'   => null,
            'phone_field'   => null,
            'company_field' => null,
            'message_field' => null,
        ], (array) ($webhook->field_mapping ?? []));

        $name    = $this->extract($data, $mapping['name_field'],    ['names', 'name', 'nombre', 'full_name', 'nombre_completo']);
        $email   = $this->extract($data, $mapping['email_field'],   ['email', 'correo', 'mail']);
        $phone   = $this->extract($data, $mapping['phone_field'],   ['phone', 'telefono', 'teléfono', 'whatsapp', 'movil', 'cel']);
        $company = $this->extract($data, $mapping['company_field'], ['company', 'empresa', 'organizacion', 'organización']);
        $message = $this->extract($data, $mapping['message_field'], ['message', 'mensaje', 'comments', 'comentarios', 'notes', 'notas']);

        // Nothing to do if email is missing — skip but record so the user sees it.
        if (! $email) {
            Log::channel('single')->warning('Inbound form webhook missing email', [
                'webhook_id' => $webhook->id, 'payload_keys' => array_keys($data),
            ]);
            $webhook->recordFailure();
            abort(422, 'email field not found in payload');
        }

        // De-dupe by email + country (don't create dups when a user submits twice).
        $existing = Lead::where('email', $email)
            ->when($webhook->country_id, fn ($q) => $q->where('country_id', $webhook->country_id))
            ->first();
        if ($existing) {
            return $existing;
        }

        return Lead::create([
            'country_id'    => $webhook->country_id,
            'name'          => $name ?: 'Sin nombre',
            'email'         => $email,
            'phone'         => $phone,
            'company'       => $company,
            'notes'         => $message,
            'source'        => $webhook->source ?? 'fluent_forms',
            'source_detail' => $webhook->name,
            'status'        => 'new',
            'utm_source'    => $payload['utm_source'] ?? null,
            'utm_medium'    => $payload['utm_medium'] ?? null,
            'utm_campaign'  => $payload['utm_campaign'] ?? null,
            'landing_page'  => $payload['landing_page'] ?? $payload['referer'] ?? null,
        ]);
    }

    /** Try the explicit mapping first, then a list of common fallback keys. */
    private function extract(array $data, ?string $explicit, array $fallbacks): ?string
    {
        if ($explicit && isset($data[$explicit]) && $data[$explicit] !== '') {
            return is_string($data[$explicit]) ? trim($data[$explicit]) : (string) $data[$explicit];
        }
        foreach ($fallbacks as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return is_string($data[$key]) ? trim($data[$key]) : (string) $data[$key];
            }
        }
        return null;
    }
}
