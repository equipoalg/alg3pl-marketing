{{-- Sidebar workspace switcher — per Claude Design dashboard-a.jsx:30-44 --}}
@php
    $currentCountry = null;
    if ($cid = session('country_filter')) {
        $currentCountry = \App\Models\Country::find($cid);
    }
@endphp
<div class="alg-sidebar-workspace" style="padding:10px 12px;">
    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:7px 10px;border-radius:6px;background:#F8FAFC;border:1px solid #E2E8F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#334155;">
        <span style="display:flex;align-items:center;gap:8px;min-width:0;">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="#64748B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3a10 10 0 010 14M10 3a10 10 0 000 14"/></svg>
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Cliente: ALG {{ $currentCountry ? strtoupper($currentCountry->code) : 'Global' }}</span>
        </span>
        {{-- Embed the country switcher as a small inline trigger that opens the dropdown --}}
        <span class="alg-workspace-trigger" style="display:flex;align-items:center;flex-shrink:0;">
            <livewire:country-switcher />
        </span>
    </div>
</div>
