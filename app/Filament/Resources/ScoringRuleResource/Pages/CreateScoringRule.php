<?php

namespace App\Filament\Resources\ScoringRuleResource\Pages;

use App\Filament\Resources\ScoringRuleResource;
use App\Models\ScoringRule;
use Filament\Resources\Pages\CreateRecord;

class CreateScoringRule extends CreateRecord
{
    protected static string $resource = ScoringRuleResource::class;

    protected function afterCreate(): void
    {
        ScoringRule::flushCache();
    }
}
