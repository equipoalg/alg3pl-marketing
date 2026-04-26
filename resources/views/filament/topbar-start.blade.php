{{-- Topbar breadcrumb: Cliente · Panorama global (per Claude Design) --}}
{{-- The country switcher lives in the sidebar workspace block, not here. --}}
@php
    $currentCountry = null;
    if ($cid = session('country_filter')) {
        $currentCountry = \App\Models\Country::find($cid);
    }
@endphp
<div style="display:flex;align-items:center;gap:10px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:#64748B;">
    <span>Cliente</span>
    <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5-5 5"/></svg>
    <span style="color:#64748B;">ALG {{ $currentCountry ? strtoupper($currentCountry->code) : 'Global' }}</span>
    <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5-5 5"/></svg>
    <span style="color:#0F172A;font-weight:500;">Panorama global</span>
</div>
