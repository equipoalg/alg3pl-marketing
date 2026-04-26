<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Country;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationGroup(): string
    {
        return 'CRM';
    }

    public static function getNavigationSort(): int
    {
        return 4;
    }

    public static function getNavigationLabel(): string
    {
        return 'Seguimiento';
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Task::where('status', '!=', 'done')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdue = Task::overdue()->count();
        return $overdue > 0 ? 'danger' : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Task')
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                    Forms\Components\Select::make('country_id')
                        ->label('Country')
                        ->options(Country::active()->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Select::make('category')
                        ->options([
                            'seo' => 'SEO',
                            'technical' => 'Technical',
                            'content' => 'Content',
                            'ux' => 'UX/Design',
                            'marketing' => 'Marketing',
                            'analytics' => 'Analytics',
                        ])
                        ->required(),
                    Forms\Components\Select::make('priority')
                        ->options([
                            'P0' => 'P0 — Critical',
                            'P1' => 'P1 — High',
                            'P2' => 'P2 — Medium',
                            'P3' => 'P3 — Low',
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'done' => 'Done',
                            'blocked' => 'Blocked',
                        ])
                        ->default('pending')
                        ->required(),
                    Forms\Components\Select::make('effort')
                        ->options([
                            '1d' => '1 day',
                            '3d' => '3 days',
                            '1w' => '1 week',
                            '2w' => '2 weeks',
                            '3w' => '3 weeks',
                            '4w' => '4 weeks',
                            '6w' => '6 weeks',
                        ]),
                    Forms\Components\Select::make('impact')
                        ->options(['+' => 'Low (+)', '++' => 'Medium (++)', '+++' => 'High (+++)']),
                    Forms\Components\TextInput::make('assignee')->maxLength(100),
                    Forms\Components\DatePicker::make('due_date'),
                ])->columns(2),

            Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')->rows(3),
                ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'P0' => 'danger',
                        'P1' => 'warning',
                        'P2' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->limit(70),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'in_progress' => 'info',
                        'blocked' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->color(fn (Task $record): string => $record->due_date && $record->due_date->isPast() && $record->status !== 'done' ? 'danger' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignee')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->options(Country::active()->pluck('name', 'id')),
                SelectFilter::make('priority')
                    ->options(['P0' => 'P0', 'P1' => 'P1', 'P2' => 'P2', 'P3' => 'P3']),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'done' => 'Done',
                        'blocked' => 'Blocked',
                    ])
                    ->default('pending'),
                SelectFilter::make('category')
                    ->options([
                        'seo' => 'SEO',
                        'technical' => 'Technical',
                        'content' => 'Content',
                        'ux' => 'UX/Design',
                        'marketing' => 'Marketing',
                        'analytics' => 'Analytics',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('mark_done')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->label('Done')
                    ->action(function (Task $record) {
                        $record->update(['status' => 'done']);
                        Notification::make()->title('Task completed')->success()->send();
                    })
                    ->visible(fn (Task $record) => $record->status !== 'done'),
                Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->label('Start')
                    ->action(function (Task $record) {
                        $record->update(['status' => 'in_progress']);
                        Notification::make()->title('Task started')->info()->send();
                    })
                    ->visible(fn (Task $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                BulkAction::make('mark_done')
                    ->icon('heroicon-o-check-circle')
                    ->label('Mark as Done')
                    ->action(fn ($records) => $records->each->update(['status' => 'done']))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('priority')
            ->defaultGroup('priority');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
