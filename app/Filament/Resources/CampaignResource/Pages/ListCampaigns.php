<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListCampaigns. Mismo patrón que ListClients/ListLeads.
 */
class ListCampaigns extends Page
{
    protected static string $resource = CampaignResource::class;
    protected string $view = 'filament.resources.campaign-resource.pages.campaigns-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'type')]
    public string $typeFilter = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'recent';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    public array $selectedIds = [];

    public string $editName = '';
    public ?string $editType = null;
    public ?string $editStatus = null;
    public ?int $editCountryId = null;
    public ?string $editStartDate = null;
    public ?string $editEndDate = null;
    public ?float $editBudget = null;
    public ?string $editDescription = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Campañas'; }
    protected function getHeaderActions(): array { return []; }

    public function mount(): void
    {
        if ($this->selectedId) $this->hydrateEditForm();
    }

    /* ───── Setters ───── */

    public function setStatusFilter(string $value): void
    {
        if (in_array($value, ['', 'draft', 'scheduled', 'active', 'paused', 'completed'], true)) {
            $this->statusFilter = $value;
        }
    }

    public function setTypeFilter(string $value): void
    {
        if (in_array($value, ['', 'email', 'whatsapp', 'social', 'seo'], true)) {
            $this->typeFilter = $value;
        }
    }

    public function setSortBy(string $value): void
    {
        if (in_array($value, ['recent', 'name', 'starts_soon', 'budget_desc'], true)) {
            $this->sortBy = $value;
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->sortBy = 'recent';
    }

    /* ───── Slide-over ───── */

    public function selectCampaign(int $id): void
    {
        $this->selectedId = $id;
        $this->hydrateEditForm();
    }

    public function closeCampaign(): void
    {
        $this->selectedId = null;
    }

    public function updatedSelectedId($value): void
    {
        if ($value) $this->hydrateEditForm();
    }

    public function hydrateEditForm(): void
    {
        if (! $this->selectedId) return;
        $c = CampaignResource::getEloquentQuery()->find($this->selectedId);
        if (! $c) return;
        $this->editName        = (string) $c->name;
        $this->editType        = $c->type;
        $this->editStatus      = $c->status;
        $this->editCountryId   = $c->country_id;
        $this->editStartDate   = $c->start_date?->format('Y-m-d');
        $this->editEndDate     = $c->end_date?->format('Y-m-d');
        $this->editBudget      = $c->budget !== null ? (float) $c->budget : null;
        $this->editDescription = $c->description;
    }

    public function saveCampaign(): void
    {
        if (! $this->selectedId) return;
        $c = CampaignResource::getEloquentQuery()->find($this->selectedId);
        if (! $c) return;
        $c->update([
            'name'        => trim($this->editName) ?: $c->name,
            'type'        => in_array($this->editType, ['email','whatsapp','social','seo'], true) ? $this->editType : $c->type,
            'status'      => in_array($this->editStatus, ['draft','scheduled','active','paused','completed'], true) ? $this->editStatus : $c->status,
            'country_id'  => $this->editCountryId ?: $c->country_id,
            'start_date'  => $this->editStartDate ?: null,
            'end_date'    => $this->editEndDate ?: null,
            'budget'      => $this->editBudget !== null ? max(0, $this->editBudget) : $c->budget,
            'description' => $this->editDescription,
        ]);
        Notification::make()->title('Campaña guardada')->success()->send();
    }

    /* ───── Inline status ───── */

    public function setCampaignStatus(int $id, string $status): void
    {
        if (! in_array($status, ['draft','scheduled','active','paused','completed'], true)) return;
        $c = CampaignResource::getEloquentQuery()->find($id);
        if (! $c) return;
        $c->update(['status' => $status]);
    }

    /* ───── Bulk ───── */

    public function toggleSelected(int $id): void
    {
        if (in_array($id, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn ($i) => $i !== $id));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function clearSelectedCampaigns(): void
    {
        $this->selectedIds = [];
    }

    public function selectAllVisible(): void
    {
        $this->selectedIds = $this->buildQuery()->limit(500)->pluck('id')->all();
    }

    public function bulkSetStatus(string $status): void
    {
        if (empty($this->selectedIds)) return;
        if (! in_array($status, ['draft','scheduled','active','paused','completed'], true)) return;
        CampaignResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['status' => $status]);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("$count campañas marcadas como $status")->success()->send();
    }

    /* ───── Saved views ───── */

    public function saveCurrentView(string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 40) return;
        $user = auth()->user();
        if (! $user) return;
        $existing = $user->pref('campaign_views', []);
        $existing = array_values(array_filter($existing, fn ($v) => ($v['name'] ?? '') !== $name));
        $existing[] = [
            'name'   => $name,
            'q'      => $this->search,
            'status' => $this->statusFilter,
            'type'   => $this->typeFilter,
            'sort'   => $this->sortBy,
        ];
        if (count($existing) > 20) $existing = array_slice($existing, -20);
        $user->setPrefs(['campaign_views' => array_values($existing)]);
        Notification::make()->title("Vista \"$name\" guardada")->success()->send();
    }

    public function loadCampaignView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('campaign_views', []);
        if (! isset($views[$index])) return;
        $v = $views[$index];
        $this->search       = $v['q']      ?? '';
        $this->statusFilter = $v['status'] ?? '';
        $this->typeFilter   = $v['type']   ?? '';
        $this->sortBy       = $v['sort']   ?? 'recent';
    }

    public function deleteCampaignView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('campaign_views', []);
        if (! isset($views[$index])) return;
        unset($views[$index]);
        $user->setPrefs(['campaign_views' => array_values($views)]);
        Notification::make()->title('Vista eliminada')->success()->send();
    }

    /* ───── Send email campaign (reuses resource action logic) ───── */

    public function sendEmailCampaign(int $id): void
    {
        $c = CampaignResource::getEloquentQuery()->find($id);
        if (! $c) return;
        if ($c->type !== 'email') {
            Notification::make()->title('Solo campañas tipo email se pueden enviar')->warning()->send();
            return;
        }
        try {
            $result = $c->dispatchEmail();
            Notification::make()
                ->title('Campaña encolada')
                ->body("Se encolaron {$result['queued']} emails para envío.")
                ->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('No se pudo enviar')
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    /* ───── Type icon helper ───── */

    public static function typeIcon(?string $type): array
    {
        return match ($type) {
            'email'    => ['icon' => '✉',  'bg' => '#DBEAFE', 'fg' => '#1E3A8A', 'label' => 'Email'],
            'whatsapp' => ['icon' => '💬', 'bg' => '#D1FAE5', 'fg' => '#065F46', 'label' => 'WhatsApp'],
            'social'   => ['icon' => '📣', 'bg' => '#EDE9FE', 'fg' => '#5B21B6', 'label' => 'Social'],
            'seo'      => ['icon' => '🔍', 'bg' => '#FEF3C7', 'fg' => '#92400E', 'label' => 'SEO'],
            default    => ['icon' => '?',   'bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)', 'label' => (string) $type],
        };
    }

    /* ───── Query + view data ───── */

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = CampaignResource::getEloquentQuery();

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('name', 'like', $like)
                  ->orWhere('description', 'like', $like);
            });
        }
        if ($this->statusFilter !== '') $q->where('status', $this->statusFilter);
        if ($this->typeFilter !== '') $q->where('type', $this->typeFilter);

        return match ($this->sortBy) {
            'name'         => $q->orderBy('name'),
            'starts_soon'  => $q->orderByRaw('start_date IS NULL, start_date ASC'),
            'budget_desc'  => $q->orderByDesc('budget')->orderByDesc('created_at'),
            default        => $q->orderByDesc('created_at'),
        };
    }

    public function getViewData(): array
    {
        $campaigns = $this->buildQuery()->with(['country', 'creator:id,name'])->limit(500)->get();

        $allQ = CampaignResource::getEloquentQuery();
        $kpis = [
            'total'     => (clone $allQ)->count(),
            'active'    => (clone $allQ)->where('status', 'active')->count(),
            'scheduled' => (clone $allQ)->where('status', 'scheduled')->count(),
            'paused'    => (clone $allQ)->where('status', 'paused')->count(),
            'completed' => (clone $allQ)->where('status', 'completed')->count(),
            'budget'    => (float) (clone $allQ)->sum('budget'),
        ];

        $selected = null;
        if ($this->selectedId) {
            $selected = CampaignResource::getEloquentQuery()
                ->with(['country', 'creator:id,name'])
                ->find($this->selectedId);
        }

        $countries = \App\Models\Country::orderBy('name')->get(['id', 'name', 'code']);
        $savedViews = auth()->user()?->pref('campaign_views', []) ?? [];

        return [
            'campaigns'      => $campaigns,
            'totalShown'     => $campaigns->count(),
            'kpis'           => $kpis,
            'selected'       => $selected,
            'selectedIds'    => $this->selectedIds,
            'countries'      => $countries,
            'savedViews'     => $savedViews,
            'currentSearch'  => $this->search,
            'currentStatus'  => $this->statusFilter,
            'currentType'    => $this->typeFilter,
            'currentSort'    => $this->sortBy,
        ];
    }
}
