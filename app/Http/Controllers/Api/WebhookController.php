<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Country;
use App\Models\Lead;
use App\Services\Lead\LeadScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Fluent Forms webhook endpoint.
     *
     * Configure in Fluent Forms > Settings > Webhooks:
     *   URL: https://your-domain.com/api/v1/webhook/fluent-forms
     *   Method: POST
     *   Secret: (set WEBHOOK_FLUENT_FORMS_SECRET in .env)
     *
     * Field mapping (Fluent Forms field name => what we expect):
     *   name, email, phone, company, service_interest, message/notes
     *
     * Country is auto-detected from the Referer header or "site_url" field.
     */
    public function fluentForms(Request $request, LeadScoringService $scoring): JsonResponse
    {
        // Verify webhook secret
        $secret = config('services.webhook.fluent_forms_secret');
        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['error' => 'Invalid webhook secret'], 403);
        }

        // Detect country from site_url, referer, or explicit country_code
        $country = $this->resolveCountry($request);
        if (!$country) {
            return response()->json(['error' => 'Could not determine country'], 422);
        }

        // Map Fluent Forms fields to Lead fields
        // Fluent Forms uses generic names (input_text, input_text_2, dropdown, description)
        // plus custom names (name, email, phone, company, etc.)
        $name = $this->extractField($request, ['name', 'nombre', 'full_name', 'nombres', 'input_text', 'your_name']);
        // If name is array (split first/last), join it
        if (is_array($name)) {
            $name = trim(($name['first_name'] ?? '') . ' ' . ($name['last_name'] ?? ''));
        }

        // Check for duplicate email before creating
        $email = $this->extractField($request, ['email', 'correo', 'email_address']);
        if ($email && Lead::where('email', strtolower(trim($email)))->exists()) {
            return response()->json(['success' => true, 'message' => 'Lead already exists', 'duplicate' => true], 200);
        }

        $lead = Lead::create([
            'country_id' => $country->id,
            'name' => $name ?: 'Sin nombre',
            'email' => $email ? strtolower(trim($email)) : null,
            'phone' => $this->extractField($request, ['phone', 'telefono', 'tel', 'phone_number', 'celular', 'input_text_2']),
            'company' => $this->extractField($request, ['company', 'empresa', 'company_name', 'compania', 'input_text_3']),
            'service_interest' => $this->extractField($request, ['service_interest', 'servicio', 'service', 'interes', 'dropdown', 'select']),
            'notes' => $this->extractField($request, ['message', 'mensaje', 'notes', 'comentarios', 'notas', 'description', 'textarea']),
            'source' => 'organic',
            'source_detail' => 'fluent_forms_webhook',
            'landing_page' => $this->extractField($request, ['_wp_http_referer', 'landing_page', 'page_url', 'source_url']),
            'utm_source' => $this->extractField($request, ['utm_source']),
            'utm_medium' => $this->extractField($request, ['utm_medium']),
            'utm_campaign' => $this->extractField($request, ['utm_campaign']),
            'status' => 'new',
        ]);

        $lead = $scoring->recalculate($lead);

        try {
            AuditLog::record('webhook:fluent_forms', $lead);
        } catch (\Throwable) {
            // Audit log may fail if no tenant context (unauthenticated webhook)
        }

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'country' => $country->code,
            'score' => $lead->score,
        ], 201);
    }

    private function resolveCountry(Request $request): ?Country
    {
        // 1. Explicit country_code field
        if ($code = $request->input('country_code')) {
            return Country::where('code', strtolower($code))->first();
        }

        // 2. From site_url or referer — extract country slug from URL path
        $url = $request->input('site_url')
            ?? $request->input('_wp_http_referer')
            ?? $request->header('Referer')
            ?? '';

        // Match country path slugs (some differ from country codes: /nic/ => ni, /pty/ => pa)
        $slugToCode = ['sv' => 'sv', 'gt' => 'gt', 'hn' => 'hn', 'nic' => 'ni', 'ni' => 'ni', 'cr' => 'cr', 'pty' => 'pa', 'pa' => 'pa', 'us' => 'us'];
        if (preg_match('#alg3pl\.com/(sv|gt|hn|nic?|cr|pty|pa|us)(/|$|\?)#i', $url, $matches)) {
            $code = $slugToCode[strtolower($matches[1])] ?? strtolower($matches[1]);
            return Country::where('code', $code)->first();
        }

        // 3. From form_id prefix convention (e.g., "sv_contact", "gt_quote")
        if ($formId = $request->input('form_id')) {
            $prefix = strtolower(substr($formId, 0, 2));
            $country = Country::where('code', $prefix)->first();
            if ($country) return $country;
        }

        return null;
    }

    private function extractField(Request $request, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            $value = $request->input($key);
            if ($value && is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }
        return null;
    }
}
