<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class LeadsByCountryWidget extends ChartWidget
{
    protected ?string $maxHeight = '260px';
    protected int|string|array $columnSpan = 1;

    public ?string $countryFilter = '';
    public string $timeRange = '30d';

    public function getHeading(): string
    {
        return $this->countryFilter ? 'Traffic Sources' : 'Users by Country';
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

        if ($this->countryFilter) {
            // Show source breakdown insight
            $top = AnalyticsSnapshot::where('date', '>=', $start)
                ->where('country_id', $this->countryFilter)
                ->selectRaw('SUM(organic_users) as org, SUM(direct_users) as dir, SUM(users) as total')
                ->first();
            if (!$top || !$top->total) return "Sin datos de tráfico los últimos {$days} días";
            $pct = round(($top->org / $top->total) * 100);
            return "Orgánico representa el {$pct}% del tráfico total";
        }

        // Top country by users
        $top = AnalyticsSnapshot::where('date', '>=', $start)
            ->selectRaw('country_id, SUM(users) as total')
            ->groupBy('country_id')
            ->orderByDesc('total')
            ->with('country')
            ->first();

        if (!$top) return "Sin datos los últimos {$days} días";
        $name = $top->country?->name ?? 'Desconocido';
        return "Líder: {$name} con " . number_format($top->total) . " usuarios";
    }

    public static function getSort(): int
    {
        return 3;
    }

    protected function getData(): array
    {
        $start = $this->getStart();

        if ($this->countryFilter) {
            return $this->getSourcesData($start);
        }
        return $this->getCountryData($start);
    }

    private function getCountryData(Carbon $start): array
    {
        $countries = Country::active()->where('is_regional', false)->get();
        $colors = ['#0E0E0C', '#3A3A36', '#6B6B50', '#9A9A92', '#B8B3A0', '#D7D3C7'];

        $labels = [];
        $data = [];
        foreach ($countries as $c) {
            $labels[] = strtoupper($c->code);
            $data[] = AnalyticsSnapshot::where('country_id', $c->id)->where('date', '>=', $start)->sum('users');
        }

        return [
            'datasets' => [['data' => $data, 'backgroundColor' => array_slice($colors, 0, count($data)), 'borderRadius' => 0, 'borderWidth' => 0, 'barPercentage' => 0.7]],
            'labels' => $labels,
        ];
    }

    private function getSourcesData(Carbon $start): array
    {
        $q = AnalyticsSnapshot::where('country_id', $this->countryFilter)->where('date', '>=', $start);

        $sources = [
            'Organic' => $q->clone()->sum('organic_users'),
            'Direct' => $q->clone()->sum('direct_users'),
            'Referral' => $q->clone()->sum('referral_users'),
            'Social' => $q->clone()->sum('social_users'),
            'Paid' => $q->clone()->sum('paid_users'),
        ];

        return [
            'datasets' => [['data' => array_values($sources), 'backgroundColor' => ['#0E0E0C', '#3A3A36', '#6B6B50', '#9A9A92', '#B8B3A0'], 'borderRadius' => 0, 'borderWidth' => 0, 'barPercentage' => 0.7]],
            'labels' => array_keys($sources),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['grid' => ['display' => false], 'ticks' => ['font' => ['size' => 11, 'weight' => 600]]],
                'y' => ['grid' => ['color' => 'rgba(14,14,12,0.04)'], 'ticks' => ['font' => ['size' => 10], 'color' => '#9A9A92'], 'beginAtZero' => true, 'border' => ['display' => false]],
            ],
            'plugins' => ['legend' => ['display' => false]],
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
