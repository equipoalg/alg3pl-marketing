<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskComment;

class TaskObserver
{
    public function updating(Task $task): void
    {
        if ($task->isDirty('status')) {
            $old = $task->getOriginal('status');
            $new = $task->status;

            $labels = [
                'pending' => 'Pendiente', 'in_progress' => 'En Progreso',
                'done' => 'Completada', 'blocked' => 'Bloqueada',
            ];

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'body' => "Estado cambiado de " . ($labels[$old] ?? $old) . " a " . ($labels[$new] ?? $new),
                'type' => 'status_change',
            ]);

            if ($new === 'in_progress' && !$task->started_at) {
                $task->started_at = now();
            }
            if ($new === 'done' && !$task->completed_at) {
                $task->completed_at = now();
            }
        }

        if ($task->isDirty('assignee')) {
            $old = $task->getOriginal('assignee') ?: 'nadie';
            $new = $task->assignee ?: 'nadie';

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'body' => "Asignado cambiado de {$old} a {$new}",
                'type' => 'assignment',
            ]);
        }
    }
}
