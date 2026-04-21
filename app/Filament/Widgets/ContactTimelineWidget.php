<?php

namespace App\Filament\Widgets;

use App\Models\Interaction;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\WhatsAppMessage;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ContactTimelineWidget extends Widget
{
    protected string $view = 'filament.widgets.contact-timeline';
    protected int|string|array $columnSpan = 'full';

    public ?string $countryFilter = '';
    public int $limit = 20;

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    public static function getSort(): int
    {
        return 10;
    }

    public function getViewData(): array
    {
        return ['events' => $this->getTimelineEvents()];
    }

    private function getTimelineEvents(): Collection
    {
        $events = collect();

        // Lead activities
        $activities = LeadActivity::with(['lead', 'user'])
            ->when($this->countryFilter, fn ($q) => $q->whereHas('lead', fn ($lq) => $lq->where('country_id', $this->countryFilter)))
            ->latest()
            ->limit($this->limit)
            ->get()
            ->map(fn ($a) => [
                'type' => 'activity',
                'icon' => $this->activityIcon($a->type),
                'color' => $this->activityColor($a->type),
                'title' => $a->lead?->name ?? 'Unknown',
                'subtitle' => ucfirst($a->type) . ': ' . \Illuminate\Support\Str::limit($a->description, 80),
                'user' => $a->user?->name,
                'timestamp' => $a->created_at,
            ]);
        $events = $events->merge($activities);

        // Client interactions
        $interactions = Interaction::with(['client', 'user'])
            ->when($this->countryFilter, fn ($q) => $q->whereHas('client', fn ($cq) => $cq->where('country_id', $this->countryFilter)))
            ->latest()
            ->limit($this->limit)
            ->get()
            ->map(fn ($i) => [
                'type' => 'interaction',
                'icon' => $this->interactionIcon($i->type),
                'color' => $this->interactionColor($i->outcome),
                'title' => $i->client?->company_name ?? 'Unknown',
                'subtitle' => $i->subject,
                'user' => $i->user?->name,
                'timestamp' => $i->created_at,
            ]);
        $events = $events->merge($interactions);

        // WhatsApp messages
        $whatsapp = WhatsAppMessage::with('lead')
            ->when($this->countryFilter, fn ($q) => $q->whereHas('lead', fn ($lq) => $lq->where('country_id', $this->countryFilter)))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($w) => [
                'type' => 'whatsapp',
                'icon' => 'chat-bubble-left-right',
                'color' => 'emerald',
                'title' => $w->lead?->name ?? $w->phone_number,
                'subtitle' => ($w->direction === 'in' ? '← ' : '→ ') . \Illuminate\Support\Str::limit($w->message, 80),
                'user' => null,
                'timestamp' => $w->created_at,
            ]);
        $events = $events->merge($whatsapp);

        // Recent leads created
        $newLeads = Lead::with('country')
            ->when($this->countryFilter, fn ($q) => $q->where('country_id', $this->countryFilter))
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($l) => [
                'type' => 'lead_created',
                'icon' => 'user-plus',
                'color' => 'blue',
                'title' => $l->name,
                'subtitle' => "New lead from {$l->source}" . ($l->country ? " ({$l->country->code})" : ''),
                'user' => null,
                'timestamp' => $l->created_at,
            ]);
        $events = $events->merge($newLeads);

        return $events->sortByDesc('timestamp')->take($this->limit)->values();
    }

    private function activityIcon(string $type): string
    {
        return match ($type) {
            'email' => 'envelope',
            'call' => 'phone',
            'whatsapp' => 'chat-bubble-left-right',
            'meeting' => 'calendar',
            'note' => 'document-text',
            'status_change' => 'arrow-path',
            'score_change' => 'chart-bar',
            default => 'clock',
        };
    }

    private function activityColor(string $type): string
    {
        return match ($type) {
            'email' => 'sky',
            'call' => 'violet',
            'whatsapp' => 'emerald',
            'meeting' => 'amber',
            'status_change' => 'blue',
            'score_change' => 'orange',
            default => 'gray',
        };
    }

    private function interactionIcon(string $type): string
    {
        return match ($type) {
            'call' => 'phone',
            'email' => 'envelope',
            'meeting' => 'calendar',
            'whatsapp' => 'chat-bubble-left-right',
            'visit' => 'map-pin',
            'quote_sent' => 'document-arrow-up',
            'quote_accepted' => 'check-circle',
            'quote_rejected' => 'x-circle',
            'complaint' => 'exclamation-triangle',
            'support_ticket' => 'wrench-screwdriver',
            default => 'document-text',
        };
    }

    private function interactionColor(string $outcome): string
    {
        return match ($outcome) {
            'positive' => 'emerald',
            'negative' => 'rose',
            'neutral' => 'gray',
            default => 'amber',
        };
    }
}
