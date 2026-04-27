<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SearchConsoleDataResource\Pages;
use App\Models\SearchConsoleData;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SearchConsoleDataResource extends Resource
{
    use \App\Filament\Concerns\ScopesByCountryFilter;

    protected static ?string $model = SearchConsoleData::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-magnifying-glass';
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
        return 'Search Console';
    }

    public static function getModelLabel(): string
    {
        return 'búsqueda';
    }

    public static function getPluralModelLabel(): string
    {
        return 'búsquedas';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('query')->label('Keyword')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('clicks')->label('Clicks')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('impressions')->label('Impresiones')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('ctr')
                    ->label('CTR')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state * 100, 2) . '%' : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Posición')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 1) : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.code')
                    ->label('País')
                    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('date')->label('Fecha')->date()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('País')
                    ->relationship('country', 'name'),
            ])
            ->defaultSort('clicks', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSearchConsoleData::route('/'),
        ];
    }
}
