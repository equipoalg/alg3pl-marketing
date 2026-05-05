<?php

namespace App\Filament\Resources\FunnelResource\Pages;

use App\Filament\Resources\FunnelResource;
use App\Models\Funnel;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListFunnels. Foco en conversion rates + drill a steps.
 */
class ListFunnels extends Page
{
    protected static string $resource = FunnelResource::class;
    protected string $view = 'filament.resources.funnel-resource.pages.funnels-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'trigger')]
    public string $triggerFilter = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'recent';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    public array $selectedIds = [];

    public string $editName = '';
    public ?string $editDescription = null;
    public ?string $editStatus = null;
    public ?string $editTriggerType = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Funnels'; }
    protected function getHeaderActions(): array { return []; }

    public function mount(): void { if ($this->selectedId) $this->hydrateEditForm(); }

    public function setStatusFilter(string $value): void
    {
        if (in_array($value, ['', 'draft', 'active', 'paused', 'archived'], true)) $this->statusFilter = $value;
    }
    public function setTriggerFilter(string $value): void
    {
        if (in_array($value, ['', 'page_visit', 'form_submit', 'api_event', 'manual'], true)) $this->triggerFilter = $value;
    }
    public function setSortBy(string $value): void
    {
        if (in_array($value, ['recent', 'name', 'conversion_desc', 'entries_desc'], true)) $this->sortBy = $value;
    }
    public function clearFilters(): void
    {
        $this->search = ''; $this->statusFilter = ''; $this->triggerFilter = ''; $this->sortBy = 'recent';
    }

    public function selectFunnel(int $id): void { $this->selectedId = $id; $this->hydrateEditForm(); }
    public function closeFunnel(): void { $this->selectedId = null; }
    public function updatedSelectedId($v): void { if ($v) $this->hydrateEditForm(); }

    public function hydrateEditForm(): void
    {
        if (! $this->selectedId) return;
        $f = FunnelResource::getEloquentQuery()->find($this->selectedId);
        if (! $f) return;
        $this->editName        = (string) $f->name;
        $this->editDescription = $f->description;
        $this->editStatus      = $f->status;
        $this->editTriggerType = $f->trigger_type;
    }

    public function saveFunnel(): void
    {
        if (! $this->selectedId) return;
        $f = FunnelResource::getEloquentQuery()->find($this->selectedId);
        if (! $f) return;
        $f->update([
            'name'         => trim($this->editName) ?: $f->name,
            'description'  => $this->editDescription,
            'status'       => in_array($this->editStatus, ['draft','active','paused','archived'], true) ? $this->editStatus : $f->status,
            'trigger_type' => in_array($this->editTriggerType, ['page_visit','form_submit','api_event','manual'], true) ? $this->editTriggerType : $f->trigger_type,
        ]);
        Notification::make()->title('Funnel guardado')->success()->send();
    }

    public function setFunnelStatus(int $id, string $status): void
    {
        if (! in_array($status, ['draft','active','paused','archived'], true)) return;
        $f = FunnelResource::getEloquentQuery()->find($id);
        if (! $f) return;
        $f->update(['status' => $status]);
    }

    public function toggleSelected(int $id): void
    {
        if (in_array($id, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn ($i) => $i !== $id));
        } else { $this->selectedIds[] = $id; }
    }
    public function clearSelectedFunnels(): void { $this->selectedIds = []; }
    public function selectAllVisible(): void
    {
        $this->selectedIds = $this->buildQuery()->limit(500)->pluck('id')->all();
    }
    public function bulkSetStatus(string $status): void
    {
        if (empty($this->selectedIds)) return;
        if (! in_array($status, ['draft','active','paused','archived'], true)) return;
        FunnelResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['status' => $status]);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("$count funnels marcados como $status")->success()->send();
    }

    public static function triggerIcon(?string $type): array
    {
        return match ($type) {
            'page_visit'  => ['icon' => '👁',  'bg' => '#DBEAFE', 'fg' => '#1E3A8A', 'label' => 'Page Visit'],
            'form_submit' => ['icon' => '📝', 'bg' => '#D1FAE5', 'fg' => '#065F46', 'label' => 'Form Submit'],
            'api_event'   => ['icon' => '⚡', 'bg' => '#FEF3C7', 'fg' => '#92400E', 'label' => 'API Event'],
            'manual'      => ['icon' => '✋', 'bg' => '#EDE9FE', 'fg' => '#5B21B6', 'label' => 'Manual'],
            default       => ['icon' => '?',   'bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)', 'label' => (string) $type],
        };
    }

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = FunnelResource::getEloquentQuery();

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('name', 'like', $like)->orWhere('description', 'like', $like);
            });
        }
        if ($this->statusFilter !== '') $q->where('status', $this->statusFilter);
        if ($this->triggerFilter !== '') $q->where('trigger_type', $this->triggerFilter);

        return match ($this->sortBy) {
            'name'             => $q->orderBy('name'),
            'conversion_desc'  => $q->orderByRaw('CASE WHEN total_entries > 0 THEN total_conversions*1.0/total_entries ELSE 0 END DESC'),
            'entries_desc'     => $q->orderByDesc('total_entries'),
            default            => $q->orderByDesc('updated_at'),
        };
    }

    public function getViewData(): array
    {
        $funnels = $this->buildQuery()->with(['country', 'steps:id,funnel_id,name,order'])->limit(500)->get();

        $allQ = FunnelResource::getEloquentQuery();
        $totalEntries = (int) (clone $allQ)->sum('total_entries');
        $totalConv = (int) (clone $allQ)->sum('total_conversions');
        $kpis = [
            'total'       => (clone $allQ)->count(),
            'active'      => (clone $allQ)->where('status', 'active')->count(),
            'paused'      => (clone $allQ)->where('status', 'paused')->count(),
            'entries'     => $totalEntries,
            'conversions' => $totalConv,
            'avgConv'     => $totalEntries > 0 ? round(($totalConv / $totalEntries) * 100, 1) : 0,
        ];

        $selected = null;
        if ($this->selectedId) {
            $selected = FunnelResource::getEloquentQuery()
                ->with(['country', 'steps' => fn ($q) => $q->orderBy('order')])
                ->find($this->selectedId);
        }

        return [
            'funnels'        => $funnels,
            'totalShown'     => $funnels->count(),
            'kpis'           => $kpis,
            'selected'       => $selected,
            'selectedIds'    => $this->selectedIds,
            'currentSearch'  => $this->search,
            'currentStatus'  => $this->statusFilter,
            'currentTrigger' => $this->triggerFilter,
            'currentSort'    => $this->sortBy,
        ];
    }
}
