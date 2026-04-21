<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

class SalesPipelineWidget extends ChartWidget
{
    protected ?string $maxHeight = '260px';
    protected int|string|array $columnSpan = 1;

    public ?string $countryFilter = '';
    public string $timeRange = '30d';

    public function getHeading(): string
    {
        return 'Sales Pipeline';
    }

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
        $this->timeRange     = session('time_range', '30d');
    }

    #[On('timeRangeUpdated')]
    public function onTimeRangeUpdated(string $timeRange): void
    {
        $this->timeRange = $timeRange;
    }

    public function getDescription(): ?string
    {
        $cacheKey = 'alg_pipeline_desc_' . ($this->countryFilter ?? 'global') . '_' . $this->timeRange;

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $q = Lead::query();
            if ($this->countryFilter) {
                $q->where('country_id', $this->countryFilter);
            }

            $total = (clone $q)->count();
            if ($total === 0) {
                return 'Sin leads todavía — conecta FluentForm para empezar';
            }

            $won  = (clone $q)->where('status', 'won')->count();
            $open = (clone $q)->whereNotIn('status', ['won', 'lost'])->count();

            if ($won > 0) {
                $rate = round(($won / $total) * 100);
                return "Tasa de cierre {$rate}% · {$open} oportunidades abiertas";
            }

            return "{$open} oportunidades activas · {$total} total en pipeline";
        });
    }

    public static function getSort(): int
    {
        return 5;
    }

    protected function getData(): array
    {
        $cacheKey = 'alg_pipeline_data_' . ($this->countryFilter ?? 'global') . '_' . $this->timeRange;

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $stages = [
                'new'         => ['label' => 'New',         'color' => '#D7D3C7'],
                'contacted'   => ['label' => 'Contacted',   'color' => '#B8B3A0'],
                'qualified'   => ['label' => 'Qualified',   'color' => '#9A9A92'],
                'proposal'    => ['label' => 'Proposal',    'color' => '#6B6B50'],
                'negotiation' => ['label' => 'Negotiation', 'color' => '#3A3A36'],
                'won'         => ['label' => 'Won',         'color' => '#0E0E0C'],
            ];

            $counts = [];
            $colors = [];
            $labels = [];

            foreach ($stages as $key => $meta) {
                $q = Lead::where('status', $key);
                if ($this->countryFilter) {
                    $q->where('country_id', $this->countryFilter);
                }
                $counts[] = $q->count();
                $colors[] = $meta['color'];
                $labels[] = $meta['label'];
            }

            return [
                'datasets' => [['data' => $counts, 'backgroundColor' => $colors, 'borderRadius' => 0, 'borderWidth' => 0, 'barPercentage' => 0.7]],
                'labels'   => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales'    => [
                'x' => ['grid' => ['color' => 'rgba(14,14,12,0.04)'], 'ticks' => ['font' => ['size' => 10], 'color' => '#9A9A92'], 'beginAtZero' => true, 'border' => ['display' => false]],
                'y' => ['grid' => ['display' => false], 'ticks' => ['font' => ['size' => 11, 'weight' => 500]]],
            ],
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}
