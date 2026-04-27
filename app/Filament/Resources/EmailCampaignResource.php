<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailCampaignResource\Pages;
use App\Models\EmailCampaign;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-paper-airplane';
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing';
    }

    public static function getNavigationSort(): int
    {
        return 4;
    }

    public static function getNavigationLabel(): string
    {
        return 'Envíos';
    }

    public static function getModelLabel(): string
    {
        return 'envío';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')->label('Asunto')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('campaign.name')->label('Campaña')->searchable(),
                Tables\Columns\TextColumn::make('from_email')->label('De')->toggleable(),
                Tables\Columns\TextColumn::make('sent_count')->label('Enviados')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('open_count')
                    ->label('Abiertos')
                    ->formatStateUsing(fn ($record) => $record->sent_count > 0
                        ? $record->open_count . ' (' . round(($record->open_count / $record->sent_count) * 100) . '%)'
                        : '0')
                    ->sortable(),
                Tables\Columns\TextColumn::make('click_count')
                    ->label('Clicks')
                    ->formatStateUsing(fn ($record) => $record->sent_count > 0
                        ? $record->click_count . ' (' . round(($record->click_count / $record->sent_count) * 100, 1) . '%)'
                        : '0')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sent_at')->label('Enviado')->dateTime()->sortable()->since(),
            ])
            ->defaultSort('sent_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
        ];
    }
}
