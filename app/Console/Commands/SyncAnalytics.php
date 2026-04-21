<?php

namespace App\Console\Commands;

use App\Models\AnalyticsSnapshot;
use App\Models\Country;
use Illuminate\Console\Command;

class SyncAnalytics extends Command
{
    protected $signature = 'analytics:sync {--country= : Country code to sync}';
    protected $description = 'Sync GA4 and GSC data for all countries';

    public function handle(): int
    {
        $countries = Country::active()->where('is_regional', false)->get();
        if ($code = $this->option('country')) {
            $countries = $countries->where('code', $code);
        }

        foreach ($countries as $country) {
            $this->info("Syncing {$country->code} ({$country->name})...");

            if (!$country->ga4_property_id) {
                $this->warn("  No GA4 property ID configured, skipping.");
                continue;
            }

            try {
                // TODO: Implement actual Google Analytics Data API call
                $this->info("  GA4 sync: pending API implementation");
                $this->info("  GSC sync: pending API implementation");
            } catch (\Throwable $e) {
                $this->error("  Error: {$e->getMessage()}");
            }
        }

        $this->info("Sync complete.");
        return Command::SUCCESS;
    }
}
