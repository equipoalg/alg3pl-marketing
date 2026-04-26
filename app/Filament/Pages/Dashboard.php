<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsSnapshot;
use App\Models\Campaign;
use App\Models\Country;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SearchConsoleData;
use Illuminate\Support\Collection;
use App\Filament\Widgets\AdMetricsWidget;
use App\Filament\Widgets\ContactTimelineWidget;
use App\Filament\Widgets\LeadHeroWidget;
use App\Filament\Widgets\LeadsByCountryWidget;
use App\Filament\Widgets\SalesPipelineWidget;
use App\Filament\Widgets\SmartAlertsWidget;
use App\Filament\Widgets\TaskProgressWidget;
use App\Filament\Widgets\TopKeywordsWidget;
use App\Filament\Widgets\TrafficOverviewWidget;
use App\Filament\Widgets\TrafficTrendWidget;
use Carbon\Carbon;
use Filament\Panel;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';
    protected Width|string|null $maxContentWidth = Width::Full;

    public ?string $countryFilter = '';
    public string $timeRange = '30d';
    public string $variant = 'a';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
        $this->timeRange     = session('time_range', '30d');
        $this->variant       = session('dashboard_variant', 'a');
    }

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
        session(['time_range' => $range]);
        $this->dispatch('timeRangeUpdated', timeRange: $range);
    }

    public function setVariant(string $variant): void
    {
        $this->variant = in_array($variant, ['a', 'b']) ? $variant : 'a';
        session(['dashboard_variant' => $this->variant]);
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public static function getNavigationSort(): ?int
    {
        return -2;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'dashboard';
    }

    public function getTitle(): string
    {
        if ($this->countryFilter) {
            $country = Country::find($this->countryFilter);
            return $country ? $country->name : 'Panorama';
        }
        return 'Panorama global';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getContentWidgets(): array
    {
        return [
            SmartAlertsWidget::class,
            LeadHeroWidget::class,
            TrafficOverviewWidget::class,
            TrafficTrendWidget::class,
            LeadsByCountryWidget::class,
            TopKeywordsWidget::class,
            SalesPipelineWidget::class,
            TaskProgressWidget::class,
            ContactTimelineWidget::class,
            AdMetricsWidget::class,
        ];
    }

    /** Variant A — Row 1: Traffic chart (wide) + Country breakdown (narrow) */
    public function getRow1WidgetsA(): array
    {
        return [TrafficTrendWidget::class, LeadsByCountryWidget::class];
    }

    /** Variant A — Row 2: Top keywords + Sales pipeline */
    public function getRow2WidgetsA(): array
    {
        return [TopKeywordsWidget::class, SalesPipelineWidget::class];
    }

    /** Variant A — Row 3: Recent leads + Activity timeline */
    public function getRow3WidgetsA(): array
    {
        return [ContactTimelineWidget::class, TaskProgressWidget::class];
    }

    /** Variant A — Row 4: Ad metrics (full) */
    public function getRow4WidgetsA(): array
    {
        return [AdMetricsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'countryFilter' => $this->countryFilter,
            'timeRange'     => $this->timeRange,
        ];
    }

    public function getViewData(): array
    {
        $country = $this->countryFilter ? Country::find($this->countryFilter) : null;

        $lastSync  = AnalyticsSnapshot::max('updated_at');
        $freshness = 'stale';
        if ($lastSync) {
            $hours     = Carbon::parse($lastSync)->diffInHours(now());
            $freshness = $hours < 6 ? 'fresh' : ($hours < 24 ? 'recent' : 'stale');
        }

        $kpis = $this->computeKpis();
        $recentLeads = $this->getRecentLeads();

        return [
            'selectedCountry' => $country,
            'lastSync'        => $lastSync ? Carbon::parse($lastSync)->diffForHumans() : 'Never',
            'freshness'       => $freshness,
            'kpis'            => $kpis,
            'kpiSparklines'   => $this->getKpiSparklines(),
            'recentLeads'     => $recentLeads,
            'trafficSeries'   => $this->getTrafficSeries(),
            'fuentes'         => $this->getFuentes(),
            'keywords'        => $this->getKeywords(),
            'activity'        => $this->getActivity(),
            'campaigns'       => $this->getCampaigns(),
            'pipelineData'    => $this->getPipelineData(),
        ];
    }

    /** Sparkline data per KPI (last 13 buckets — visually a sparkline trail). */
    private function getKpiSparklines(): array
    {
        $start = $this->getRangeStart();
        $countryId = $this->countryFilter ?: null;

        // Leads cumulative weekly buckets (compressed into 13 points)
        $leadsBuckets = $this->bucketize(
            Lead::query()
                ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
                ->where('created_at', '>=', $start->copy()->subDays(90))
                ->orderBy('created_at')
                ->pluck('created_at')
                ->map(fn ($d) => $d->format('Y-m-d'))
                ->countBy()
                ->toArray(),
            13
        );

        // Cuentas: distinct companies cumulative — approximate via lead count
        $cuentas = array_map(fn ($v) => max(0, $v), $leadsBuckets);

        // Campañas active over time — flat for now
        $campanas = array_fill(0, 13, max(1, Campaign::where('status', 'active')->count()));

        // Conversion rate trend — derived from won/closed per bucket
        $conversion = array_fill(0, 13, 0);
        for ($i = 0; $i < 13; $i++) {
            $cutoff = now()->subDays((13 - $i) * 7);
            $closed = Lead::query()->where('created_at', '<=', $cutoff)
                ->whereIn('status', ['won', 'lost'])
                ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
                ->count();
            $won = Lead::query()->where('created_at', '<=', $cutoff)
                ->where('status', 'won')
                ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
                ->count();
            $conversion[$i] = $closed > 0 ? round(($won / $closed) * 100, 1) : 0;
        }

        return [
            'leads'    => array_values($leadsBuckets),
            'cuentas'  => array_values($cuentas),
            'campanas' => array_values($campanas),
            'tasa'     => $conversion,
        ];
    }

    /**
     * Traffic series for last N days from AnalyticsSnapshot.
     * Returns ['labels' => [...], 'series' => ['organic'=>..., 'directo'=>..., 'referido'=>...]]
     */
    private function getTrafficSeries(): array
    {
        $start = $this->getRangeStart();
        $end   = now();
        $days  = max(1, (int) $start->diffInDays($end));

        $query = AnalyticsSnapshot::query()
            ->where('date', '>=', $start)
            ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
            ->selectRaw('date, SUM(organic_users) as organic, SUM(direct_users) as direct, SUM(referral_users) as referral')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->date)->format('Y-m-d'));

        $labels = []; $organic = []; $directo = []; $referido = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $labels[]  = $d->format($days > 60 ? 'M d' : 'd M');
            $row       = $query->get($key);
            $organic[] = (int) ($row->organic ?? 0);
            $directo[] = (int) ($row->direct  ?? 0);
            $referido[]= (int) ($row->referral ?? 0);
        }

        return [
            'labels'   => $labels,
            'organic'  => $organic,
            'directo'  => $directo,
            'referido' => $referido,
        ];
    }

    /** Source breakdown for the FuentesCard (horizontal bars). */
    private function getFuentes(): array
    {
        $start = $this->getRangeStart();
        $row = AnalyticsSnapshot::query()
            ->where('date', '>=', $start)
            ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
            ->selectRaw('SUM(organic_users) as organic, SUM(direct_users) as direct, SUM(referral_users) as referral, SUM(social_users) as social, SUM(paid_users) as paid')
            ->first();

        $sources = [
            ['label' => 'Orgánico', 'value' => (int) ($row->organic ?? 0)],
            ['label' => 'Directo',  'value' => (int) ($row->direct ?? 0)],
            ['label' => 'Referido', 'value' => (int) ($row->referral ?? 0)],
            ['label' => 'Social',   'value' => (int) ($row->social ?? 0)],
            ['label' => 'Pagado',   'value' => (int) ($row->paid ?? 0)],
        ];

        $total = max(1, array_sum(array_column($sources, 'value')));
        return array_map(fn ($s) => array_merge($s, ['share' => round($s['value'] / $total * 100, 1)]), $sources);
    }

    /** Top keywords for the KeywordsCard. */
    private function getKeywords(int $limit = 8): Collection
    {
        $start = $this->getRangeStart();
        return SearchConsoleData::query()
            ->where('date', '>=', $start)
            ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
            ->whereNotNull('query')
            ->where('query', '!=', '')
            ->selectRaw('query as kw, SUM(clicks) as clicks, SUM(impressions) as impr, ROUND(AVG(position), 1) as pos')
            ->groupBy('query')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();
    }

    /** Recent activity for the ActivityCard. */
    private function getActivity(int $limit = 6): Collection
    {
        return LeadActivity::query()
            ->with(['lead', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Recent campaigns for the CampaignsCard. */
    private function getCampaigns(int $limit = 5): Collection
    {
        return Campaign::query()
            ->where('type', 'email')
            ->withSum('emailCampaigns as sent_count', 'sent_count')
            ->withSum('emailCampaigns as open_count', 'open_count')
            ->withSum('emailCampaigns as click_count', 'click_count')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Pipeline stage data for variant A (funnel) and B (strip). */
    public function getPipelineData(): array
    {
        $stages = [
            ['id' => 'new',         'label' => 'Nuevo',        'color' => '#CBD5E1'],
            ['id' => 'contacted',   'label' => 'Contactado',   'color' => '#94A3B8'],
            ['id' => 'qualified',   'label' => 'Calificado',   'color' => '#1E3A8A'],
            ['id' => 'proposal',    'label' => 'Propuesta',    'color' => '#1E3A8A'],
            ['id' => 'negotiation', 'label' => 'Negociación',  'color' => '#2563EB'],
            ['id' => 'won',         'label' => 'Ganado',       'color' => '#166534'],
            ['id' => 'lost',        'label' => 'Perdido',      'color' => '#9F1239'],
        ];
        $counts = Lead::query()
            ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        return array_map(fn ($s) => array_merge($s, ['count' => (int) ($counts[$s['id']] ?? 0)]), $stages);
    }

    /** Compress a [date => count] map into N evenly-sized buckets. */
    private function bucketize(array $byDate, int $buckets): array
    {
        if (empty($byDate)) return array_fill(0, $buckets, 0);
        $values = array_values($byDate);
        $size = max(1, intdiv(count($values), $buckets));
        $out = array_fill(0, $buckets, 0);
        foreach ($values as $i => $v) {
            $b = min($buckets - 1, intdiv($i, $size));
            $out[$b] += $v;
        }
        return $out;
    }

    /** Compute the 4 hero KPIs for the top of the dashboard. */
    private function computeKpis(): array
    {
        $start = $this->getRangeStart();
        $previousStart = $this->getRangeStart()->copy()->sub(now()->diffAsCarbonInterval($start));

        $leadQuery = Lead::query();
        $previousLeadQuery = Lead::query();
        if ($this->countryFilter) {
            $leadQuery->where('country_id', $this->countryFilter);
            $previousLeadQuery->where('country_id', $this->countryFilter);
        }

        $totalLeads = (clone $leadQuery)->where('created_at', '>=', $start)->count();
        $previousLeads = (clone $previousLeadQuery)
            ->whereBetween('created_at', [$previousStart, $start])
            ->count();
        $leadDelta = $previousLeads > 0
            ? round((($totalLeads - $previousLeads) / $previousLeads) * 100, 1)
            : 0.0;

        $wonLeads = (clone $leadQuery)
            ->where('created_at', '>=', $start)
            ->where('status', 'won')
            ->count();
        $closedLeads = (clone $leadQuery)
            ->where('created_at', '>=', $start)
            ->whereIn('status', ['won', 'lost'])
            ->count();
        $conversion = $closedLeads > 0
            ? round(($wonLeads / $closedLeads) * 100, 1)
            : 0.0;

        $activeAccounts = (clone $leadQuery)
            ->where('status', '!=', 'lost')
            ->whereNotNull('company')
            ->distinct('company')
            ->count('company');

        $activeCampaigns = class_exists(\App\Models\EmailCampaign::class)
            ? \App\Models\EmailCampaign::where('status', 'active')->count()
            : 0;

        return [
            ['id' => 'leads', 'label' => 'Leads totales', 'value' => $totalLeads, 'delta' => $leadDelta, 'sub' => "vs {$previousLeads} período anterior"],
            ['id' => 'cuentas', 'label' => 'Cuentas activas', 'value' => $activeAccounts, 'delta' => 0, 'sub' => 'empresas únicas'],
            ['id' => 'campanas', 'label' => 'Campañas activas', 'value' => $activeCampaigns, 'delta' => 0, 'sub' => 'corriendo ahora'],
            ['id' => 'tasa', 'label' => 'Tasa conversión', 'value' => $conversion . '%', 'delta' => 0, 'sub' => "{$wonLeads} ganados / {$closedLeads} cerrados"],
        ];
    }

    private function getRecentLeads(int $limit = 6): \Illuminate\Support\Collection
    {
        $query = Lead::query()->with('country')->latest();
        if ($this->countryFilter) {
            $query->where('country_id', $this->countryFilter);
        }
        return $query->limit($limit)->get();
    }

    private function getRangeStart(): Carbon
    {
        return match ($this->timeRange) {
            '7d'  => now()->subDays(7),
            '90d' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };
    }
}
