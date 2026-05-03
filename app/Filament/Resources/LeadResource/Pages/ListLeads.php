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

    /** Status filter — URL-bound so /admin/leads?status=won lands pre-filtered. */
    #[Url(as: 'status')]
    public string $statusFilter = '';

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

    public function selectLead(int $id): void
    {
        $this->selectedId = $id;
        $this->replyText = '';
        $this->markRead($id);
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

        return [
            'grouped'      => $grouped,
            'totalShown'   => $leads->count(),
            'selected'     => $selected,
            'folderCounts' => [
                'all'     => $totalAll,
                'unread'  => $totalUnread,
                'hot'     => $totalHot,
                'pinned'  => $totalPinned,
            ],
            'statuses'     => [
                'new'         => 'Nuevos',
                'contacted'   => 'Contactados',
                'qualified'   => 'Calificados',
                'proposal'    => 'Propuesta',
                'negotiation' => 'Negociación',
                'won'         => 'Ganados',
                'lost'        => 'Perdidos',
            ],
        ];
    }
}
