<?php

namespace App\Filament\Resources\CountryReportResource\Pages;

use App\Filament\Resources\CountryReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountryReports extends ListRecords
{
    protected static string $resource = CountryReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
