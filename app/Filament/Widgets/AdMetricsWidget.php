<?php

namespace App\Filament\Widgets;

use App\Jobs\SyncAdMetricsJob;
use App\Models\AdMetric;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class AdMetricsWidget extends Widget
{
    protected static ?int $sort = 8;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.ad-metrics';

    public ?string $countryFilter = '';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    #[On('countryFilterUpdated')]
    public function onCountryFilterUpdated(string $countryFilter): void
    {
        $this->countryFilter = $countryFilter;
    }

    public function triggerSync(): void
    {
        SyncAdMetricsJob::dispatch($this->countryFilter ? (int) $this->countryFilter : null);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Sincronización iniciada. Los datos se actualizarán en breve.',
        ]);
    }

    public function getViewData(): array
    {
        $query = AdMetric::recent(30);

        if ($this->countryFilter) {
            $query->byCountry((int) $this->countryFilter);
        }

        $records = $query->get();

        $platforms = ['google', 'meta', 'linkedin'];
        $byPlatform = [];

        foreach ($platforms as $platform) {
            $rows = $records->where('platform', $platform);

            $spend  = $rows->sum('spend');
            $leads  = $rows->sum('leads_generated');
            $clicks = $rows->sum('clicks');
            $imps   = $rows->sum('impressions');
            $cpl    = ($leads > 0 && $spend > 0) ? round($spend / $leads, 2) : null;
            $ctr    = ($imps > 0) ? round(($clicks / $imps) * 100, 2) : 0;

            $byPlatform[$platform] = [
                'spend'  => $spend,
                'leads'  => $leads,
                'cpl'    => $cpl,
                'ctr'    => $ctr,
                'clicks' => $clicks,
                'imps'   => $imps,
                'count'  => $rows->count(),
            ];
        }

        $totals = [
            'spend' => $records->sum('spend'),
            'leads' => $records->sum('leads_generated'),
            'cpl'   => null,
        ];

        if ($totals['leads'] > 0 && $totals['spend'] > 0) {
            $totals['cpl'] = round($totals['spend'] / $totals['leads'], 2);
        }

        $lastSync = $records->max('synced_at');

        return [
            'byPlatform'    => $byPlatform,
            'totals'        => $totals,
            'hasData'       => $records->isNotEmpty(),
            'lastSync'      => $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : null,
        ];
    }
}
