<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Imports\LeadImporter;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;

/**
 * Inbox-style replacement of the standard Filament list page.
 *
 * /admin/leads now renders as a Gmail/Linear-style split view: scrollable
 * lead list on the left (with search + status chips), full contact card +
 * activity timeline on the right. Click a row to update the right panel —
 * does NOT navigate to the edit page (the user complaint that prompted
 * this rewrite). The "Editar" button inside the right panel takes you to
 * the actual edit form when you need it.
 *
 * Country filter from the sidebar (session('country_filter')) is honored
 * via the same trait used by the resource's getEloquentQuery.
 */
class ListLeads extends Page
{
    protected static string $resource = LeadResource::class;
    protected string $view = 'filament.resources.lead-resource.pages.leads-inbox';
    protected Width|string|null $maxContentWidth = Width::Full;

    public ?int $selectedId = null;
    public string $statusFilter = '';
    public string $search = '';

    public function mount(): void
    {
        // Default selection = newest lead, so the right panel isn't empty on first load
        $first = $this->buildQuery()->first();
        if ($first) {
            $this->selectedId = $first->id;
        }
    }

    public function selectLead(int $id): void
    {
        $this->selectedId = $id;
    }

    public function setStatus(string $value): void
    {
        $this->statusFilter = $value;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(LeadImporter::class)
                ->label('Import CSV'),
            Actions\CreateAction::make()->label('Nuevo lead'),
        ];
    }

    /**
     * Eloquent base query — uses the resource's query (which already applies
     * the ScopesByCountryFilter trait), then layers status/search filters.
     */
    protected function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return LeadResource::getEloquentQuery()
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) {
                $like = '%' . $this->search . '%';
                $q->where(function ($x) use ($like) {
                    $x->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('company', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('created_at', 'desc');
    }

    public function getViewData(): array
    {
        $leads = $this->buildQuery()
            ->with('country')
            ->limit(500)
            ->get();

        $selected = null;
        if ($this->selectedId) {
            $selected = Lead::with(['country', 'tags', 'activities' => fn ($q) => $q->latest()->limit(20)])
                ->find($this->selectedId);
        }

        return [
            'leads'        => $leads,
            'selected'     => $selected,
            'statuses'     => [
                ''           => 'Todos',
                'new'        => 'Nuevos',
                'contacted'  => 'Contactados',
                'qualified'  => 'Calificados',
                'proposal'   => 'Propuesta',
                'negotiation'=> 'Negociación',
                'won'        => 'Ganados',
                'lost'       => 'Perdidos',
            ],
        ];
    }
}
