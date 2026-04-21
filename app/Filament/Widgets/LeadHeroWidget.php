<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsSnapshot;
use App\Models\Lead;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

class LeadHeroWidget extends Widget
{
    protected string $view = 'filament.widgets.lead-hero';
    protected int|string|array $columnSpan = 'full';

    public ?string $countryFilter = '';
    public string $timeRange = '30d';

    public static function getSort(): int
    {
        return 0;
    }

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

    public function getViewData(): array
    {
        $cacheKey = 'alg_hero_' . ($this->countryFilter ?? 'global') . '_' . $this->timeRange;

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            [$start, $prevStart] = $this->dates();

            $countryId = $this->countryFilter ?: null;

            // Current period lead count
            $currentCount = Lead::where('created_at', '>=', $start)
                ->when($countryId, fn($q) => $q->where('country_id', $countryId))
                ->count();

            // Previous period lead count
            $prevCount = Lead::whereBetween('created_at', [$prevStart, $start])
                ->when($countryId, fn($q) => $q->where('country_id', $countryId))
                ->count();

            // Delta percentage
            $delta = $prevCount > 0
                ? round(($currentCount - $prevCount) / $prevCount * 100, 1)
                : 0;

            // Conversion rate: leads / users from AnalyticsSnapshot
            $totalUsers = AnalyticsSnapshot::where('date', '>=', $start)
                ->when($countryId, fn($q) => $q->where('country_id', $countryId))
                ->sum('users');

            $conversionRate = $totalUsers > 0
                ? round(($currentCount / $totalUsers) * 100, 2)
                : 0;

            // Status distribution for the funnel bar
            $statuses = Lead::query()
                ->when($countryId, fn($q) => $q->where('country_id', $countryId))
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            // 7-day sparkline — single query instead of 7 separate queries (N+1 fix)
            $startDate = now()->subDays(6)->startOfDay();
            $endDate   = now()->endOfDay();

            $sparkData = Lead::query()
                ->when($countryId, fn($q) => $q->where('country_id', $countryId))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            // Fill all 7 days (missing days get 0)
            $spark = [];
            for ($i = 6; $i >= 0; $i--) {
                $day    = now()->subDays($i)->toDateString();
                $spark[] = (int) ($sparkData[$day] ?? 0);
            }

            return [
                'count'          => $currentCount,
                'delta'          => $delta,
                'conversionRate' => $conversionRate,
                'statuses'       => $statuses,
                'spark'          => $spark,
            ];
        });
    }

    private function dates(): array
    {
        $start = match ($this->timeRange) {
            '7d'  => now()->subDays(7),
            '90d' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };

        $days      = (int) $start->diffInDays(now());
        $prevStart = now()->subDays($days * 2);

        return [$start, $prevStart];
    }
}
