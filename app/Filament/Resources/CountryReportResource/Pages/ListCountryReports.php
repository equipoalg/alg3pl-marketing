<?php

namespace App\Filament\Resources\CountryReportResource\Pages;

use App\Filament\Resources\CountryReportResource;
use App\Models\CountryReport;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Custom CRM-style ListCountryReports. País × período + drill a findings.
 */
class ListCountryReports extends Page
{
    protected static string $resource = CountryReportResource::class;
    protected string $view = 'filament.resources.country-report-resource.pages.country-reports-hub';
    protected Width|string|null $maxContentWidth = Width::Full;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $typeFilter = '';

    #[Url(as: 'country')]
    public ?int $countryFilter = null;

    #[Url(as: 'sort')]
    public string $sortBy = 'recent';

    #[Url(as: 'selected')]
    public ?int $selectedId = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable { return ''; }
    public function getTitle(): string { return 'Reportes'; }
    protected function getHeaderActions(): array { return []; }

    public function setTypeFilter(string $value): void
    {
        if (in_array($value, ['', 'seo', 'marketing', 'sales'], true)) $this->typeFilter = $value;
    }
    public function setSortBy(string $value): void
    {
        if (in_array($value, ['recent', 'period_desc', 'country'], true)) $this->sortBy = $value;
    }
    public function clearFilters(): void
    {
        $this->search = ''; $this->typeFilter = ''; $this->countryFilter = null; $this->sortBy = 'recent';
    }

    public function selectReport(int $id): void { $this->selectedId = $id; }
    public function closeReport(): void { $this->selectedId = null; }

    public static function typeIcon(?string $type): array
    {
        return match ($type) {
            'seo'       => ['icon' => '🔍', 'bg' => '#FEF3C7', 'fg' => '#92400E', 'label' => 'SEO & Analytics'],
            'marketing' => ['icon' => '📣', 'bg' => '#DBEAFE', 'fg' => '#1E3A8A', 'label' => 'Marketing'],
            'sales'     => ['icon' => '💰', 'bg' => '#D1FAE5', 'fg' => '#065F46', 'label' => 'Sales'],
            default     => ['icon' => '?',  'bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)', 'label' => (string) $type],
        };
    }

    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = CountryReportResource::getEloquentQuery();

        if ($this->search !== '') {
            $like = '%' . $this->search . '%';
            $q->where(function ($x) use ($like) {
                $x->where('period', 'like', $like)->orWhere('summary', 'like', $like);
            });
        }
        if ($this->typeFilter !== '') $q->where('type', $this->typeFilter);
        if ($this->countryFilter) $q->where('country_id', $this->countryFilter);

        return match ($this->sortBy) {
            'period_desc' => $q->orderByDesc('period'),
            'country'     => $q->join('countries', 'country_reports.country_id', '=', 'countries.id')
                                ->orderBy('countries.name')
                                ->select('country_reports.*'),
            default       => $q->orderByDesc('updated_at'),
        };
    }

    public function getViewData(): array
    {
        $reports = $this->buildQuery()->with('country')->limit(500)->get();

        $allQ = CountryReportResource::getEloquentQuery();
        $kpis = [
            'total'     => (clone $allQ)->count(),
            'seo'       => (clone $allQ)->where('type', 'seo')->count(),
            'marketing' => (clone $allQ)->where('type', 'marketing')->count(),
            'sales'     => (clone $allQ)->where('type', 'sales')->count(),
            'countries' => (clone $allQ)->distinct('country_id')->count('country_id'),
        ];

        $selected = null;
        if ($this->selectedId) {
            $selected = CountryReportResource::getEloquentQuery()->with('country')->find($this->selectedId);
        }

        $countries = \App\Models\Country::orderBy('name')->get(['id', 'code', 'name']);

        return [
            'reports'        => $reports,
            'totalShown'     => $reports->count(),
            'kpis'           => $kpis,
            'selected'       => $selected,
            'countries'      => $countries,
            'currentSearch'  => $this->search,
            'currentType'    => $this->typeFilter,
            'currentCountry' => $this->countryFilter,
            'currentSort'    => $this->sortBy,
        ];
    }
}
