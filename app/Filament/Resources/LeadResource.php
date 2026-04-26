<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Country;
use App\Models\Tag;
use App\Services\Lead\LeadScoringService;
use App\Services\Quote\QuoteGeneratorService;
use App\Services\WhatsApp\WhatsAppService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationGroup(): string
    {
        return 'CRM';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return 'Leads';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead Information')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->maxLength(50),
                    Forms\Components\TextInput::make('company')->maxLength(255),
                    Forms\Components\TextInput::make('position')->maxLength(255),
                    Forms\Components\Select::make('country_id')
                        ->label('Country')
                        ->options(Country::pluck('name', 'id'))
                        ->required(),
                ])->columns(2),

            Section::make('Status & Score')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'new' => 'New',
                            'contacted' => 'Contacted',
                            'qualified' => 'Qualified',
                            'proposal' => 'Proposal',
                            'negotiation' => 'Negotiation',
                            'won' => 'Won',
                            'lost' => 'Lost',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('score')
                        ->numeric()->minValue(0)->maxValue(100)
                        ->default(0),
                    Forms\Components\Select::make('source')
                        ->options([
                            'organic' => 'Organic Search',
                            'paid' => 'Paid Ads',
                            'social' => 'Social Media',
                            'referral' => 'Referral',
                            'direct' => 'Direct',
                            'email' => 'Email',
                            'whatsapp' => 'WhatsApp',
                        ]),
                    Forms\Components\TextInput::make('service_interest')->maxLength(255),
                ])->columns(2),

            Section::make('UTM Tracking')
                ->schema([
                    Forms\Components\TextInput::make('utm_source'),
                    Forms\Components\TextInput::make('utm_medium'),
                    Forms\Components\TextInput::make('utm_campaign'),
                    Forms\Components\TextInput::make('landing_page'),
                ])->columns(2)->collapsed(),

            Section::make('Tags')
                ->schema([
                    Forms\Components\Select::make('tags')
                        ->multiple()
                        ->relationship('tags', 'name')
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\ColorPicker::make('color')->default('#6366F1'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $data['tenant_id'] = auth()->user()->tenant_id ?? 1;
                            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
                            return Tag::create($data)->id;
                        }),
                ]),

            Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')->rows(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('company')->searchable(),
                Tables\Columns\TextColumn::make('country.name')->label('Country')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('score')->badge()->sortable(),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('País')
                    ->options(Country::active()->pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'qualified' => 'Calificado',
                        'proposal' => 'Propuesta',
                        'negotiation' => 'Negociación',
                        'won' => 'Ganado',
                        'lost' => 'Perdido',
                    ]),
                SelectFilter::make('month')
                    ->label('Mes')
                    ->options(function () {
                        $months = [];
                        for ($i = 0; $i < 18; $i++) {
                            $date = now()->subMonths($i)->startOfMonth();
                            $key = $date->format('Y-m');
                            $months[$key] = $date->translatedFormat('F Y');
                        }
                        return $months;
                    })
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'], fn ($q, $v) =>
                            $q->whereYear('leads.created_at', substr($v, 0, 4))
                              ->whereMonth('leads.created_at', substr($v, 5, 2))
                        )
                    ),
                SelectFilter::make('source_detail')
                    ->label('Origen')
                    ->options([
                        'fluent_forms_webhook' => 'Formulario Web (nuevo)',
                        'Fluent Forms Blog 1' => 'Sitio Principal',
                        'Fluent Forms Blog 2' => 'El Salvador',
                        'Fluent Forms Blog 16' => 'Guatemala',
                        'Fluent Forms Blog 29' => 'Nicaragua',
                        'Fluent Forms Blog 30' => 'Panamá',
                        'Fluent Forms Blog 31' => 'Honduras',
                        'Fluent Forms Blog 32' => 'Costa Rica',
                        'Fluent Forms Blog 38' => 'USA / Miami',
                    ]),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                EditAction::make(),
                Action::make('recalculate_score')
                    ->icon('heroicon-o-arrow-path')
                    ->label('Score')
                    ->action(function (Lead $record) {
                        app(LeadScoringService::class)->recalculate($record);
                        Notification::make()->title("Score: {$record->score}")->success()->send();
                    }),
                Action::make('generate_quote')
                    ->icon('heroicon-o-document-text')
                    ->label('Quote')
                    ->color('success')
                    ->action(function (Lead $record) {
                        $html = app(QuoteGeneratorService::class)->generateHtml($record);
                        $filename = 'quote-' . $record->id . '.html';
                        $path = storage_path("app/quotes/{$filename}");
                        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
                        file_put_contents($path, $html);
                        Notification::make()->title('Quote generated')->body($filename)->success()->send();
                    })
                    ->visible(fn (Lead $record) => $record->score >= 40),

                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->modalHeading(fn (Lead $record) => "Send WhatsApp to {$record->name}")
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->label('Mensaje')
                            ->required()
                            ->rows(3)
                            ->default(fn (Lead $record) =>
                                "Hola {$record->name}, le contactamos de ALG3PL. ¿En qué podemos ayudarle?"
                            ),
                    ])
                    ->action(function (Lead $record, array $data) {
                        $wa = app(WhatsAppService::class);
                        $result = $wa->sendMessage($record->phone, $data['message']);

                        // Log as LeadActivity
                        LeadActivity::create([
                            'lead_id'     => $record->id,
                            'user_id'     => auth()->id(),
                            'type'        => 'whatsapp',
                            'description' => $result['success']
                                ? "WhatsApp sent. Message ID: " . ($result['message_id'] ?? 'N/A')
                                : "WhatsApp FAILED: " . ($result['error'] ?? 'Unknown error'),
                        ]);

                        if ($result['success']) {
                            Notification::make()
                                ->title('WhatsApp enviado')
                                ->body("Mensaje enviado a {$record->phone}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error al enviar WhatsApp')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Lead $record) => !empty($record->phone)),
            ])
            ->bulkActions([
                BulkAction::make('recalculate_scores')
                    ->icon('heroicon-o-arrow-path')
                    ->label('Recalculate Scores')
                    ->action(function ($records) {
                        $service = app(LeadScoringService::class);
                        $records->each(fn ($r) => $service->recalculate($r));
                        Notification::make()->title('Scores recalculated')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
