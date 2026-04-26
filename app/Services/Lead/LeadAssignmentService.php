<?php

namespace App\Services\Lead;

use App\Models\CountryConfig;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Assigns inbound leads to the right person based on country config:
 *
 *   - Hot lead (score >= 70):  primary_manager (if set)
 *   - Otherwise:                round-robin across webhook_assignees
 *   - Otherwise:                no assignment (null)
 *
 * Round-robin state is kept in cache per country (24h TTL) so consecutive
 * webhooks from the same country distribute evenly.
 */
class LeadAssignmentService
{
    public const HOT_LEAD_THRESHOLD = 70;
    public const ROUND_ROBIN_TTL = 86400; // 24h

    /**
     * Resolve and persist the assignment on the given lead.
     * Returns the assigned user, or null if no candidate found.
     */
    public function assign(Lead $lead): ?User
    {
        if (! $lead->country_id) {
            return null;
        }

        $config = CountryConfig::where('country_id', $lead->country_id)->first();
        if (! $config) {
            return null;
        }

        $assignee = $this->resolveAssignee($lead, $config);
        if (! $assignee) {
            return null;
        }

        $lead->assigned_to = $assignee->id;
        $lead->save();

        return $assignee;
    }

    /**
     * Pure resolver — no side effects. Useful for previewing or testing.
     */
    public function resolveAssignee(Lead $lead, CountryConfig $config): ?User
    {
        // Hot lead → primary manager (if email set and user exists)
        if (($lead->score ?? 0) >= self::HOT_LEAD_THRESHOLD && $config->primary_manager) {
            $manager = User::where('email', $config->primary_manager)->first();
            if ($manager) {
                return $manager;
            }
        }

        // Otherwise → round-robin among webhook_assignees
        $emails = array_values(array_filter($config->webhook_assignees ?? []));
        if (empty($emails)) {
            return null;
        }

        $cacheKey = "lead_assignment.last_index.country_{$lead->country_id}";
        $lastIndex = (int) Cache::get($cacheKey, -1);
        $nextIndex = ($lastIndex + 1) % count($emails);

        $candidate = User::where('email', $emails[$nextIndex])->first();

        // If the candidate user doesn't exist, walk forward until we find one
        // (or exhaust the list — then fall through to null).
        $attempts = 0;
        while (! $candidate && $attempts < count($emails)) {
            $nextIndex = ($nextIndex + 1) % count($emails);
            $candidate = User::where('email', $emails[$nextIndex])->first();
            $attempts++;
        }

        if ($candidate) {
            Cache::put($cacheKey, $nextIndex, self::ROUND_ROBIN_TTL);
        }

        return $candidate;
    }
}
