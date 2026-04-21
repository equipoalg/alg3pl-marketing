<?php

namespace App\Filament\Resources\CountryReportResource\Pages;

use App\Filament\Resources\CountryReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryReport extends EditRecord
{
    protected static string $resource = CountryReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
