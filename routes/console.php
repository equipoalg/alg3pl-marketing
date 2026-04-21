<?php

use App\Jobs\SyncAnalyticsData;
use App\Jobs\SyncSearchConsoleData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily GA4 sync — runs at 3:00 AM
Schedule::job(new SyncAnalyticsData())->dailyAt('03:00');

// Daily GSC sync — runs at 3:30 AM
Schedule::job(new SyncSearchConsoleData())->dailyAt('03:30');

// Weekly full historical sync (last 90 days) — Sunday 2:00 AM
Schedule::job(new SyncAnalyticsData(
    startDate: now()->subDays(90)->format('Y-m-d'),
    endDate: now()->format('Y-m-d')
))->weekly()->sundays()->at('02:00');
