<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Imports\LeadImporter;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use App\Models\LeadActivity;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Outlook-style inbox for /admin/leads.
 *
 * Layout: Filament chrome is suppressed via the view's <style> block. The page
 * renders edge-to-edge with a 40px toolbar (search + filters + actions), a
 * 380px scrollable list grouped by date (Hoy / Ayer / Esta semana / Más
 * antiguo), and a flex-1 reading pane with prev/next + pin + quick-reply.
 *
 * State that persists across this session:
 *   - readIds   : array<int>  — leads the user has opened (used to render
 *                              non-bold + 60% opacity so unread leads stand out)
 *   - pinnedIds : array<int>  — leads pinned to the top of the list
 *   - folder    : string      — 'all' | 'unread' | 'pinned' | 'hot'
 *
 * Country filter from the sidebar (session('country_filter')) is honored via
 * LeadResource::getEloquentQuery() which applies the ScopesByCountryFilter
 * trait.
 */
class ListLeads extends Page
{
    protected static string $resource = LeadResource::class;
    protected string $view = 'filament.resources.lead-resource.pages.leads-inbox';
    protected Width|string|null $maxContentWidth = Width::Full;

    /** Selected lead — URL-bound so /admin/leads?selected=42 opens that lead in the right pane. */
    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    /**
     * Active rendering mode.
     *   - 'contacts' (default): tabular CRM-style — avatar + name + company + email + status + score
     *   - 'companies': leads agrupados por su columna `company` (freetext)
     *   - 'inbox': layout master/detail Outlook-like (legacy, accesible via toggle)
     */
    #[Url(as: 'view')]
    public string $viewMode = 'contacts';

    /** Status filter — URL-bound so /admin/leads?status=won lands pre-filtered. */
    #[Url(as: 'status')]
    public string $statusFilter = '';

    /**
     * Period filter — URL-bound so the dashboard can deep-link to the same
     * time-range it shows. Accepts: '7d', '30d', '90d', 'ytd', 'today'.
     * Empty = no time filter (show all). Applied as created_at >= cutoff.
     */
    #[Url(as: 'period')]
    public string $periodFilter = '';

    /** Folder filter — URL-bound: ?folder=hot, ?folder=pinned, etc. */
    #[Url(as: 'folder')]
    public string $folder = 'all';

    /** Search box — URL-bound so we can deep-link search results. */
    #[Url(as: 'q')]
    public string $search = '';

    public string $replyText = '';

    /** @var array<int> */
    public array $readIds = [];
    /** @var array<int> */
    public array $pinnedIds = [];

    /** Multi-select bulk state — IDs marcados via checkbox en la vista Contactos. */
    public array $selectedLeadIds = [];

    public function mount(): void
    {
        // Hydrate session-backed state
        $this->readIds = session('inbox_read_ids', []);
        $this->pinnedIds = session('inbox_pinned_ids', []);

        // If the URL didn't pre-select a lead via ?selected=N, default to the newest.
        if ($this->selectedId === null) {
            $first = $this->buildQuery()->first();
            if ($first) {
                $this->selectedId = $first->id;
            }
        }
        if ($this->selectedId) {
            $this->markRead($this->selectedId);
            // Pre-fill form fields if URL had ?selected=N (so slide-over is editable on first paint).
            $this->hydrateEditForm();
        }
    }

    /** Hide Filament's auto-rendered <h1> heading — the inbox toolbar replaces it */
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getTitle(): string
    {
        return 'Bandeja de entrada';
    }

    /** Hide the standard "header actions" — Import CSV and Nuevo lead live in the inbox toolbar */
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function setViewMode(string $value): void
    {
        if (in_array($value, ['inbox', 'contacts', 'companies'], true)) {
            $this->viewMode = $value;
        }
    }

    public function selectLead(int $id): void
    {
        $this->selectedId = $id;
        $this->replyText = '';
        $this->markRead($id);
        // Hydrate the slide-over form so the contacts/companies views can edit inline.
        $this->hydrateEditForm();
    }

    public function closeLead(): void
    {
        $this->selectedId = null;
    }

    /**
     * Color-hashed avatar from email — used by the contacts/companies tables.
     * Returns ['initials', 'bg', 'fg']. 8-color palette indexed by crc32(email).
     */
    public static function avatarFor(?string $email, ?string $fallbackName = null): array
    {
        if (! $email && ! $fallbackName) {
            return ['initials' => '?', 'bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)'];
        }
        $key = $email ?: $fallbackName;
        if ($email) {
            $local = explode('@', $email)[0];
            $parts = preg_split('/[._\-+]/', $local) ?: [$local];
            $initials = strtoupper(substr($parts[0] ?? '?', 0, 1));
            if (count($parts) > 1) {
                $initials .= strtoupper(substr($parts[1], 0, 1));
            } else {
                $initials .= strtoupper(substr($local, 1, 1));
            }
        } else {
            $words = preg_split('/\s+/', trim($fallbackName)) ?: [$fallbackName];
            $initials = strtoupper(substr($words[0] ?? '?', 0, 1));
            if (count($words) > 1) {
                $initials .= strtoupper(substr($words[1], 0, 1));
            }
        }
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

    /** Save inline edits from the slide-over (contacts/companies views). */
    public string $editName = '';
    public string $editEmail = '';
    public ?string $editPhone = null;
    public ?string $editCompany = null;
    public ?string $editNotes = null;
    public ?string $editStatus = null;

    public function hydrateEditForm(): void
    {
        if (! $this->selectedId) return;
        $lead = Lead::find($this->selectedId);
        if (! $lead) return;
        $this->editName    = (string) $lead->name;
        $this->editEmail   = (string) $lead->email;
        $this->editPhone   = $lead->phone;
        $this->editCompany = $lead->company;
        $this->editNotes   = $lead->notes;
        $this->editStatus  = $lead->status;
    }

    public function updatedSelectedId($value): void
    {
        if ($value) $this->hydrateEditForm();
    }

    public function saveLead(): void
    {
        if (! $this->selectedId) return;
        $lead = Lead::find($this->selectedId);
        if (! $lead) return;
        $lead->update([
            'name'    => trim($this->editName) ?: $lead->name,
            'email'   => trim($this->editEmail) ?: $lead->email,
            'phone'   => $this->editPhone,
            'company' => $this->editCompany,
            'notes'   => $this->editNotes,
            'status'  => in_array($this->editStatus, array_keys($this->statusOptions()), true) ? $this->editStatus : $lead->status,
        ]);
        \Filament\Notifications\Notification::make()->title('Lead guardado')->success()->send();
    }

    public static function statusOptions(): array
    {
        return [
            'new'         => 'Nuevos',
            'contacted'   => 'Contactados',
            'qualified'   => 'Calificados',
            'proposal'    => 'Propuesta',
            'negotiation' => 'Negociación',
            'won'         => 'Ganados',
            'lost'        => 'Perdidos',
        ];
    }

    /* ───── Bulk actions (Contactos view) — operate on $selectedLeadIds ───── */

    public function toggleSelectedLead(int $id): void
    {
        if (in_array($id, $this->selectedLeadIds, true)) {
            $this->selectedLeadIds = array_values(array_filter($this->selectedLeadIds, fn ($i) => $i !== $id));
        } else {
            $this->selectedLeadIds[] = $id;
        }
    }

    public function clearSelectedLeads(): void
    {
        $this->selectedLeadIds = [];
    }

    public function selectAllVisible(): void
    {
        $this->selectedLeadIds = $this->buildQuery()->limit(500)->pluck('id')->all();
    }

    public function bulkSetStatus(string $status): void
    {
        if (empty($this->selectedLeadIds)) return;
        if (! array_key_exists($status, self::statusOptions())) return;
        Lead::whereIn('id', $this->selectedLeadIds)->update(['status' => $status]);
        $count = count($this->selectedLeadIds);
        $label = self::statusOptions()[$status];
        $this->selectedLeadIds = [];
        Notification::make()->title("$count leads marcados como $label")->success()->send();
    }

    public function bulkAssignToMe(): void
    {
        if (empty($this->selectedLeadIds)) return;
        $userId = auth()->id();
        if (! $userId) return;
        Lead::whereIn('id', $this->selectedLeadIds)->update(['assigned_to' => $userId]);
        $count = count($this->selectedLeadIds);
        $this->selectedLeadIds = [];
        Notification::make()->title("$count leads asignados a ti")->success()->send();
    }

    /** Inline status change — un solo lead, sin abrir slide-over. Silent (high-frequency). */
    public function setLeadStatus(int $id, string $status): void
    {
        if (! array_key_exists($status, self::statusOptions())) return;
        $lead = Lead::find($id);
        if (! $lead) return;
        $lead->update(['status' => $status]);
    }

    public function nextLead(): void
    {
        $list = $this->buildQuery()->pluck('id')->all();
        $idx = array_search($this->selectedId, $list, true);
        if ($idx !== false && isset($list[$idx + 1])) {
            $this->selectLead($list[$idx + 1]);
        }
    }

    public function prevLead(): void
    {
        $list = $this->buildQuery()->pluck('id')->all();
        $idx = array_search($this->selectedId, $list, true);
        if ($idx !== false && $idx > 0) {
            $this->selectLead($list[$idx - 1]);
        }
    }

    public function markRead(int $id): void
    {
        if (! in_array($id, $this->readIds, true)) {
            $this->readIds[] = $id;
            session(['inbox_read_ids' => $this->readIds]);
        }
    }

    public function markUnread(int $id): void
    {
        $this->readIds = array_values(array_filter($this->readIds, fn ($x) => $x !== $id));
        session(['inbox_read_ids' => $this->readIds]);
    }

    public function togglePin(int $id): void
    {
        if (in_array($id, $this->pinnedIds, true)) {
            $this->pinnedIds = array_values(array_filter($this->pinnedIds, fn ($x) => $x !== $id));
        } else {
            $this->pinnedIds[] = $id;
        }
        session(['inbox_pinned_ids' => $this->pinnedIds]);
    }

    public function setFolder(string $value): void
    {
        if (in_array($value, ['all', 'unread', 'pinned', 'hot'], true)) {
            $this->folder = $value;
            $this->statusFilter = ''; // folder and status are exclusive top-level filters
        }
    }

    public function setStatus(string $value): void
    {
        $this->statusFilter = $value;
        $this->folder = 'all';
    }

    public function addNote(): void
    {
        $text = trim($this->replyText);
        if ($text === '' || ! $this->selectedId) {
            return;
        }
        LeadActivity::create([
            'lead_id'     => $this->selectedId,
            'user_id'     => auth()->id(),
            'type'        => 'note',
            'description' => $text,
        ]);
        $this->replyText = '';
        Notification::make()->title('Nota agregada')->success()->send();
    }

    /**
     * Eloquent base query — uses the resource's query (which already applies
     * the ScopesByCountryFilter trait), then layers status/search/folder filters.
     * Pinned leads come first, then everything else by created_at desc.
     */
    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = LeadResource::getEloquentQuery();

        if ($this->folder === 'unread') {
            $q->whereNotIn('id', array_merge($this->readIds, [0]));
        } elseif ($this->folder === 'pinned') {
            $q->whereIn('id', array_merge($this->pinnedIds, [0]));
        } elseif ($this->folder === 'hot') {
            $q->where('score', '>=', 80);
        }

        if ($this->statusFilter !== '') {
            $q->where('status', $this->statusFilter);
        }

        // Period filter — only one value allowed; matches the dashboard's $timeRange semantic.
        if ($this->periodFilter !== '') {
            $cutoff = match ($this->periodFilter) {
                'today' => now()->startOfDay(),
                '7d'    => now()->subDays(7),
                '30d'   => now()->subDays(30),
                '90d'   => now()->subDays(90),
                'ytd'   => now()->startOfYear(),
                default => null,
            };
            if ($cutoff) {
                $q->where('created_at', '>=', $cutoff);
            }
        }

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('company', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('notes', 'like', $like);
            });
        }

        // Pinned-first ordering: case statement that buckets pinned to 0, others to 1
        $pinnedCsv = empty($this->pinnedIds) ? '0' : implode(',', array_map('intval', $this->pinnedIds));
        return $q->orderByRaw("CASE WHEN id IN ($pinnedCsv) THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');
    }

    public function getViewData(): array
    {
        $leads = $this->buildQuery()->with('country')->limit(500)->get();

        // Group by date bucket: Hoy / Ayer / Esta semana / Anterior
        $now = now();
        $startToday = $now->copy()->startOfDay();
        $startYesterday = $now->copy()->subDay()->startOfDay();
        $startWeek = $now->copy()->startOfWeek();

        $grouped = [
            'pinned'     => collect(),
            'today'      => collect(),
            'yesterday'  => collect(),
            'thisWeek'   => collect(),
            'older'      => collect(),
        ];

        foreach ($leads as $lead) {
            if (in_array($lead->id, $this->pinnedIds, true)) {
                $grouped['pinned']->push($lead);
                continue;
            }
            if ($lead->created_at >= $startToday) {
                $grouped['today']->push($lead);
            } elseif ($lead->created_at >= $startYesterday) {
                $grouped['yesterday']->push($lead);
            } elseif ($lead->created_at >= $startWeek) {
                $grouped['thisWeek']->push($lead);
            } else {
                $grouped['older']->push($lead);
            }
        }

        // Sidebar folder counts (computed once)
        $allQ = LeadResource::getEloquentQuery();
        $totalAll = (clone $allQ)->count();
        $totalUnread = (clone $allQ)->whereNotIn('id', array_merge($this->readIds, [0]))->count();
        $totalHot = (clone $allQ)->where('score', '>=', 80)->count();
        $totalPinned = empty($this->pinnedIds) ? 0 : (clone $allQ)->whereIn('id', $this->pinnedIds)->count();

        $selected = null;
        if ($this->selectedId) {
            $selected = Lead::with([
                'country',
                'tags',
                'activities' => fn ($q) => $q->with('user:id,name')->latest()->limit(50),
            ])->find($this->selectedId);
        }

        // Companies grouping — only computed when needed (saves work for inbox/contacts views).
        // Each entry: name, count, latest_at, breakdown_by_status, countries, total_estimated_value.
        $companies = collect();
        if ($this->viewMode === 'companies') {
            $companies = $leads->groupBy(fn ($l) => trim((string) $l->company) ?: '— Sin empresa —')
                ->map(function ($group, $name) {
                    return [
                        'name'      => $name,
                        'count'     => $group->count(),
                        'latest'    => $group->max('created_at'),
                        'leads'     => $group,
                        'statuses'  => $group->groupBy('status')->map->count()->toArray(),
                        'countries' => $group->pluck('country.code')->filter()->unique()->values()->toArray(),
                        'value'     => (float) $group->sum('estimated_value'),
                    ];
                })
                ->sortByDesc('count')
                ->values();
        }

        return [
            'leads'        => $leads, // flat collection — used by contacts table
            'grouped'      => $grouped, // date-bucket grouping — used by inbox view
            'companies'    => $companies, // groupBy(company) — used by companies view
            'viewMode'     => $this->viewMode,
            'totalShown'   => $leads->count(),
            'selected'     => $selected,
            'folderCounts' => [
                'all'     => $totalAll,
                'unread'  => $totalUnread,
                'hot'     => $totalHot,
                'pinned'  => $totalPinned,
            ],
            'statuses'     => self::statusOptions(),
        ];
    }
}
