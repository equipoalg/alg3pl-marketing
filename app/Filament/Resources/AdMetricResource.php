<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdMetricResource\Pages;
use App\Models\AdMetric;
use App\Models\Country;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdMetricResource extends Resource
{
    protected static ?string $model = AdMetric::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string | \UnitEnum | null $navigationGroup = 'Analytics';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel      = 'Métrica Publicitaria';
    protected static ?string $pluralModelLabel = 'Métricas Publicitarias';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) return false;

        $role = method_exists($user, 'hasRole') ? $user->hasRole(['admin', 'super_admin', 'manager']) : true;
        return (bool) $role;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificación')
                ->schema([
                    Forms\Components\Select::make('country_id')
                        ->label('País')
                        ->options(Country::pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('platform')
                        ->label('Plataforma')
                        ->options([
                            'google'   => 'Google Ads',
                            'meta'     => 'Meta Ads (Facebook/Instagram)',
                            'linkedin' => 'LinkedIn Ads',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('campaign_name')
                        ->label('Nombre de Campaña')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Período')
                ->schema([
                    Forms\Components\DatePicker::make('period_start')
                        ->label('Inicio del Período')
                        ->required(),
                    Forms\Components\DatePicker::make('period_end')
                        ->label('Fin del Período')
                        ->required(),
                ])->columns(2),

            Section::make('Métricas de Rendimiento')
                ->schema([
                    Forms\Components\TextInput::make('impressions')
                        ->label('Impresiones')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('clicks')
                        ->label('Clics')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('spend')
                        ->label('Gasto (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),

                    Forms\Components\TextInput::make('leads_generated')
                        ->label('Leads Generados')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('cost_per_lead')
                        ->label('Costo por Lead (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->nullable(),

                    Forms\Components\TextInput::make('roas')
                        ->label('ROAS')
                        ->numeric()
                        ->nullable()
                        ->helperText('Return on Ad Spend (ej: 3.5 = 350%)'),
                ])->columns(3),

            Section::make('Notas')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('synced_at')
                        ->label('Última sincronización')
                        ->nullable(),
                ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('platform')
                    ->label('Plataforma')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'google'   => 'info',
                        'meta'     => 'warning',
                        'linkedin' => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'google'   => 'Google Ads',
                        'meta'     => 'Meta Ads',
                        'linkedin' => 'LinkedIn',
                        default    => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('campaign_name')
                    ->label('Campaña')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('period_start')
                    ->label('Período')
                    ->formatStateUsing(fn ($record) => $record->period_start->format('d/m') . ' – ' . $record->period_end->format('d/m/Y'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('spend')
                    ->label('Gasto')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('leads_generated')
                    ->label('Leads')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cost_per_lead')
                    ->label('CPL')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('synced_at')
                    ->label('Synced')
                    ->since()
                    ->sortable()
                    ->placeholder('Manual'),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('País')
                    ->options(Country::pluck('name', 'id')),

                SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options([
                        'google'   => 'Google Ads',
                        'meta'     => 'Meta Ads',
                        'linkedin' => 'LinkedIn',
                    ]),
            ])
            ->defaultSort('period_start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdMetrics::route('/'),
            'create' => Pages\CreateAdMetric::route('/create'),
            'edit'   => Pages\EditAdMetric::route('/{record}/edit'),
        ];
    }
}
