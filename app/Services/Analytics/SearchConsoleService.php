<?php

namespace App\Services\Analytics;

use App\Models\Country;
use App\Models\SearchConsoleData;
use Google\Client as GoogleClient;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;

class SearchConsoleService
{
    private SearchConsole $service;

    public function __construct()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(config('services.google.credentials_path'));
        $client->addScope(SearchConsole::WEBMASTERS_READONLY);

        $this->service = new SearchConsole($client);
    }

    public function syncQueryData(Country $country, string $startDate, string $endDate): void
    {
        $siteUrl = $country->gsc_property_url;
        if (!$siteUrl) return;

        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['query', 'page', 'date']);
        $request->setRowLimit(1000);
        $request->setStartRow(0);

        $response = $this->service->searchanalytics->query($siteUrl, $request);

        foreach ($response->getRows() as $row) {
            $keys = $row->getKeys();

            SearchConsoleData::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'date' => $keys[2],
                    'query' => $keys[0],
                    'page' => $keys[1],
                ],
                [
                    'clicks' => $row->getClicks(),
                    'impressions' => $row->getImpressions(),
                    'ctr' => round($row->getCtr() * 100, 2),
                    'position' => round($row->getPosition(), 1),
                ]
            );
        }
    }

    public function syncAllCountries(string $startDate, string $endDate): array
    {
        $results = [];
        $countries = Country::active()->whereNotNull('gsc_property_url')->get();

        foreach ($countries as $country) {
            try {
                $this->syncQueryData($country, $startDate, $endDate);
                $results[$country->code] = 'success';
            } catch (\Exception $e) {
                $results[$country->code] = "error: {$e->getMessage()}";
            }
        }

        return $results;
    }

    public function getTopQueries(Country $country, int $limit = 20): array
    {
        return SearchConsoleData::topQueries($country->id, $limit)->get()->toArray();
    }

    public function getTopPages(Country $country, int $limit = 20): array
    {
        return SearchConsoleData::topPages($country->id, $limit)->get()->toArray();
    }
}
