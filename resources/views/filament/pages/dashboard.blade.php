<x-filament-panels::page>

{{-- REPORT HEADER --}}
<div style="background:#FBFAF6;border:1px solid #D7D3C7;margin-bottom:16px;overflow:hidden;">
    {{-- Top bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-bottom:1px solid #E4E0D6;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:22px;height:22px;border:1px solid #0E0E0C;display:grid;place-items:center;font-family:'Geist Mono',ui-monospace,monospace;font-size:10px;font-weight:500;color:#0E0E0C;">A</div>
            <div style="display:flex;align-items:baseline;gap:10px;">
                <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:#9A9A92;">ALG3PL · Intelligence</span>
                <span style="color:#D7D3C7;">/</span>
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:#0E0E0C;letter-spacing:-0.01em;">
                    @if($selectedCountry) {{ $selectedCountry->name }} @else Panorama global @endif
                </span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="display:flex;align-items:center;gap:6px;padding:4px 10px;border:1px solid #E4E0D6;background:#F7F5F0;">
                <span style="width:5px;height:5px;background:{{ $freshness === 'fresh' ? 'oklch(45% 0.05 130)' : ($freshness === 'recent' ? '#8a6d00' : 'oklch(48% 0.12 30)') }};"></span>
                <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:10px;color:#9A9A92;letter-spacing:.04em;">{{ $lastSync }}</span>
            </div>
            <a href="/admin/leads/create" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#0E0E0C;color:#F7F5F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;text-decoration:none;letter-spacing:-0.005em;transition:opacity 120ms ease;" onmouseover="this.style.opacity='.84'" onmouseout="this.style.opacity='1'">
                + Nuevo lead
            </a>
        </div>
    </div>

    {{-- Slicer bar --}}
    <div style="display:flex;align-items:center;padding:0;border-bottom:1px solid #E4E0D6;overflow-x:auto;">
        <div style="display:flex;align-items:center;padding:0 18px;border-right:1px solid #E4E0D6;height:42px;gap:2px;flex-shrink:0;">
            <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#9A9A92;margin-right:10px;">Período</span>
            @foreach(['7d'=>'7 días','30d'=>'30 días','90d'=>'90 días','ytd'=>'Este año'] as $val=>$lbl)
            <button wire:click="setTimeRange('{{ $val }}')" style="padding:5px 12px;border:1px solid {{ $this->timeRange === $val ? '#0E0E0C' : 'transparent' }};cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:{{ $this->timeRange === $val ? '500' : '400' }};background:{{ $this->timeRange === $val ? '#0E0E0C' : 'transparent' }};color:{{ $this->timeRange === $val ? '#F7F5F0' : '#3A3A36' }};transition:all 120ms ease;letter-spacing:-0.005em;">{{ $lbl }}</button>
            @endforeach
        </div>
        <div style="display:flex;align-items:center;padding:0 18px;height:42px;gap:10px;flex-shrink:0;">
            <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#9A9A92;">País</span>
            <livewire:country-switcher />
        </div>
        <div style="flex:1;"></div>
        <div style="display:flex;align-items:center;padding:0 18px;height:42px;gap:4px;border-left:1px solid #E4E0D6;flex-shrink:0;">
            <a href="/admin/leads" style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#9A9A92;text-decoration:none;padding:5px 10px;letter-spacing:.04em;text-transform:uppercase;transition:color 120ms ease;" onmouseover="this.style.color='#0E0E0C'" onmouseout="this.style.color='#9A9A92'">Leads</a>
            <a href="/admin" style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#9A9A92;text-decoration:none;padding:5px 10px;letter-spacing:.04em;text-transform:uppercase;transition:color 120ms ease;" onmouseover="this.style.color='#0E0E0C'" onmouseout="this.style.color='#9A9A92'">Dashboard</a>
        </div>
    </div>
</div>

{{-- WIDGETS --}}
<div style="position:relative;">
    <div wire:loading.flex wire:target="setTimeRange, select" style="position:absolute;inset:0;z-index:10;background:rgba(247,245,240,0.8);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
        <div style="display:flex;align-items:center;gap:10px;padding:12px 20px;background:#FBFAF6;border:1px solid #D7D3C7;">
            <svg style="width:14px;height:14px;color:#0E0E0C;animation:alg-spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/></svg>
            <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#3A3A36;letter-spacing:.06em;text-transform:uppercase;">Actualizando</span>
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
