<?php

namespace App\Jobs;

use App\Models\Country;
use App\Services\Analytics\SearchConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSearchConsoleData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public ?int $countryId = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {}

    public function handle(SearchConsoleService $gsc): void
    {
        $startDate = $this->startDate ?? now()->subDays(7)->format('Y-m-d');
        $endDate = $this->endDate ?? now()->format('Y-m-d');

        if ($this->countryId) {
            $country = Country::find($this->countryId);
            if ($country) {
                Log::info("Syncing GSC for {$country->code}: {$startDate} to {$endDate}");
                $gsc->syncQueryData($country, $startDate, $endDate);
            }
        } else {
            Log::info("Syncing GSC for all countries: {$startDate} to {$endDate}");
            $gsc->syncAllCountries($startDate, $endDate);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SyncSearchConsoleData job failed: " . $exception->getMessage());
    }
}
