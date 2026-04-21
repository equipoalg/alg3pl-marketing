<?php

namespace App\Filament\Resources\SmartlinkResource\Pages;

use App\Filament\Resources\SmartlinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmartlink extends EditRecord
{
    protected static string $resource = SmartlinkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
