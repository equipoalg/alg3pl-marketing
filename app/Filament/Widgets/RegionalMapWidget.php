<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Country;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class RegionalMapWidget extends Widget
{
    protected string $view = 'filament.widgets.regional-map';
    protected int|string|array $columnSpan = 1;
    protected static ?int $sort = 3;

    public ?string $countryFilter = '';

    public function mount(): void
    {
        $this->countryFilter = session('country_filter', '');
    }

    public function getViewData(): array
    {
        $cacheKey = 'alg_regional_map_' . ($this->countryFilter ?? 'global');

        $mapData = Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $countries = Country::active()->where('is_regional', false)->get();
            $data      = [];
            $maxLeads  = 1;

            foreach ($countries as $c) {
                $count            = Lead::where('country_id', $c->id)->count();
                $data[$c->code]   = ['name' => $c->name, 'id' => $c->id, 'leads' => $count];
                if ($count > $maxLeads) {
                    $maxLeads = $count;
                }
            }

            foreach ($data as $code => &$d) {
                $intensity  = $d['leads'] / $maxLeads;
                $d['color'] = "rgba(14,14,12," . round(0.08 + $intensity * 0.5, 2) . ")";
            }

            return $data;
        });

        // Apply selected state outside cache (depends on live filter value)
        foreach ($mapData as $code => &$d) {
            $d['selected'] = $this->countryFilter == $d['id'];
        }

        return ['mapData' => $mapData, 'filter' => $this->countryFilter];
    }
}
