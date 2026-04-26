<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\Lead;
use App\Services\Email\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Per-recipient worker for an EmailCampaign send. Dispatched by
 * Campaign::dispatchEmail() (one job per lead) so failures isolate.
 *
 * On shared hosting where queue:work runs --once via cron, leads will
 * be sent in batches as the worker tick processes them.
 */
class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 60;

    public function __construct(
        public EmailCampaign $emailCampaign,
        public Lead $lead,
    ) {}

    public function handle(EmailService $email): void
    {
        $email->sendCampaignToLead($this->emailCampaign, $this->lead);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendCampaignEmailJob failed permanently', [
            'lead_id'           => $this->lead->id,
            'email_campaign_id' => $this->emailCampaign->id,
            'error'             => $e->getMessage(),
        ]);
        $this->emailCampaign->increment('bounce_count');
    }
}
