<?php

namespace App\Jobs;

use App\Models\Funnel;
use App\Models\FunnelEnrollment;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnrollLeadInFunnel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public Funnel $funnel
    ) {}

    public function handle(): void
    {
        // Don't double-enroll
        $existing = FunnelEnrollment::where('funnel_id', $this->funnel->id)
            ->where('lead_id', $this->lead->id)
            ->whereIn('status', ['active', 'completed'])
            ->exists();

        if ($existing) return;

        // Check audience rules
        if (!$this->matchesAudience()) return;

        $firstStep = $this->funnel->steps()->orderBy('order')->first();
        if (!$firstStep) return;

        $enrollment = FunnelEnrollment::create([
            'funnel_id' => $this->funnel->id,
            'lead_id' => $this->lead->id,
            'current_step' => $firstStep->order,
            'status' => 'active',
            'enrolled_at' => now(),
            'step_history' => [],
        ]);

        $this->funnel->increment('total_entries');
        $firstStep->increment('entries_count');

        // Start processing
        ProcessFunnelStep::dispatch($enrollment);
    }

    private function matchesAudience(): bool
    {
        $rules = $this->funnel->audience_rules;
        if (!$rules) return true;

        $lead = $this->lead;

        // Check country filter
        if (!empty($rules['country'])) {
            $countryCode = $lead->country?->code;
            if (!in_array($countryCode, $rules['country'])) return false;
        }

        // Check source filter
        if (!empty($rules['source'])) {
            if (!in_array($lead->source, $rules['source'])) return false;
        }

        // Check score filter
        if (isset($rules['min_score'])) {
            if ($lead->score < $rules['min_score']) return false;
        }

        // Check status filter
        if (!empty($rules['status'])) {
            if (!in_array($lead->status, $rules['status'])) return false;
        }

        return true;
    }
}
