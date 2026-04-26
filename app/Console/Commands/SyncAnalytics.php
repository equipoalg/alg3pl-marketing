<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\Analytics\GoogleAnalyticsService;
use App\Services\Analytics\SearchConsoleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncAnalytics extends Command
{
    protected $signature = 'analytics:sync
        {--country= : Country code to sync (default: all active countries)}
        {--days=1 : Number of past days to sync, ending yesterday (default: 1)}
        {--start= : Override start date (Y-m-d format)}
        {--end= : Override end date (Y-m-d format)}
        {--gsc : Also sync Google Search Console data}
        {--no-ga : Skip GA4 sync (useful with --gsc to only run GSC)}';

    protected $description = 'Sync GA4 (and optionally GSC) data for one or all active countries';

    public function handle(GoogleAnalyticsService $ga, SearchConsoleService $gsc): int
    {
        // Resolve date range
        if ($this->option('start') || $this->option('end')) {
            $startDate = $this->option('start') ?? now()->subDay()->format('Y-m-d');
            $endDate   = $this->option('end')   ?? now()->subDay()->format('Y-m-d');
        } else {
            $days      = max(1, (int) $this->option('days'));
            $endDate   = now()->subDay()->format('Y-m-d');
            $startDate = now()->subDays($days)->format('Y-m-d');
        }

        // Resolve target countries
        $query = Country::active()->where('is_regional', false);
        if ($code = $this->option('country')) {
            $query->where('code', strtolower($code));
        }
        $countries = $query->get();

        if ($countries->isEmpty()) {
            $this->error('No active non-regional countries match.');
            return self::FAILURE;
        }

        $this->info("Syncing {$startDate} → {$endDate} for {$countries->count()} country/ies");
        $this->newLine();

        $hadError = false;

        foreach ($countries as $country) {
            $this->line("─── {$country->code} · {$country->name} ───");

            // GA4
            if (! $this->option('no-ga')) {
                if (! $country->ga4_property_id) {
                    $this->warn("  GA4: skip (no ga4_property_id configured)");
                } else {
                    $results = $ga->syncDateRange($country, $startDate, $endDate);
                    $this->printDailyResults('  GA4', $results, $hadError);
                }
            }

            // GSC
            if ($this->option('gsc')) {
                if (! $country->gsc_property_url) {
                    $this->warn("  GSC: skip (no gsc_property_url configured)");
                } else {
                    try {
                        $gsc->syncQueryData($country, $startDate, $endDate);
                        $this->info("  GSC: synced {$startDate} → {$endDate}");
                    } catch (\Throwable $e) {
                        $hadError = true;
                        $this->error("  GSC: " . $e->getMessage());
                    }
                }
            }

            $this->newLine();
        }

        if ($hadError) {
            $this->warn('Sync completed with errors. See output above.');
            return self::FAILURE;
        }

        $this->info('Sync complete.');
        return self::SUCCESS;
    }

    private function printDailyResults(string $prefix, array $results, bool &$hadError): void
    {
        $ok = collect($results)->filter(fn ($v) => $v === 'success')->count();
        $err = count($results) - $ok;

        if ($err === 0) {
            $this->info("{$prefix}: {$ok}/{$ok} days OK");
            return;
        }

        $hadError = true;
        $this->warn("{$prefix}: {$ok}/" . count($results) . " days OK · {$err} errors");
        foreach ($results as $date => $status) {
            if ($status !== 'success') {
                $this->line("    {$date}  {$status}");
            }
        }
    }
}
