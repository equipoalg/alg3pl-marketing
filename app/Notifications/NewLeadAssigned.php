<?php

namespace App\Notifications;

use App\Models\Lead;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;

class NewLeadAssigned extends BaseNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Lead $lead)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return Notification::make()
            ->title('Nuevo lead asignado')
            ->body(
                'Nuevo lead asignado: ' . $this->lead->name .
                ($this->lead->company ? ' de ' . $this->lead->company : '')
            )
            ->icon('heroicon-o-user-plus')
            ->iconColor('success')
            ->actions([
                Action::make('ver_lead')
                    ->label('Ver lead')
                    ->url('/admin/leads/' . $this->lead->id)
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
