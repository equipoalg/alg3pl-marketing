<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * /admin/analytics — real-look Google Analytics 4 "Reports overview" page.
 *
 * Replaces the generic Filament list of AnalyticsSnapshot rows with the layout
 * users recognize from analytics.google.com/analytics: top metrics row →
 * comparison time chart → "Where do users come from?" channel breakdown →
 * "What pages do they visit?" + by-country bars. Country filter from sidebar
 * scopes everything; "Global" view aggregates across all countries.
 */
class AnalyticsTrafficDashboard extends Page
{
    protected string $view = 'filament.pages.analytics-traffic-dashboard';
    protected Width|string|null $maxContentWidth = Width::Full;

    public string $period = '28d';

    /** Drilldown filters from the dashboard cards. URL-bound so deep links work. */
    #[Url(as: 'channel')]
    public string $channel = '';

    #[Url(as: 'country')]
    public string $countryCode = '';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationLabel(): string
    {
        return 'Tráfico';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Analytics';
    }

    public static function getNavigationSort(): int
    {
        return 1;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analytics';
    }

    public function getTitle(): string
    {
        return 'Tráfico — Visión general';
    }

    public function setPeriod(string $value): void
    {
        if (in_array($value, ['7d', '28d', '90d', '12m'], true)) {
            $this->period = $value;
        }
    }

    public function getViewData(): array
    {
        // ?country=<code> from a dashboard drilldown overrides the session country filter
        $countryId = null;
        if ($this->countryCode !== '') {
            $countryId = Country::where('code', strtoupper($this->countryCode))->value('id');
        }
        if ($countryId === null && session('country_filter')) {
            $countryId = (int) session('country_filter');
        }

        [$start, $end] = $this->resolvePeriod();
        $days = $start->diffInDays($end) + 1;
        $prevStart = $start->copy()->subDays($days);
        $prevEnd = $start->copy()->subDay();

        $base = AnalyticsSnapshot::query()->whereBetween('date', [$start, $end]);
        if ($countryId) {
            $base->where('country_id', $countryId);
        }
        $prevBase = AnalyticsSnapshot::query()->whereBetween('date', [$prevStart, $prevEnd]);
        if ($countryId) {
            $prevBase->where('country_id', $countryId);
        }

        // Top metrics
        $totals = (clone $base)->selectRaw(
            'COALESCE(SUM(users),0) as users, '.
            'COALESCE(SUM(new_users),0) as new_users, '.
            'COALESCE(SUM(sessions),0) as sessions, '.
            'COALESCE(SUM(page_views),0) as page_views, '.
            'COALESCE(AVG(NULLIF(avg_session_duration,0)),0) as avg_duration, '.
            'COALESCE(AVG(NULLIF(bounce_rate,0)),0) as bounce_rate, '.
            'COALESCE(SUM(conversions),0) as conversions'
        )->first();

        $prev = (clone $prevBase)->selectRaw(
            'COALESCE(SUM(users),0) as users, '.
            'COALESCE(SUM(sessions),0) as sessions, '.
            'COALESCE(SUM(page_views),0) as page_views, '.
            'COALESCE(SUM(conversions),0) as conversions'
        )->first();

        // Daily users series (current + previous period for comparison line)
        $currentDaily = (clone $base)
            ->selectRaw('date, COALESCE(SUM(users),0) as users, COALESCE(SUM(sessions),0) as sessions')
            ->groupBy('date')->orderBy('date')->get()->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $previousDaily = (clone $prevBase)
            ->selectRaw('date, COALESCE(SUM(users),0) as users')
            ->groupBy('date')->orderBy('date')->get()->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $usersSeries = $sessionsSeries = $previousSeries = $labels = [];
        $cursor = $start->copy();
        $prevCursor = $prevStart->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $prevKey = $prevCursor->format('Y-m-d');
            $usersSeries[]    = (int) ($currentDaily->get($key)->users ?? 0);
            $sessionsSeries[] = (int) ($currentDaily->get($key)->sessions ?? 0);
            $previousSeries[] = (int) ($previousDaily->get($prevKey)->users ?? 0);
            $labels[]         = $cursor->format('M j');
            $cursor->addDay();
            $prevCursor->addDay();
        }

        // Channel breakdown (totals across period)
        $channels = (clone $base)->selectRaw(
            'COALESCE(SUM(organic_users),0) as organic, '.
            'COALESCE(SUM(direct_users),0) as direct, '.
            'COALESCE(SUM(referral_users),0) as referral, '.
            'COALESCE(SUM(social_users),0) as social, '.
            'COALESCE(SUM(paid_users),0) as paid'
        )->first();
        $channelTotal = max(1, $channels->organic + $channels->direct + $channels->referral + $channels->social + $channels->paid);

        // By country bars (only when in Global view)
        $byCountry = collect();
        if (! $countryId) {
            $byCountry = AnalyticsSnapshot::query()
                ->whereBetween('date', [$start, $end])
                ->join('countries', 'analytics_snapshots.country_id', '=', 'countries.id')
                ->selectRaw('countries.code as code, countries.name as name, SUM(users) as users')
                ->groupBy('countries.id', 'countries.code', 'countries.name')
                ->orderByDesc('users')
                ->limit(8)
                ->get();
        }

        return [
            'period'         => $this->period,
            'startDate'      => $start->format('d M Y'),
            'endDate'        => $end->format('d M Y'),
            'totals'         => $totals,
            'prev'           => $prev,
            'usersSeries'    => $usersSeries,
            'sessionsSeries' => $sessionsSeries,
            'previousSeries' => $previousSeries,
            'labels'         => $labels,
            'channels'       => $channels,
            'channelTotal'   => $channelTotal,
            'byCountry'      => $byCountry,
            'isGlobal'       => ! $countryId,
            'highlightChannel' => $this->channel,                       // 'organic' | 'direct' | etc.
            'highlightCountry' => strtoupper($this->countryCode),       // 'SV' | 'GT' | etc.
        ];
    }

    /** @return array{0:\Carbon\Carbon,1:\Carbon\Carbon} */
    private function resolvePeriod(): array
    {
        $end = now()->endOfDay();
        $start = match ($this->period) {
            '7d'  => $end->copy()->subDays(6)->startOfDay(),
            '90d' => $end->copy()->subDays(89)->startOfDay(),
            '12m' => $end->copy()->subMonths(12)->startOfDay(),
            default => $end->copy()->subDays(27)->startOfDay(),
        };
        return [$start, $end];
    }
}
