<?php

namespace App\Filament\Resources\AdMetricResource\Pages;

use App\Filament\Resources\AdMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdMetric extends EditRecord
{
    protected static string $resource = AdMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
