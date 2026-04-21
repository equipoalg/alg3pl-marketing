<?php

namespace App\Jobs;

use App\Models\Funnel;
use App\Models\FunnelEnrollment;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Tag;
use App\Services\Email\BounceHandlerService;
use App\Services\Lead\LeadScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessFunnelStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public FunnelEnrollment $enrollment
    ) {}

    public function handle(): void
    {
        $enrollment = $this->enrollment;

        if ($enrollment->status !== 'active') return;

        $funnel = $enrollment->funnel;
        $step = $funnel->steps()->where('order', $enrollment->current_step)->first();

        if (!$step) {
            // No more steps — mark as completed
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $funnel->increment('total_conversions');
            return;
        }

        // Check delay
        $lastStepTime = $enrollment->updated_at;
        if ($step->delay_hours > 0 && $lastStepTime->addHours($step->delay_hours)->isFuture()) {
            // Re-queue with delay
            self::dispatch($enrollment)->delay(
                $lastStepTime->addHours($step->delay_hours)
            );
            return;
        }

        // Check condition (if set)
        if ($step->condition && !$this->evaluateCondition($step->condition, $enrollment)) {
            // Skip step, move to next
            $this->advanceToNext($enrollment, $step);
            return;
        }

        // Execute the action
        $success = $this->executeAction($step, $enrollment);

        if ($success) {
            $step->increment('completions_count');

            // Record in step history
            $history = $enrollment->step_history ?? [];
            $history[] = [
                'step' => $step->order,
                'action' => $step->action_type,
                'executed_at' => now()->toISOString(),
                'success' => true,
            ];
            $enrollment->update(['step_history' => $history]);

            // Advance to next step
            $this->advanceToNext($enrollment, $step);
        }
    }

    private function executeAction(FunnelStep $step, FunnelEnrollment $enrollment): bool
    {
        $lead = $enrollment->lead;
        $config = $step->action_config ?? [];

        try {
            match ($step->action_type) {
                'send_email' => $this->sendEmail($lead, $config),
                'send_whatsapp' => $this->sendWhatsApp($lead, $config),
                'wait_delay' => true, // delay already handled above
                'wait_condition' => true, // condition already handled above
                'assign_score' => $this->assignScore($lead, $config),
                'assign_tag' => $this->assignTag($lead, $config),
                'notify_sales' => $this->notifySales($lead, $config),
                'create_task' => $this->createTask($lead, $enrollment, $config),
                'webhook' => $this->fireWebhook($lead, $config),
                default => false,
            };
            return true;
        } catch (\Exception $e) {
            Log::error("Funnel step execution failed", [
                'enrollment_id' => $enrollment->id,
                'step_id' => $step->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function sendEmail(?Lead $lead, array $config): void
    {
        if (!$lead?->canReceiveEmail()) return;

        $bounceHandler = app(BounceHandlerService::class);
        if ($bounceHandler->isSuppressed($lead->email)) return;

        $subject = $config['subject'] ?? 'Update from ALG3PL';
        $body = $config['body'] ?? '';

        // Replace variables
        $body = str_replace(
            ['{nombre}', '{empresa}', '{email}', '{pais}'],
            [$lead->name, $lead->company ?? '', $lead->email, $lead->country?->name ?? ''],
            $body
        );

        Mail::raw($body, function ($message) use ($lead, $subject) {
            $message->to($lead->email, $lead->name)
                    ->subject($subject);
        });
    }

    private function sendWhatsApp(?Lead $lead, array $config): void
    {
        if (!$lead?->phone) return;

        // Create WhatsApp message record
        \App\Models\WhatsAppMessage::create([
            'lead_id' => $lead->id,
            'direction' => 'out',
            'message' => $config['message'] ?? '',
            'template_name' => $config['template'] ?? null,
            'phone_number' => $lead->phone,
            'status' => 'pending',
        ]);

        // TODO: integrate with WhatsApp Cloud API for actual sending
    }

    private function assignScore(?Lead $lead, array $config): void
    {
        if (!$lead) return;

        $adjustment = $config['adjustment'] ?? 0;
        $lead->score = max(0, min(100, $lead->score + $adjustment));
        $lead->save();
    }

    private function assignTag(?Lead $lead, array $config): void
    {
        if (!$lead) return;

        $tagIds = $config['tag_ids'] ?? [];
        foreach ($tagIds as $tagId) {
            $lead->tags()->syncWithoutDetaching([
                $tagId => ['source' => 'funnel'],
            ]);
        }
    }

    private function notifySales(?Lead $lead, array $config): void
    {
        if (!$lead) return;

        $userId = $config['user_id'] ?? $lead->assigned_to;
        if (!$userId) return;

        // Create a lead activity as notification
        $lead->activities()->create([
            'user_id' => null,
            'type' => 'note',
            'description' => $config['message'] ?? "Funnel notification: Lead requires attention",
            'next_action' => $config['action'] ?? 'Follow up',
            'next_action_date' => now()->addDays($config['days'] ?? 1),
        ]);
    }

    private function createTask(?Lead $lead, FunnelEnrollment $enrollment, array $config): void
    {
        if (!$lead) return;

        $lead->activities()->create([
            'user_id' => $lead->assigned_to,
            'type' => 'note',
            'description' => $config['title'] ?? 'Funnel-generated task',
            'next_action' => $config['description'] ?? '',
            'next_action_date' => now()->addDays($config['due_days'] ?? 3),
        ]);
    }

    private function fireWebhook(?Lead $lead, array $config): void
    {
        $url = $config['url'] ?? null;
        if (!$url) return;

        Http::timeout(10)->post($url, [
            'event' => 'funnel.step_completed',
            'lead' => $lead?->toArray(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    private function evaluateCondition(array $condition, FunnelEnrollment $enrollment): bool
    {
        $lead = $enrollment->lead;
        if (!$lead) return false;

        $field = $condition['field'] ?? null;
        $op = $condition['op'] ?? '=';
        $value = $condition['value'] ?? null;

        if (!$field) return true;

        $actual = data_get($lead, $field);

        return match ($op) {
            '=' => $actual == $value,
            '!=' => $actual != $value,
            '>' => $actual > $value,
            '>=' => $actual >= $value,
            '<' => $actual < $value,
            '<=' => $actual <= $value,
            'in' => in_array($actual, (array) $value),
            'has_tag' => $lead->tags()->whereIn('tags.id', (array) $value)->exists(),
            default => true,
        };
    }

    private function advanceToNext(FunnelEnrollment $enrollment, FunnelStep $currentStep): void
    {
        $nextStep = $enrollment->funnel->steps()
            ->where('order', '>', $currentStep->order)
            ->orderBy('order')
            ->first();

        if ($nextStep) {
            $enrollment->update(['current_step' => $nextStep->order]);
            $nextStep->increment('entries_count');

            // Dispatch next step processing
            if ($nextStep->delay_hours > 0) {
                self::dispatch($enrollment)->delay(now()->addHours($nextStep->delay_hours));
            } else {
                self::dispatch($enrollment);
            }
        } else {
            // Funnel complete
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $enrollment->funnel->increment('total_conversions');
        }
    }
}
