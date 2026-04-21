<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScoringRuleResource\Pages;
use App\Filament\Traits\HasRoleAccess;
use App\Models\ScoringRule;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ScoringRuleResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = ScoringRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Scoring Rules';

    protected static ?string $modelLabel = 'Scoring Rule';

    protected static ?string $pluralModelLabel = 'Scoring Rules';

    protected static ?int $navigationSort = 10;

    public static function getAllowedRoles(): array
    {
        return ['admin', 'super_admin'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rule Configuration')
                ->schema([
                    Forms\Components\TextInput::make('factor')
                        ->label('Factor Key')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. source_organic')
                        ->helperText('Unique identifier: category_value (e.g. source_organic, status_qualified)'),

                    Forms\Components\TextInput::make('label')
                        ->label('Display Label')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('weight')
                        ->label('Weight (0–100)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0),

                    Forms\Components\Select::make('category')
                        ->label('Category')
                        ->required()
                        ->options([
                            'source'      => 'Source',
                            'status'      => 'Status',
                            'engagement'  => 'Engagement',
                            'geography'   => 'Geography',
                        ]),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('factor')
                    ->label('Factor')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Weight')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'source'     => 'info',
                        'status'     => 'warning',
                        'engagement' => 'success',
                        'geography'  => 'gray',
                        default      => 'gray',
                    }),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'source'     => 'Source',
                        'status'     => 'Status',
                        'engagement' => 'Engagement',
                        'geography'  => 'Geography',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn () => ScoringRule::flushCache()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('category')
            ->reorderable('weight');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListScoringRules::route('/'),
            'create' => Pages\CreateScoringRule::route('/create'),
            'edit'   => Pages\EditScoringRule::route('/{record}/edit'),
        ];
    }
}
