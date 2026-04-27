<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryReportResource\Pages;
use App\Models\Country;
use App\Models\CountryReport;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CountryReportResource extends Resource
{
    protected static ?string $model = CountryReport::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-chart-bar';
    }

    public static function getNavigationGroup(): string
    {
        return 'Analytics';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return 'Reportes';
    }

    public static function getModelLabel(): string
    {
        return 'reporte';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Reportes';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Report Info')
                ->schema([
                    Forms\Components\Select::make('country_id')
                        ->label('Country')
                        ->options(Country::active()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Forms\Components\TextInput::make('period')
                        ->required()
                        ->placeholder('2024-2026 YTD'),
                    Forms\Components\Select::make('type')
                        ->options(['seo' => 'SEO & Analytics', 'marketing' => 'Marketing', 'sales' => 'Sales'])
                        ->default('seo'),
                    Forms\Components\Textarea::make('summary')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(3),

            Section::make('KPIs')
                ->schema([
                    Forms\Components\KeyValue::make('kpis')
                        ->keyLabel('Metric')
                        ->valueLabel('Value')
                        ->addActionLabel('Add KPI'),
                ]),

            Section::make('Findings & Opportunities')
                ->schema([
                    Forms\Components\Repeater::make('findings')
                        ->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\Textarea::make('detail')->rows(2),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    Forms\Components\Repeater::make('opportunities')
                        ->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\Textarea::make('detail')->rows(2),
                            Forms\Components\Select::make('impact')
                                ->options(['+' => 'Low', '++' => 'Medium', '+++' => 'High']),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                ])->columns(1),

            Section::make('Raw Data')
                ->schema([
                    Forms\Components\KeyValue::make('ga4_data')
                        ->keyLabel('Metric')
                        ->valueLabel('Value'),
                    Forms\Components\KeyValue::make('gsc_data')
                        ->keyLabel('Metric')
                        ->valueLabel('Value'),
                ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('summary')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('findings')
                    ->label('Findings')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' items' : '—'),
                Tables\Columns\TextColumn::make('opportunities')
                    ->label('Opps')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' items' : '—'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->options(Country::active()->pluck('name', 'id')),
            ])
            ->recordAction('view_report')
            ->actions([
                \Filament\Actions\Action::make('view_report')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalHeading(fn (CountryReport $record) => ($record->country?->name ?? '—') . ' — ' . $record->period)
                    ->modalContent(fn (CountryReport $record) => view('filament.modals.country-report', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                \Filament\Actions\Action::make('print')
                    ->label('Imprimir / PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (CountryReport $record): string => route('country-report.print', $record))
                    ->openUrlInNewTab(),
                EditAction::make()->label('Editar')->icon('heroicon-o-pencil'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountryReports::route('/'),
            'create' => Pages\CreateCountryReport::route('/create'),
            'edit' => Pages\EditCountryReport::route('/{record}/edit'),
        ];
    }
}
