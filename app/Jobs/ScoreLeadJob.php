<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\Lead\LeadScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScoreLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    public function __construct(
        public readonly Lead $lead,
    ) {}

    public function handle(LeadScoringService $scorer): void
    {
        // Re-fetch fresh model in case it changed since dispatch
        $lead = $this->lead->fresh();

        if ($lead === null) {
            // Lead was deleted before the job ran — nothing to do
            return;
        }

        $lead->score = $scorer->calculate($lead);
        $lead->saveQuietly();
    }
}
