<?php

namespace App\Notifications;

use App\Models\Task;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;

class TaskOverdue extends BaseNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return Notification::make()
            ->title('Tarea vencida')
            ->body('Tarea vencida: ' . $this->task->title)
            ->icon('heroicon-o-clock')
            ->iconColor('danger')
            ->actions([
                Action::make('ver_kanban')
                    ->label('Ver en Kanban')
                    ->url('/admin/kanban')
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
