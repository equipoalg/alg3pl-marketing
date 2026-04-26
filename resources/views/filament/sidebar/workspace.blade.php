{{-- Sidebar workspace switcher — per Claude Design dashboard-a.jsx:30-44 --}}
@php
    $currentCountry = null;
    if ($cid = session('country_filter')) {
        $currentCountry = \App\Models\Country::find($cid);
    }
    $label = $currentCountry
        ? 'Cliente: ALG ' . strtoupper($currentCountry->code)
        : 'Cliente: Global';
@endphp
<div style="padding:10px 12px;" class="alg-sidebar-workspace">
    {{-- The CountrySwitcher livewire component is the actual interactive control. --}}
    {{-- We wrap it so the trigger button matches the design's pill style. --}}
    <livewire:country-switcher />
</div>
