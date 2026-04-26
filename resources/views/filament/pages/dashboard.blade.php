<x-filament-panels::page>

{{-- REPORT HEADER --}}
<div style="background:#FFFFFF;border:1px solid #D6D3D1;margin-bottom:16px;overflow:hidden;">
    {{-- Top bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-bottom:1px solid #E7E5E4;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:22px;height:22px;border:1px solid #0C0A09;display:grid;place-items:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;color:#0C0A09;">A</div>
            <div style="display:flex;align-items:baseline;gap:10px;">
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:#57534E;">ALG3PL · Intelligence</span>
                <span style="color:#D6D3D1;">/</span>
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:#0C0A09;letter-spacing:-0.01em;">
                    @if($selectedCountry) {{ $selectedCountry->name }} @else Panorama global @endif
                </span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="display:flex;align-items:center;gap:6px;padding:4px 10px;border:1px solid #E7E5E4;background:#FAFAF9;">
                <span style="width:5px;height:5px;background:{{ $freshness === 'fresh' ? '#1E3A8A' : ($freshness === 'recent' ? '#92400E' : '#9F1239') }};"></span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:#57534E;letter-spacing:.04em;">{{ $lastSync }}</span>
            </div>
            <a href="/admin/leads/create" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#0C0A09;color:#FAFAF9;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;text-decoration:none;letter-spacing:-0.005em;transition:opacity 120ms ease;" onmouseover="this.style.opacity='.84'" onmouseout="this.style.opacity='1'">
                + Nuevo lead
            </a>
        </div>
    </div>

    {{-- Slicer bar --}}
    <div style="display:flex;align-items:center;padding:0;border-bottom:1px solid #E7E5E4;overflow-x:auto;">
        <div style="display:flex;align-items:center;padding:0 18px;border-right:1px solid #E7E5E4;height:42px;gap:2px;flex-shrink:0;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#57534E;margin-right:10px;">Período</span>
            @foreach(['7d'=>'7 días','30d'=>'30 días','90d'=>'90 días','ytd'=>'Este año'] as $val=>$lbl)
            <button wire:click="setTimeRange('{{ $val }}')" style="padding:5px 12px;border:1px solid {{ $this->timeRange === $val ? '#0C0A09' : 'transparent' }};cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:{{ $this->timeRange === $val ? '500' : '400' }};background:{{ $this->timeRange === $val ? '#0C0A09' : 'transparent' }};color:{{ $this->timeRange === $val ? '#FAFAF9' : '#292524' }};transition:all 120ms ease;letter-spacing:-0.005em;">{{ $lbl }}</button>
            @endforeach
        </div>
        <div style="display:flex;align-items:center;padding:0 18px;height:42px;gap:10px;flex-shrink:0;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#57534E;">País</span>
            <livewire:country-switcher />
        </div>
        <div style="flex:1;"></div>
        <div style="display:flex;align-items:center;padding:0 18px;height:42px;gap:4px;border-left:1px solid #E7E5E4;flex-shrink:0;">
            <a href="/admin/leads" style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#57534E;text-decoration:none;padding:5px 10px;letter-spacing:.04em;text-transform:uppercase;transition:color 120ms ease;" onmouseover="this.style.color='#0C0A09'" onmouseout="this.style.color='#57534E'">Leads</a>
            <a href="/admin" style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#57534E;text-decoration:none;padding:5px 10px;letter-spacing:.04em;text-transform:uppercase;transition:color 120ms ease;" onmouseover="this.style.color='#0C0A09'" onmouseout="this.style.color='#57534E'">Dashboard</a>
        </div>
    </div>
</div>

{{-- WIDGETS --}}
<div style="position:relative;">
    <div wire:loading.flex wire:target="setTimeRange, select" style="position:absolute;inset:0;z-index:10;background:rgba(247,245,240,0.8);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
        <div style="display:flex;align-items:center;gap:10px;padding:12px 20px;background:#FFFFFF;border:1px solid #D6D3D1;">
            <svg style="width:14px;height:14px;color:#0C0A09;animation:alg-spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/></svg>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#292524;letter-spacing:.06em;text-transform:uppercase;">Actualizando</span>
        </div>
    </div>

    <x-filament-widgets::widgets
        :widgets="$this->getContentWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
        :data="$this->getHeaderWidgetsData()"
    />
</div>

<style>
@keyframes alg-spin { to { transform: rotate(360deg); } }
</style>

</x-filament-panels::page>
