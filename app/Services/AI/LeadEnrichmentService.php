<?php

namespace App\Services\AI;

use Anthropic\Client;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

/**
 * Enriches a Lead by asking Claude for a 1-2 line summary + a concrete
 * suggested next action + a priority bucket. Output is cached on the lead
 * (notes appended) so the cost is paid once per lead unless re-run.
 *
 * Configure:
 *   ANTHROPIC_API_KEY=sk-ant-...
 *   ANTHROPIC_MODEL=claude-haiku-4-5   (default — cheapest sufficient model)
 */
class LeadEnrichmentService
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly string $model = 'claude-haiku-4-5',
    ) {}

    /**
     * Returns ['summary' => string, 'next_action' => string, 'priority' => string]
     * or ['error' => string] on failure / missing credentials.
     */
    public function enrich(Lead $lead): array
    {
        $key = $this->apiKey ?? config('services.anthropic.api_key', env('ANTHROPIC_API_KEY'));
        if (empty($key)) {
            return ['error' => 'ANTHROPIC_API_KEY no configurado en .env.'];
        }

        $prompt = $this->buildPrompt($lead);
        $model  = config('services.anthropic.model', env('ANTHROPIC_MODEL', $this->model));

        try {
            $client = new Client(apiKey: $key);
            $message = $client->messages->create(
                maxTokens: 600,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: $model,
            );

            // Extract text content from the response
            $text = '';
            foreach ($message->content as $block) {
                if (isset($block->text)) $text .= $block->text;
                elseif (is_array($block) && isset($block['text'])) $text .= $block['text'];
            }

            $parsed = $this->parseJson($text);
            if (! $parsed) {
                Log::warning('LeadEnrichmentService: could not parse JSON from Claude', ['raw' => $text]);
                return ['error' => 'No se pudo parsear la respuesta de Claude.', 'raw' => $text];
            }

            // Persist the enrichment as a lead activity for the timeline
            $lead->activities()->create([
                'type'        => 'note',
                'description' => "AI · {$parsed['summary']}\n→ {$parsed['next_action']}",
                'next_action' => $parsed['next_action'] ?? null,
                'next_action_date' => match ($parsed['priority'] ?? 'medium') {
                    'high'   => now()->addDay(),
                    'medium' => now()->addDays(3),
                    'low'    => now()->addWeek(),
                    default  => now()->addDays(3),
                },
            ]);

            return $parsed;
        } catch (\Throwable $e) {
            Log::warning('LeadEnrichmentService: Anthropic call failed', [
                'lead_id' => $lead->id,
                'error'   => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    private function buildPrompt(Lead $lead): string
    {
        $country = $lead->country?->name ?? '—';
        $notes   = trim($lead->notes ?? '') ?: '(sin notas)';

        return <<<PROMPT
Eres un asistente comercial de ALG3PL, una empresa 3PL de logística en Centroamérica.
Te paso datos de un lead inbound. Devuelve EXCLUSIVAMENTE un objeto JSON con esta forma:
{"summary": "1-2 líneas en español describiendo el lead y su intención", "next_action": "acción concreta que el rep debe ejecutar en las próximas 24-72h", "priority": "high|medium|low"}

Datos del lead:
- Nombre: {$lead->name}
- Empresa: {$lead->company}
- País: {$country}
- Email: {$lead->email}
- Teléfono: {$lead->phone}
- Servicio de interés: {$lead->service_interest}
- Score actual: {$lead->score}
- Notas/mensaje: {$notes}

Reglas:
- Si el lead menciona urgencia/timeline corto → priority "high"
- Si pide cotización específica con volumen → priority "high"
- Si solo pide info general → priority "medium"
- Si parece estudiante / sin empresa real → priority "low"
- next_action debe ser un verbo claro (Llamar / Enviar cotización / Calificar BANT / etc.) + a quién
- summary en español neutro, sin marketingés

Responde SOLO con el JSON, sin texto antes o después.
PROMPT;
    }

    private function parseJson(string $text): ?array
    {
        // Claude sometimes wraps JSON in code fences — strip those first
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($text));
        $data = json_decode($text, true);
        if (! is_array($data)) return null;
        return [
            'summary'     => (string) ($data['summary'] ?? ''),
            'next_action' => (string) ($data['next_action'] ?? ''),
            'priority'    => in_array($data['priority'] ?? '', ['high', 'medium', 'low']) ? $data['priority'] : 'medium',
        ];
    }
}
