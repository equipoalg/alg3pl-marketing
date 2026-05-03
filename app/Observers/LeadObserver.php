<?php

namespace App\Observers;

use App\Jobs\ScoreLeadJob;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NewLeadAssigned;
use App\Services\Notifications\SlackNotifier;
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

        // Mirror the lead's company into the Cuentas (clients) table so the CRM
        // module reflects what came in via Fluent Forms / API.
        $this->syncClientFromLead($lead);
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

            // Promote the matching Client to 'active' when the lead is won —
            // demote to 'inactive' on lost. Other statuses → leave as 'prospect'.
            $this->syncClientFromLead($lead);
        }

        // If the company or country was changed, re-sync the Client mirror.
        if ($lead->isDirty('company') || $lead->isDirty('country_id')) {
            $this->syncClientFromLead($lead);
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

            // Slack alert when crossing the HOT threshold (e.g. 79 → 85).
            // Fires only on the *crossing* — not on every update of an already-hot lead.
            if ($newScore >= SlackNotifier::HOT_LEAD_SCORE && $oldScore < SlackNotifier::HOT_LEAD_SCORE) {
                app(SlackNotifier::class)->notifyHotLead($lead->fresh(['country', 'assignedUser']));
            }
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

    /**
     * Mirror a lead's company into the `clients` table so the Cuentas resource
     * shows actual companies imported from Fluent Forms / API.
     *
     * Idempotent: firstOrCreate on (tenant_id, country_id, company_name).
     * Promotes the client to 'active' if the lead is 'won'; demotes to
     * 'inactive' on 'lost'; otherwise leaves the existing status alone.
     */
    private function syncClientFromLead(Lead $lead): void
    {
        $companyName = trim((string) $lead->company);
        if ($companyName === '' || $lead->country_id === null) {
            return;
        }

        // Resolve tenant from country (countries.tenant_id is reliable) → fall
        // back to the first/only tenant in the database.
        $tenantId = $lead->country?->tenant_id
            ?? Tenant::query()->orderBy('id')->value('id');
        if ($tenantId === null) {
            return; // no tenant → can't satisfy NOT NULL FK
        }

        // Skip the BelongsToTenant scope so the firstOrCreate works regardless
        // of the request-time tenant binding (HTTP, queue, command, etc.).
        $client = Client::withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id'    => $tenantId,
                'country_id'   => $lead->country_id,
                'company_name' => $companyName,
            ],
            [
                'status'                 => 'prospect',
                'tier'                   => 'smb',
                'health_score'           => 50,
                'primary_contact_name'   => $lead->name,
                'primary_contact_email'  => $lead->email,
                'primary_contact_phone'  => $lead->phone,
            ]
        );

        // Backfill primary contact if currently empty
        $dirty = false;
        foreach ([
            'primary_contact_name'  => $lead->name,
            'primary_contact_email' => $lead->email,
            'primary_contact_phone' => $lead->phone,
        ] as $field => $value) {
            if (! $client->{$field} && $value) {
                $client->{$field} = $value;
                $dirty = true;
            }
        }

        // Promote to active when a lead becomes 'won'; mark inactive on 'lost'.
        // Don't reset 'active' on subsequent prospect-stage leads.
        if ($lead->status === 'won' && $client->status !== 'active') {
            $client->status = 'active';
            if (! $client->contract_start) {
                $client->contract_start = now();
            }
            $dirty = true;
        }

        if ($dirty) {
            $client->save();
        }
    }
}
