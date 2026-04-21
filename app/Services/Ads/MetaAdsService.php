<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAdsService
{
    private const API_BASE  = 'https://graph.facebook.com/v18.0';
    private const TIMEOUT   = 15;

    /**
     * Fetch campaign insights for the last 30 days.
     *
     * Returns an array of campaign metrics, or [] on error / missing credentials.
     *
     * Each item:
     *   campaign_name, impressions, clicks, spend, date_start, date_stop
     */
    public function getCampaignInsights(string $adAccountId = ''): array
    {
        $token     = config('services.meta.page_access_token', env('META_PAGE_ACCESS_TOKEN', ''));
        $accountId = $adAccountId ?: config('services.meta.ad_account_id', env('META_AD_ACCOUNT_ID', ''));

        if (empty($token) || empty($accountId)) {
            Log::debug('MetaAdsService: credentials missing, skipping sync.');
            return [];
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::API_BASE . '/act_' . $accountId . '/insights', [
                    'access_token' => $token,
                    'fields'       => 'campaign_name,impressions,clicks,spend,date_start,date_stop',
                    'date_preset'  => 'last_30d',
                    'level'        => 'campaign',
                ]);

            if ($response->failed()) {
                Log::warning('MetaAdsService: API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            $data = $response->json('data', []);

            return array_map(fn ($row) => [
                'campaign_name' => $row['campaign_name'] ?? 'Unknown',
                'impressions'   => (int) ($row['impressions'] ?? 0),
                'clicks'        => (int) ($row['clicks'] ?? 0),
                'spend'         => (float) ($row['spend'] ?? 0),
                'date_start'    => $row['date_start'] ?? now()->subDays(30)->toDateString(),
                'date_stop'     => $row['date_stop'] ?? now()->toDateString(),
            ], $data);

        } catch (\Throwable $e) {
            Log::error('MetaAdsService: exception during sync', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
