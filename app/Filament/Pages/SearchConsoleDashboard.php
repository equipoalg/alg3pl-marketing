<?php

namespace App\Filament\Pages;

use App\Models\SearchConsoleData;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

/**
 * /admin/search-console — real-look Google Search Console dashboard.
 *
 * Replaces the generic Filament list page with the layout users recognize from
 * search.google.com/search-console: 4 KPI tiles → time-series chart → tabbed
 * detail tables (queries / pages / countries / dates). Country filter from
 * sidebar (session('country_filter')) scopes everything.
 */
class SearchConsoleDashboard extends Page
{
    protected string $view = 'filament.pages.search-console-dashboard';
    protected Width|string|null $maxContentWidth = Width::Full;

    public string $period = '28d';
    public string $tab = 'queries';

    /** Drilldown filter from the dashboard's keyword rows: ?kw=alg+el+salvador. */
    #[Url(as: 'kw')]
    public string $keywordFilter = '';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-magnifying-glass';
    }

    public static function getNavigationLabel(): string
    {
        return 'Search Console';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Analytics';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'search-console';
    }

    public function getTitle(): string
    {
        return 'Search Console';
    }

    public function setPeriod(string $value): void
    {
        if (in_array($value, ['7d', '28d', '3m', '6m', '12m', '16m'], true)) {
            $this->period = $value;
        }
    }

    public function setTab(string $value): void
    {
        if (in_array($value, ['queries', 'pages', 'countries', 'dates'], true)) {
            $this->tab = $value;
        }
    }

    public function getViewData(): array
    {
        $countryId = session('country_filter') ? (int) session('country_filter') : null;

        // When a kw drilldown landed here, widen the default period from 28d to
        // 3 months so we don't hide rows just outside the recent window. Users
        // can still explicitly switch back to 7d/28d.
        if ($this->keywordFilter !== '' && $this->period === '28d') {
            $this->period = '3m';
        }

        [$start, $end] = $this->resolvePeriod();

        // Base query, scoped by country if selected
        $base = SearchConsoleData::query()->whereBetween('date', [$start, $end]);
        if ($countryId) {
            $base->where('country_id', $countryId);
        }

        // KPI totals
        $totals = (clone $base)->selectRaw(
            'COALESCE(SUM(clicks),0) as clicks, '.
            'COALESCE(SUM(impressions),0) as impressions, '.
            'COALESCE(AVG(NULLIF(position,0)),0) as avg_position, '.
            'CASE WHEN SUM(impressions)>0 THEN (SUM(clicks)/SUM(impressions))*100 ELSE 0 END as avg_ctr'
        )->first();

        // Previous period for delta
        $days = $start->diffInDays($end) + 1;
        $prevStart = $start->copy()->subDays($days);
        $prevEnd = $start->copy()->subDay();
        $prev = SearchConsoleData::query()
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->selectRaw(
                'COALESCE(SUM(clicks),0) as clicks, '.
                'COALESCE(SUM(impressions),0) as impressions, '.
                'COALESCE(AVG(NULLIF(position,0)),0) as avg_position, '.
                'CASE WHEN SUM(impressions)>0 THEN (SUM(clicks)/SUM(impressions))*100 ELSE 0 END as avg_ctr'
            )->first();

        // Daily series (clicks + impressions for the chart)
        $series = (clone $base)
            ->selectRaw('date, COALESCE(SUM(clicks),0) as clicks, COALESCE(SUM(impressions),0) as impressions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates with zeros (chart needs continuous data)
        $clicksSeries = [];
        $impressionsSeries = [];
        $labels = [];
        $cursor = $start->copy();
        $byDate = $series->keyBy(fn ($r) => $r->date->format('Y-m-d'));
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $row = $byDate->get($key);
            $clicksSeries[] = (int) ($row->clicks ?? 0);
            $impressionsSeries[] = (int) ($row->impressions ?? 0);
            $labels[] = $cursor->format('M j');
            $cursor->addDay();
        }

        // Tabbed detail
        $rows = match ($this->tab) {
            'queries' => (clone $base)
                ->selectRaw('query as label, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(NULLIF(position,0)) as position, CASE WHEN SUM(impressions)>0 THEN (SUM(clicks)/SUM(impressions))*100 ELSE 0 END as ctr')
                ->whereNotNull('query')
                ->when($this->keywordFilter !== '', fn ($q) => $q->where('query', 'like', '%' . $this->keywordFilter . '%'))
                ->groupBy('query')
                ->orderByDesc('clicks')
                ->limit(50)
                ->get(),
            'pages' => (clone $base)
                ->selectRaw('page as label, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(NULLIF(position,0)) as position, CASE WHEN SUM(impressions)>0 THEN (SUM(clicks)/SUM(impressions))*100 ELSE 0 END as ctr')
                ->whereNotNull('page')
                ->groupBy('page')
                ->orderByDesc('clicks')
                ->limit(50)
                ->get(),
            'countries' => SearchConsoleData::query()
                ->whereBetween('date', [$start, $end])
                ->join('countries', 'search_console_data.country_id', '=', 'countries.id')
                ->selectRaw('countries.name as label, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(NULLIF(position,0)) as position, CASE WHEN SUM(impressions)>0 THEN (SUM(clicks)/SUM(impressions))*100 ELSE 0 END as ctr')
                ->groupBy('countries.id', 'countries.name')
                ->orderByDesc('clicks')
                ->limit(50)
                ->get(),
            'dates' => (clone $base)
                ->selectRaw('date as label, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(NULLIF(position,0)) as position, CASE WHEN SUM(impressions)>0 THEN (SUM(clicks)/SUM(impressions))*100 ELSE 0 END as ctr')
                ->groupBy('date')
                ->orderByDesc('date')
                ->limit(50)
                ->get(),
            default => collect(),
        };

        // ──────────────────────────────────────────────
        //  INSIGHTS — winners / losers / opportunities / quick wins / position dist
        // ──────────────────────────────────────────────

        // Per-query stats current vs previous period — used by winners/losers
        $currentByQuery = (clone $base)
            ->whereNotNull('query')->where('query', '!=', '')
            ->selectRaw('query, SUM(clicks) as c, SUM(impressions) as i, AVG(NULLIF(position,0)) as p')
            ->groupBy('query')
            ->get()
            ->keyBy('query');

        $prevByQuery = SearchConsoleData::query()
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereNotNull('query')->where('query', '!=', '')
            ->selectRaw('query, SUM(clicks) as c, SUM(impressions) as i, AVG(NULLIF(position,0)) as p')
            ->groupBy('query')
            ->get()
            ->keyBy('query');

        // Compute click delta per query (only for queries present in current)
        $deltas = $currentByQuery->map(function ($r, $q) use ($prevByQuery) {
            $prev = $prevByQuery->get($q);
            $delta = (int) $r->c - (int) ($prev->c ?? 0);
            return [
                'query'       => $q,
                'clicks'      => (int) $r->c,
                'prev_clicks' => (int) ($prev->c ?? 0),
                'delta'       => $delta,
                'impressions' => (int) $r->i,
                'position'    => round((float) $r->p, 1),
                'ctr'         => $r->i > 0 ? round(($r->c / $r->i) * 100, 1) : 0,
            ];
        });

        $winners = $deltas->where('delta', '>', 0)->sortByDesc('delta')->take(5)->values();
        $losers  = $deltas->where('delta', '<', 0)->sortBy('delta')->take(5)->values();

        // CTR opportunities — high impressions but low CTR (relative)
        // Threshold: impressions > 50 AND ctr < 2.0 AND position <= 20
        $opportunities = $currentByQuery
            ->filter(fn ($r) => $r->i > 50 && $r->i > 0 && ($r->c / $r->i) * 100 < 2.0 && $r->p <= 20)
            ->map(fn ($r, $q) => [
                'query'       => $q,
                'clicks'      => (int) $r->c,
                'impressions' => (int) $r->i,
                'ctr'         => round(($r->c / max(1, $r->i)) * 100, 2),
                'position'    => round((float) $r->p, 1),
            ])
            ->sortByDesc('impressions')
            ->take(5)
            ->values();

        // Quick wins — queries on page 2 of Google (positions 11-20) with decent
        // impressions. Pushing them into page 1 = traffic gain.
        $quickWins = $currentByQuery
            ->filter(fn ($r) => $r->p >= 11 && $r->p <= 20 && $r->i >= 20)
            ->map(fn ($r, $q) => [
                'query'       => $q,
                'clicks'      => (int) $r->c,
                'impressions' => (int) $r->i,
                'position'    => round((float) $r->p, 1),
                'ctr'         => $r->i > 0 ? round(($r->c / $r->i) * 100, 1) : 0,
            ])
            ->sortByDesc('impressions')
            ->take(5)
            ->values();

        // Position distribution: counts in top3 / 4-10 / 11-20 / 21+
        $positionBuckets = ['top3' => 0, 'page1' => 0, 'page2' => 0, 'beyond' => 0];
        foreach ($currentByQuery as $r) {
            $pos = (float) $r->p;
            if ($pos > 0 && $pos <= 3)        $positionBuckets['top3']++;
            elseif ($pos > 3 && $pos <= 10)   $positionBuckets['page1']++;
            elseif ($pos > 10 && $pos <= 20)  $positionBuckets['page2']++;
            elseif ($pos > 20)                $positionBuckets['beyond']++;
        }
        $totalQueries = array_sum($positionBuckets);

        return [
            'period'      => $this->period,
            'tab'         => $this->tab,
            'startDate'   => $start->format('d M Y'),
            'endDate'     => $end->format('d M Y'),
            'totals'      => $totals,
            'prev'        => $prev,
            'clicksSeries' => $clicksSeries,
            'impressionsSeries' => $impressionsSeries,
            'labels'      => $labels,
            'rows'        => $rows,
            'keywordFilter' => $this->keywordFilter,
            // Insights
            'winners'         => $winners,
            'losers'          => $losers,
            'opportunities'   => $opportunities,
            'quickWins'       => $quickWins,
            'positionBuckets' => $positionBuckets,
            'totalQueries'    => $totalQueries,
        ];
    }

    /** @return array{0:\Carbon\Carbon,1:\Carbon\Carbon} */
    private function resolvePeriod(): array
    {
        $end = now()->endOfDay();
        $start = match ($this->period) {
            '7d'  => $end->copy()->subDays(6)->startOfDay(),
            '3m'  => $end->copy()->subMonths(3)->startOfDay(),
            '6m'  => $end->copy()->subMonths(6)->startOfDay(),
            '12m' => $end->copy()->subMonths(12)->startOfDay(),
            '16m' => $end->copy()->subMonths(16)->startOfDay(),
            default => $end->copy()->subDays(27)->startOfDay(),
        };
        return [$start, $end];
    }
}
