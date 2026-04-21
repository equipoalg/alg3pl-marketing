<?php

namespace App\Filament\Resources\CountryConfigResource\Pages;

use App\Filament\Resources\CountryConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountryConfigs extends ListRecords
{
    protected static string $resource = CountryConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
