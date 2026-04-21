<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmartlinkResource\Pages;
use App\Models\Smartlink;
use App\Models\Tag;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SmartlinkResource extends Resource
{
    protected static ?string $model = Smartlink::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-link';
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing';
    }

    public static function getNavigationSort(): int
    {
        return 4;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Smartlink Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->maxLength(50)
                        ->helperText('Leave blank to auto-generate'),
                    Forms\Components\TextInput::make('destination_url')
                        ->required()
                        ->url()
                        ->maxLength(500),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ])->columns(2),

            Section::make('Actions on Click')
                ->schema([
                    Forms\Components\Select::make('tags_to_apply')
                        ->multiple()
                        ->options(Tag::pluck('name', 'id'))
                        ->helperText('Tags to auto-apply when clicked'),
                    Forms\Components\TextInput::make('score_adjustment')
                        ->numeric()
                        ->default(0)
                        ->helperText('Score points to add (+) or subtract (-) on click'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Short URL')
                    ->formatStateUsing(fn ($state) => "/sl/{$state}")
                    ->copyable(),
                Tables\Columns\TextColumn::make('destination_url')->limit(40),
                Tables\Columns\TextColumn::make('click_count')->label('Clicks')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmartlinks::route('/'),
            'create' => Pages\CreateSmartlink::route('/create'),
            'edit' => Pages\EditSmartlink::route('/{record}/edit'),
        ];
    }
}
