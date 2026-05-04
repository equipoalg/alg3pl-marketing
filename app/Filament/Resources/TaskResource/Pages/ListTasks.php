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
        $task = Task::find($taskId);
        if (! $task) return;
        $task->update(['status' => $newStatus]);
        // Silent — no notification on DnD/inline-edit because they're high-frequency
    }

    public function setPriority(int $taskId, string $newPriority): void
    {
        if (! in_array($newPriority, ['P0', 'P1', 'P2', 'P3'], true)) return;
        $task = Task::find($taskId);
        if (! $task) return;
        $task->update(['priority' => $newPriority]);
    }

    public function setCategory(int $taskId, string $newCategory): void
    {
        if (! in_array($newCategory, ['seo', 'technical', 'content', 'ux', 'marketing', 'analytics'], true)) return;
        $task = Task::find($taskId);
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
        $task = Task::find($taskId);
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
        $task = Task::find($this->selectedId);
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
        Task::where('id', $this->selectedId)->delete();
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

    public function mount(): void
    {
        // If URL has ?selected=N on first load, populate edit fields too
        if ($this->selectedId) {
            $this->selectTask($this->selectedId);
        }
    }

    /* ───── Filter presets ───── */

    /**
     * Catalog of preset filters: label, icon, and the closure that applies
     * the filter to a Task query.
     *
     * @return array<string, array{label:string, icon:string, apply:callable}>
     */
    public function filterPresets(): array
    {
        $userEmail = auth()->user()?->email;
        return [
            'all' => [
                'label' => 'Todas',
                'icon'  => '◆',
                'apply' => fn ($q) => $q,
            ],
            'mine' => [
                'label' => 'Asignadas a mí',
                'icon'  => '◉',
                'apply' => fn ($q) => $q->where('assignee', $userEmail),
            ],
            'overdue' => [
                'label' => 'Vencidas',
                'icon'  => '◒',
                'apply' => fn ($q) => $q->whereDate('due_date', '<', now())->where('status', '!=', 'done'),
            ],
            'unassigned' => [
                'label' => 'Sin asignar',
                'icon'  => '○',
                'apply' => fn ($q) => $q->whereNull('assignee')->orWhere('assignee', ''),
            ],
            'no_due' => [
                'label' => 'Sin fecha',
                'icon'  => '∅',
                'apply' => fn ($q) => $q->whereNull('due_date'),
            ],
            'high_priority' => [
                'label' => 'Alta prioridad',
                'icon'  => '★',
                'apply' => fn ($q) => $q->whereIn('priority', ['P0', 'P1'])->where('status', '!=', 'done'),
            ],
            'in_progress' => [
                'label' => 'En progreso',
                'icon'  => '▶',
                'apply' => fn ($q) => $q->where('status', 'in_progress'),
            ],
            'blocked' => [
                'label' => 'Bloqueadas',
                'icon'  => '⛔',
                'apply' => fn ($q) => $q->where('status', 'blocked'),
            ],
            'done' => [
                'label' => 'Completadas',
                'icon'  => '✓',
                'apply' => fn ($q) => $q->where('status', 'done'),
            ],
        ];
    }

    /* ───── View data ───── */

    public function getViewData(): array
    {
        $presets = $this->filterPresets();

        // Build base query (apply preset + search + country scope from sidebar)
        $base = TaskResource::getEloquentQuery()->with('country');

        $applyPreset = $presets[$this->filterPreset]['apply'] ?? null;
        if ($applyPreset) {
            $base = call_user_func($applyPreset, $base);
        }

        if ($this->searchTerm !== '') {
            $like = '%' . $this->searchTerm . '%';
            $base->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhere('assignee', 'like', $like);
            });
        }

        // Pre-compute counts for the sidebar badges (one quick count per preset)
        $presetCounts = [];
        foreach ($presets as $key => $preset) {
            $sub = TaskResource::getEloquentQuery();
            $presetCounts[$key] = (clone call_user_func($preset['apply'], $sub))->count();
        }

        // Fetch tasks once; both list & kanban modes use this collection
        $tasks = (clone $base)
            ->orderByRaw("CASE priority WHEN 'P0' THEN 0 WHEN 'P1' THEN 1 WHEN 'P2' THEN 2 WHEN 'P3' THEN 3 ELSE 9 END")
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->limit(500)
            ->get();

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
        // for display in the detail view)
        $selected = null;
        if ($this->selectedId) {
            $selected = Task::with('country')->find($this->selectedId);
        }

        return [
            'tasks'         => $tasks,
            'grouped'       => $grouped,
            'kanbanColumns' => $kanbanColumns,
            'presets'       => $presets,
            'presetCounts'  => $presetCounts,
            'totalShown'    => $tasks->count(),
            'selected'      => $selected,
        ];
    }
}
