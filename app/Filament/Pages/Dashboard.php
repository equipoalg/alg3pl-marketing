<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use App\Models\Lead;
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
            'recentLeads'     => $recentLeads,
        ];
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
