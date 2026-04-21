<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsSnapshot;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class TrafficTrendWidget extends ChartWidget
{
    protected ?string $maxHeight = '260px';
    protected int|string|array $columnSpan = 1;

    public ?string $countryFilter = '';
    public string $timeRange = '30d';

    public function getHeading(): string
    {
        return 'Traffic Trend';
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

    public function getDescription(): ?string
    {
        $start = $this->getStart();
        $days  = (int) $start->diffInDays(now());

        $current  = AnalyticsSnapshot::where('date', '>=', $start)
            ->when($this->countryFilter, fn($q) => $q->where('country_id', $this->countryFilter))
            ->sum('users');

        $prevStart = now()->subDays($days * 2);
        $previous  = AnalyticsSnapshot::whereBetween('date', [$prevStart, $start])
            ->when($this->countryFilter, fn($q) => $q->where('country_id', $this->countryFilter))
            ->sum('users');

        if ($previous <= 0) return "Últimos {$days} días · Sin datos previos para comparar";

        $pct = round((($current - $previous) / $previous) * 100, 1);
        $dir = $pct >= 0 ? '↑' : '↓';
        $word = $pct >= 0 ? 'más' : 'menos';

        return "{$dir} " . abs($pct) . "% {$word} usuarios que el período anterior";
    }

    public static function getSort(): int
    {
        return 2;
    }

    protected function getData(): array
    {
        $start = $this->getStart();
        $query = AnalyticsSnapshot::where('date', '>=', $start);
        if ($this->countryFilter) $query->where('country_id', $this->countryFilter);

        $data = $query
            ->selectRaw('date, SUM(users) as users, SUM(organic_users) as organic, SUM(direct_users) as direct')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $fmt = $this->timeRange === '7d' ? 'D' : ($this->timeRange === '90d' || $this->timeRange === 'ytd' ? 'M d' : 'd');

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $data->pluck('users')->toArray(),
                    'borderColor' => '#0E0E0C',
                    'backgroundColor' => 'rgba(14,14,12,0.04)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                ],
                [
                    'label' => 'Organic',
                    'data' => $data->pluck('organic')->toArray(),
                    'borderColor' => '#6B6B50',
                    'backgroundColor' => 'rgba(107,107,80,0.05)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 1.5,
                    'pointRadius' => 0,
                ],
                [
                    'label' => 'Direct',
                    'data' => $data->pluck('direct')->toArray(),
                    'borderColor' => 'rgba(154,154,146,0.7)',
                    'fill' => false,
                    'tension' => 0.35,
                    'borderWidth' => 1,
                    'borderDash' => [4, 3],
                    'pointRadius' => 0,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($d) => Carbon::parse($d)->format($fmt))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8, 'font' => ['size' => 10]]],
                'y' => ['grid' => ['color' => 'rgba(14,14,12,0.04)'], 'ticks' => ['font' => ['size' => 10], 'color' => '#9A9A92'], 'beginAtZero' => true, 'border' => ['display' => false]],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'pointStyle' => 'circle', 'padding' => 16, 'font' => ['size' => 11]]],
            ],
            'interaction' => ['intersect' => false, 'mode' => 'index'],
        ];
    }

    private function getStart(): Carbon
    {
        return match ($this->timeRange) {
            '7d' => now()->subDays(7),
            '90d' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };
    }
}
