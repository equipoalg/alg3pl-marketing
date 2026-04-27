<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsSnapshotResource\Pages;
use App\Models\AnalyticsSnapshot;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnalyticsSnapshotResource extends Resource
{
    protected static ?string $model = AnalyticsSnapshot::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-trending-up';
    }

    public static function getNavigationGroup(): string
    {
        return 'Analytics';
    }

    public static function getNavigationSort(): int
    {
        return 1;
    }

    public static function getNavigationLabel(): string
    {
        return 'Tráfico';
    }

    public static function getModelLabel(): string
    {
        return 'snapshot';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tráfico';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('Fecha')->date()->sortable(),
                Tables\Columns\TextColumn::make('country.code')
                    ->label('País')
                    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('users')->label('Usuarios')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('sessions')->label('Sesiones')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('organic_users')->label('Orgánico')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('direct_users')->label('Directo')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('referral_users')->label('Referido')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('social_users')->label('Social')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('paid_users')->label('Pagado')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('conversions')->label('Conv.')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('bounce_rate')
                    ->label('Bounce')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 1) . '%' : '—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('País')
                    ->relationship('country', 'name'),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsSnapshots::route('/'),
        ];
    }
}
