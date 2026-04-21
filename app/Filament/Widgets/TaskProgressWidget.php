<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class TaskProgressWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    public ?string $countryFilter = '';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    #[On('timeRangeUpdated')]
    public function onTimeRangeUpdated(string $timeRange): void
    {
        // Tasks don't change by time range but we listen to avoid errors
    }

    protected function getStats(): array
    {
        $q = Task::query();
        if ($this->countryFilter) {
            $q->where('country_id', $this->countryFilter);
        }

        $total = (clone $q)->count();
        $done = (clone $q)->where('status', 'done')->count();
        $inProgress = (clone $q)->where('status', 'in_progress')->count();
        $pending = (clone $q)->where('status', 'pending')->count();
        $blocked = (clone $q)->where('status', 'blocked')->count();
        $overdue = (clone $q)->overdue()->count();

        $p0 = (clone $q)->where('priority', 'P0')->where('status', '!=', 'done')->count();
        $pct = $total > 0 ? round(($done / $total) * 100) : 0;

        return [
            Stat::make('Task Progress', "{$pct}%")
                ->description("{$done}/{$total} completed")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($pct >= 80 ? 'success' : ($pct >= 40 ? 'warning' : 'danger')),

            Stat::make('P0 Critical', (string) $p0)
                ->description('tasks need attention')
                ->descriptionIcon('heroicon-m-fire')
                ->color($p0 > 0 ? 'danger' : 'success'),

            Stat::make('In Progress', (string) $inProgress)
                ->description("{$pending} pending · {$blocked} blocked")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Overdue', (string) $overdue)
                ->description('past due date')
                ->descriptionIcon('heroicon-m-clock')
                ->color($overdue > 0 ? 'danger' : 'success'),
        ];
    }
}
