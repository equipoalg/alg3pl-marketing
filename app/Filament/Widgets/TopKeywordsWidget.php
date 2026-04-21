<?php

namespace App\Filament\Widgets;

use App\Models\SearchConsoleData;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Livewire\Attributes\On;

class TopKeywordsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Keywords';
    protected static ?int $sort = 4;

    public ?string $countryFilter = '';
    public string $timeRange = '30d';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
        $this->timeRange = session('time_range', '30d');
    }

    #[On('timeRangeUpdated')]
    public function onTimeRangeUpdated(string $timeRange): void
    {
        $this->timeRange = $timeRange;
    }

    public function getTableRecordKey($record): string
    {
        return $record->query ?? '';
    }

    public function getTableDescription(): ?string
    {
        $start = $this->getStart();
        $days = (int) $start->diffInDays(now());
        return "Google Search Console · Last {$days} days";
    }

    public function table(Table $table): Table
    {
        $start = $this->getStart();

        $query = SearchConsoleData::query()
            ->where('date', '>=', $start)
            ->whereNotNull('query')
            ->where('query', '!=', '');

        if ($this->countryFilter) {
            $query->where('country_id', $this->countryFilter);
        }

        return $table
            ->query(
                $query
                    ->selectRaw('query, SUM(clicks) as clicks, SUM(impressions) as impressions, ROUND(AVG(position), 1) as avg_position')
                    ->groupBy('query')
                    ->orderByDesc('clicks')
                    ->limit(12)
            )
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('query')
                    ->label('Keyword')
                    ->searchable()
                    ->weight('medium')
                    ->size('sm')
                    ->wrap(),
                Tables\Columns\TextColumn::make('clicks')
                    ->label('Clicks')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('impressions')
                    ->label('Impr.')
                    ->sortable()
                    ->numeric()
                    ->alignEnd()
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('avg_position')
                    ->label('Pos.')
                    ->sortable()
                    ->alignEnd()
                    ->size('sm')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (float) $state <= 3 => 'success',
                        (float) $state <= 10 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('No keyword data')
            ->emptyStateDescription('Sync Google Search Console to see keywords.')
            ->emptyStateIcon('heroicon-o-magnifying-glass');
    }

    private function getStart(): Carbon
    {
        return match ($this->timeRange) {
            '7d' => now()->subDays(7),
            '90d' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };
    }
}
