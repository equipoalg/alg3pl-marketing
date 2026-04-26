<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\Lead;
use Livewire\Component;

class CountrySwitcher extends Component
{
    public ?string $selected = '';

    public function mount(): void
    {
        $this->selected = session('country_filter', '');
    }

    public function select(?string $id = null): void
    {
        $this->selected = $id ?? '';
        session(['country_filter' => $this->selected]);
        $this->redirect(request()->header('Referer', '/admin'));
    }

    public function render()
    {
        // Lead counts for the badge — scoped to selected country (or global).
        $leadQuery = Lead::query();
        if ($this->selected) {
            $leadQuery->where('country_id', $this->selected);
        }
        $leadCount = (clone $leadQuery)->count();
        $newLeadCount = (clone $leadQuery)->where('created_at', '>=', now()->subDay())->count();

        return view('livewire.country-switcher', [
            'countries' => Country::active()
                ->where('is_regional', false)
                // CASE WHEN works on both MySQL and SQLite (FIELD is MySQL-only)
                ->orderByRaw("CASE code WHEN 'sv' THEN 1 WHEN 'gt' THEN 2 WHEN 'hn' THEN 3 WHEN 'ni' THEN 4 WHEN 'cr' THEN 5 WHEN 'pa' THEN 6 ELSE 99 END")
                ->get(),
            'leadCount' => $leadCount,
            'newLeadCount' => $newLeadCount,
        ]);
    }
}
