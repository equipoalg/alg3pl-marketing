<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Ads insights service. Mirrors MetaAdsService's surface so the
 * dashboard's AdMetricsWidget can consume both interchangeably.
 *
 * Google Ads API is OAuth-based and requires:
 *   GOOGLE_ADS_DEVELOPER_TOKEN     (apply at ads.google.com/apis/manage)
 *   GOOGLE_ADS_CUSTOMER_ID         (10-digit MCC or sub-account ID, no dashes)
 *   GOOGLE_ADS_LOGIN_CUSTOMER_ID   (manager account ID, optional)
 *   GOOGLE_ADS_OAUTH_CLIENT_ID
 *   GOOGLE_ADS_OAUTH_CLIENT_SECRET
 *   GOOGLE_ADS_REFRESH_TOKEN       (long-lived OAuth refresh token)
 *
 * Without those env vars, every call returns [] gracefully (no exceptions).
 *
 * NOTE: This implementation uses the REST endpoint with OAuth refresh-token
 * flow. For full GAQL features, swap to googleads/google-ads-php SDK.
 */
class GoogleAdsService
{
    private const API_VERSION = 'v18';
    private const TIMEOUT     = 20;

    /**
     * Returns last-30-day campaign insights, same shape as MetaAdsService:
     *   [{campaign_name, impressions, clicks, spend, date_start, date_stop}, ...]
     */
    public function getCampaignInsights(): array
    {
        $devToken      = config('services.google_ads.developer_token', env('GOOGLE_ADS_DEVELOPER_TOKEN'));
        $customerId    = config('services.google_ads.customer_id',     env('GOOGLE_ADS_CUSTOMER_ID'));
        $loginCustomer = config('services.google_ads.login_customer_id', env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'));
        $clientId      = config('services.google_ads.oauth_client_id',     env('GOOGLE_ADS_OAUTH_CLIENT_ID'));
        $clientSecret  = config('services.google_ads.oauth_client_secret', env('GOOGLE_ADS_OAUTH_CLIENT_SECRET'));
        $refreshToken  = config('services.google_ads.refresh_token',       env('GOOGLE_ADS_REFRESH_TOKEN'));

        if (! $devToken || ! $customerId || ! $clientId || ! $clientSecret || ! $refreshToken) {
            Log::debug('GoogleAdsService: credentials missing, skipping sync.');
            return [];
        }

        // Step 1: exchange refresh token for an access token
        $accessToken = $this->refreshAccessToken($clientId, $clientSecret, $refreshToken);
        if (! $accessToken) {
            return [];
        }

        // Step 2: query campaign performance via GAQL
        $gaql = "SELECT
                    campaign.name,
                    metrics.impressions,
                    metrics.clicks,
                    metrics.cost_micros,
                    segments.date
                 FROM campaign
                 WHERE segments.date DURING LAST_30_DAYS
                 ORDER BY metrics.cost_micros DESC
                 LIMIT 50";

        try {
            $headers = [
                'Authorization'    => "Bearer {$accessToken}",
                'developer-token'  => $devToken,
                'Content-Type'     => 'application/json',
            ];
            if ($loginCustomer) {
                $headers['login-customer-id'] = preg_replace('/\D/', '', $loginCustomer);
            }

            $cleanCustomer = preg_replace('/\D/', '', $customerId);
            $url = "https://googleads.googleapis.com/" . self::API_VERSION . "/customers/{$cleanCustomer}/googleAds:search";

            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders($headers)
                ->post($url, ['query' => $gaql]);

            if ($response->failed()) {
                Log::warning('GoogleAdsService: API error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 400),
                ]);
                return [];
            }

            $rows = $response->json('results', []);
            return array_map(function ($row) {
                $costMicros = (int) ($row['metrics']['costMicros'] ?? 0);
                return [
                    'campaign_name' => $row['campaign']['name'] ?? '—',
                    'impressions'   => (int) ($row['metrics']['impressions'] ?? 0),
                    'clicks'        => (int) ($row['metrics']['clicks'] ?? 0),
                    'spend'         => round($costMicros / 1_000_000, 2),  // micros → currency units
                    'date_start'    => $row['segments']['date'] ?? null,
                    'date_stop'     => $row['segments']['date'] ?? null,
                ];
            }, $rows);
        } catch (\Throwable $e) {
            Log::warning('GoogleAdsService: request failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /** Exchange a long-lived refresh token for a short-lived access token. */
    private function refreshAccessToken(string $clientId, string $clientSecret, string $refreshToken): ?string
    {
        try {
            $response = Http::asForm()->timeout(self::TIMEOUT)->post('https://oauth2.googleapis.com/token', [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ]);
            if ($response->failed()) {
                Log::warning('GoogleAdsService: OAuth refresh failed', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 200),
                ]);
                return null;
            }
            return $response->json('access_token');
        } catch (\Throwable $e) {
            Log::warning('GoogleAdsService: OAuth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
