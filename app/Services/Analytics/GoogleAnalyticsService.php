<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;

class GoogleAnalyticsService
{
    private BetaAnalyticsDataClient $client;

    public function __construct()
    {
        $this->client = new BetaAnalyticsDataClient([
            'credentials' => config('services.google.credentials_path'),
        ]);
    }

    public function syncDailyData(Country $country, string $date): void
    {
        $propertyId = $country->ga4_property_id;
        if (!$propertyId) return;

        $request = new RunReportRequest([
            'property' => "properties/{$propertyId}",
            'date_ranges' => [new DateRange(['start_date' => $date, 'end_date' => $date])],
            'dimensions' => [
                new Dimension(['name' => 'sessionDefaultChannelGroup']),
            ],
            'metrics' => [
                new Metric(['name' => 'activeUsers']),
                new Metric(['name' => 'newUsers']),
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'averageSessionDuration']),
                new Metric(['name' => 'bounceRate']),
                new Metric(['name' => 'conversions']),
            ],
        ]);
        $response = $this->client->runReport($request);

        $data = [
            'country_id' => $country->id,
            'date' => $date,
            'users' => 0,
            'new_users' => 0,
            'sessions' => 0,
            'page_views' => 0,
            'avg_session_duration' => 0,
            'bounce_rate' => 0,
            'organic_users' => 0,
            'direct_users' => 0,
            'referral_users' => 0,
            'social_users' => 0,
            'paid_users' => 0,
            'conversions' => 0,
        ];

        foreach ($response->getRows() as $row) {
            $channel = $row->getDimensionValues()[0]->getValue();
            $users = (int) $row->getMetricValues()[0]->getValue();
            $newUsers = (int) $row->getMetricValues()[1]->getValue();
            $sessions = (int) $row->getMetricValues()[2]->getValue();
            $pageViews = (int) $row->getMetricValues()[3]->getValue();
            $avgDuration = (int) $row->getMetricValues()[4]->getValue();
            $bounceRate = (float) $row->getMetricValues()[5]->getValue();
            $conversions = (int) $row->getMetricValues()[6]->getValue();

            $data['users'] += $users;
            $data['new_users'] += $newUsers;
            $data['sessions'] += $sessions;
            $data['page_views'] += $pageViews;
            $data['avg_session_duration'] = $avgDuration;
            $data['bounce_rate'] = $bounceRate;
            $data['conversions'] += $conversions;

            match (strtolower($channel)) {
                'organic search' => $data['organic_users'] += $users,
                'direct' => $data['direct_users'] += $users,
                'referral' => $data['referral_users'] += $users,
                'organic social' => $data['social_users'] += $users,
                'paid search', 'paid social' => $data['paid_users'] += $users,
                default => null,
            };
        }

        AnalyticsSnapshot::updateOrCreate(
            ['country_id' => $country->id, 'date' => $date],
            $data
        );
    }

    public function syncAllCountries(string $date): array
    {
        $results = [];
        $countries = Country::active()->get();

        foreach ($countries as $country) {
            try {
                $this->syncDailyData($country, $date);
                $results[$country->code] = 'success';
            } catch (\Exception $e) {
                $results[$country->code] = "error: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Sync a date range for a single country (inclusive on both ends).
     * Loops syncDailyData per day; safe to re-run (uses updateOrCreate).
     */
    public function syncDateRange(Country $country, string $startDate, string $endDate): array
    {
        $results = [];
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $iso = $d->format('Y-m-d');
            try {
                $this->syncDailyData($country, $iso);
                $results[$iso] = 'success';
            } catch (\Throwable $e) {
                $results[$iso] = "error: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Sync a date range across all active countries.
     * Returns shape: ['country_code' => ['date' => 'success'|'error: ...']]
     */
    public function syncAllCountriesRange(string $startDate, string $endDate): array
    {
        $results = [];
        foreach (Country::active()->get() as $country) {
            $results[$country->code] = $this->syncDateRange($country, $startDate, $endDate);
        }
        return $results;
    }
}
