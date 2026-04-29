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
    use \App\Filament\Concerns\ScopesByCountryFilter;

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
                        ->required()->maxLength(255)
                        ->placeholder('Fluent Forms — ALG SV')
                        ->helperText('Tip: usa el formato "Fluent Forms — ALG XX" para webhooks de formularios'),
                    Forms\Components\Select::make('direction')
                        ->options(['inbound' => 'Inbound', 'outbound' => 'Outbound'])
                        ->required()->default('inbound')->live(),
                    Forms\Components\Select::make('source')
                        ->label('Tipo de fuente')
                        ->options([
                            'fluent_forms' => 'Fluent Forms (WordPress)',
                            'form'         => 'Form genérico',
                            null           => 'Generic webhook (events)',
                        ])
                        ->visible(fn ($get) => $get('direction') === 'inbound')
                        ->live() // re-render so country_id / mapping sections appear when source picked
                        ->helperText('Fluent Forms parsea el payload directo a Lead. Generic ejecuta eventos del WebhookDispatcher.'),
                    Forms\Components\Select::make('country_id')
                        ->label('País destino')
                        ->relationship('country', 'name')
                        ->searchable()->preload()
                        ->visible(fn ($get) => in_array($get('source'), ['fluent_forms', 'form']))
                        ->helperText('Los leads que entren por este webhook se asignan a este país.'),
                    Forms\Components\TextInput::make('url')
                        ->url()->maxLength(500)
                        ->visible(fn ($get) => $get('direction') === 'outbound')
                        ->required(fn ($get) => $get('direction') === 'outbound'),
                    Forms\Components\TextInput::make('secret')
                        ->password()->maxLength(255)
                        ->helperText('Para verificar firma HMAC (opcional)'),
                    Forms\Components\Select::make('events')
                        ->multiple()
                        ->options(array_combine(
                            WebhookDispatcher::availableEvents(),
                            array_map(fn ($e) => str_replace('.', ' → ', $e), WebhookDispatcher::availableEvents())
                        ))
                        ->visible(fn ($get) => ! in_array($get('source'), ['fluent_forms', 'form'])),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ])->columns(2),

            Section::make('Mapping de campos (Fluent Forms)')
                ->description('Si dejás esto vacío, el sistema busca automáticamente nombres comunes (name, email, phone, etc.). Solo llenar si tu form usa nombres distintos.')
                ->visible(fn ($get) => in_array($get('source'), ['fluent_forms', 'form']))
                ->schema([
                    Forms\Components\KeyValue::make('field_mapping')
                        ->keyLabel('Campo Lead')
                        ->valueLabel('Nombre del campo en Fluent Forms')
                        ->default([
                            'name_field' => 'names',
                            'email_field' => 'email',
                            'phone_field' => 'phone',
                            'company_field' => 'company',
                            'message_field' => 'message',
                        ])
                        ->helperText('Las claves válidas son: name_field, email_field, phone_field, company_field, message_field'),
                ]),

            Section::make('Cómo configurar Fluent Forms')
                ->description('Pasos en WordPress después de crear este webhook')
                ->visible(fn ($get, $record) => $record && in_array($get('source'), ['fluent_forms', 'form']))
                ->schema([
                    Forms\Components\Placeholder::make('fluent_instructions')
                        ->label('')
                        ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                            '<div style="font-family:monospace;font-size:12px;line-height:1.6;">'
                            . '1. En WordPress → Fluent Forms → tu form → <b>Settings & Integrations</b> → Webhook<br>'
                            . '2. URL: <code style="background:#f5f5f4;padding:2px 6px;border-radius:3px;">'
                            . url("/webhook/{$record->id}") . '</code><br>'
                            . '3. Method: <b>POST</b> · Request format: <b>JSON</b><br>'
                            . '4. (Opcional) Header: <code>X-Webhook-Signature</code> con HMAC del secret<br>'
                            . '5. Save & test con un envío de prueba — debería aparecer un Lead nuevo en /admin/leads</div>'
                        )),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('direction')->badge()->sortable(),
                Tables\Columns\TextColumn::make('source')->badge()->sortable()
                    ->color(fn ($state) => $state === 'fluent_forms' ? 'success' : 'gray')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('country.code')->label('País')
                    ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')->limit(40)->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('success_count')->label('OK'),
                Tables\Columns\TextColumn::make('failure_count')->label('Fail'),
                Tables\Columns\TextColumn::make('last_triggered_at')->dateTime()->sortable()->since(),
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
