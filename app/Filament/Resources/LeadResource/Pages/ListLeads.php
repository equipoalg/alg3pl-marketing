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

    /**
     * Sort order — URL-bound. Valid values:
     *   recent (default), score_desc, value_desc, stalled_first
     */
    #[Url(as: 'sort')]
    public string $sortBy = 'recent';

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

    /* ───── Tags (slide-over) ───── */

    public function attachTagToSelected(int $tagId): void
    {
        if (! $this->selectedId) return;
        $lead = Lead::find($this->selectedId);
        if (! $lead) return;
        $lead->tags()->syncWithoutDetaching([$tagId]);
    }

    public function detachTagFromSelected(int $tagId): void
    {
        if (! $this->selectedId) return;
        $lead = Lead::find($this->selectedId);
        if (! $lead) return;
        $lead->tags()->detach($tagId);
    }

    /* ───── Snooze / Follow-up reminder ───── */

    /**
     * Snooze el lead seleccionado hasta una fecha futura.
     * Acepta presets relativos: 1h, 3h, tomorrow, monday, week.
     */
    public function snoozeSelected(string $when): void
    {
        if (! $this->selectedId) return;
        $until = match ($when) {
            '1h'       => now()->addHour(),
            '3h'       => now()->addHours(3),
            'tomorrow' => now()->addDay()->setTime(9, 0),
            'monday'   => now()->next('Monday')->setTime(9, 0),
            'week'     => now()->addWeek()->setTime(9, 0),
            default    => null,
        };
        if (! $until) return;
        Lead::where('id', $this->selectedId)->update(['snoozed_until' => $until]);
        \Filament\Notifications\Notification::make()
            ->title('Snooze activado')
            ->body('Vuelve el ' . $until->translatedFormat('d M H:i'))
            ->success()->send();
        $this->selectedId = null; // close the slide-over so the lead disappears
    }

    public function unsnoozeSelected(): void
    {
        if (! $this->selectedId) return;
        Lead::where('id', $this->selectedId)->update(['snoozed_until' => null]);
        \Filament\Notifications\Notification::make()->title('Snooze removido')->success()->send();
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
        } elseif ($this->folder === 'snoozed') {
            // Folder Snoozed: leads aún en estado snooze (snoozed_until > now())
            $q->where('snoozed_until', '>', now());
        }

        // Por default (cualquier folder excepto 'snoozed'), ocultar los leads
        // que están durmiendo. Si snoozed_until pasó, vuelven al inbox automáticamente.
        if ($this->folder !== 'snoozed') {
            $q->where(function ($qq) {
                $qq->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
            });
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
        $q->orderByRaw("CASE WHEN id IN ($pinnedCsv) THEN 0 ELSE 1 END");

        // Smart sort — fallback a recent
        return match ($this->sortBy) {
            'score_desc'    => $q->orderByDesc('score')->orderByDesc('created_at'),
            'value_desc'    => $q->orderByDesc('estimated_value')->orderByDesc('created_at'),
            // Stalled first: leads sin activity reciente Y en pipeline activo
            'stalled_first' => $q->orderByRaw("CASE WHEN status IN ('contacted','qualified','proposal','negotiation') THEN 0 ELSE 1 END")
                                 ->orderBy('updated_at', 'asc'),
            default         => $q->orderBy('created_at', 'desc'),
        };
    }

    public function setSortBy(string $value): void
    {
        if (in_array($value, ['recent', 'score_desc', 'value_desc', 'stalled_first'], true)) {
            $this->sortBy = $value;
        }
    }

    /* ───── Saved views (per-user, persisted en users.preferences) ───── */

    public const SAVED_VIEW_NAME_MAX = 40;
    public const SAVED_VIEW_MAX_COUNT = 20;

    public function saveCurrentView(string $name): void
    {
        $name = trim($name);
        if ($name === '') return;
        if (mb_strlen($name) > self::SAVED_VIEW_NAME_MAX) {
            $name = mb_substr($name, 0, self::SAVED_VIEW_NAME_MAX);
        }
        $user = auth()->user();
        if (! $user) return;

        $existing = $user->pref('lead_views', []);
        // Dedup por nombre
        $existing = array_values(array_filter($existing, fn ($v) => ($v['name'] ?? '') !== $name));
        $existing[] = [
            'name'   => $name,
            'view'   => $this->viewMode,
            'status' => $this->statusFilter,
            'period' => $this->periodFilter,
            'folder' => $this->folder,
            'q'      => $this->search,
            'sort'   => $this->sortBy,
        ];
        if (count($existing) > self::SAVED_VIEW_MAX_COUNT) {
            $existing = array_slice($existing, -self::SAVED_VIEW_MAX_COUNT);
        }
        $user->setPrefs(['lead_views' => array_values($existing)]);
        Notification::make()->title("Vista \"$name\" guardada")->success()->send();
    }

    public function loadLeadView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('lead_views', []);
        if (! isset($views[$index])) return;
        $v = $views[$index];
        $this->viewMode      = $v['view']   ?? 'contacts';
        $this->statusFilter  = $v['status'] ?? '';
        $this->periodFilter  = $v['period'] ?? '';
        $this->folder        = $v['folder'] ?? 'all';
        $this->search        = $v['q']      ?? '';
        $this->sortBy        = $v['sort']   ?? 'recent';
    }

    public function deleteLeadView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('lead_views', []);
        if (! isset($views[$index])) return;
        unset($views[$index]);
        $user->setPrefs(['lead_views' => array_values($views)]);
        Notification::make()->title('Vista eliminada')->success()->send();
    }

    public function getViewData(): array
    {
        // Cargar leads + last activity timestamp en una sola query (subSelect)
        // para poder marcar "Stalled" sin N+1.
        $leads = $this->buildQuery()
            ->with('country')
            ->withMax('activities as latest_activity_at', 'created_at')
            ->limit(500)
            ->get();

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
        // Note: counts excluyen leads snoozed activos para que el folder "Todos"
        // no infle el número con leads que el usuario ya pospuso.
        $allQ = LeadResource::getEloquentQuery();
        $awakeQ = (clone $allQ)->where(function ($qq) {
            $qq->whereNull('snoozed_until')->orWhere('snoozed_until', '<=', now());
        });
        $totalAll     = (clone $awakeQ)->count();
        $totalUnread  = (clone $awakeQ)->whereNotIn('id', array_merge($this->readIds, [0]))->count();
        $totalHot     = (clone $awakeQ)->where('score', '>=', 80)->count();
        $totalPinned  = empty($this->pinnedIds) ? 0 : (clone $awakeQ)->whereIn('id', $this->pinnedIds)->count();
        $totalSnoozed = (clone $allQ)->where('snoozed_until', '>', now())->count();

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

        // Available tags for the slide-over chip picker (only loaded when we have a selected lead)
        $availableTags = collect();
        if ($selected) {
            $availableTags = \App\Models\Tag::orderBy('name')->get(['id', 'name', 'color']);
        }

        // User-saved views (per-user, JSON in preferences)
        $savedViews = auth()->user()?->pref('lead_views', []) ?? [];

        return [
            'leads'         => $leads, // flat collection — used by contacts table
            'grouped'       => $grouped, // date-bucket grouping — used by inbox view
            'companies'     => $companies, // groupBy(company) — used by companies view
            'viewMode'      => $this->viewMode,
            'totalShown'    => $leads->count(),
            'selected'      => $selected,
            'availableTags' => $availableTags,
            'folderCounts'  => [
                'all'     => $totalAll,
                'unread'  => $totalUnread,
                'hot'     => $totalHot,
                'pinned'  => $totalPinned,
                'snoozed' => $totalSnoozed,
            ],
            'statuses'      => self::statusOptions(),
            'sortBy'        => $this->sortBy,
            'savedViews'    => $savedViews,
        ];
    }
}
