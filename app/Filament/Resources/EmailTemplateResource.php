<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\Country;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class EmailTemplateResource extends Resource
{
    use \App\Filament\Concerns\ScopesByCountryFilter;

    protected static ?string $model = EmailTemplate::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-envelope';
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing';
    }

    public static function getNavigationSort(): int
    {
        return 3;
    }

    public static function getNavigationLabel(): string
    {
        return 'Email Templates';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Template Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('category')
                        ->options([
                            'welcome' => 'Welcome',
                            'follow_up' => 'Follow Up',
                            'nurturing' => 'Nurturing',
                            'quote' => 'Quote',
                            'newsletter' => 'Newsletter',
                            'notification' => 'Notification',
                            'custom' => 'Custom',
                        ])
                        ->required(),
                    Forms\Components\Select::make('country_id')
                        ->label('Country')
                        ->options(Country::pluck('name', 'id'))
                        ->placeholder('All Countries'),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ])->columns(2),

            Section::make('Email Content')
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Use {nombre}, {empresa}, {pais} for dynamic content'),
                    Forms\Components\RichEditor::make('body_html')
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Variables: {nombre}, {empresa}, {email}, {pais}, {servicio}'),
                    Forms\Components\Textarea::make('body_text')
                        ->label('Plain Text Version')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Variables')
                ->schema([
                    Forms\Components\Repeater::make('variables')
                        ->schema([
                            Forms\Components\TextInput::make('key')
                                ->required()
                                ->maxLength(50),
                            Forms\Components\TextInput::make('default')
                                ->maxLength(255),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->collapsible()
                        ->collapsed(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->badge()->sortable(),
                Tables\Columns\TextColumn::make('subject')->limit(40),
                Tables\Columns\TextColumn::make('country.name')->label('Country')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('usage_count')->label('Used')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'welcome' => 'Welcome',
                        'follow_up' => 'Follow Up',
                        'nurturing' => 'Nurturing',
                        'quote' => 'Quote',
                        'newsletter' => 'Newsletter',
                        'notification' => 'Notification',
                        'custom' => 'Custom',
                    ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
