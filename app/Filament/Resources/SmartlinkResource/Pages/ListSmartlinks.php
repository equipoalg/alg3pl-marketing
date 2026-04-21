<?php

namespace App\Filament\Resources\SmartlinkResource\Pages;

use App\Filament\Resources\SmartlinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmartlinks extends ListRecords
{
    protected static string $resource = SmartlinkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
