{{-- Sidebar workspace switcher — per Claude Design dashboard-a.jsx:30-44 --}}
@php
    $currentCountry = null;
    if ($cid = session('country_filter')) {
        $currentCountry = \App\Models\Country::find($cid);
    }
    // Short label keeps the pill from truncating in 224px sidebar
    $label = $currentCountry
        ? 'ALG ' . strtoupper($currentCountry->code)
        : 'ALG Global';
@endphp
<div class="alg-sidebar-workspace" style="padding:10px 12px;">
    <div class="alg-sidebar-workspace-pill" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:7px 10px;border-radius:6px;background:#F8FAFC;border:1px solid #E2E8F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#334155;gap:6px;min-width:0;">
        <span style="display:flex;align-items:center;gap:7px;min-width:0;flex:1;">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="#64748B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3a10 10 0 010 14M10 3a10 10 0 000 14"/></svg>
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;" title="{{ $label }}">{{ $label }}</span>
        </span>
        {{-- Embed the country switcher as a small inline trigger that opens the dropdown --}}
        <span class="alg-workspace-trigger" style="display:flex;align-items:center;flex-shrink:0;">
            <livewire:country-switcher />
        </span>
    </div>
</div>
