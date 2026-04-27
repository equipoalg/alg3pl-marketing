<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Task;
use App\Observers\LeadObserver;
use App\Observers\TaskObserver;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Lead::observe(LeadObserver::class);
        Task::observe(TaskObserver::class);

        // Spanish locale for Carbon → "hace 3 horas" instead of "3 hours ago"
        Carbon::setLocale('es');
        CarbonImmutable::setLocale('es');
    }
}
