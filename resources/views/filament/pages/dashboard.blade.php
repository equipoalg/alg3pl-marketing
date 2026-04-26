{{--
  Dashboard ALG · Shell
  Renders the page header (title + range tabs + variant toggle) and
  includes the chosen variant partial. Source design: claude.ai/design
  bundle (Iz0SYnOaqUPIiur-PIu8pA) — A "classic refined" / B "editorial".
--}}
<x-filament-panels::page>

{{-- PAGE HEADER --}}
<div style="padding: 4px 0 20px; display: flex; flex-direction: column; gap: 14px;">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
        <div style="min-width: 0;">
            <h1 style="margin: 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 22px; font-weight: 600; letter-spacing: -0.02em; color: #0F172A;">
                @if($selectedCountry){{ $selectedCountry->name }}@else Panorama global @endif
            </h1>
            <p style="margin: 6px 0 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13px; color: #94A3B8; max-width: 560px; line-height: 1.5;">
                Vista resumen del CRM y desempeño de marketing —
                @switch($timeRange)
                    @case('7d') últimos 7 días @break
                    @case('90d') últimos 90 días @break
                    @case('ytd') este año @break
                    @default últimos 30 días
                @endswitch ·
                <span style="color: {{ $freshness === 'fresh' ? '#166534' : ($freshness === 'recent' ? '#92400E' : '#9F1239') }};">
                    sincronizado {{ $lastSync }}
                </span>
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            {{-- Range tabs (per design) --}}
            <div style="display: inline-flex; border: 1px solid #E2E8F0; border-radius: 6px; padding: 2px; background: #FFFFFF;">
                @foreach(['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'ytd' => 'Año'] as $val => $lbl)
                <button wire:click="setTimeRange('{{ $val }}')" type="button" style="padding: 5px 11px; border: 0; border-radius: 4px; cursor: pointer; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; font-weight: {{ $timeRange === $val ? '500' : '400' }}; color: {{ $timeRange === $val ? '#0F172A' : '#94A3B8' }}; background: {{ $timeRange === $val ? '#F8FAFC' : 'transparent' }}; transition: all 150ms ease-out;">{{ $lbl }}</button>
                @endforeach
            </div>

            {{-- Filtros (per design) --}}
            <button type="button" style="display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid #E2E8F0;background:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;cursor:pointer;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h14M5 10h10M7 14h6"/></svg>
                Filtros
            </button>

            {{-- Exportar (per design) --}}
            <button type="button" style="display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid #E2E8F0;background:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;cursor:pointer;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v10M5 9l5 4 5-4M3 16h14"/></svg>
                Exportar
            </button>

            {{-- Variant toggle — discrete pill on the far right --}}
            <div style="display:inline-flex;border:1px solid #E2E8F0;border-radius:6px;padding:2px;background:#FFFFFF;margin-left:6px;" title="Cambiar layout">
                @foreach(['a' => 'A', 'b' => 'B'] as $v => $lbl)
                <button wire:click="setVariant('{{ $v }}')" type="button" style="padding:5px 9px;border:0;border-radius:4px;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;color:{{ $variant === $v ? '#0F172A' : '#94A3B8' }};background:{{ $variant === $v ? '#F8FAFC' : 'transparent' }};transition:all 150ms ease-out;letter-spacing:0.04em;">{{ $lbl }}</button>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Body data attribute drives sidebar mode (B = compact icon rail, A = wide) --}}
<script>document.body.dataset.algVariant = @js($variant);</script>

{{-- LOADING OVERLAY --}}
<div style="position: relative;">
    <div wire:loading.flex wire:target="setTimeRange, setVariant, select" style="position: absolute; inset: 0; z-index: 10; background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(2px); align-items: center; justify-content: center;">
        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 6px;">
            <svg style="width: 14px; height: 14px; color: #1E3A8A; animation: alg-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/>
            </svg>
            <span style="font-family: ui-monospace,'SF Mono',Menlo,monospace; font-size: 11px; color: #334155; letter-spacing: 0.06em; text-transform: uppercase;">Actualizando</span>
        </div>
    </div>

    {{-- VARIANT BODY --}}
    @if($variant === 'b')
        @include('filament.pages.dashboard-b', compact('kpis', 'recentLeads'))
    @else
        @include('filament.pages.dashboard-a', compact('kpis', 'recentLeads'))
    @endif
</div>

<style>
@keyframes alg-spin { to { transform: rotate(360deg); } }
</style>

</x-filament-panels::page>
