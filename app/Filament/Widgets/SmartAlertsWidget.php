<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Task;
use App\Models\AnalyticsSnapshot;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class SmartAlertsWidget extends Widget
{
    protected string $view = 'filament.widgets.smart-alerts';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -1;

    public ?string $countryFilter = '';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    public function getViewData(): array
    {
        $cacheKey = 'alg_alerts_' . ($this->countryFilter ?? 'global');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            $alerts = [];
            $f      = $this->countryFilter ?: null;

            $staleP0 = Lead::where('status', 'new')
                ->where('created_at', '<', now()->subHours(48))
                ->when($f, fn($q) => $q->where('country_id', $f))
                ->count();
            if ($staleP0 > 0) {
                $alerts[] = ['type' => 'danger', 'icon' => 'fire',
                    'text' => "{$staleP0} leads nuevos sin contactar hace +48h"];
            }

            $overdue = Task::overdue()->when($f, fn($q) => $q->where('country_id', $f))->count();
            if ($overdue > 0) {
                $alerts[] = ['type' => 'warning', 'icon' => 'clock',
                    'text' => "{$overdue} tareas vencidas necesitan atención"];
            }

            $recent = AnalyticsSnapshot::where('date', '>=', now()->subDays(7))
                ->when($f, fn($q) => $q->where('country_id', $f))->sum('users');
            $prev   = AnalyticsSnapshot::whereBetween('date', [now()->subDays(14), now()->subDays(7)])
                ->when($f, fn($q) => $q->where('country_id', $f))->sum('users');
            if ($prev > 0) {
                $delta = round(($recent - $prev) / $prev * 100, 1);
                if ($delta < -15) {
                    $alerts[] = ['type' => 'warning', 'icon' => 'arrow-trending-down',
                        'text' => "Tráfico cayó {$delta}% vs semana anterior"];
                }
            }

            $lastSync = AnalyticsSnapshot::max('date');
            if ($lastSync && \Carbon\Carbon::parse($lastSync)->diffInDays(now()) > 3) {
                $alerts[] = ['type' => 'info', 'icon' => 'arrow-path',
                    'text' => "Datos de analytics sin sincronizar hace " . \Carbon\Carbon::parse($lastSync)->diffInDays(now()) . " días"];
            }

            return ['alerts' => $alerts];
        });
    }
}
