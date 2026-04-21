<?php

namespace App\Services\Client;

use App\Models\Client;
use App\Models\Interaction;
use App\Models\ScheduledMaintenance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthMonitorService
{
    /**
     * Recalculate health score for a client.
     */
    public function calculateHealth(Client $client): int
    {
        $score = 50; // base score

        // Recent interactions (last 30 days) — positive signal
        $recentInteractions = $client->interactions()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $score += min(15, $recentInteractions * 3);

        // Interaction sentiment
        $positiveCount = $client->interactions()
            ->where('created_at', '>=', now()->subDays(90))
            ->where('outcome', 'positive')
            ->count();
        $negativeCount = $client->interactions()
            ->where('created_at', '>=', now()->subDays(90))
            ->where('outcome', 'negative')
            ->count();
        $score += ($positiveCount * 3) - ($negativeCount * 5);

        // Contract status
        if ($client->contract_end) {
            $daysUntilExpiry = now()->diffInDays($client->contract_end, false);
            if ($daysUntilExpiry < 0) {
                $score -= 20; // expired
            } elseif ($daysUntilExpiry < 30) {
                $score -= 10; // expiring soon
            } elseif ($daysUntilExpiry > 180) {
                $score += 10; // long-term
            }
        }

        // Revenue trend
        if ($client->monthly_volume > 0) {
            $score += 5;
        }

        // Overdue tasks penalty
        $overdueCount = $client->maintenances()
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();
        $score -= $overdueCount * 5;

        // Complaints penalty
        $complaints = $client->interactions()
            ->where('type', 'complaint')
            ->where('created_at', '>=', now()->subDays(90))
            ->count();
        $score -= $complaints * 8;

        return max(0, min(100, $score));
    }

    /**
     * Recalculate all client health scores.
     */
    public function recalculateAll(): int
    {
        $count = 0;
        Client::where('status', 'active')->chunk(50, function ($clients) use (&$count) {
            foreach ($clients as $client) {
                $newScore = $this->calculateHealth($client);
                if ($client->health_score !== $newScore) {
                    $client->update(['health_score' => $newScore]);
                    $count++;
                }
            }
        });
        return $count;
    }

    /**
     * Get at-risk clients (health < 40).
     */
    public function getAtRiskClients(?int $tenantId = null): Collection
    {
        return Client::where('status', 'active')
            ->where('health_score', '<', 40)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('health_score')
            ->get();
    }

    /**
     * Predict churn probability based on health score and trends.
     */
    public function churnProbability(Client $client): array
    {
        $score = $client->health_score;
        $probability = match (true) {
            $score < 20 => 0.85,
            $score < 40 => 0.60,
            $score < 60 => 0.30,
            $score < 80 => 0.10,
            default => 0.05,
        };

        // Factors
        $factors = [];
        if ($client->contract_end && $client->isContractExpiringSoon()) {
            $factors[] = 'Contract expiring within 30 days';
            $probability = min(1, $probability + 0.15);
        }

        $recentNegative = $client->interactions()
            ->where('outcome', 'negative')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        if ($recentNegative > 0) {
            $factors[] = "{$recentNegative} negative interactions in last 30 days";
            $probability = min(1, $probability + 0.1 * $recentNegative);
        }

        $lastInteraction = $client->interactions()->latest()->first();
        if ($lastInteraction && $lastInteraction->created_at->diffInDays(now()) > 60) {
            $factors[] = 'No interaction in 60+ days';
            $probability = min(1, $probability + 0.2);
        }

        return [
            'probability' => round($probability, 2),
            'risk_level' => $probability >= 0.6 ? 'high' : ($probability >= 0.3 ? 'medium' : 'low'),
            'health_score' => $score,
            'factors' => $factors,
            'recommendation' => $this->getRecommendation($probability, $factors),
        ];
    }

    private function getRecommendation(float $probability, array $factors): string
    {
        if ($probability >= 0.6) {
            return 'Immediate intervention required. Schedule executive-level meeting and prepare retention offer.';
        }
        if ($probability >= 0.3) {
            return 'Proactive outreach recommended. Schedule service review and gather feedback.';
        }
        return 'Client is healthy. Maintain regular check-in schedule.';
    }
}
