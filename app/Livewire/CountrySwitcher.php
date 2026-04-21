<?php

namespace App\Livewire;

use App\Models\Country;
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
        return view('livewire.country-switcher', [
            'countries' => Country::active()
                ->where('is_regional', false)
                ->orderByRaw("FIELD(code, 'sv','gt','hn','ni','cr','pa')")
                ->get(),
        ]);
    }
}
