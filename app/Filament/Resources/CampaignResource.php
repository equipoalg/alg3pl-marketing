<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Country;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-megaphone';
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing';
    }

    public static function getNavigationSort(): int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Campaign Details')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->options([
                            'email' => 'Email',
                            'whatsapp' => 'WhatsApp',
                            'social' => 'Social Media',
                            'seo' => 'SEO Content',
                        ])->required(),
                    Forms\Components\Select::make('country_id')
                        ->label('Country')
                        ->options(Country::pluck('name', 'id'))
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'scheduled' => 'Scheduled',
                            'active' => 'Active',
                            'paused' => 'Paused',
                            'completed' => 'Completed',
                        ])->default('draft'),
                ])->columns(2),

            Section::make('Schedule')
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_at'),
                    Forms\Components\DateTimePicker::make('ends_at'),
                ])->columns(2),

            Section::make('Content')
                ->schema([
                    Forms\Components\Textarea::make('description')->rows(3),
                    Forms\Components\KeyValue::make('audience_filter')
                        ->label('Audience Filters')
                        ->reorderable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('country.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->options(Country::pluck('name', 'id')),
                SelectFilter::make('type')
                    ->options([
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                        'social' => 'Social Media',
                        'seo' => 'SEO Content',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
