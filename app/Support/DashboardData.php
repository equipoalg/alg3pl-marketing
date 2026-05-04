<?php

namespace App\Support;

use App\Models\AnalyticsSnapshot;
use App\Models\Campaign;
use App\Models\Country;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SearchConsoleData;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Real-data feeder for the ALG dashboard. Queries the DB filtered by the
 * provided country (null = all countries). Returns the same shape as
 * DashboardMockData::all() so the existing blades just plug in.
 *
 * If the DB returns nothing meaningful (zero leads, zero analytics for the
 * range), we fall back to DashboardMockData so the dashboard is visually
 * rich even on a cold install.
 */
class DashboardData
{
    public static function all(?int $countryId = null, string $timeRange = '30d'): array
    {
        // 5-minute cache per (country, range) tuple — bumps TTFB from ~900ms to ~150ms.
        // Bust when leads/clients/campaigns mutate (LeadObserver could fire ::forget too,
        // but a 5-min TTL is fine for a marketing dashboard).
        $cacheKey = sprintf('alg:dashboard:%s:%s', $countryId ?? 'all', $timeRange);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function () use ($countryId, $timeRange) {
            return self::computeAll($countryId, $timeRange);
        });
    }

    /**
     * Uncached payload generator. Extracted from all() so we can both cache
     * the heavy work and still let unit tests / one-off scripts skip the cache.
     */
    public static function computeAll(?int $countryId = null, string $timeRange = '30d'): array
    {
        $start = self::rangeStart($timeRange);

        $totalLeads = self::leadQuery($countryId)->count();

        // If absolutely no real data exists, return mocks (with navSections
        // always real because they're static UI scaffolding).
        if ($totalLeads === 0) {
            $mock = DashboardMockData::all();
            $mock['_dataSource'] = 'mock';
            return $mock;
        }

        return [
            'navSections'    => self::navSections($countryId),
            'kpis'           => self::kpis($countryId, $start),
            'trafficSeries'  => self::trafficSeries($countryId, $start),
            'trafficLabels'  => self::trafficLabels($start),
            'fuentes'        => self::fuentes($countryId, $start),
            'keywords'       => self::keywords($countryId, $start),
            'pipelineStages' => self::pipelineStages($countryId),
            'recentLeads'    => self::recentLeads($countryId),
            'campaigns'      => self::campaigns($countryId),
            'activity'       => self::activity($countryId),
            'tasks'          => self::tasks($countryId),
            'byCountry'      => self::byCountry($start),
            '_dataSource'    => 'real',
        ];
    }

    /**
     * Force a cache rebuild on the next request. Call from observers when data
     * the dashboard depends on (leads, clients, campaigns) changes.
     */
    public static function forgetCache(): void
    {
        // Wipe all variants — Laravel doesn't support wildcard forgets natively
        // on file/database drivers. Walking the small known set is OK.
        foreach (['all', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'] as $cid) {
            foreach (['7d', '30d', '90d', 'ytd'] as $range) {
                \Illuminate\Support\Facades\Cache::forget("alg:dashboard:{$cid}:{$range}");
            }
        }
    }

    public static function rangeStart(string $timeRange): Carbon
    {
        return match ($timeRange) {
            '7d'  => now()->subDays(7),
            '90d' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };
    }

    private static function leadQuery(?int $countryId)
    {
        return Lead::query()->when($countryId, fn ($q) => $q->where('country_id', $countryId));
    }

    public static function kpis(?int $countryId, Carbon $start): array
    {
        $totalLeads = self::leadQuery($countryId)->where('created_at', '>=', $start)->count();

        $previousStart = $start->copy()->sub(now()->diffAsCarbonInterval($start));
        $previousLeads = self::leadQuery($countryId)
            ->whereBetween('created_at', [$previousStart, $start])->count();
        $leadDelta = $previousLeads > 0
            ? round((($totalLeads - $previousLeads) / $previousLeads) * 100, 1)
            : 0.0;

        $wonLeads = self::leadQuery($countryId)
            ->where('created_at', '>=', $start)->where('status', 'won')->count();
        $closedLeads = self::leadQuery($countryId)
            ->where('created_at', '>=', $start)->whereIn('status', ['won', 'lost'])->count();
        $conversion = $closedLeads > 0 ? round(($wonLeads / $closedLeads) * 100, 1) : 0.0;

        $activeAccounts = self::leadQuery($countryId)
            ->where('status', '!=', 'lost')->whereNotNull('company')
            ->distinct('company')->count('company');

        $activeCampaigns = Campaign::where('status', 'active')
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->count();

        // 13-bucket sparklines (last 13 weeks)
        $leadsSpark = self::sparkline(fn ($cutoff) => self::leadQuery($countryId)
            ->where('created_at', '>=', $cutoff->copy()->subDays(7))
            ->where('created_at', '<', $cutoff)
            ->count());

        $accountsSpark = self::sparkline(fn ($cutoff) => self::leadQuery($countryId)
            ->where('created_at', '<', $cutoff)
            ->whereNotNull('company')->distinct('company')->count('company'));

        $campsSpark = array_fill(0, 13, max(1, $activeCampaigns));

        $convSpark = self::sparkline(function ($cutoff) use ($countryId) {
            $q = self::leadQuery($countryId)->where('created_at', '<=', $cutoff);
            $closed = (clone $q)->whereIn('status', ['won', 'lost'])->count();
            $won    = (clone $q)->where('status', 'won')->count();
            return $closed > 0 ? round(($won / $closed) * 100, 1) : 0;
        });

        return [
            ['id' => 'leads',    'label' => 'Leads totales',     'value' => $totalLeads,
             'delta' => $leadDelta, 'sparkColor' => 'accent', 'sub' => "vs {$previousLeads} período anterior",
             'series' => $leadsSpark],
            ['id' => 'cuentas',  'label' => 'Cuentas activas',   'value' => $activeAccounts,
             'delta' => 0, 'sparkColor' => 'ink', 'sub' => 'empresas únicas',
             'series' => $accountsSpark],
            ['id' => 'campanas', 'label' => 'Campañas activas',  'value' => $activeCampaigns,
             'delta' => 0, 'sparkColor' => 'ink', 'sub' => 'corriendo ahora',
             'series' => $campsSpark],
            ['id' => 'tasa',     'label' => 'Tasa de conversión','value' => $conversion . '%',
             'delta' => 0, 'sparkColor' => 'accent', 'sub' => "{$wonLeads} ganados / {$closedLeads} cerrados",
             'series' => $convSpark],
        ];
    }

    /** Helper: build 13-element sparkline array using a callback that takes a Carbon cutoff. */
    private static function sparkline(callable $fn): array
    {
        $out = [];
        for ($i = 12; $i >= 0; $i--) {
            $cutoff = now()->subDays($i * 7);
            $out[] = (float) $fn($cutoff);
        }
        return $out;
    }

    public static function trafficSeries(?int $countryId, Carbon $start): array
    {
        $end = now();
        $rows = AnalyticsSnapshot::query()
            ->where('date', '>=', $start)
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->selectRaw('date, SUM(organic_users) as o, SUM(direct_users) as d, SUM(referral_users) as r')
            ->groupBy('date')->orderBy('date')->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->format('Y-m-d'));

        $organic = []; $directo = []; $referido = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $row = $rows->get($d->format('Y-m-d'));
            $organic[]  = (int) ($row->o ?? 0);
            $directo[]  = (int) ($row->d ?? 0);
            $referido[] = (int) ($row->r ?? 0);
        }
        return ['organic' => $organic, 'directo' => $directo, 'referido' => $referido];
    }

    public static function trafficLabels(Carbon $start): array
    {
        $months = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $end = now();
        $out = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $out[] = $d->day . ' ' . $months[$d->month - 1];
        }
        return $out;
    }

    public static function fuentes(?int $countryId, Carbon $start): array
    {
        $row = AnalyticsSnapshot::query()
            ->where('date', '>=', $start)
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->selectRaw('SUM(organic_users) as o, SUM(direct_users) as d, SUM(referral_users) as r, SUM(social_users) as s, SUM(paid_users) as p')
            ->first();

        // 'key' is the slug used by /admin/analytics?channel=<key> drilldown
        $sources = [
            ['key' => 'organic',  'label' => 'Orgánico', 'value' => (int) ($row->o ?? 0)],
            ['key' => 'direct',   'label' => 'Directo',  'value' => (int) ($row->d ?? 0)],
            ['key' => 'referral', 'label' => 'Referido', 'value' => (int) ($row->r ?? 0)],
            ['key' => 'social',   'label' => 'Social',   'value' => (int) ($row->s ?? 0)],
            ['key' => 'paid',     'label' => 'Pagado',   'value' => (int) ($row->p ?? 0)],
        ];
        $total = max(1, array_sum(array_column($sources, 'value')));
        return array_map(fn ($s) => array_merge($s, [
            'share' => round($s['value'] / $total, 4),
            'trend' => 0,
        ]), $sources);
    }

    public static function keywords(?int $countryId, Carbon $start): array
    {
        return SearchConsoleData::query()
            ->where('date', '>=', $start)
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereNotNull('query')->where('query', '!=', '')
            ->selectRaw('query as kw, SUM(clicks) as clicks, SUM(impressions) as impr, ROUND(AVG(position), 1) as pos')
            ->groupBy('query')->orderByDesc('clicks')->limit(10)->get()
            ->map(fn ($k) => [
                'kw' => $k->kw, 'clicks' => (int) $k->clicks, 'impr' => (int) $k->impr,
                'pos' => (float) $k->pos, 'delta' => 0,
            ])->toArray();
    }

    public static function pipelineStages(?int $countryId): array
    {
        $stages = [
            ['id' => 'new',         'label' => 'Nuevo',      'color' => 'ink-5'],
            ['id' => 'contacted',   'label' => 'Contactado', 'color' => 'ink-4'],
            ['id' => 'qualified',   'label' => 'Calificado', 'color' => 'accent'],
            ['id' => 'proposal',    'label' => 'Propuesta',  'color' => 'accent'],
            ['id' => 'won',         'label' => 'Ganado',     'color' => 'pos'],
            ['id' => 'lost',        'label' => 'Perdido',    'color' => 'neg'],
        ];
        $counts = self::leadQuery($countryId)
            ->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        return array_map(fn ($s) => array_merge($s, ['count' => (int) ($counts[$s['id']] ?? 0)]), $stages);
    }

    public static function recentLeads(?int $countryId, int $limit = 6): array
    {
        return self::leadQuery($countryId)
            ->with('country')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($l) {
                $parts = preg_split('/\s+/', trim((string) $l->name)) ?: ['?'];
                $initials = strtoupper(substr($parts[0] ?? '?', 0, 1) . substr(end($parts) ?: '', 0, 1));
                $stageMap = [
                    'new' => 'Nuevo', 'contacted' => 'Contactado', 'qualified' => 'Calificado',
                    'proposal' => 'Propuesta', 'negotiation' => 'Negociación',
                    'won' => 'Ganado', 'lost' => 'Perdido',
                ];
                return [
                    'id' => $l->id, // for /admin/leads?selected=<id> deep link
                    'name' => $l->name ?: '—',
                    'company' => $l->company ?: '—',
                    'country' => strtoupper($l->country->code ?? '—'),
                    'value' => $l->estimated_value ? '$' . number_format((float) $l->estimated_value) : '—',
                    'stage' => $stageMap[$l->status] ?? ucfirst((string) $l->status),
                    'time' => $l->created_at?->diffForHumans() ?? '—',
                    'initials' => $initials,
                ];
            })->toArray();
    }

    public static function campaigns(?int $countryId, int $limit = 5): array
    {
        return Campaign::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->withSum('emailCampaigns as sent_count', 'sent_count')
            ->withSum('emailCampaigns as open_count', 'open_count')
            ->withSum('emailCampaigns as click_count', 'click_count')
            ->latest()->limit($limit)->get()
            ->map(function ($c) {
                $statusMap = ['active' => 'Activa', 'paused' => 'Pausada', 'scheduled' => 'Programada', 'draft' => 'Borrador', 'completed' => 'Completada'];
                $sent = (int) ($c->sent_count ?? 0);
                return [
                    'id' => $c->id, // for /admin/campaigns/<id>/edit deep link
                    'name' => $c->name,
                    'status' => $statusMap[$c->status] ?? ucfirst((string) $c->status),
                    'sent' => $sent,
                    'open' => $sent > 0 ? round(($c->open_count ?? 0) / $sent, 4) : null,
                    'click' => $sent > 0 ? round(($c->click_count ?? 0) / $sent, 4) : null,
                    'spend' => '$' . number_format((float) ($c->budget ?? 0)),
                ];
            })->toArray();
    }

    /**
     * Pending/in-progress tasks for the dashboard's "Tareas y seguimiento" panel.
     * Returns the 5 most-relevant unfinished tasks for this country (or all
     * countries when global). Falls back to mock data when the table is empty
     * so a fresh install still shows the panel populated.
     */
    public static function tasks(?int $countryId, int $limit = 5): array
    {
        $rows = Task::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByRaw("CASE priority
                WHEN 'P0 — Critical' THEN 1
                WHEN 'P1 — High' THEN 2
                WHEN 'alta' THEN 1
                WHEN 'media' THEN 2
                ELSE 3 END")
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            // No real tasks yet — keep the panel non-empty during onboarding
            return DashboardMockData::tasks();
        }

        return $rows->map(function (Task $t) {
            // Normalize priority labels onto the 3-bucket palette the blade uses
            $prio = strtolower((string) $t->priority);
            $bucket = match (true) {
                str_contains($prio, 'p0') || str_contains($prio, 'critical') || str_contains($prio, 'alta') => 'alta',
                str_contains($prio, 'p1') || str_contains($prio, 'high')     || str_contains($prio, 'media') => 'media',
                default => 'baja',
            };
            return [
                'id'       => $t->id,
                'title'    => $t->title,
                'priority' => $bucket,
                'due'      => $t->due_date?->translatedFormat('d M') ?? 'Sin fecha',
            ];
        })->toArray();
    }

    public static function activity(?int $countryId, int $limit = 6): array
    {
        return LeadActivity::query()
            ->with(['lead', 'user'])
            ->when($countryId, fn ($q) => $q->whereHas('lead', fn ($q2) => $q2->where('country_id', $countryId)))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($a) => [
                'lead_id' => $a->lead_id, // for /admin/leads?selected=<lead_id> deep link
                'actor' => $a->user?->name ?? 'Sistema',
                'action' => $a->description ?: ($a->type ?? 'evento'),
                'time' => $a->created_at?->format('H:i') ?? '—',
            ])->toArray();
    }

    public static function byCountry(Carbon $start): array
    {
        return AnalyticsSnapshot::query()
            ->where('date', '>=', $start)
            ->selectRaw('country_id, SUM(sessions) as sessions')
            ->groupBy('country_id')
            ->orderByDesc('sessions')
            ->limit(6)->with('country')
            ->get()
            ->map(fn ($r) => [
                'code' => strtoupper($r->country->code ?? ''), // for /admin/analytics?country=<code> deep link
                'label' => strtoupper($r->country->code ?? '?'),
                'value' => (int) $r->sessions,
            ])->toArray();
    }

    public static function navSections(?int $countryId): array
    {
        // Real counts for badge numbers in the sidebar.
        $accountsCount = Lead::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereNotNull('company')->distinct('company')->count('company');
        $leadsCount = Lead::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))->count();
        $campsCount = Campaign::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->where('status', 'active')->count();

        // Reuse the static structure but inject real counts where relevant
        $base = DashboardMockData::navSections();
        foreach ($base as &$section) {
            foreach ($section['items'] as &$item) {
                if ($item['id'] === 'cuentas')   $item['count'] = $accountsCount;
                if ($item['id'] === 'leads')     $item['count'] = $leadsCount;
                if ($item['id'] === 'campanas')  $item['count'] = $campsCount;
            }
        }
        return $base;
    }
}
