<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FunnelResource\Pages;
use App\Models\Country;
use App\Models\Funnel;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FunnelResource extends Resource
{
    use \App\Filament\Concerns\ScopesByCountryFilter;

    protected static ?string $model = Funnel::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-funnel'; }
    public static function getNavigationGroup(): string { return 'Marketing'; }
    public static function getNavigationSort(): int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funnel Details')
                ->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\Textarea::make('description')->rows(2),
                    Forms\Components\Select::make('country_id')->label('Country')
                        ->options(Country::pluck('name', 'id')),
                    Forms\Components\Select::make('status')
                        ->options(['draft' => 'Draft', 'active' => 'Active', 'paused' => 'Paused', 'archived' => 'Archived'])
                        ->default('draft'),
                    Forms\Components\Select::make('trigger_type')
                        ->options(['page_visit' => 'Page Visit', 'form_submit' => 'Form Submit', 'api_event' => 'API Event', 'manual' => 'Manual'])
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country.name')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match($state) {
                        'active' => 'success', 'draft' => 'gray',
                        'paused' => 'warning', 'archived' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('trigger_type')->badge(),
                Tables\Columns\TextColumn::make('total_entries')->label('Entries'),
                Tables\Columns\TextColumn::make('total_conversions')->label('Conversions'),
                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label('Conv %')
                    ->formatStateUsing(fn ($record) => $record->conversion_rate . '%'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFunnels::route('/'),
            'create' => Pages\CreateFunnel::route('/create'),
            'edit' => Pages\EditFunnel::route('/{record}/edit'),
        ];
    }
}
