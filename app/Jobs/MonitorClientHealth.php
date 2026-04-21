<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\LeadActivity;
use App\Services\Client\HealthMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorClientHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(HealthMonitorService $service): void
    {
        $updated = $service->recalculateAll();
        Log::info("Client health scores recalculated", ['updated' => $updated]);

        // Alert for at-risk clients
        $atRisk = $service->getAtRiskClients();
        foreach ($atRisk as $client) {
            $churn = $service->churnProbability($client);

            if ($churn['risk_level'] === 'high') {
                Log::warning("High churn risk: {$client->company_name}", $churn);

                // Create an alert activity for the assigned user
                if ($client->assigned_to) {
                    // Use the interactions table for client alerts
                    $client->interactions()->create([
                        'tenant_id' => $client->tenant_id,
                        'user_id' => null,
                        'type' => 'note',
                        'subject' => "ALERT: High churn risk ({$churn['probability']}%)",
                        'body' => "Factors: " . implode(', ', $churn['factors']) . "\n\nRecommendation: " . $churn['recommendation'],
                        'outcome' => 'pending',
                    ]);
                }
            }
        }
    }
}
