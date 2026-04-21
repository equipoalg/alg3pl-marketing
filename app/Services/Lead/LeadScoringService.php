<?php

namespace App\Services\Lead;

use App\Models\AnalyticsSnapshot;
use App\Models\Lead;
use App\Models\ScoringRule;
use App\Models\SearchConsoleData;

class LeadScoringService
{
    // High-value landing pages — still hardcoded (not yet in DB)
    private array $highValuePages = [
        '/cotizacion', '/quote', '/contacto', '/contact',
        '/servicios', '/services', '/tarifas', '/pricing',
        '/sv/', '/gt/', '/hn/', '/ni/', '/cr/', '/pa/',
    ];

    public function calculate(Lead $lead): int
    {
        $score = 0;

        // Source score (0-25) — DB-backed via ScoringRule
        $score += $this->getSourceScore($lead);

        // Status score (0-55) — DB-backed via ScoringRule
        $score += $this->getStatusScore($lead);

        // Contact completeness (0-10)
        if ($lead->email) $score += 3;
        if ($lead->phone) $score += 4;
        if ($lead->company) $score += 3;

        // Landing page relevance (0-10)
        if ($lead->landing_page) {
            foreach ($this->highValuePages as $page) {
                if (str_contains($lead->landing_page, $page)) {
                    $score += 10;
                    break;
                }
            }
        }

        // GA4 enrichment: country traffic quality (0-8)
        $score += $this->getCountryTrafficScore($lead);

        // GSC enrichment: keyword intent (0-7)
        $score += $this->getKeywordIntentScore($lead);

        // Engagement bonus: tags, activities, email verification (0-10)
        $score += $this->getEngagementScore($lead);

        return min(100, $score);
    }

    /**
     * Get source score from DB-backed scoring rules (cached 1 hour).
     * Falls back to 10 (direct) if factor not found.
     */
    private function getSourceScore(Lead $lead): int
    {
        $source = $lead->source ?? 'direct';
        $factor = 'source_' . $source;

        return ScoringRule::weightFor($factor, 10);
    }

    /**
     * Get status score from DB-backed scoring rules (cached 1 hour).
     * Falls back to 0 if factor not found.
     */
    private function getStatusScore(Lead $lead): int
    {
        $status = $lead->status ?? 'new';
        $factor = 'status_' . $status;

        return ScoringRule::weightFor($factor, 0);
    }

    /**
     * Score based on the country's organic traffic quality from GA4.
     * Countries with higher conversion rates get a boost.
     */
    private function getCountryTrafficScore(Lead $lead): int
    {
        if (!$lead->country_id) return 0;

        $snapshot = AnalyticsSnapshot::where('country_id', $lead->country_id)
            ->where('date', '>=', now()->subDays(30))
            ->orderByDesc('date')
            ->first();

        if (!$snapshot) return 0;

        $score = 0;

        // High organic ratio = quality traffic country
        if ($snapshot->sessions > 0) {
            $organicRatio = $snapshot->organic_users / max(1, $snapshot->users);
            if ($organicRatio > 0.5) $score += 4;
            elseif ($organicRatio > 0.3) $score += 2;
        }

        // High conversions = proven demand
        if ($snapshot->conversions > 5) $score += 4;
        elseif ($snapshot->conversions > 0) $score += 2;

        return $score;
    }

    /**
     * Score based on GSC keyword intent.
     * If the lead's landing page ranks for high-intent queries, boost score.
     *
     * Security: landing_page is sanitised with addslashes() before being used in a
     * LIKE query to prevent SQL injection. The leading wildcard is intentionally
     * omitted (suffix match only) to avoid full-table scans.
     */
    private function getKeywordIntentScore(Lead $lead): int
    {
        if (!$lead->landing_page || !$lead->country_id) return 0;

        // Sanitise to prevent SQL injection; no leading wildcard for index performance
        $safePage = addslashes($lead->landing_page);

        $highIntentKeywords = SearchConsoleData::where('country_id', $lead->country_id)
            ->where('page', 'like', $safePage . '%')
            ->where('date', '>=', now()->subDays(30))
            ->where('position', '<=', 10)
            ->get();

        if ($highIntentKeywords->isEmpty()) return 0;

        $score = 0;
        foreach ($highIntentKeywords as $kw) {
            $query = strtolower($kw->query);
            // Transactional intent keywords
            if (str_contains($query, 'cotizacion') || str_contains($query, 'precio') ||
                str_contains($query, 'costo') || str_contains($query, 'contratar') ||
                str_contains($query, 'quote') || str_contains($query, 'price')) {
                $score += 3;
                break;
            }
            // Navigational intent
            if (str_contains($query, 'alg') || str_contains($query, '3pl')) {
                $score += 2;
                break;
            }
        }

        // Bonus for pages with high CTR
        $avgCtr = $highIntentKeywords->avg('ctr');
        if ($avgCtr > 0.05) $score += 4;
        elseif ($avgCtr > 0.02) $score += 2;

        return min(7, $score);
    }

    /**
     * Score based on lead engagement signals.
     */
    private function getEngagementScore(Lead $lead): int
    {
        $score = 0;

        // Email verified = engaged
        if ($lead->isVerified()) $score += 3;

        // Has tags = segmented/interacted
        if ($lead->tags()->count() > 0) $score += 2;

        // Recent activities
        $recentActivities = $lead->activities()
            ->where('created_at', '>=', now()->subDays(14))
            ->count();
        if ($recentActivities >= 3) $score += 5;
        elseif ($recentActivities >= 1) $score += 2;

        return min(10, $score);
    }

    public function recalculate(Lead $lead): Lead
    {
        $lead->score = $this->calculate($lead);
        $lead->save();
        return $lead;
    }

    public function recalculateAll(): void
    {
        Lead::chunk(100, function ($leads) {
            foreach ($leads as $lead) {
                $lead->score = $this->calculate($lead);
                $lead->save();
            }
        });
    }

    /**
     * Get scoring breakdown for a lead (for UI display).
     */
    public function breakdown(Lead $lead): array
    {
        return [
            'source'     => ['label' => 'Traffic Source',       'score' => $this->getSourceScore($lead),          'max' => 25],
            'status'     => ['label' => 'Pipeline Stage',       'score' => $this->getStatusScore($lead),          'max' => 55],
            'contact'    => ['label' => 'Contact Info',         'score' => ($lead->email ? 3 : 0) + ($lead->phone ? 4 : 0) + ($lead->company ? 3 : 0), 'max' => 10],
            'landing'    => ['label' => 'Landing Page',         'score' => $this->getLandingScore($lead),         'max' => 10],
            'traffic'    => ['label' => 'Country Traffic (GA4)','score' => $this->getCountryTrafficScore($lead),  'max' => 8],
            'keywords'   => ['label' => 'Keyword Intent (GSC)', 'score' => $this->getKeywordIntentScore($lead),   'max' => 7],
            'engagement' => ['label' => 'Engagement',           'score' => $this->getEngagementScore($lead),      'max' => 10],
            'total'      => ['label' => 'Total Score',          'score' => $this->calculate($lead),               'max' => 100],
        ];
    }

    private function getLandingScore(Lead $lead): int
    {
        if (!$lead->landing_page) return 0;
        foreach ($this->highValuePages as $page) {
            if (str_contains($lead->landing_page, $page)) return 10;
        }
        return 0;
    }
}
