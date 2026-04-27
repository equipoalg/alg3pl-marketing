<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-globe-alt'; }
    public static function getNavigationGroup(): string { return 'Settings'; }
    public static function getNavigationSort(): int { return 1; }
    public static function getNavigationLabel(): string { return 'Países'; }
    public static function getModelLabel(): string { return 'país'; }
    public static function getPluralModelLabel(): string { return 'Países'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identidad')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Código ISO')->required()->maxLength(2)
                        ->disabledOn('edit')
                        ->helperText('2 letras (sv, gt, hn, cr, ni, pa, us)'),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')->required(),
                    Forms\Components\TextInput::make('website_url')
                        ->label('URL del sitio web')
                        ->placeholder('https://alg-sv.com')
                        ->url(),
                ])->columns(3),

            Section::make('Google Analytics & Search Console')
                ->description('Credenciales de tracking por país. Las credenciales JSON están en storage/google-credentials.json compartidas — aquí solo se setea qué property/site usa cada país.')
                ->schema([
                    Forms\Components\TextInput::make('ga4_property_id')
                        ->label('GA4 Property ID')
                        ->placeholder('123456789')
                        ->helperText('Property ID numérico (no incluyas "properties/"). Buscar en GA4 → Admin → Property Settings.'),
                    Forms\Components\TextInput::make('gsc_property_url')
                        ->label('GSC Site URL')
                        ->placeholder('sc-domain:alg-sv.com  ó  https://alg-sv.com/')
                        ->helperText('Si verificaste el dominio entero usa "sc-domain:..."; si solo un prefijo URL usa la URL completa con slash final.'),
                    Forms\Components\TextInput::make('google_ads_account')
                        ->label('Google Ads Account ID')
                        ->placeholder('123-456-7890'),
                ])->columns(2),

            Section::make('Localización')
                ->schema([
                    Forms\Components\Select::make('timezone')
                        ->label('Timezone')
                        ->options([
                            'America/El_Salvador' => 'El Salvador',
                            'America/Guatemala' => 'Guatemala',
                            'America/Tegucigalpa' => 'Honduras',
                            'America/Costa_Rica' => 'Costa Rica',
                            'America/Managua' => 'Nicaragua',
                            'America/Panama' => 'Panamá',
                            'America/New_York' => 'USA/Miami (EST)',
                        ])->default('America/Guatemala'),
                    Forms\Components\TextInput::make('currency')
                        ->label('Moneda')->maxLength(3)->default('USD'),
                    Forms\Components\TextInput::make('phone_prefix')
                        ->label('Prefijo telefónico')->placeholder('+503'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo')->default(true),
                    Forms\Components\Toggle::make('is_regional')
                        ->label('Es región (no país individual)')
                        ->helperText('Marcar para "ALG Centroamérica" o similar. Excluido del sync de analytics.'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Código')
                    ->formatStateUsing(fn ($state) => strtoupper((string) $state))
                    ->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('País')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('website_url')->label('Web')->limit(30)->toggleable(),
                Tables\Columns\IconColumn::make('ga4_property_id')
                    ->label('GA4')
                    ->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')->falseColor('danger')
                    ->getStateUsing(fn ($record) => filled($record->ga4_property_id)),
                Tables\Columns\IconColumn::make('gsc_property_url')
                    ->label('GSC')
                    ->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')->falseColor('danger')
                    ->getStateUsing(fn ($record) => filled($record->gsc_property_url)),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCountries::route('/'),
            'edit'   => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
