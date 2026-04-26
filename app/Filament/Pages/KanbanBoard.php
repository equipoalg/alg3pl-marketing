<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\TaskComment;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;

class KanbanBoard extends Page
{
    protected string $view = 'filament.pages.kanban-board';
    protected Width|string|null $maxContentWidth = Width::Full;

    public ?string $countryFilter = '';
    public bool $myTasksOnly = false;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-view-columns';
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing search';
    }

    public static function getNavigationSort(): int
    {
        return 1;
    }

    public static function getNavigationLabel(): string
    {
        return 'Kanban Board';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'kanban';
    }

    public function getTitle(): string
    {
        return 'Kanban Board';
    }

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    public function toggleMyTasks(): void
    {
        $this->myTasksOnly = !$this->myTasksOnly;
    }

    public function moveTask(int $taskId, string $newStatus, int $newPosition): void
    {
        $task = Task::findOrFail($taskId);

        Task::where('status', $newStatus)
            ->where('position', '>=', $newPosition)
            ->increment('position');

        $task->update([
            'status' => $newStatus,
            'position' => $newPosition,
        ]);
    }

    public function markDone(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => 'done', 'completed_at' => now()]);
        Notification::make()->title('Tarea completada')->success()->send();
    }

    public function duplicateTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $new = $task->replicate(['status', 'started_at', 'completed_at', 'actual_hours']);
        $new->status = 'pending';
        $new->started_at = null;
        $new->completed_at = null;
        $new->actual_hours = null;
        $new->position = Task::where('status', 'pending')->max('position') + 1;
        if ($new->checklist) {
            $new->checklist = collect($new->checklist)->map(fn($item) => array_merge($item, ['done' => false]))->toArray();
        }
        $new->save();
        Notification::make()->title('Tarea duplicada')->success()->send();
    }

    public function quickAddTask(string $title, string $status = 'pending'): void
    {
        if (trim($title) === '') return;

        $pos = Task::where('status', $status)->max('position') + 1;

        Task::create([
            'title' => trim($title),
            'status' => $status,
            'position' => $pos,
            'priority' => 'P2',
            'category' => 'seo',
            'country_id' => $this->countryFilter ?: null,
            'assignee' => auth()->user()?->email,
        ]);

        Notification::make()->title('Tarea creada')->success()->send();
    }

    public function getViewData(): array
    {
        $query = Task::query()->with('country');

        if ($this->countryFilter) {
            $query->where('country_id', $this->countryFilter);
        }
        if ($this->myTasksOnly && auth()->user()) {
            $query->where('assignee', auth()->user()->email);
        }

        $tasks = $query->orderBy('position')->orderBy('priority')->get();

        return [
            'columns' => [
                'pending' => ['label' => 'Pendiente', 'color' => '#6B7280', 'icon' => 'clock', 'tasks' => $tasks->where('status', 'pending')->values()],
                'in_progress' => ['label' => 'En Progreso', 'color' => '#3B82F6', 'icon' => 'play', 'tasks' => $tasks->where('status', 'in_progress')->values()],
                'blocked' => ['label' => 'Bloqueada', 'color' => '#EF4444', 'icon' => 'no-symbol', 'tasks' => $tasks->where('status', 'blocked')->values()],
                'done' => ['label' => 'Completada', 'color' => '#10B981', 'icon' => 'check-circle', 'tasks' => $tasks->where('status', 'done')->values()],
            ],
            'totalTasks' => $tasks->count(),
        ];
    }
}
