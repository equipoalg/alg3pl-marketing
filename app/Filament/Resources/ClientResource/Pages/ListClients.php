<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListClients page.
 *
 * Reemplaza la ListRecords default de Filament por un layout custom que
 * matches el resto del CRM (Empresas, Contactos, Bandeja). Layout:
 *   - Toolbar (search, status/tier chips, sort, saved views)
 *   - Renewals banner (clientes con contract_end ≤ 30 días)
 *   - 6 KPI tiles (total · active · prospect · churned · sum revenue · avg health)
 *   - Table con slide-over al click + inline status + bulk select
 *   - Bulk action bar flotante
 */
class ListClients extends Page
{
    protected static string $resource = ClientResource::class;
    protected string $view = 'filament.resources.client-resource.pages.clients-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    /* ───── State (URL-bound para deep-linking) ───── */

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'tier')]
    public string $tierFilter = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'company_name';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    /** Bulk select state — IDs de clientes con checkbox marcado. */
    public array $selectedIds = [];

    /** Inline-edit form state (cargada del slide-over). */
    public string $editCompanyName = '';
    public ?string $editIndustry = null;
    public ?string $editStatus = null;
    public ?string $editTier = null;
    public ?int $editHealthScore = null;
    public ?string $editPrimaryContactName = null;
    public ?string $editPrimaryContactEmail = null;
    public ?string $editPrimaryContactPhone = null;
    public ?string $editNotes = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Clientes'; }
    protected function getHeaderActions(): array { return []; }

    public function mount(): void
    {
        if ($this->selectedId) {
            $this->hydrateEditForm();
        }
    }

    /* ───── Setters with validation ───── */

    public function setStatusFilter(string $value): void
    {
        if (in_array($value, ['', 'prospect', 'active', 'inactive', 'churned'], true)) {
            $this->statusFilter = $value;
        }
    }

    public function setTierFilter(string $value): void
    {
        if (in_array($value, ['', 'enterprise', 'mid_market', 'smb'], true)) {
            $this->tierFilter = $value;
        }
    }

    public function setSortBy(string $value): void
    {
        if (in_array($value, ['company_name', 'health_desc', 'value_desc', 'contract_soon', 'recent'], true)) {
            $this->sortBy = $value;
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->tierFilter = '';
        $this->sortBy = 'company_name';
    }

    /* ───── Slide-over ───── */

    public function selectClient(int $id): void
    {
        $this->selectedId = $id;
        $this->hydrateEditForm();
    }

    public function closeClient(): void
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
        $client = ClientResource::getEloquentQuery()->find($this->selectedId);
        if (! $client) return;
        $this->editCompanyName        = (string) $client->company_name;
        $this->editIndustry           = $client->industry;
        $this->editStatus             = $client->status;
        $this->editTier               = $client->tier;
        $this->editHealthScore        = $client->health_score;
        $this->editPrimaryContactName = $client->primary_contact_name;
        $this->editPrimaryContactEmail= $client->primary_contact_email;
        $this->editPrimaryContactPhone= $client->primary_contact_phone;
        $this->editNotes              = $client->notes;
    }

    public function saveClient(): void
    {
        if (! $this->selectedId) return;
        $client = ClientResource::getEloquentQuery()->find($this->selectedId);
        if (! $client) return;
        $client->update([
            'company_name'         => trim($this->editCompanyName) ?: $client->company_name,
            'industry'             => $this->editIndustry,
            'status'               => in_array($this->editStatus, ['prospect','active','inactive','churned'], true) ? $this->editStatus : $client->status,
            'tier'                 => in_array($this->editTier, ['enterprise','mid_market','smb'], true) ? $this->editTier : $client->tier,
            'health_score'         => $this->editHealthScore !== null ? max(0, min(100, (int) $this->editHealthScore)) : $client->health_score,
            'primary_contact_name' => $this->editPrimaryContactName,
            'primary_contact_email'=> $this->editPrimaryContactEmail,
            'primary_contact_phone'=> $this->editPrimaryContactPhone,
            'notes'                => $this->editNotes,
        ]);
        Notification::make()->title('Cliente guardado')->success()->send();
    }

    /* ───── Inline status (sin abrir slide-over) ───── */

    public function setClientStatus(int $id, string $status): void
    {
        if (! in_array($status, ['prospect','active','inactive','churned'], true)) return;
        $client = ClientResource::getEloquentQuery()->find($id);
        if (! $client) return;
        $client->update(['status' => $status]);
    }

    /* ───── Bulk actions ───── */

    public function toggleSelected(int $id): void
    {
        if (in_array($id, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn ($i) => $i !== $id));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function clearSelectedClients(): void
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
        if (! in_array($status, ['prospect','active','inactive','churned'], true)) return;
        ClientResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['status' => $status]);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("$count clientes marcados como $status")->success()->send();
    }

    public function bulkAssignToMe(): void
    {
        if (empty($this->selectedIds)) return;
        $userId = auth()->id();
        if (! $userId) return;
        ClientResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['assigned_to' => $userId]);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("$count clientes asignados a ti")->success()->send();
    }

    /* ───── Saved views ───── */

    public function saveCurrentView(string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 40) return;
        $user = auth()->user();
        if (! $user) return;
        $existing = $user->pref('client_views', []);
        $existing = array_values(array_filter($existing, fn ($v) => ($v['name'] ?? '') !== $name));
        $existing[] = [
            'name'   => $name,
            'q'      => $this->search,
            'status' => $this->statusFilter,
            'tier'   => $this->tierFilter,
            'sort'   => $this->sortBy,
        ];
        if (count($existing) > 20) $existing = array_slice($existing, -20);
        $user->setPrefs(['client_views' => array_values($existing)]);
        Notification::make()->title("Vista \"$name\" guardada")->success()->send();
    }

    public function loadClientView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('client_views', []);
        if (! isset($views[$index])) return;
        $v = $views[$index];
        $this->search        = $v['q']      ?? '';
        $this->statusFilter  = $v['status'] ?? '';
        $this->tierFilter    = $v['tier']   ?? '';
        $this->sortBy        = $v['sort']   ?? 'company_name';
    }

    public function deleteClientView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('client_views', []);
        if (! isset($views[$index])) return;
        unset($views[$index]);
        $user->setPrefs(['client_views' => array_values($views)]);
        Notification::make()->title('Vista eliminada')->success()->send();
    }

    /* ───── Avatar helper ───── */

    public static function avatarFor(?string $name): array
    {
        $key = $name ?: '?';
        $words = preg_split('/\s+/', trim($key)) ?: [$key];
        $initials = strtoupper(substr($words[0] ?? '?', 0, 1));
        if (count($words) > 1) $initials .= strtoupper(substr($words[1], 0, 1));
        $palette = [
            ['bg' => '#FEE2E2', 'fg' => '#9F1239'],
            ['bg' => '#FEF3C7', 'fg' => '#92400E'],
            ['bg' => '#D1FAE5', 'fg' => '#065F46'],
            ['bg' => '#DBEAFE', 'fg' => '#1E3A8A'],
            ['bg' => '#E0E7FF', 'fg' => '#3730A3'],
            ['bg' => '#EDE9FE', 'fg' => '#5B21B6'],
            ['bg' => '#FCE7F3', 'fg' => '#9D174D'],
            ['bg' => '#F1F5F9', 'fg' => '#334155'],
        ];
        $idx = abs(crc32($key)) % count($palette);
        return [
            'initials' => $initials ?: '?',
            'bg'       => $palette[$idx]['bg'],
            'fg'       => $palette[$idx]['fg'],
        ];
    }

    /* ───── Query + view data ───── */

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = ClientResource::getEloquentQuery();

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('company_name', 'like', $like)
                  ->orWhere('trade_name', 'like', $like)
                  ->orWhere('primary_contact_name', 'like', $like)
                  ->orWhere('primary_contact_email', 'like', $like)
                  ->orWhere('industry', 'like', $like);
            });
        }

        if ($this->statusFilter !== '') {
            $q->where('status', $this->statusFilter);
        }

        if ($this->tierFilter !== '') {
            $q->where('tier', $this->tierFilter);
        }

        return match ($this->sortBy) {
            'health_desc'   => $q->orderByDesc('health_score')->orderBy('company_name'),
            'value_desc'    => $q->orderByDesc('annual_revenue')->orderBy('company_name'),
            'contract_soon' => $q->orderByRaw('contract_end IS NULL, contract_end ASC'),
            'recent'        => $q->orderByDesc('updated_at'),
            default         => $q->orderBy('company_name'),
        };
    }

    public function getViewData(): array
    {
        $clients = $this->buildQuery()
            ->with(['country', 'assignedUser:id,name'])
            ->limit(500)
            ->get();

        // KPIs (sobre toda la cartera con scope, no solo el subset filtrado)
        $allQ = ClientResource::getEloquentQuery();
        $kpis = [
            'total'      => (clone $allQ)->count(),
            'active'     => (clone $allQ)->where('status', 'active')->count(),
            'prospect'   => (clone $allQ)->where('status', 'prospect')->count(),
            'churned'    => (clone $allQ)->where('status', 'churned')->count(),
            'revenue'    => (float) (clone $allQ)->sum('annual_revenue'),
            'avgHealth'  => (int) round((clone $allQ)->avg('health_score') ?? 0),
        ];

        // Renewals (contract_end en próximos 30 días)
        $renewalsCount = (clone $allQ)
            ->whereNotNull('contract_end')
            ->whereBetween('contract_end', [now(), now()->addDays(30)])
            ->count();

        $selected = null;
        if ($this->selectedId) {
            $selected = ClientResource::getEloquentQuery()
                ->with(['country', 'assignedUser:id,name'])
                ->find($this->selectedId);
        }

        $savedViews = auth()->user()?->pref('client_views', []) ?? [];

        return [
            'clients'        => $clients,
            'totalShown'     => $clients->count(),
            'kpis'           => $kpis,
            'renewalsCount'  => $renewalsCount,
            'selected'       => $selected,
            'selectedIds'    => $this->selectedIds,
            'savedViews'     => $savedViews,
            'currentSearch'  => $this->search,
            'currentStatus'  => $this->statusFilter,
            'currentTier'    => $this->tierFilter,
            'currentSort'    => $this->sortBy,
        ];
    }
}
