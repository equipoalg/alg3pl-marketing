<?php

namespace App\Filament\Pages;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * /admin/analytics — Visión general estilo GA4 "Reports overview".
 *
 * Layout: KPI tiles con sparklines clickables → comparison line chart →
 * channel breakdown clickable → by-country bars (global) o engagement panel
 * (per-country). Country filter del sidebar scopea todo; "Global" agrega
 * todos los países.
 *
 * State (todo URL-bound para deep-linking):
 *   - period: 7d | 28d | 90d | 12m
 *   - channel: '' | organic | direct | referral | social | paid (filter chip)
 *   - countryCode: '' | SV | GT | HN | NI | CR | PA (filter chip)
 *   - compareMode: prev | yoy | none
 */
class AnalyticsTrafficDashboard extends Page
{
    protected string $view = 'filament.pages.analytics-traffic-dashboard';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'period')]
    public string $period = '28d';

    /** Comparison mode: vs período anterior, vs año anterior, o sin comparar. */
    #[Url(as: 'compare')]
    public string $compareMode = 'prev';

    /** Drilldown filters — clickable chips. */
    #[Url(as: 'channel')]
    public string $channel = '';

    #[Url(as: 'country')]
    public string $countryCode = '';

    /** Custom date range — opcional, sobreescribe $period si ambos están. */
    #[Url(as: 'from')]
    public string $customFrom = '';

    #[Url(as: 'to')]
    public string $customTo = '';

    public static function getNavigationIcon(): string { return 'heroicon-o-chart-bar'; }
    public static function getNavigationLabel(): string { return 'Tráfico'; }
    public static function getNavigationGroup(): ?string { return 'Analytics'; }
    public static function getNavigationSort(): int { return 1; }
    public static function getSlug(?Panel $panel = null): string { return 'analytics'; }
    public function getTitle(): string { return 'Tráfico — Visión general'; }

    /* ───── Setters with validation ───── */

    public function setPeriod(string $value): void
    {
        if (in_array($value, ['7d', '28d', '90d', '12m'], true)) {
            $this->period = $value;
            // Reset custom range si se elige preset
            $this->customFrom = '';
            $this->customTo = '';
        }
    }

    public function setCompareMode(string $value): void
    {
        if (in_array($value, ['prev', 'yoy', 'none'], true)) {
            $this->compareMode = $value;
        }
    }

    public function setChannel(string $value): void
    {
        if (in_array($value, ['', 'organic', 'direct', 'referral', 'social', 'paid'], true)) {
            $this->channel = $value;
        }
    }

    public function setCountry(string $value): void
    {
        $valid = ['', 'SV', 'GT', 'HN', 'NI', 'CR', 'PA'];
        if (in_array(strtoupper($value), $valid, true)) {
            $this->countryCode = strtoupper($value);
        }
    }

    public function clearAllFilters(): void
    {
        $this->channel = '';
        $this->countryCode = '';
        $this->customFrom = '';
        $this->customTo = '';
        $this->period = '28d';
        $this->compareMode = 'prev';
    }

    /* ───── Saved views (per-user, en preferences) ───── */

    public function saveCurrentView(string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 40) return;
        $user = auth()->user();
        if (! $user) return;
        $existing = $user->pref('analytics_views', []);
        $existing = array_values(array_filter($existing, fn ($v) => ($v['name'] ?? '') !== $name));
        $existing[] = [
            'name'    => $name,
            'period'  => $this->period,
            'compare' => $this->compareMode,
            'channel' => $this->channel,
            'country' => $this->countryCode,
            'from'    => $this->customFrom,
            'to'      => $this->customTo,
        ];
        if (count($existing) > 20) $existing = array_slice($existing, -20);
        $user->setPrefs(['analytics_views' => array_values($existing)]);
        Notification::make()->title("Vista \"$name\" guardada")->success()->send();
    }

    public function loadAnalyticsView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('analytics_views', []);
        if (! isset($views[$index])) return;
        $v = $views[$index];
        $this->period      = $v['period']  ?? '28d';
        $this->compareMode = $v['compare'] ?? 'prev';
        $this->channel     = $v['channel'] ?? '';
        $this->countryCode = $v['country'] ?? '';
        $this->customFrom  = $v['from']    ?? '';
        $this->customTo    = $v['to']      ?? '';
    }

    public function deleteAnalyticsView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('analytics_views', []);
        if (! isset($views[$index])) return;
        unset($views[$index]);
        $user->setPrefs(['analytics_views' => array_values($views)]);
        Notification::make()->title('Vista eliminada')->success()->send();
    }

    /* ───── Goals (per-user en preferences, simple monthly target de sesiones) ───── */

    public function setMonthlyGoal(int $sessions): void
    {
        $user = auth()->user();
        if (! $user) return;
        $sessions = max(0, $sessions);
        $user->setPrefs(['analytics_goal_sessions' => $sessions]);
        Notification::make()->title('Goal actualizado')->body("Meta mensual: {$sessions} sesiones")->success()->send();
    }

    public function getViewData(): array
    {
        $countryId = null;
        if ($this->countryCode !== '') {
            $countryId = Country::where('code', strtoupper($this->countryCode))->value('id');
        }
        if ($countryId === null && session('country_filter')) {
            $countryId = (int) session('country_filter');
        }

        [$start, $end] = $this->resolvePeriod();
        $days = $start->diffInDays($end) + 1;

        // Comparison range según mode
        [$prevStart, $prevEnd] = match ($this->compareMode) {
            'yoy'  => [$start->copy()->subYear(), $end->copy()->subYear()],
            'none' => [null, null],
            default => [$start->copy()->subDays($days), $start->copy()->subDay()],
        };

        $base = AnalyticsSnapshot::query()->whereBetween('date', [$start, $end]);
        if ($countryId) $base->where('country_id', $countryId);
        if ($this->channel !== '') {
            // Filter to days where this channel had >0 users
            $col = $this->channel . '_users';
            $base->where($col, '>', 0);
        }

        $prevBase = null;
        if ($prevStart) {
            $prevBase = AnalyticsSnapshot::query()->whereBetween('date', [$prevStart, $prevEnd]);
            if ($countryId) $prevBase->where('country_id', $countryId);
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

        $prev = $prevBase ? (clone $prevBase)->selectRaw(
            'COALESCE(SUM(users),0) as users, '.
            'COALESCE(SUM(new_users),0) as new_users, '.
            'COALESCE(SUM(sessions),0) as sessions, '.
            'COALESCE(SUM(page_views),0) as page_views, '.
            'COALESCE(AVG(NULLIF(bounce_rate,0)),0) as bounce_rate, '.
            'COALESCE(SUM(conversions),0) as conversions'
        )->first() : null;

        // Daily series
        $currentDaily = (clone $base)
            ->selectRaw('date, COALESCE(SUM(users),0) as users, COALESCE(SUM(sessions),0) as sessions, COALESCE(SUM(page_views),0) as page_views')
            ->groupBy('date')->orderBy('date')->get()->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $previousDaily = collect();
        if ($prevBase) {
            $previousDaily = (clone $prevBase)
                ->selectRaw('date, COALESCE(SUM(users),0) as users')
                ->groupBy('date')->orderBy('date')->get()->keyBy(fn ($r) => $r->date->format('Y-m-d'));
        }

        $usersSeries = $sessionsSeries = $pageViewsSeries = $previousSeries = $labels = [];
        $cursor = $start->copy();
        $prevCursor = $prevStart?->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $usersSeries[]    = (int) ($currentDaily->get($key)->users ?? 0);
            $sessionsSeries[] = (int) ($currentDaily->get($key)->sessions ?? 0);
            $pageViewsSeries[] = (int) ($currentDaily->get($key)->page_views ?? 0);
            if ($prevCursor) {
                $prevKey = $prevCursor->format('Y-m-d');
                $previousSeries[] = (int) ($previousDaily->get($prevKey)->users ?? 0);
                $prevCursor->addDay();
            }
            $labels[] = $cursor->format('M j');
            $cursor->addDay();
        }

        // Per-KPI sparklines (last 7 days within current range)
        $sparkSlice = fn ($arr) => array_slice($arr, max(0, count($arr) - 7));
        $sparks = [
            'users'      => $sparkSlice($usersSeries),
            'sessions'   => $sparkSlice($sessionsSeries),
            'page_views' => $sparkSlice($pageViewsSeries),
        ];

        // Channel breakdown
        $channels = (clone $base)->selectRaw(
            'COALESCE(SUM(organic_users),0) as organic, '.
            'COALESCE(SUM(direct_users),0) as direct, '.
            'COALESCE(SUM(referral_users),0) as referral, '.
            'COALESCE(SUM(social_users),0) as social, '.
            'COALESCE(SUM(paid_users),0) as paid'
        )->first();
        $channelTotal = max(1, $channels->organic + $channels->direct + $channels->referral + $channels->social + $channels->paid);

        // By country (only Global)
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

        // Anomaly detection: compara último día vs mediana de últimos 7 mismos-días-de-la-semana
        $anomaly = $this->detectAnomaly($currentDaily);

        // Freshness — última fecha con data
        $latestSnapshot = AnalyticsSnapshot::max('date');
        $freshness = $latestSnapshot
            ? \Carbon\Carbon::parse($latestSnapshot)
            : null;

        // Goal tracking — meta mensual del usuario
        $monthlyGoal = (int) (auth()->user()?->pref('analytics_goal_sessions', 0) ?? 0);
        $monthSessions = AnalyticsSnapshot::query()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfDay()])
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->sum('sessions');

        // Saved views
        $savedViews = auth()->user()?->pref('analytics_views', []) ?? [];

        return [
            'period'           => $this->period,
            'compareMode'      => $this->compareMode,
            'startDate'        => $start->format('d M Y'),
            'endDate'          => $end->format('d M Y'),
            'startDateRaw'     => $start->format('Y-m-d'),
            'endDateRaw'       => $end->format('Y-m-d'),
            'totals'           => $totals,
            'prev'             => $prev,
            'usersSeries'      => $usersSeries,
            'sessionsSeries'   => $sessionsSeries,
            'pageViewsSeries'  => $pageViewsSeries,
            'previousSeries'   => $previousSeries,
            'labels'           => $labels,
            'sparks'           => $sparks,
            'channels'         => $channels,
            'channelTotal'     => $channelTotal,
            'byCountry'        => $byCountry,
            'isGlobal'         => ! $countryId,
            'currentChannel'   => $this->channel,
            'currentCountry'   => strtoupper($this->countryCode),
            'anomaly'          => $anomaly,
            'freshness'        => $freshness,
            'monthlyGoal'      => $monthlyGoal,
            'monthSessions'    => (int) $monthSessions,
            'savedViews'       => $savedViews,
            'customFrom'       => $this->customFrom,
            'customTo'         => $this->customTo,
        ];
    }

    /** @return array{0:\Carbon\Carbon,1:\Carbon\Carbon} */
    private function resolvePeriod(): array
    {
        // Custom range overrides preset
        if ($this->customFrom !== '' && $this->customTo !== '') {
            try {
                $start = \Carbon\Carbon::parse($this->customFrom)->startOfDay();
                $end = \Carbon\Carbon::parse($this->customTo)->endOfDay();
                if ($start->lte($end)) return [$start, $end];
            } catch (\Throwable) { /* fall through to preset */ }
        }
        $end = now()->endOfDay();
        $start = match ($this->period) {
            '7d'  => $end->copy()->subDays(6)->startOfDay(),
            '90d' => $end->copy()->subDays(89)->startOfDay(),
            '12m' => $end->copy()->subMonths(12)->startOfDay(),
            default => $end->copy()->subDays(27)->startOfDay(),
        };
        return [$start, $end];
    }

    /**
     * Detección simple de anomalía: el día MÁS RECIENTE con data vs la mediana
     * de los últimos 7 mismos-días-de-la-semana. Si la diferencia es > 30% —
     * arriba o abajo — devolvemos el delta y el tipo.
     *
     * @return array{level:string, message:string, delta:int}|null
     */
    private function detectAnomaly($daily): ?array
    {
        if ($daily->isEmpty()) return null;
        $sorted = $daily->sortKeys();
        $lastKey = $sorted->keys()->last();
        $last = $sorted[$lastKey] ?? null;
        if (! $last || ($last->users ?? 0) === 0) return null;

        $lastDate = \Carbon\Carbon::parse($lastKey);
        $sameWeekday = $sorted->filter(function ($_, $key) use ($lastDate, $lastKey) {
            if ($key === $lastKey) return false;
            return \Carbon\Carbon::parse($key)->dayOfWeek === $lastDate->dayOfWeek;
        });
        if ($sameWeekday->count() < 2) return null;

        $values = $sameWeekday->pluck('users')->sort()->values();
        $count = $values->count();
        $median = $count % 2 ? $values[(int) ($count / 2)] : ($values[$count / 2 - 1] + $values[$count / 2]) / 2;
        if ($median == 0) return null;

        $delta = round((($last->users - $median) / $median) * 100);
        if (abs($delta) < 30) return null;

        $weekdayName = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'][$lastDate->dayOfWeek];
        return [
            'level' => $delta < 0 ? 'critical' : 'good',
            'message' => $delta < 0
                ? "Tráfico bajó {$delta}% el {$weekdayName} vs el promedio de últimos {$weekdayName}s."
                : "Tráfico subió +{$delta}% el {$weekdayName} vs el promedio de últimos {$weekdayName}s.",
            'delta' => (int) $delta,
        ];
    }
}
