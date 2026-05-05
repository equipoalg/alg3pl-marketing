<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListEmailCampaigns. Vista de métricas de envíos:
 * KPIs agregados (open rate / click rate / bounce rate) + tabla con
 * detalle por envío + slide-over con breakdown.
 */
class ListEmailCampaigns extends Page
{
    protected static string $resource = EmailCampaignResource::class;
    protected string $view = 'filament.resources.email-campaign-resource.pages.email-campaigns-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'period')]
    public string $period = '30d';

    #[Url(as: 'sort')]
    public string $sortBy = 'sent_at_desc';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Envíos'; }
    protected function getHeaderActions(): array { return []; }

    public function setPeriod(string $value): void
    {
        if (in_array($value, ['7d', '30d', '90d', 'all'], true)) $this->period = $value;
    }
    public function setSortBy(string $value): void
    {
        if (in_array($value, ['sent_at_desc', 'open_rate_desc', 'click_rate_desc', 'sent_count_desc'], true)) {
            $this->sortBy = $value;
        }
    }
    public function clearFilters(): void
    {
        $this->search = '';
        $this->period = '30d';
        $this->sortBy = 'sent_at_desc';
    }

    public function selectSend(int $id): void { $this->selectedId = $id; }
    public function closeSend(): void { $this->selectedId = null; }

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = EmailCampaign::query()->with(['campaign', 'template:id,name,category']);

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('subject', 'like', $like)
                  ->orWhere('from_email', 'like', $like)
                  ->orWhereHas('campaign', fn ($c) => $c->where('name', 'like', $like));
            });
        }

        if ($this->period !== 'all') {
            $cutoff = match ($this->period) {
                '7d'  => now()->subDays(7),
                '30d' => now()->subDays(30),
                '90d' => now()->subDays(90),
                default => null,
            };
            if ($cutoff) $q->where('sent_at', '>=', $cutoff);
        }

        return match ($this->sortBy) {
            'open_rate_desc'   => $q->orderByRaw('CASE WHEN sent_count > 0 THEN open_count*1.0/sent_count ELSE 0 END DESC'),
            'click_rate_desc'  => $q->orderByRaw('CASE WHEN sent_count > 0 THEN click_count*1.0/sent_count ELSE 0 END DESC'),
            'sent_count_desc'  => $q->orderByDesc('sent_count'),
            default            => $q->orderByDesc('sent_at'),
        };
    }

    public function getViewData(): array
    {
        $sends = $this->buildQuery()->limit(500)->get();

        $allQ = EmailCampaign::query();
        $totalSent   = (int) (clone $allQ)->sum('sent_count');
        $totalOpen   = (int) (clone $allQ)->sum('open_count');
        $totalClick  = (int) (clone $allQ)->sum('click_count');
        $totalBounce = (int) (clone $allQ)->sum('bounce_count');
        $totalUnsub  = (int) (clone $allQ)->sum('unsubscribe_count');

        $kpis = [
            'sends'       => (clone $allQ)->count(),
            'sent'        => $totalSent,
            'open_rate'   => $totalSent > 0 ? round(($totalOpen / $totalSent) * 100, 1) : 0,
            'click_rate'  => $totalSent > 0 ? round(($totalClick / $totalSent) * 100, 1) : 0,
            'bounce_rate' => $totalSent > 0 ? round(($totalBounce / $totalSent) * 100, 1) : 0,
            'unsub'       => $totalUnsub,
        ];

        $selected = null;
        if ($this->selectedId) {
            $selected = EmailCampaign::with(['campaign', 'template:id,name,category'])->find($this->selectedId);
        }

        return [
            'sends'         => $sends,
            'totalShown'    => $sends->count(),
            'kpis'          => $kpis,
            'selected'      => $selected,
            'currentSearch' => $this->search,
            'currentPeriod' => $this->period,
            'currentSort'   => $this->sortBy,
        ];
    }
}
