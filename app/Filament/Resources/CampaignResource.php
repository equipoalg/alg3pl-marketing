<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Country;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
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

    public static function getNavigationLabel(): string
    {
        return 'Campañas';
    }

    public static function getModelLabel(): string
    {
        return 'campaña';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Campañas';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Campaign::where('status', 'active')->count();
        return $count > 0 ? (string) $count : null;
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
            ])
            ->recordActions([
                Action::make('sendEmail')
                    ->label('Enviar emails')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Campaign $record) => $record->type === 'email')
                    ->requiresConfirmation()
                    ->modalHeading('Enviar campaña por email')
                    ->modalDescription(fn (Campaign $record) => 'Esto encolará un job por cada lead elegible (email válido, no unsubscribed, status != lost) en el país de la campaña. Audiencia estimada: ' . $record->resolveEmailAudience()->count() . ' leads.')
                    ->modalSubmitActionLabel('Enviar ahora')
                    ->action(function (Campaign $record) {
                        try {
                            $result = $record->dispatchEmail();
                            Notification::make()
                                ->title('Campaña encolada')
                                ->body("Se encolaron {$result['queued']} emails para envío.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('No se pudo enviar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
