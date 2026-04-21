<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
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

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
        $this->timeRange     = session('time_range', '30d');
    }

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
        session(['time_range' => $range]);
        $this->dispatch('timeRangeUpdated', timeRange: $range);
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

        return [
            'selectedCountry' => $country,
            'lastSync'        => $lastSync ? Carbon::parse($lastSync)->diffForHumans() : 'Never',
            'freshness'       => $freshness,
        ];
    }
}
