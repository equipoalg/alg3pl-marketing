<?php

namespace App\Services\AI;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use App\Models\Lead;
use App\Models\SearchConsoleData;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OnuxService
{
    private const DEFAULT_ANTHROPIC_MODEL = 'claude-3-5-haiku-20241022';
    private const DEFAULT_OPENAI_MODEL = 'gpt-4.1-mini';
    private const DEFAULT_OPENAI_BASE_URL = 'https://api.openai.com';
    private const DEFAULT_QWEN_MODEL = 'qwen-plus';
    private const DEFAULT_ANTHROPIC_BASE_URL = 'https://api.anthropic.com';
    private const DEFAULT_QWEN_BASE_URL = 'https://dashscope-us.aliyuncs.com/compatible-mode/v1';

    public function briefing(?User $user = null): array
    {
        $context = $this->context($user);
        $insights = $this->insights($context);

        return [
            'scope' => $context['scope'],
            'metrics' => $context['metrics'],
            'insights' => $insights,
            'ai_configured' => $this->isAiConfigured(),
            'ai_provider' => $this->provider(),
            'ai_model' => $this->model(),
            'ai_missing_key' => $this->missingApiKeyName(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }

    public function answer(string $question, ?User $user = null, array $history = []): string
    {
        $question = trim($question);
        if ($question === '') {
            $question = 'Dame el resumen ejecutivo y las acciones prioritarias.';
        }

        $context = $this->context($user);
        $localAnswer = $this->localAnswer($question, $context);

        if ($this->provider() === 'openai') {
            return $this->answerWithOpenAI($question, $context, $localAnswer, $history);
        }

        if ($this->provider() === 'qwen') {
            return $this->answerWithQwen($question, $context, $localAnswer, $history);
        }

        return $this->answerWithAnthropic($question, $context, $localAnswer, $history);
    }

    private function answerWithAnthropic(string $question, array $context, string $localAnswer, array $history = []): string
    {
        $key = $this->anthropicApiKey();
        if ($key === '') {
            return $localAnswer . "\n\nNota: 5PL está usando análisis local porque ANTHROPIC_API_KEY no está configurado. Cuando agreguemos la llave en producción, responderá con IA sobre estos mismos datos.";
        }

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                ])
                ->acceptJson()
                ->timeout(35)
                ->post($this->anthropicBaseUrl() . '/v1/messages', [
                    'model' => $this->anthropicModel(),
                    'max_tokens' => 900,
                    'temperature' => 0.2,
                    'system' => $this->systemPrompt($context),
                    'messages' => $this->conversationMessages($question, $history),
                ]);

            if (! $response->successful()) {
                Log::warning('5PL Anthropic answer failed', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return $localAnswer . "\n\nNota: Anthropic no respondió correctamente, así que usé el análisis local para no detener la operación.";
            }

            $text = $this->extractAnthropicText((array) $response->json());

            return trim($text) !== '' ? trim($text) : $localAnswer;
        } catch (\Throwable $e) {
            Log::warning('5PL Anthropic answer exception', ['error' => $e->getMessage()]);
            return $localAnswer . "\n\nNota: no pude conectar con el modelo de IA, así que respondí con el análisis local para no detener la operación.";
        }
    }

    public function answerForWhatsApp(string $question, ?User $user = null): string
    {
        return mb_substr($this->answer($question, $user), 0, 3200);
    }

    public function isAiConfigured(): bool
    {
        return match ($this->provider()) {
            'qwen' => $this->qwenApiKey() !== '',
            'openai' => $this->openAiApiKey() !== '',
            default => $this->anthropicApiKey() !== '',
        };
    }

    private function provider(): string
    {
        $provider = strtolower(trim((string) config('services.ai.provider', env('AI_PROVIDER', 'openai'))));

        return in_array($provider, ['openai', 'anthropic', 'qwen'], true) ? $provider : 'openai';
    }

    private function anthropicApiKey(): string
    {
        return trim((string) config('services.anthropic.api_key', env('ANTHROPIC_API_KEY', '')));
    }

    private function missingApiKeyName(): string
    {
        return match ($this->provider()) {
            'qwen' => 'QWEN_API_KEY',
            'openai' => 'OPENAI_API_KEY',
            default => 'ANTHROPIC_API_KEY',
        };
    }

    private function model(): string
    {
        return match ($this->provider()) {
            'qwen' => $this->qwenModel(),
            'openai' => $this->openAiModel(),
            default => $this->anthropicModel(),
        };
    }

    private function anthropicBaseUrl(): string
    {
        return rtrim(trim((string) config('services.anthropic.base_url', env('ANTHROPIC_BASE_URL', self::DEFAULT_ANTHROPIC_BASE_URL))) ?: self::DEFAULT_ANTHROPIC_BASE_URL, '/');
    }

    private function anthropicModel(): string
    {
        return trim((string) config('services.anthropic.model', env('ANTHROPIC_MODEL', self::DEFAULT_ANTHROPIC_MODEL))) ?: self::DEFAULT_ANTHROPIC_MODEL;
    }

    private function openAiApiKey(): string
    {
        return trim((string) config('services.openai.api_key', env('OPENAI_API_KEY', '')));
    }

    private function openAiBaseUrl(): string
    {
        return rtrim(trim((string) config('services.openai.base_url', env('OPENAI_BASE_URL', self::DEFAULT_OPENAI_BASE_URL))) ?: self::DEFAULT_OPENAI_BASE_URL, '/');
    }

    private function openAiModel(): string
    {
        return trim((string) config('services.openai.model', env('OPENAI_MODEL', self::DEFAULT_OPENAI_MODEL))) ?: self::DEFAULT_OPENAI_MODEL;
    }

    private function qwenApiKey(): string
    {
        return trim((string) config('services.qwen.api_key', env('QWEN_API_KEY', env('DASHSCOPE_API_KEY', ''))));
    }

    private function qwenBaseUrl(): string
    {
        return rtrim(trim((string) config('services.qwen.base_url', env('QWEN_BASE_URL', self::DEFAULT_QWEN_BASE_URL))) ?: self::DEFAULT_QWEN_BASE_URL, '/');
    }

    private function qwenModel(): string
    {
        return trim((string) config('services.qwen.model', env('QWEN_MODEL', self::DEFAULT_QWEN_MODEL))) ?: self::DEFAULT_QWEN_MODEL;
    }

    private function answerWithOpenAI(string $question, array $context, string $localAnswer, array $history = []): string
    {
        $key = $this->openAiApiKey();
        if ($key === '') {
            return $localAnswer . "\n\nNota: 5PL está usando análisis local porque OPENAI_API_KEY no está configurado. Cuando agreguemos la llave en producción, responderá con IA sobre estos mismos datos.";
        }

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->timeout(35)
                ->post($this->openAiBaseUrl() . '/v1/responses', [
                    'model' => $this->openAiModel(),
                    'instructions' => $this->systemPrompt($context),
                    'input' => $this->conversationMessages($question, $history),
                    'temperature' => 0.2,
                    'max_output_tokens' => 900,
                ]);

            if (! $response->successful()) {
                Log::warning('5PL OpenAI answer failed', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return $localAnswer . "\n\nNota: OpenAI no respondió correctamente, así que usé el análisis local para no detener la operación.";
            }

            $text = $this->extractOpenAiText((array) $response->json());

            return trim($text) !== '' ? trim($text) : $localAnswer;
        } catch (\Throwable $e) {
            Log::warning('5PL OpenAI answer exception', ['error' => $e->getMessage()]);
            return $localAnswer . "\n\nNota: no pude conectar con OpenAI, así que respondí con el análisis local para no detener la operación.";
        }
    }

    private function answerWithQwen(string $question, array $context, string $localAnswer, array $history = []): string
    {
        $key = $this->qwenApiKey();
        if ($key === '') {
            return $localAnswer . "\n\nNota: 5PL está usando análisis local porque QWEN_API_KEY no está configurado. Cuando agreguemos la llave de Qwen/DashScope en producción, responderá con IA sobre estos mismos datos.";
        }

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->timeout(35)
                ->post($this->qwenBaseUrl() . '/chat/completions', [
                    'model' => $this->qwenModel(),
                    'messages' => array_merge(
                        [['role' => 'system', 'content' => $this->systemPrompt($context)]],
                        $this->conversationMessages($question, $history),
                    ),
                    'temperature' => 0.2,
                    'max_tokens' => 900,
                ]);

            if (! $response->successful()) {
                Log::warning('5PL Qwen answer failed', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 500),
                ]);

                return $localAnswer . "\n\nNota: Qwen no respondió correctamente, así que usé el análisis local para no detener la operación.";
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            return $text !== '' ? $text : $localAnswer;
        } catch (\Throwable $e) {
            Log::warning('5PL Qwen answer exception', ['error' => $e->getMessage()]);

            return $localAnswer . "\n\nNota: no pude conectar con Qwen, así que respondí con el análisis local para no detener la operación.";
        }
    }

    private function context(?User $user): array
    {
        $countryId = $this->countryId($user);
        $country = $countryId ? Country::find($countryId) : null;
        $start = now()->subDays(30);
        $previousStart = now()->subDays(60);

        $leadBase = Lead::query()->when($countryId, fn ($q) => $q->where('country_id', $countryId));
        $taskBase = Task::query()->when($countryId, fn ($q) => $q->where('country_id', $countryId));

        $leads30 = (clone $leadBase)->where('created_at', '>=', $start)->count();
        $leadsPrevious = (clone $leadBase)
            ->whereBetween('created_at', [$previousStart, $start])
            ->count();

        $closed = (clone $leadBase)->whereIn('status', ['won', 'lost'])->count();
        $won = (clone $leadBase)->where('status', 'won')->count();
        $conversion = $closed > 0 ? round(($won / $closed) * 100, 1) : 0.0;

        $sessions30 = $this->sumIfTable('analytics_snapshots', AnalyticsSnapshot::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->where('date', '>=', $start), 'sessions');

        $sessionsPrevious = $this->sumIfTable('analytics_snapshots', AnalyticsSnapshot::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereBetween('date', [$previousStart, $start]), 'sessions');

        $topQueries = Schema::hasTable('search_console_data')
            ? SearchConsoleData::query()
                ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
                ->where('date', '>=', $start)
                ->selectRaw('query, SUM(clicks) as clicks, SUM(impressions) as impressions')
                ->groupBy('query')
                ->orderByDesc('clicks')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'query' => $row->query,
                    'clicks' => (int) $row->clicks,
                    'impressions' => (int) $row->impressions,
                ])
                ->all()
            : [];

        $recentHotLeads = (clone $leadBase)
            ->with(['country', 'assignedUser'])
            ->where('score', '>=', 70)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'name' => $lead->name,
                'company' => $lead->company,
                'country' => $lead->country?->code,
                'score' => $lead->score,
                'status' => $lead->status,
                'assigned_to' => $lead->assignedUser?->name,
            ])
            ->all();

        return [
            'scope' => $country ? $country->name . ' (' . strtoupper($country->code) . ')' : 'Global',
            'country_id' => $countryId,
            'metrics' => [
                'leads_30d' => $leads30,
                'leads_previous_30d' => $leadsPrevious,
                'lead_delta_pct' => $leadsPrevious > 0 ? round((($leads30 - $leadsPrevious) / $leadsPrevious) * 100, 1) : 0.0,
                'hot_leads' => (clone $leadBase)->where('score', '>=', 70)->count(),
                'new_leads' => (clone $leadBase)->where('status', 'new')->count(),
                'unassigned_leads' => (clone $leadBase)->whereNull('assigned_to')->count(),
                'conversion_rate' => $conversion,
                'overdue_tasks' => (clone $taskBase)->whereDate('due_date', '<', now())->where('status', '!=', 'done')->count(),
                'blocked_tasks' => (clone $taskBase)->where('status', 'blocked')->count(),
                'sessions_30d' => $sessions30,
                'sessions_previous_30d' => $sessionsPrevious,
                'sessions_delta_pct' => $sessionsPrevious > 0 ? round((($sessions30 - $sessionsPrevious) / $sessionsPrevious) * 100, 1) : 0.0,
            ],
            'recent_hot_leads' => $recentHotLeads,
            'top_queries' => $topQueries,
        ];
    }

    private function insights(array $context): array
    {
        $m = $context['metrics'];
        $items = [];

        if ($m['hot_leads'] > 0) {
            $items[] = [
                'title' => 'Atender leads calientes',
                'body' => "{$m['hot_leads']} leads tienen score alto. Prioriza llamada o WhatsApp hoy, antes de que se enfrien.",
                'tone' => 'urgent',
            ];
        }

        if ($m['unassigned_leads'] > 0) {
            $items[] = [
                'title' => 'Asignar responsables',
                'body' => "{$m['unassigned_leads']} leads no tienen responsable. Asignalos por pais para evitar fuga comercial.",
                'tone' => 'warning',
            ];
        }

        if ($m['overdue_tasks'] > 0) {
            $items[] = [
                'title' => 'Cerrar seguimiento vencido',
                'body' => "{$m['overdue_tasks']} tareas estan vencidas. El jefe de ventas deberia revisar bloqueos y reasignar.",
                'tone' => 'warning',
            ];
        }

        if ($m['sessions_delta_pct'] < -10) {
            $items[] = [
                'title' => 'Revisar caida de trafico',
                'body' => "Las sesiones bajaron {$m['sessions_delta_pct']}% vs el periodo anterior. Revisa Search Console, campanas y formularios.",
                'tone' => 'warning',
            ];
        }

        if ($m['leads_30d'] > $m['leads_previous_30d'] && $m['conversion_rate'] < 5) {
            $items[] = [
                'title' => 'Mas leads, baja conversion',
                'body' => 'Hay volumen entrando, pero la conversion sigue baja. Conviene auditar tiempos de respuesta y calidad de contacto.',
                'tone' => 'info',
            ];
        }

        if (empty($items)) {
            $items[] = [
                'title' => 'Operacion estable',
                'body' => 'No veo una alerta critica inmediata. El siguiente paso es mejorar velocidad de seguimiento y mantener datos limpios.',
                'tone' => 'neutral',
            ];
        }

        return array_slice($items, 0, 4);
    }

    private function localAnswer(string $question, array $context): string
    {
        $m = $context['metrics'];
        $insights = $this->insights($context);
        $lines = [
            "5PL · Fifth Predictive Layer · {$context['scope']}",
            "Leads 30d: {$m['leads_30d']} ({$m['lead_delta_pct']}% vs periodo anterior). Hot: {$m['hot_leads']}. Sin asignar: {$m['unassigned_leads']}. Conversion: {$m['conversion_rate']}%.",
            '',
            'Acciones recomendadas:',
        ];

        foreach ($insights as $idx => $insight) {
            $lines[] = ($idx + 1) . '. ' . $insight['title'] . ': ' . $insight['body'];
        }

        if (! empty($context['recent_hot_leads'])) {
            $lines[] = '';
            $lines[] = 'Leads a mirar primero:';
            foreach ($context['recent_hot_leads'] as $lead) {
                $lines[] = '- ' . trim(($lead['company'] ?: $lead['name']) . ' · score ' . $lead['score'] . ' · ' . $lead['status']);
            }
        }

        if (str_contains(mb_strtolower($question), 'seo') && ! empty($context['top_queries'])) {
            $lines[] = '';
            $lines[] = 'Consultas SEO con traccion:';
            foreach ($context['top_queries'] as $query) {
                $lines[] = '- ' . $query['query'] . ' · ' . $query['clicks'] . ' clics / ' . $query['impressions'] . ' impresiones';
            }
        }

        return implode("\n", $lines);
    }

    private function conversationMessages(string $question, array $history): array
    {
        $messages = [];
        $seenUser = false;

        foreach (array_slice($history, -8) as $message) {
            $role = $message['role'] ?? null;
            $content = trim((string) ($message['content'] ?? ''));

            if (! in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            if (! $seenUser && $role === 'assistant') {
                continue;
            }

            $seenUser = $seenUser || $role === 'user';
            $last = array_key_last($messages);

            if ($last !== null && $messages[$last]['role'] === $role) {
                $messages[$last]['content'] .= "\n\n" . $content;
                continue;
            }

            $messages[] = ['role' => $role, 'content' => $content];
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        return $messages;
    }

    private function systemPrompt(array $context): string
    {
        $json = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Eres 5PL, Fifth Predictive Layer, la capa inteligente de ALG3PL.
Tu trabajo es conectar data B2B, predecir oportunidades y convertir informacion dispersa de CRM, marketing, SEO y seguimiento comercial en acciones comerciales.

Reglas:
- Responde en espanol claro, ejecutivo y accionable.
- No inventes datos fuera del JSON.
- Si hay riesgo comercial, dilo directo.
- Da 3 a 5 acciones priorizadas con responsable sugerido.
- Si el usuario pertenece a un pais, no sugieras mirar otros paises.
- Evita texto largo. Maximo 8 bullets.

Contexto JSON:
{$json}
PROMPT;
    }

    private function extractOpenAiText(array $payload): string
    {
        $text = trim((string) data_get($payload, 'output_text', ''));
        if ($text !== '') {
            return $text;
        }

        foreach ((array) data_get($payload, 'output', []) as $item) {
            foreach ((array) data_get($item, 'content', []) as $content) {
                if (! is_array($content)) {
                    continue;
                }

                if (in_array($content['type'] ?? null, ['output_text', 'text'], true)) {
                    $text .= (string) ($content['text'] ?? '');
                }
            }
        }

        return trim($text);
    }

    private function extractAnthropicText(array $payload): string
    {
        $text = '';

        foreach ((array) data_get($payload, 'content', []) as $block) {
            if (is_array($block) && ($block['type'] ?? null) === 'text') {
                $text .= (string) ($block['text'] ?? '');
            }
        }

        return trim($text);
    }

    private function countryId(?User $user): ?int
    {
        $user ??= auth()->user();
        if ($user?->country_id) {
            return (int) $user->country_id;
        }

        return session('country_filter') ? (int) session('country_filter') : null;
    }

    private function sumIfTable(string $table, $query, string $column): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) $query->sum($column);
    }
}
