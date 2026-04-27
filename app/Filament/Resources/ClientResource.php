<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\Country;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-building-office-2'; }
    public static function getNavigationGroup(): string { return 'CRM'; }
    public static function getNavigationSort(): int { return 1; }
    public static function getNavigationLabel(): string { return 'Cuentas'; }
    public static function getModelLabel(): string { return 'cuenta'; }
    public static function getPluralModelLabel(): string { return 'Cuentas'; }
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();
        return $count > 0 ? (string) $count : null;
    }
    /** Global search — Buscar ⌘K hits this */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Company Info')
                ->schema([
                    Forms\Components\TextInput::make('company_name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('trade_name')->maxLength(255),
                    Forms\Components\TextInput::make('tax_id')->label('Tax ID (NIT/RUC/RTN)'),
                    Forms\Components\TextInput::make('industry'),
                    Forms\Components\Select::make('country_id')->label('Country')
                        ->options(Country::pluck('name', 'id'))->required(),
                    Forms\Components\Select::make('tier')
                        ->options(['enterprise' => 'Enterprise', 'mid_market' => 'Mid Market', 'smb' => 'SMB']),
                    Forms\Components\Select::make('status')
                        ->options(['prospect' => 'Prospect', 'active' => 'Active', 'inactive' => 'Inactive', 'churned' => 'Churned']),
                    Forms\Components\Select::make('assigned_to')->label('Assigned To')
                        ->options(User::pluck('name', 'id'))->searchable(),
                ])->columns(2),

            Section::make('Primary Contact')
                ->schema([
                    Forms\Components\TextInput::make('primary_contact_name'),
                    Forms\Components\TextInput::make('primary_contact_email')->email(),
                    Forms\Components\TextInput::make('primary_contact_phone'),
                    Forms\Components\Textarea::make('address')->rows(2),
                    Forms\Components\TextInput::make('city'),
                ])->columns(2),

            Section::make('Business Metrics')
                ->schema([
                    Forms\Components\TextInput::make('annual_revenue')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('monthly_volume')->numeric()->suffix('CBM/shipments'),
                    Forms\Components\DatePicker::make('contract_start'),
                    Forms\Components\DatePicker::make('contract_end'),
                    Forms\Components\TextInput::make('health_score')->numeric()->minValue(0)->maxValue(100),
                ])->columns(3),

            Section::make('Services & Routes')
                ->schema([
                    Forms\Components\TagsInput::make('services_contracted')
                        ->placeholder('Add service...'),
                    Forms\Components\Textarea::make('notes')->rows(3),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('tier')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match($state) {
                        'active' => 'success', 'prospect' => 'info',
                        'inactive' => 'gray', 'churned' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('health_score')->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 70 => 'success', $state >= 40 => 'warning', default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('annual_revenue')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('assignedUser.name')->label('Owner'),
                Tables\Columns\TextColumn::make('contract_end')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('country_id')->label('Country')->options(Country::pluck('name', 'id')),
                SelectFilter::make('status')->options([
                    'prospect' => 'Prospect', 'active' => 'Active',
                    'inactive' => 'Inactive', 'churned' => 'Churned',
                ]),
                SelectFilter::make('tier')->options([
                    'enterprise' => 'Enterprise', 'mid_market' => 'Mid Market', 'smb' => 'SMB',
                ]),
            ])
            ->defaultSort('company_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
