<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsSnapshot;
use App\Models\Lead;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class TrafficOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public ?string $countryFilter = '';
    public string $timeRange = '30d';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
        $this->timeRange = session('time_range', '30d');
    }

    #[On('timeRangeUpdated')]
    public function onTimeRangeUpdated(string $timeRange): void
    {
        $this->timeRange = $timeRange;
    }

    protected function getStats(): array
    {
        [$start, $prevStart, $prevEnd] = $this->dates();

        $q = AnalyticsSnapshot::where('date', '>=', $start);
        $qPrev = AnalyticsSnapshot::whereBetween('date', [$prevStart, $prevEnd]);
        $leadQ = Lead::where('created_at', '>=', $start);

        if ($this->countryFilter) {
            $q = $q->where('country_id', $this->countryFilter);
            $qPrev = $qPrev->where('country_id', $this->countryFilter);
            $leadQ = $leadQ->where('country_id', $this->countryFilter);
        }

        // Users
        $users = (clone $q)->sum('users');
        $prevUsers = (clone $qPrev)->sum('users');
        $growth = $prevUsers > 0 ? round((($users - $prevUsers) / $prevUsers) * 100, 1) : 0;

        // Sessions
        $sessions = (clone $q)->sum('sessions');
        $prevSess = (clone $qPrev)->sum('sessions');
        $sessG = $prevSess > 0 ? round((($sessions - $prevSess) / $prevSess) * 100, 1) : 0;

        // Organic
        $organic = (clone $q)->sum('organic_users');
        $prevOrganic = (clone $qPrev)->sum('organic_users');
        $orgGrowth = $prevOrganic > 0 ? round((($organic - $prevOrganic) / $prevOrganic) * 100, 1) : 0;
        $orgPct = $users > 0 ? round(($organic / $users) * 100) : 0;

        // Leads
        $leads = $leadQ->count();
        $prevLeads = Lead::whereBetween('created_at', [$prevStart, $prevEnd])
            ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
            ->count();
        $leadGrowth = $prevLeads > 0
            ? round((($leads - $prevLeads) / $prevLeads) * 100, 1)
            : ($leads > 0 ? 100 : 0);

        // Bounce Rate
        $bounceRate = round((clone $q)->avg('bounce_rate') ?? 0, 1);
        $prevBounce = round((clone $qPrev)->avg('bounce_rate') ?? 0, 1);
        $bounceDelta = $prevBounce > 0
            ? round((($bounceRate - $prevBounce) / $prevBounce) * 100, 1)
            : 0;

        // Sparklines (last 7 points)
        $spark = AnalyticsSnapshot::where('date', '>=', now()->subDays(7));
        if ($this->countryFilter) {
            $spark = $spark->where('country_id', $this->countryFilter);
        }
        $uSpark = $spark->clone()->selectRaw('date, SUM(users) as t')->groupBy('date')->orderBy('date')->pluck('t')->toArray();
        $sSpark = $spark->clone()->selectRaw('date, SUM(sessions) as t')->groupBy('date')->orderBy('date')->pluck('t')->toArray();
        $oSpark = $spark->clone()->selectRaw('date, SUM(organic_users) as t')->groupBy('date')->orderBy('date')->pluck('t')->toArray();

        // Lead sparkline (last 7 days)
        $lSpark = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $lSpark[] = Lead::whereDate('created_at', $day->toDateString())
                ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
                ->count();
        }

        // Determine lead description
        $hasLeadActivity = $leads > 0 || $prevLeads > 0;
        $leadDescription = $hasLeadActivity ? $this->trend($leadGrowth) : 'No leads yet';
        $leadIcon = $hasLeadActivity
            ? ($leadGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            : 'heroicon-m-user-plus';
        $leadColor = $hasLeadActivity
            ? ($leadGrowth >= 0 ? 'success' : 'danger')
            : 'gray';

        return [
            Stat::make('Total Users', $this->fmt($users))
                ->description($this->trend($growth))
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger')
                ->chart($uSpark),

            Stat::make('Sessions', $this->fmt($sessions))
                ->description($this->trend($sessG))
                ->descriptionIcon($sessG >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($sessG >= 0 ? 'success' : 'danger')
                ->chart($sSpark),

            Stat::make('Organic Traffic', $this->fmt($organic))
                ->description("{$this->trend($orgGrowth)} · {$orgPct}% of total")
                ->descriptionIcon($orgGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orgGrowth >= 0 ? 'success' : 'danger')
                ->chart($oSpark),

            Stat::make('Leads Captured', number_format($leads))
                ->description($leadDescription)
                ->descriptionIcon($leadIcon)
                ->color($leadColor)
                ->chart($lSpark),

            Stat::make('Bounce Rate', "{$bounceRate}%")
                ->description($this->trend($bounceDelta))
                ->descriptionIcon($bounceDelta <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($bounceDelta <= 0 ? 'success' : 'danger'),
        ];
    }

    private function dates(): array
    {
        $start = match ($this->timeRange) {
            '7d' => now()->subDays(7),
            '90d' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };
        $days = (int) $start->diffInDays(now());
        return [$start, now()->subDays($days * 2), $start];
    }

    private function fmt(int|float $n): string
    {
        if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'M';
        if ($n >= 1_000) return round($n / 1_000, 1) . 'K';
        return number_format($n);
    }

    private function trend(float $pct): string
    {
        return ($pct >= 0 ? '+' : '') . "{$pct}% vs prev period";
    }
}
