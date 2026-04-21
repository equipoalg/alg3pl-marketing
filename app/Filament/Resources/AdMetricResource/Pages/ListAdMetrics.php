<?php

namespace App\Filament\Resources\AdMetricResource\Pages;

use App\Filament\Resources\AdMetricResource;
use App\Jobs\SyncAdMetricsJob;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListAdMetrics extends ListRecords
{
    protected static string $resource = AdMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('Sincronizar Meta Ads')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    SyncAdMetricsJob::dispatch();
                    Notification::make()
                        ->title('Sincronización iniciada')
                        ->body('Los datos de Meta Ads se actualizarán en breve.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
