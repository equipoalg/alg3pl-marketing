<x-filament-panels::page>

{{-- REPORT HEADER --}}
<div style="background:#FFFFFF;border:1px solid #CBD0D8;margin-bottom:16px;overflow:hidden;">
    {{-- Top bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 22px;border-bottom:1px solid #E2E5EA;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:22px;height:22px;border:1px solid #1A1D21;display:grid;place-items:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;color:#1A1D21;">A</div>
            <div style="display:flex;align-items:baseline;gap:10px;">
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:#8B95A5;">ALG3PL · Intelligence</span>
                <span style="color:#CBD0D8;">/</span>
                <span style="font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:13px;font-weight:500;color:#1A1D21;letter-spacing:-0.01em;">
                    @if($selectedCountry) {{ $selectedCountry->name }} @else Panorama global @endif
                </span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="display:flex;align-items:center;gap:6px;padding:4px 10px;border:1px solid #E2E5EA;background:#F7F8FA;">
                <span style="width:5px;height:5px;background:{{ $freshness === 'fresh' ? '#00243D' : ($freshness === 'recent' ? '#D4880F' : '#C4314B') }};"></span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:#8B95A5;letter-spacing:.04em;">{{ $lastSync }}</span>
            </div>
            <a href="/admin/leads/create" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#1A1D21;color:#F7F8FA;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;font-weight:500;text-decoration:none;letter-spacing:-0.005em;transition:opacity 120ms ease;" onmouseover="this.style.opacity='.84'" onmouseout="this.style.opacity='1'">
                + Nuevo lead
            </a>
        </div>
    </div>

    {{-- Slicer bar --}}
    <div style="display:flex;align-items:center;padding:0;border-bottom:1px solid #E2E5EA;overflow-x:auto;">
        <div style="display:flex;align-items:center;padding:0 18px;border-right:1px solid #E2E5EA;height:42px;gap:2px;flex-shrink:0;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#8B95A5;margin-right:10px;">Período</span>
            @foreach(['7d'=>'7 días','30d'=>'30 días','90d'=>'90 días','ytd'=>'Este año'] as $val=>$lbl)
            <button wire:click="setTimeRange('{{ $val }}')" style="padding:5px 12px;border:1px solid {{ $this->timeRange === $val ? '#1A1D21' : 'transparent' }};cursor:pointer;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:12px;font-weight:{{ $this->timeRange === $val ? '500' : '400' }};background:{{ $this->timeRange === $val ? '#1A1D21' : 'transparent' }};color:{{ $this->timeRange === $val ? '#F7F8FA' : '#4A5568' }};transition:all 120ms ease;letter-spacing:-0.005em;">{{ $lbl }}</button>
            @endforeach
        </div>
        <div style="display:flex;align-items:center;padding:0 18px;height:42px;gap:10px;flex-shrink:0;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#8B95A5;">País</span>
            <livewire:country-switcher />
        </div>
        <div style="flex:1;"></div>
        <div style="display:flex;align-items:center;padding:0 18px;height:42px;gap:4px;border-left:1px solid #E2E5EA;flex-shrink:0;">
            <a href="/admin/leads" style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#8B95A5;text-decoration:none;padding:5px 10px;letter-spacing:.04em;text-transform:uppercase;transition:color 120ms ease;" onmouseover="this.style.color='#1A1D21'" onmouseout="this.style.color='#8B95A5'">Leads</a>
            <a href="/admin" style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#8B95A5;text-decoration:none;padding:5px 10px;letter-spacing:.04em;text-transform:uppercase;transition:color 120ms ease;" onmouseover="this.style.color='#1A1D21'" onmouseout="this.style.color='#8B95A5'">Dashboard</a>
        </div>
    </div>
</div>

{{-- WIDGETS --}}
<div style="position:relative;">
    <div wire:loading.flex wire:target="setTimeRange, select" style="position:absolute;inset:0;z-index:10;background:rgba(247,245,240,0.8);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
        <div style="display:flex;align-items:center;gap:10px;padding:12px 20px;background:#FFFFFF;border:1px solid #CBD0D8;">
            <svg style="width:14px;height:14px;color:#1A1D21;animation:alg-spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/></svg>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#4A5568;letter-spacing:.06em;text-transform:uppercase;">Actualizando</span>
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
