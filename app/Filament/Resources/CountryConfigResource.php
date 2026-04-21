<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryConfigResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\Country;
use App\Models\CountryConfig;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CountryConfigResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = CountryConfig::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Country Settings';

    protected static ?string $modelLabel = 'Country Config';

    protected static ?string $pluralModelLabel = 'Country Settings';

    protected static ?int $navigationSort = 11;

    public static function getAllowedRoles(): array
    {
        return ['super_admin'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Country')
                ->schema([
                    Forms\Components\Select::make('country_id')
                        ->label('País')
                        ->options(Country::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->unique(ignoreRecord: true),
                ]),

            Section::make('Objectives & Team')
                ->schema([
                    Forms\Components\TextInput::make('monthly_lead_goal')
                        ->label('Meta mensual de leads')
                        ->numeric()
                        ->required()
                        ->default(50)
                        ->minValue(0),

                    Forms\Components\TextInput::make('primary_manager')
                        ->label('Manager principal')
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('monthly_fee')
                        ->label('Tarifa mensual (USD)')
                        ->numeric()
                        ->required()
                        ->default(150.00)
                        ->prefix('$'),
                ])->columns(3),

            Section::make('Webhook Assignees')
                ->description('Emails/usernames to notify when a webhook lead arrives for this country.')
                ->schema([
                    Forms\Components\TagsInput::make('webhook_assignees')
                        ->label('Assignees')
                        ->placeholder('Add email and press Enter')
                        ->nullable(),
                ]),

            Section::make('Active Services')
                ->description('Services currently offered in this country.')
                ->schema([
                    Forms\Components\TagsInput::make('active_services')
                        ->label('Services')
                        ->placeholder('Add service and press Enter')
                        ->suggestions([
                            'Warehousing', 'Distribution', '3PL', 'Last Mile', 'Cross-docking',
                            'Cold Chain', 'E-commerce Fulfillment', 'Customs Brokerage',
                        ])
                        ->nullable(),
                ]),

            Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas internas')
                        ->rows(4)
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('monthly_lead_goal')
                    ->label('Meta mensual leads')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('primary_manager')
                    ->label('Manager principal')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('monthly_fee')
                    ->label('Tarifa mensual')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('country.name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCountryConfigs::route('/'),
            'create' => Pages\CreateCountryConfig::route('/create'),
            'edit'   => Pages\EditCountryConfig::route('/{record}/edit'),
        ];
    }
}
