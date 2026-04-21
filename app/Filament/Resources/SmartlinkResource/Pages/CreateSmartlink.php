<?php

namespace App\Filament\Resources\SmartlinkResource\Pages;

use App\Filament\Resources\SmartlinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmartlink extends CreateRecord
{
    protected static string $resource = SmartlinkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id ?? 1;
        return $data;
    }
}
