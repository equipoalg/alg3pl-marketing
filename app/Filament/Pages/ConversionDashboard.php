<?php

namespace App\Filament\Pages;

use App\Models\Campaign;
use App\Models\Country;
use App\Models\Lead;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Illuminate\Support\Carbon;

/**
 * /admin/conversion — funnel + win-rate analytics.
 *
 * Reuses the same hero / chart / breakdown layout as AnalyticsTrafficDashboard
 * and SearchConsoleDashboard, but the data set is the lead funnel (Nuevo →
 * Contactado → Calificado → Propuesta → Ganado / Perdido). Country filter from
 * sidebar (session('country_filter')) scopes everything.
 *
 * The dashboard's "Conversión" KPI links here.
 */
class ConversionDashboard extends Page
{
    protected string $view = 'filament.pages.conversion-dashboard';
    protected Width|string|null $maxContentWidth = Width::Full;

    public string $period = '30d';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-funnel';
    }

    public static function getNavigationLabel(): string
    {
        return 'Conversión';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Analytics';
    }

    public static function getNavigationSort(): int
    {
        return 3;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'conversion';
    }

    public function getTitle(): string
    {
        return 'Conversión — Funnel y tasa de cierre';
    }

    public function setPeriod(string $value): void
    {
        if (in_array($value, ['7d', '30d', '90d', 'ytd'], true)) {
            $this->period = $value;
        }
    }

    public function getViewData(): array
    {
        $countryId = session('country_filter') ? (int) session('country_filter') : null;
        [$start, $end] = $this->resolvePeriod();

        // Base lead query for the period (scoped by country if active)
        $base = Lead::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId));

        // Funnel stages, ordered. We count leads created in the period that
        // CURRENTLY sit in each stage (or have moved past it for the cumulative
        // funnel viz).
        $stageOrder = [
            'new'         => 'Nuevo',
            'contacted'   => 'Contactado',
            'qualified'   => 'Calificado',
            'proposal'    => 'Propuesta',
            'negotiation' => 'Negociación',
            'won'         => 'Ganado',
        ];

        // Count of leads currently in each stage (snapshot)
        $stageCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        // Cumulative funnel: at each stage, how many leads have made it AT LEAST
        // that far (i.e. their current status is that stage OR a later one).
        $stageRank = ['new' => 0, 'contacted' => 1, 'qualified' => 2, 'proposal' => 3, 'negotiation' => 4, 'won' => 5];
        $allLeads = (clone $base)->get(['status']);
        $funnelStages = [];
        foreach ($stageOrder as $key => $label) {
            $r = $stageRank[$key];
            $reached = $allLeads->filter(function ($l) use ($r, $stageRank) {
                return ($stageRank[$l->status] ?? -1) >= $r;
            })->count();
            $funnelStages[] = [
                'key'     => $key,
                'label'   => $label,
                'count'   => $reached,
                'current' => (int) ($stageCounts[$key] ?? 0),
            ];
        }

        // KPIs
        $totalLeads     = (clone $base)->count();
        $wonLeads       = (clone $base)->where('status', 'won')->count();
        $lostLeads      = (clone $base)->where('status', 'lost')->count();
        $closedLeads    = $wonLeads + $lostLeads;
        $conversionRate = $closedLeads > 0 ? round(($wonLeads / $closedLeads) * 100, 1) : 0.0;
        // Velocity: average days from creation to last update for won leads (rough proxy)
        $velocityDays = (clone $base)
            ->where('status', 'won')
            ->whereColumn('updated_at', '>=', 'created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as days')
            ->value('days');
        $velocityDays = $velocityDays !== null ? round((float) $velocityDays, 1) : null;

        // Previous period comparison
        $days = $start->diffInDays($end) + 1;
        $prevStart = $start->copy()->subDays($days);
        $prevEnd   = $start->copy()->subDay();
        $prevBase = Lead::query()
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId));
        $prevWon    = (clone $prevBase)->where('status', 'won')->count();
        $prevLost   = (clone $prevBase)->where('status', 'lost')->count();
        $prevClosed = $prevWon + $prevLost;
        $prevRate   = $prevClosed > 0 ? round(($prevWon / $prevClosed) * 100, 1) : 0.0;
        $rateDelta  = $prevRate > 0 ? round($conversionRate - $prevRate, 1) : 0.0;

        // Weekly conversion rate series — last 13 weeks ending today.
        $weeklyRate = [];
        $weeklyLabels = [];
        for ($i = 12; $i >= 0; $i--) {
            $weekEnd   = now()->subWeeks($i);
            $weekStart = $weekEnd->copy()->subDays(7);
            $w = Lead::query()
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->when($countryId, fn ($q) => $q->where('country_id', $countryId));
            $wWon    = (clone $w)->where('status', 'won')->count();
            $wLost   = (clone $w)->where('status', 'lost')->count();
            $wClosed = $wWon + $wLost;
            $weeklyRate[] = $wClosed > 0 ? round(($wWon / $wClosed) * 100, 1) : 0.0;
            $weeklyLabels[] = $weekEnd->format('d M');
        }

        // Breakdown by source — count + win rate
        $bySource = (clone $base)
            ->selectRaw("
                COALESCE(NULLIF(source, ''), 'unknown') as source,
                COUNT(*) as total,
                SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) as won,
                SUM(CASE WHEN status='lost' THEN 1 ELSE 0 END) as lost
            ")
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(function ($r) {
                $closed = $r->won + $r->lost;
                $r->rate = $closed > 0 ? round(($r->won / $closed) * 100, 1) : 0.0;
                $r->source_label = $r->source === 'unknown'
                    ? '— (sin origen)'
                    : ucfirst(str_replace('_', ' ', $r->source));
                return $r;
            });

        // Breakdown by country (only when in Global view)
        $byCountry = collect();
        if (! $countryId) {
            $byCountry = Lead::query()
                ->whereBetween('created_at', [$start, $end])
                ->join('countries', 'leads.country_id', '=', 'countries.id')
                ->selectRaw("
                    countries.code as code,
                    countries.name as name,
                    COUNT(*) as total,
                    SUM(CASE WHEN leads.status='won' THEN 1 ELSE 0 END) as won,
                    SUM(CASE WHEN leads.status='lost' THEN 1 ELSE 0 END) as lost
                ")
                ->groupBy('countries.id', 'countries.code', 'countries.name')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(function ($r) {
                    $closed = $r->won + $r->lost;
                    $r->rate = $closed > 0 ? round(($r->won / $closed) * 100, 1) : 0.0;
                    return $r;
                });
        }

        return [
            'period'         => $this->period,
            'startDate'      => $start->format('d M Y'),
            'endDate'        => $end->format('d M Y'),
            'totalLeads'     => $totalLeads,
            'wonLeads'       => $wonLeads,
            'lostLeads'      => $lostLeads,
            'conversionRate' => $conversionRate,
            'rateDelta'      => $rateDelta,
            'velocityDays'   => $velocityDays,
            'funnelStages'   => $funnelStages,
            'weeklyRate'     => $weeklyRate,
            'weeklyLabels'   => $weeklyLabels,
            'bySource'       => $bySource,
            'byCountry'      => $byCountry,
            'isGlobal'       => ! $countryId,
        ];
    }

    /** @return array{0:Carbon,1:Carbon} */
    private function resolvePeriod(): array
    {
        $end = now()->endOfDay();
        $start = match ($this->period) {
            '7d'  => $end->copy()->subDays(7)->startOfDay(),
            '90d' => $end->copy()->subDays(90)->startOfDay(),
            'ytd' => $end->copy()->startOfYear(),
            default => $end->copy()->subDays(30)->startOfDay(),
        };
        return [$start, $end];
    }
}
