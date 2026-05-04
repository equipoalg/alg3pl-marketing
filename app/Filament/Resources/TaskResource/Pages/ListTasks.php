<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Tasks "hub" — replaces the default list with a Rocketlane-style 2-column UI.
 *
 * Left column: filter preset sidebar (Todo / Asignadas a mí / Vencidas / etc.)
 * Right column: toolbar (search + view toggle + group-by + new) + body
 *
 * Body switches between two views via $viewMode:
 *   - 'list'   → grouped table (default)
 *   - 'kanban' → drag-drop columns by status (4 cols: pending / in_progress
 *                / blocked / done)
 *
 * URL-bound state (#[Url]) so deep-links and back-button work.
 */
class ListTasks extends Page
{
    protected static string $resource = TaskResource::class;
    protected string $view = 'filament.resources.task-resource.pages.tasks-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    /** Active rendering mode. */
    #[Url(as: 'view')]
    public string $viewMode = 'list';

    /** Filter preset key — see filterPresets() for options. */
    #[Url(as: 'preset')]
    public string $filterPreset = 'all';

    /** What to group rows by in list mode. */
    #[Url(as: 'group')]
    public string $groupBy = 'status';

    /** Free-text search across title / assignee. */
    #[Url(as: 'q')]
    public string $searchTerm = '';

    /** Currently-open task in the right slide-over pane (null = closed). */
    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    /** Inline-edit form state (loaded from selectedTask). */
    public string $editTitle = '';
    public ?string $editDescription = null;
    public ?string $editDueDate = null;
    public ?string $editAssignee = null;

    /** Multi-select bulk state — IDs of currently-checked tasks. */
    public array $selectedIds = [];

    /** Filter chips (multi-select, AND-combined). URL-bound as comma list. */
    #[Url(as: 'priority')]
    public string $priorityFilter = '';

    #[Url(as: 'cat')]
    public string $categoryFilter = '';

    #[Url(as: 'due')]
    public string $dueFilter = '';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return ''; // suppress Filament heading; our custom toolbar replaces it
    }

    public function getTitle(): string
    {
        return 'Seguimiento';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    /* ───── Mutators (Livewire-bindable) ───── */

    public function setViewMode(string $value): void
    {
        if (in_array($value, ['list', 'kanban'], true)) {
            $this->viewMode = $value;
        }
    }

    public function setFilterPreset(string $value): void
    {
        if (array_key_exists($value, $this->filterPresets())) {
            $this->filterPreset = $value;
        }
    }

    public function setGroupBy(string $value): void
    {
        if (in_array($value, ['status', 'priority', 'category', 'country', 'assignee', 'none'], true)) {
            $this->groupBy = $value;
        }
    }

    /* ───── Task actions (kanban + list share these) ───── */

    public function moveTaskStatus(int $taskId, string $newStatus): void
    {
        if (! in_array($newStatus, ['pending', 'in_progress', 'blocked', 'done'], true)) {
            return;
        }
        // Use scoped query so a forged taskId from another country can't be touched.
        $task = TaskResource::getEloquentQuery()->find($taskId);
        if (! $task) return;
        $task->update(['status' => $newStatus]);
        // Silent — no notification on DnD/inline-edit because they're high-frequency
    }

    public function setPriority(int $taskId, string $newPriority): void
    {
        if (! in_array($newPriority, ['P0', 'P1', 'P2', 'P3'], true)) return;
        $task = TaskResource::getEloquentQuery()->find($taskId);
        if (! $task) return;
        $task->update(['priority' => $newPriority]);
    }

    public function setCategory(int $taskId, string $newCategory): void
    {
        if (! in_array($newCategory, ['seo', 'technical', 'content', 'ux', 'marketing', 'analytics'], true)) return;
        $task = TaskResource::getEloquentQuery()->find($taskId);
        if (! $task) return;
        $task->update(['category' => $newCategory]);
    }

    public function markDone(int $taskId): void
    {
        $this->moveTaskStatus($taskId, 'done');
    }

    public function quickAdd(string $title, string $status = 'pending'): void
    {
        $title = trim($title);
        if ($title === '') return;

        Task::create([
            'title'      => $title,
            'status'     => $status,
            'priority'   => 'P2',
            'category'   => 'seo',
            'country_id' => session('country_filter') ? (int) session('country_filter') : null,
            'assignee'   => auth()->user()?->email,
        ]);
        Notification::make()->title('Tarea creada')->success()->send();
    }

    /* ───── Slide-over (right pane) ───── */

    /** Open the right pane for the given task — also hydrates inline-edit fields. */
    public function selectTask(int $taskId): void
    {
        $this->selectedId = $taskId;
        $task = TaskResource::getEloquentQuery()->find($taskId);
        if (! $task) {
            $this->selectedId = null;
            return;
        }
        $this->editTitle       = (string) $task->title;
        $this->editDescription = $task->description;
        $this->editDueDate     = $task->due_date?->format('Y-m-d');
        $this->editAssignee    = $task->assignee;
    }

    public function closeDetail(): void
    {
        $this->selectedId = null;
    }

    public function saveDetail(): void
    {
        if (! $this->selectedId) return;
        $task = TaskResource::getEloquentQuery()->find($this->selectedId);
        if (! $task) return;
        $task->update([
            'title'       => trim($this->editTitle) ?: $task->title,
            'description' => $this->editDescription,
            'due_date'    => $this->editDueDate ?: null,
            'assignee'    => $this->editAssignee ?: null,
        ]);
        Notification::make()->title('Tarea guardada')->success()->send();
    }

    public function deleteSelected(): void
    {
        if (! $this->selectedId) return;
        TaskResource::getEloquentQuery()->where('id', $this->selectedId)->delete();
        $this->selectedId = null;
        Notification::make()->title('Tarea eliminada')->success()->send();
    }

    /** Re-hydrate edit fields when selectedId changes via URL navigation */
    public function updatedSelectedId($value): void
    {
        if ($value) {
            $this->selectTask((int) $value);
        }
    }

    /* ───── Filter chips (toggle on/off — URL-bound CSV) ───── */

    private function toggleCsv(string $current, string $value): string
    {
        $items = array_filter(explode(',', $current));
        if (in_array($value, $items, true)) {
            $items = array_filter($items, fn ($i) => $i !== $value);
        } else {
            $items[] = $value;
        }
        return implode(',', $items);
    }

    public function togglePriorityChip(string $value): void
    {
        if (in_array($value, ['P0', 'P1', 'P2', 'P3'], true)) {
            $this->priorityFilter = $this->toggleCsv($this->priorityFilter, $value);
        }
    }

    public function toggleCategoryChip(string $value): void
    {
        if (in_array($value, ['seo', 'technical', 'content', 'ux', 'marketing', 'analytics'], true)) {
            $this->categoryFilter = $this->toggleCsv($this->categoryFilter, $value);
        }
    }

    public function toggleDueChip(string $value): void
    {
        if (in_array($value, ['today', 'this_week', 'this_month', 'overdue'], true)) {
            // Due is single-select (these ranges are mutually exclusive)
            $this->dueFilter = $this->dueFilter === $value ? '' : $value;
        }
    }

    public function clearAllChips(): void
    {
        $this->priorityFilter = '';
        $this->categoryFilter = '';
        $this->dueFilter = '';
    }

    /* ───── Bulk actions (operate on $selectedIds) ───── */

    public function toggleSelected(int $taskId): void
    {
        if (in_array($taskId, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn ($i) => $i !== $taskId));
        } else {
            $this->selectedIds[] = $taskId;
        }
    }

    public function clearSelected(): void
    {
        $this->selectedIds = [];
    }

    public function bulkMarkDone(): void
    {
        if (empty($this->selectedIds)) return;
        // Scope through TaskResource so cross-tenant IDs in $selectedIds (forged
        // via DOM tampering or Livewire payload) cannot leak into another country.
        TaskResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['status' => 'done']);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("$count tareas marcadas como completadas")->success()->send();
    }

    public function bulkSetPriority(string $priority): void
    {
        if (empty($this->selectedIds)) return;
        if (! in_array($priority, ['P0', 'P1', 'P2', 'P3'], true)) return;
        TaskResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['priority' => $priority]);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("Prioridad $priority aplicada a $count tareas")->success()->send();
    }

    public function bulkAssignToMe(): void
    {
        if (empty($this->selectedIds)) return;
        $email = auth()->user()?->email;
        if (! $email) return;
        TaskResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->update(['assignee' => $email]);
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        Notification::make()->title("$count tareas asignadas a ti")->success()->send();
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedIds)) return;
        $count = count($this->selectedIds);
        TaskResource::getEloquentQuery()->whereIn('id', $this->selectedIds)->delete();
        $this->selectedIds = [];
        Notification::make()->title("$count tareas eliminadas")->success()->send();
    }

    /* ───── Saved views (persisted per-user in JSON preferences) ───── */

    /**
     * Snapshot the current filter+group+search+chips combo and save it under
     * a user-supplied name. Stored at users.preferences->task_views[].
     */
    public function saveCurrentView(string $name): void
    {
        $name = trim($name);
        if ($name === '') return;
        $user = auth()->user();
        if (! $user) return;

        $existing = $user->pref('task_views', []);
        $existing[] = [
            'name'     => $name,
            'view'     => $this->viewMode,
            'preset'   => $this->filterPreset,
            'group'    => $this->groupBy,
            'search'   => $this->searchTerm,
            'priority' => $this->priorityFilter,
            'cat'      => $this->categoryFilter,
            'due'      => $this->dueFilter,
        ];
        $user->setPrefs(['task_views' => array_values($existing)]);
        Notification::make()->title("Vista \"$name\" guardada")->success()->send();
    }

    /** Restore a saved view by index. */
    public function loadView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('task_views', []);
        if (! isset($views[$index])) return;
        $v = $views[$index];
        $this->viewMode       = $v['view']     ?? 'list';
        $this->filterPreset   = $v['preset']   ?? 'all';
        $this->groupBy        = $v['group']    ?? 'status';
        $this->searchTerm     = $v['search']   ?? '';
        $this->priorityFilter = $v['priority'] ?? '';
        $this->categoryFilter = $v['cat']      ?? '';
        $this->dueFilter      = $v['due']      ?? '';
    }

    public function deleteView(int $index): void
    {
        $user = auth()->user();
        if (! $user) return;
        $views = $user->pref('task_views', []);
        if (! isset($views[$index])) return;
        unset($views[$index]);
        $user->setPrefs(['task_views' => array_values($views)]);
        Notification::make()->title('Vista eliminada')->success()->send();
    }

    public function mount(): void
    {
        // If URL has ?selected=N on first load, populate edit fields too
        if ($this->selectedId) {
            $this->selectTask($this->selectedId);
        }
    }

    /* ───── Filter presets ───── */

    /**
     * Catalog of preset filters: label, icon, the closure that applies
     * the filter to a Task query, and a SQL CASE expression used by the
     * batched count (all 9 counts in 1 query — see countPresetsBatched()).
     *
     * The 'apply' closure and 'sqlCase' fragment must encode the same
     * predicate; if you change one, change the other.
     *
     * @return array<string, array{label:string, icon:string, apply:callable, sqlCase:string, binds:array}>
     */
    public function filterPresets(): array
    {
        $userEmail = auth()->user()?->email ?? '__nobody__@invalid';
        $today     = today()->format('Y-m-d');
        return [
            'all' => [
                'label'   => 'Todas',
                'icon'    => '◆',
                'apply'   => fn ($q) => $q,
                'sqlCase' => '1=1',
                'binds'   => [],
            ],
            'mine' => [
                'label'   => 'Asignadas a mí',
                'icon'    => '◉',
                'apply'   => fn ($q) => $q->where('assignee', $userEmail),
                'sqlCase' => 'assignee = ?',
                'binds'   => [$userEmail],
            ],
            'overdue' => [
                'label'   => 'Vencidas',
                'icon'    => '◒',
                'apply'   => fn ($q) => $q->whereDate('due_date', '<', now())->where('status', '!=', 'done'),
                'sqlCase' => "due_date < ? AND status != 'done'",
                'binds'   => [$today],
            ],
            'unassigned' => [
                'label'   => 'Sin asignar',
                'icon'    => '○',
                // Wrap the OR in a closure so the AND clause from country-scope
                // isn't broken: WHERE country=X AND (assignee IS NULL OR assignee='')
                // (without the closure it becomes: WHERE country=X AND assignee IS NULL OR assignee='' — leak)
                'apply'   => fn ($q) => $q->where(fn ($qq) => $qq->whereNull('assignee')->orWhere('assignee', '')),
                'sqlCase' => "(assignee IS NULL OR assignee = '')",
                'binds'   => [],
            ],
            'no_due' => [
                'label'   => 'Sin fecha',
                'icon'    => '∅',
                'apply'   => fn ($q) => $q->whereNull('due_date'),
                'sqlCase' => 'due_date IS NULL',
                'binds'   => [],
            ],
            'high_priority' => [
                'label'   => 'Alta prioridad',
                'icon'    => '★',
                'apply'   => fn ($q) => $q->whereIn('priority', ['P0', 'P1'])->where('status', '!=', 'done'),
                'sqlCase' => "priority IN ('P0','P1') AND status != 'done'",
                'binds'   => [],
            ],
            'in_progress' => [
                'label'   => 'En progreso',
                'icon'    => '▶',
                'apply'   => fn ($q) => $q->where('status', 'in_progress'),
                'sqlCase' => "status = 'in_progress'",
                'binds'   => [],
            ],
            'blocked' => [
                'label'   => 'Bloqueadas',
                'icon'    => '⛔',
                'apply'   => fn ($q) => $q->where('status', 'blocked'),
                'sqlCase' => "status = 'blocked'",
                'binds'   => [],
            ],
            'done' => [
                'label'   => 'Completadas',
                'icon'    => '✓',
                'apply'   => fn ($q) => $q->where('status', 'done'),
                'sqlCase' => "status = 'done'",
                'binds'   => [],
            ],
        ];
    }

    /**
     * Compute all 9 sidebar preset counts in a single SQL query, scoped by
     * the user's country and narrowed by the active chips + search.
     *
     * Old code ran 9 count() queries inside a foreach (~30-50ms in MySQL with
     * indexes); this batches them via SUM(CASE WHEN ... THEN 1 ELSE 0 END)
     * for 1 round-trip.
     *
     * @return array<string, int>
     */
    private function countPresetsBatched(array $presets): array
    {
        $selectParts = [];
        $bindings    = [];
        foreach ($presets as $key => $preset) {
            // Use a sanitized alias matching the preset key (only \w chars allowed).
            $alias = 'c_' . preg_replace('/\W+/', '_', $key);
            $selectParts[] = "SUM(CASE WHEN {$preset['sqlCase']} THEN 1 ELSE 0 END) AS {$alias}";
            $bindings = array_merge($bindings, $preset['binds']);
        }
        $selectExpr = implode(', ', $selectParts);

        $base = TaskResource::getEloquentQuery();
        $base = $this->applyChipsAndSearch($base);

        $row = $base->selectRaw($selectExpr, $bindings)->first();

        $counts = [];
        foreach ($presets as $key => $_) {
            $alias        = 'c_' . preg_replace('/\W+/', '_', $key);
            $counts[$key] = (int) ($row->{$alias} ?? 0);
        }
        return $counts;
    }

    /* ───── View data ───── */

    /** Hard ceiling on rows fetched per render — protects the page from
     *  loading 10k+ rows in memory if a country has explosive growth. */
    public const VIEWPORT_LIMIT = 1000;

    /**
     * Apply chip filters + search to a query. Extracted so we can reuse it
     * both for the active fetch AND for sidebar preset counts (so each preset
     * count honestly reflects what the user would see if they clicked it).
     */
    private function applyChipsAndSearch($q)
    {
        if ($this->searchTerm !== '') {
            $like = '%' . $this->searchTerm . '%';
            $q->where(function ($qq) use ($like) {
                $qq->where('title', 'like', $like)
                   ->orWhere('description', 'like', $like)
                   ->orWhere('assignee', 'like', $like);
            });
        }
        $priorities = array_filter(explode(',', $this->priorityFilter));
        if (! empty($priorities)) {
            $q->whereIn('priority', $priorities);
        }
        $categories = array_filter(explode(',', $this->categoryFilter));
        if (! empty($categories)) {
            $q->whereIn('category', $categories);
        }
        if ($this->dueFilter !== '') {
            match ($this->dueFilter) {
                'today'      => $q->whereDate('due_date', today()),
                'this_week'  => $q->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]),
                'this_month' => $q->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()]),
                'overdue'    => $q->whereDate('due_date', '<', now())->where('status', '!=', 'done'),
                default      => null,
            };
        }
        return $q;
    }

    public function getViewData(): array
    {
        $presets = $this->filterPresets();

        // Build base query (apply preset + search/chips + country scope from sidebar)
        $base = TaskResource::getEloquentQuery()->with('country');

        $applyPreset = $presets[$this->filterPreset]['apply'] ?? null;
        if ($applyPreset) {
            $base = call_user_func($applyPreset, $base);
        }
        $base = $this->applyChipsAndSearch($base);

        // Honest counts:
        //   $totalUnfiltered  → tasks in user's country, NO filters at all
        //   $totalAfterFilter → tasks matching active preset + chips + search (before viewport limit)
        $totalUnfiltered  = TaskResource::getEloquentQuery()->count();
        $totalAfterFilter = (clone $base)->count();

        // Pre-compute counts for the sidebar badges. Each preset count REFLECTS
        // the active chips + search, so the user sees how many results each
        // preset would yield if they clicked it RIGHT NOW.
        // Batched: all 9 counts in 1 SQL query (was 9 separate count() calls).
        $presetCounts = $this->countPresetsBatched($presets);

        // Fetch tasks once; both list & kanban modes use this collection.
        // Note: viewport limit at 1000 with explicit "wasLimited" flag — old
        // behavior silently dropped rows past 500.
        $tasks = (clone $base)
            ->orderByRaw("CASE priority WHEN 'P0' THEN 0 WHEN 'P1' THEN 1 WHEN 'P2' THEN 2 WHEN 'P3' THEN 3 ELSE 9 END")
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->limit(self::VIEWPORT_LIMIT)
            ->get();
        $wasLimited = $totalAfterFilter > self::VIEWPORT_LIMIT;

        // For list mode: group by $groupBy
        $grouped = collect();
        if ($this->viewMode === 'list' && $this->groupBy !== 'none') {
            $grouped = $tasks->groupBy(function ($t) {
                return match ($this->groupBy) {
                    'priority' => $t->priority ?: 'P3',
                    'category' => $t->category ?: 'seo',
                    'country'  => $t->country?->name ?? '— sin país —',
                    'assignee' => $t->assignee ?: '— sin asignar —',
                    default    => $t->status ?: 'pending',
                };
            });
        }

        // For kanban mode: 4 fixed columns by status
        $kanbanColumns = [];
        if ($this->viewMode === 'kanban') {
            $kanbanColumns = [
                'pending'     => ['label' => 'Pendiente',   'color' => 'var(--alg-ink-4)',  'tasks' => $tasks->where('status', 'pending')->values()],
                'in_progress' => ['label' => 'En progreso', 'color' => 'var(--alg-accent)', 'tasks' => $tasks->where('status', 'in_progress')->values()],
                'blocked'     => ['label' => 'Bloqueada',   'color' => 'var(--alg-neg)',    'tasks' => $tasks->where('status', 'blocked')->values()],
                'done'        => ['label' => 'Completada',  'color' => 'var(--alg-pos)',    'tasks' => $tasks->where('status', 'done')->values()],
            ];
        }

        // Selected task for the slide-over pane (load with country relation
        // for display in the detail view) — must respect country scope.
        $selected = null;
        if ($this->selectedId) {
            $selected = TaskResource::getEloquentQuery()->with('country')->find($this->selectedId);
        }

        // Focus banner — count overdue + P0/P1 due today, scoped by country session
        $banner = self::computeFocusBanner();

        // User-saved views (from users.preferences JSON)
        $savedViews = auth()->user()?->pref('task_views', []) ?? [];

        return [
            'tasks'             => $tasks,
            'grouped'           => $grouped,
            'kanbanColumns'     => $kanbanColumns,
            'presets'           => $presets,
            'presetCounts'      => $presetCounts,
            'totalShown'        => $tasks->count(),       // rows actually rendered
            'totalAfterFilter'  => $totalAfterFilter,     // matches active filters (no viewport limit)
            'totalUnfiltered'   => $totalUnfiltered,      // country scope only — no filters
            'wasLimited'        => $wasLimited,           // true if filtered count > VIEWPORT_LIMIT
            'viewportLimit'     => self::VIEWPORT_LIMIT,
            'selected'          => $selected,
            'banner'            => $banner,
            'savedViews'        => $savedViews,
        ];
    }

    /**
     * Counts of "what should worry me right now":
     *   - overdue: due_date < today AND status != done
     *   - dueTodayHigh: due_date = today AND priority IN (P0, P1) AND status != done
     *
     * Returns ['overdue' => int, 'dueTodayHigh' => int, 'level' => 'critical'|'good'|'warning']
     */
    public static function computeFocusBanner(): array
    {
        $base = TaskResource::getEloquentQuery()->where('status', '!=', 'done');
        $overdue      = (clone $base)->whereDate('due_date', '<', today())->count();
        $dueTodayHigh = (clone $base)->whereDate('due_date', today())->whereIn('priority', ['P0', 'P1'])->count();

        $level = match (true) {
            $overdue > 0                        => 'critical',
            $dueTodayHigh > 0                   => 'warning',
            default                             => 'good',
        };

        return [
            'overdue'      => $overdue,
            'dueTodayHigh' => $dueTodayHigh,
            'level'        => $level,
        ];
    }

    /* ───── Visual helpers (single source of truth for all blade callsites) ───── */

    /** Returns ['bg' => css, 'fg' => css] for a priority chip. */
    public static function priorityColor(?string $priority): array
    {
        return match ($priority) {
            'P0'    => ['bg' => 'var(--alg-neg-soft)',    'fg' => 'var(--alg-neg)'],
            'P1'    => ['bg' => 'var(--alg-warn-soft)',   'fg' => 'var(--alg-warn)'],
            'P2'    => ['bg' => 'var(--alg-accent-soft)', 'fg' => 'var(--alg-accent)'],
            default => ['bg' => 'var(--alg-surface-2)',   'fg' => 'var(--alg-ink-4)'],
        };
    }

    /** Returns ['bg' => css, 'fg' => css] for a status badge. */
    public static function statusColor(?string $status): array
    {
        return match ($status) {
            'done'        => ['bg' => 'var(--alg-pos-soft)',    'fg' => 'var(--alg-pos)'],
            'in_progress' => ['bg' => 'var(--alg-accent-soft)', 'fg' => 'var(--alg-accent)'],
            'blocked'     => ['bg' => 'var(--alg-neg-soft)',    'fg' => 'var(--alg-neg)'],
            default       => ['bg' => 'var(--alg-surface-2)',   'fg' => 'var(--alg-ink-3)'],
        };
    }

    /** Spanish label for a status enum. */
    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending'     => 'Pendiente',
            'in_progress' => 'En progreso',
            'blocked'     => 'Bloqueada',
            'done'        => 'Completada',
            default       => $status ?? '',
        };
    }

    /**
     * Color-hashed avatar from an email — for the assignee chip.
     * Returns ['initials', 'bg', 'fg'] suitable for inline style use.
     */
    public static function avatarFor(?string $email): array
    {
        if (! $email) {
            return ['initials' => '?', 'bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)'];
        }
        // Initials: take chars before @ and split by . / _ / -
        $local = explode('@', $email)[0];
        $parts = preg_split('/[._\-+]/', $local) ?: [$local];
        $initials = strtoupper(substr($parts[0] ?? '?', 0, 1));
        if (count($parts) > 1) {
            $initials .= strtoupper(substr($parts[1], 0, 1));
        } else {
            $initials .= strtoupper(substr($local, 1, 1));
        }
        // 8-color palette, indexed by hash of email
        $palette = [
            ['bg' => '#FEE2E2', 'fg' => '#9F1239'], // rose
            ['bg' => '#FEF3C7', 'fg' => '#92400E'], // amber
            ['bg' => '#D1FAE5', 'fg' => '#065F46'], // emerald
            ['bg' => '#DBEAFE', 'fg' => '#1E3A8A'], // blue
            ['bg' => '#E0E7FF', 'fg' => '#3730A3'], // indigo
            ['bg' => '#EDE9FE', 'fg' => '#5B21B6'], // violet
            ['bg' => '#FCE7F3', 'fg' => '#9D174D'], // pink
            ['bg' => '#F1F5F9', 'fg' => '#334155'], // slate
        ];
        $idx = abs(crc32($email)) % count($palette);
        return [
            'initials' => $initials ?: '?',
            'bg'       => $palette[$idx]['bg'],
            'fg'       => $palette[$idx]['fg'],
        ];
    }
}
