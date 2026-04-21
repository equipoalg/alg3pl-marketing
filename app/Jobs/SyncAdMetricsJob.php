<?php

namespace App\Jobs;

use App\Models\AdMetric;
use App\Models\Country;
use App\Services\Ads\MetaAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAdMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly ?int $countryId = null
    ) {}

    public function handle(MetaAdsService $metaService): void
    {
        Log::info('SyncAdMetricsJob: starting Meta Ads sync', ['country_id' => $this->countryId]);

        $campaigns = $metaService->getCampaignInsights();

        if (empty($campaigns)) {
            Log::info('SyncAdMetricsJob: no data returned from Meta Ads API.');
            return;
        }

        // Use the provided country or the first active country as a default
        $countryId = $this->countryId ?? Country::where('is_active', true)->value('id');

        if (! $countryId) {
            Log::warning('SyncAdMetricsJob: no country_id available, aborting.');
            return;
        }

        $synced = 0;

        foreach ($campaigns as $row) {
            $cpl = null;
            if (! empty($row['leads']) && $row['leads'] > 0 && $row['spend'] > 0) {
                $cpl = round($row['spend'] / $row['leads'], 2);
            }

            AdMetric::updateOrCreate(
                [
                    'country_id'    => $countryId,
                    'platform'      => 'meta',
                    'campaign_name' => $row['campaign_name'],
                    'period_start'  => $row['date_start'],
                    'period_end'    => $row['date_stop'],
                ],
                [
                    'impressions'     => $row['impressions'],
                    'clicks'          => $row['clicks'],
                    'spend'           => $row['spend'],
                    'leads_generated' => $row['leads'] ?? 0,
                    'cost_per_lead'   => $cpl,
                    'synced_at'       => now(),
                ]
            );

            $synced++;
        }

        Log::info('SyncAdMetricsJob: completed.', ['records_synced' => $synced]);
    }

    /**
     * Convenience factory — dispatch for a specific country or globally.
     */
    public static function dispatchForCountry(?int $countryId = null): void
    {
        static::dispatch($countryId);
    }
}
