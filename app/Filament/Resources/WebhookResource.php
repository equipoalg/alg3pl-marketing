<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\Webhook;
use App\Services\Webhook\WebhookDispatcher;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-path-rounded-square';
    }

    public static function getNavigationGroup(): string
    {
        return 'Settings';
    }

    public static function getNavigationSort(): int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Webhook Configuration')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('direction')
                        ->options(['inbound' => 'Inbound', 'outbound' => 'Outbound'])
                        ->required(),
                    Forms\Components\TextInput::make('url')
                        ->url()
                        ->maxLength(500)
                        ->visible(fn ($get) => $get('direction') === 'outbound')
                        ->required(fn ($get) => $get('direction') === 'outbound'),
                    Forms\Components\TextInput::make('secret')
                        ->password()
                        ->maxLength(255)
                        ->helperText('For HMAC signature verification'),
                    Forms\Components\Select::make('events')
                        ->multiple()
                        ->options(array_combine(
                            WebhookDispatcher::availableEvents(),
                            array_map(fn ($e) => str_replace('.', ' → ', $e), WebhookDispatcher::availableEvents())
                        ))
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('direction')->badge()->sortable(),
                Tables\Columns\TextColumn::make('url')->limit(40),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('success_count')->label('OK'),
                Tables\Columns\TextColumn::make('failure_count')->label('Fail'),
                Tables\Columns\TextColumn::make('last_triggered_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
