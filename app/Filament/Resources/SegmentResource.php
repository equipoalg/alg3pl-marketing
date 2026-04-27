<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SegmentResource\Pages;
use App\Models\Country;
use App\Models\Segment;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SegmentResource extends Resource
{
    use \App\Filament\Concerns\ScopesByCountryFilter;

    protected static ?string $model = Segment::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-table-cells';
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing search';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return 'Tabla';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Segment Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->options(['static' => 'Static', 'dynamic' => 'Dynamic'])
                        ->default('dynamic')
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->rows(2),
                ])->columns(2),

            Section::make('Rules')
                ->description('Define conditions for dynamic segments')
                ->schema([
                    Forms\Components\Select::make('rules.logic')
                        ->label('Match')
                        ->options(['and' => 'ALL conditions (AND)', 'or' => 'ANY condition (OR)'])
                        ->default('and'),
                    Forms\Components\Repeater::make('rules.conditions')
                        ->label('Conditions')
                        ->schema([
                            Forms\Components\Select::make('field')
                                ->options([
                                    'country_id' => 'Country',
                                    'status' => 'Status',
                                    'source' => 'Source',
                                    'score' => 'Score',
                                    'created_at' => 'Created Date',
                                    'service_interest' => 'Service Interest',
                                    'email_verified_at' => 'Email Verified',
                                ])
                                ->required(),
                            Forms\Components\Select::make('op')
                                ->label('Operator')
                                ->options([
                                    '=' => 'Equals',
                                    '!=' => 'Not Equals',
                                    '>' => 'Greater Than',
                                    '>=' => 'Greater or Equal',
                                    '<' => 'Less Than',
                                    '<=' => 'Less or Equal',
                                    'in' => 'Is In',
                                    'not_in' => 'Not In',
                                    'is_null' => 'Is Empty',
                                    'is_not_null' => 'Is Not Empty',
                                    'has_tag' => 'Has Tag',
                                    'days_ago' => 'Within Days',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('value')
                                ->helperText('Comma-separated for "in" operators'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->collapsible(),
                ])
                ->visible(fn ($get) => $get('type') === 'dynamic'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('cached_count')
                    ->label('Leads')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_calculated_at')
                    ->label('Last Calc')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Action::make('recalculate')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn (Segment $record) => $record->recalculate())
                    ->requiresConfirmation(),
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSegments::route('/'),
            'create' => Pages\CreateSegment::route('/create'),
            'edit' => Pages\EditSegment::route('/{record}/edit'),
        ];
    }
}
