<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use Livewire\Attributes\Url;

/**
 * Empresas — top-level page that aggregates the freetext `Lead.company`
 * column into a unified company-centric view.
 *
 * Why a custom page (and not a Resource): Empresa isn't a persisted entity.
 * It's a derivation: GROUP BY trim(company) over the leads table. This page
 * surfaces that aggregation as if it were a first-class concept, so the
 * operator can scan companies in their pipeline without going through
 * /admin/leads?view=companies.
 *
 * Differences vs the in-leads `?view=companies` toggle:
 *   - Standalone URL bookmarkable (/admin/empresas)
 *   - Search by company name (URL-bound `?q=`)
 *   - Filter by "has won lead" / "has open lead" (via $statusFilter)
 *   - Click on row → /admin/leads?view=contacts&q={empresa} (drill into contacts)
 */
class EmpresasPage extends Page
{
    protected string $view = 'filament.pages.empresas';
    protected Width|string|null $maxContentWidth = Width::Full;

    /** Search by company name. */
    #[Url(as: 'q')]
    public string $search = '';

    /** Filter to companies that have at least one lead in this status. */
    #[Url(as: 'status')]
    public string $statusFilter = '';

    /** Country filter (ID). Empty = todos los países. */
    #[Url(as: 'country')]
    public ?int $countryFilter = null;

    /**
     * Sort order. Valid values:
     *   count_desc (default) | value_desc | latest | stalled
     */
    #[Url(as: 'sort')]
    public string $sortBy = 'count_desc';

    /** Selected company name (for the slide-over right pane). */
    #[Url(as: 'selected')]
    public ?string $selectedEmpresa = null;

    /** Set of empresa names that are inline-expanded (chevron ▾). Session-only. */
    public array $expandedEmpresas = [];

    public static function getNavigationIcon(): string { return 'heroicon-o-building-office'; }
    public static function getNavigationGroup(): string { return 'CRM'; }
    public static function getNavigationSort(): int { return 2; }
    public static function getNavigationLabel(): string { return 'Empresas'; }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'empresas';
    }

    public function getTitle(): string
    {
        return 'Empresas';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return ''; // suppress Filament heading; toolbar replaces it
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->countryFilter = null;
        $this->sortBy = 'count_desc';
    }

    public function selectEmpresa(string $name): void
    {
        $this->selectedEmpresa = $name;
    }

    public function closeEmpresa(): void
    {
        $this->selectedEmpresa = null;
    }

    public function toggleExpand(string $name): void
    {
        if (in_array($name, $this->expandedEmpresas, true)) {
            $this->expandedEmpresas = array_values(array_filter($this->expandedEmpresas, fn ($n) => $n !== $name));
        } else {
            $this->expandedEmpresas[] = $name;
        }
    }

    /**
     * Convertir empresa → Cliente directamente desde Empresas page.
     * Crea un Client con company_name=$name, primary contact = lead más reciente,
     * country = el más común dentro del grupo.
     */
    public function convertEmpresaToClient(string $name): void
    {
        $name = trim($name);
        if ($name === '' || $name === '— Sin empresa —') {
            \Filament\Notifications\Notification::make()->title('Empresa inválida')->warning()->send();
            return;
        }

        // Anti-duplicado: si ya existe un Client con ese company_name, no duplicar
        $existing = \App\Models\Client::where('company_name', $name)->first();
        if ($existing) {
            \Filament\Notifications\Notification::make()
                ->title('Ya existe Cliente')
                ->body("$name — Cliente #{$existing->id}")
                ->warning()->send();
            return;
        }

        // Buscar el lead más reciente con esta empresa para usarlo como primary contact
        $repLead = Lead::where('company', $name)
            ->orderByDesc('created_at')
            ->first();

        if (! $repLead) {
            \Filament\Notifications\Notification::make()->title('No se encontraron leads para esta empresa')->warning()->send();
            return;
        }

        $client = \App\Models\Client::create([
            'country_id'            => $repLead->country_id,
            'assigned_to'           => $repLead->assigned_to,
            'company_name'          => $name,
            'tier'                  => 'smb',
            'status'                => 'active',
            'primary_contact_name'  => $repLead->name,
            'primary_contact_email' => $repLead->email,
            'primary_contact_phone' => $repLead->phone,
            'health_score'          => 70,
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Cliente creado')
            ->body("$name — agregado a /admin/clients")
            ->success()->send();
    }

    public function getViewData(): array
    {
        // Country scope from the global sidebar selector — same as everywhere else.
        $base = LeadResource::getEloquentQuery()
            ->with('country')
            // Cargar latest_activity con sub-query para el sort 'stalled' sin N+1
            ->withMax('activities as latest_activity_at', 'created_at');

        if ($this->statusFilter !== '') {
            $base->where('status', $this->statusFilter);
        }

        if ($this->countryFilter) {
            $base->where('country_id', $this->countryFilter);
        }

        // Apply search to company name BEFORE we group, so the agg only includes
        // matching companies (cheaper than filtering the agg in PHP).
        if ($this->search !== '') {
            $base->where('company', 'like', '%' . $this->search . '%');
        }

        $leads = $base->limit(2000)->get();

        // Group by trimmed company (freetext). Empty/null falls into "Sin empresa".
        $companies = $leads->groupBy(fn ($l) => trim((string) $l->company) ?: '— Sin empresa —')
            ->map(function ($group, $name) {
                // Latest activity across all leads de la empresa (para sort 'stalled')
                $latestActivity = $group->map(fn ($l) => $l->latest_activity_at ?: $l->created_at)->max();
                return [
                    'name'            => $name,
                    'count'           => $group->count(),
                    'latest'          => $group->max('created_at'),
                    'latest_activity' => $latestActivity,
                    'leads'           => $group,
                    'statuses'        => $group->groupBy('status')->map->count()->toArray(),
                    'countries'       => $group->pluck('country.code')->filter()->unique()->values()->toArray(),
                    'value'           => (float) $group->sum('estimated_value'),
                    'has_won'         => $group->where('status', 'won')->isNotEmpty(),
                    'has_open'        => $group->whereNotIn('status', ['won', 'lost'])->isNotEmpty(),
                ];
            });

        // Smart sort
        $companies = match ($this->sortBy) {
            'value_desc' => $companies->sortByDesc('value'),
            'latest'     => $companies->sortByDesc('latest'),
            // Stalled = empresas con leads abiertos cuyo último touch es el más viejo
            'stalled'    => $companies->filter(fn ($c) => $c['has_open'])->sortBy('latest_activity'),
            default      => $companies->sortByDesc('count'),
        };
        $companies = $companies->values();

        // Resolve selected empresa for slide-over rendering
        $selected = null;
        if ($this->selectedEmpresa) {
            $selected = $companies->firstWhere('name', $this->selectedEmpresa);
        }

        return [
            'companies'        => $companies,
            'totalCompanies'   => $companies->count(),
            'totalLeads'       => $leads->count(),
            'selected'         => $selected,
            'expandedEmpresas' => $this->expandedEmpresas,
            'sortBy'           => $this->sortBy,
            'countries'        => \App\Models\Country::orderBy('name')->get(['id', 'code', 'name']),
            'statuses'         => [
                'new'         => 'Nuevos',
                'contacted'   => 'Contactados',
                'qualified'   => 'Calificados',
                'proposal'    => 'Propuesta',
                'negotiation' => 'Negociación',
                'won'         => 'Ganados',
                'lost'        => 'Perdidos',
            ],
        ];
    }
}
