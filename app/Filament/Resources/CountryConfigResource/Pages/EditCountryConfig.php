<?php

namespace App\Filament\Resources\CountryConfigResource\Pages;

use App\Filament\Resources\CountryConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryConfig extends EditRecord
{
    protected static string $resource = CountryConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
