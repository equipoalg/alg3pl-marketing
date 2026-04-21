<?php

namespace App\Observers;

use App\Jobs\ScoreLeadJob;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLeadAssigned;
use App\Services\Webhook\WebhookDispatcher;

class LeadObserver
{
    public function created(Lead $lead): void
    {
        // Async lead scoring — dispatched to queue (QUEUE_CONNECTION=database)
        ScoreLeadJob::dispatch($lead);

        // Fire webhook
        app(WebhookDispatcher::class)->dispatch('lead.created', [
            'lead_id' => $lead->id,
            'name'    => $lead->name,
            'email'   => $lead->email,
            'country' => $lead->country?->code,
            'source'  => $lead->source,
            'score'   => $lead->score,
        ], $lead->country?->tenant_id ?? null);

        // Auto-enroll in matching funnels
        $this->autoEnrollInFunnels($lead);
    }

    public function updated(Lead $lead): void
    {
        // Fire status change webhook
        if ($lead->isDirty('status')) {
            app(WebhookDispatcher::class)->dispatch('lead.status_changed', [
                'lead_id'    => $lead->id,
                'old_status' => $lead->getOriginal('status'),
                'new_status' => $lead->status,
                'score'      => $lead->score,
            ]);

            // Log the status change as activity
            $lead->activities()->create([
                'type'        => 'status_change',
                'description' => "Status changed from {$lead->getOriginal('status')} to {$lead->status}",
            ]);
        }

        // Fire score change webhook
        if ($lead->isDirty('score')) {
            $oldScore = $lead->getOriginal('score');
            $newScore = $lead->score;

            app(WebhookDispatcher::class)->dispatch('lead.score_changed', [
                'lead_id'   => $lead->id,
                'old_score' => $oldScore,
                'new_score' => $newScore,
            ]);
        }

        // Fire assignment webhook + in-app notification
        if ($lead->isDirty('assigned_to') && $lead->assigned_to) {
            app(WebhookDispatcher::class)->dispatch('lead.assigned', [
                'lead_id'       => $lead->id,
                'assigned_to'   => $lead->assigned_to,
                'assignee_name' => $lead->assignedUser?->name,
            ]);

            // Dispatch database notification to newly assigned user
            $newUser = User::find($lead->assigned_to);
            if ($newUser) {
                $newUser->notify(new NewLeadAssigned($lead));
            }
        }
    }

    private function autoEnrollInFunnels(Lead $lead): void
    {
        $funnels = \App\Models\Funnel::where('status', 'active')->get();

        foreach ($funnels as $funnel) {
            // Check trigger type
            if ($funnel->trigger_type !== 'form_submit' && $funnel->trigger_type !== 'api_event') {
                continue;
            }

            \App\Jobs\EnrollLeadInFunnel::dispatch($lead, $funnel);
        }
    }
}
