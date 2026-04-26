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
            <h1 style="margin: 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 22px; font-weight: 600; letter-spacing: -0.02em; color: #0C0A09;">
                @if($selectedCountry){{ $selectedCountry->name }}@else Panorama global @endif
            </h1>
            <p style="margin: 6px 0 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13px; color: #78716C; max-width: 560px; line-height: 1.5;">
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
            {{-- Variant toggle --}}
            <div style="display: inline-flex; border: 1px solid #E7E5E4; border-radius: 6px; padding: 2px; background: #FFFFFF;">
                @foreach(['a' => 'A · Clásica', 'b' => 'B · Editorial'] as $v => $lbl)
                <button wire:click="setVariant('{{ $v }}')" type="button" style="padding: 5px 11px; border: 0; border-radius: 4px; cursor: pointer; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; font-weight: {{ $variant === $v ? '500' : '400' }}; color: {{ $variant === $v ? '#0C0A09' : '#78716C' }}; background: {{ $variant === $v ? '#F5F5F4' : 'transparent' }}; transition: all 150ms ease-out;">{{ $lbl }}</button>
                @endforeach
            </div>

            {{-- Range tabs --}}
            <div style="display: inline-flex; border: 1px solid #E7E5E4; border-radius: 6px; padding: 2px; background: #FFFFFF;">
                @foreach(['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'ytd' => 'Año'] as $val => $lbl)
                <button wire:click="setTimeRange('{{ $val }}')" type="button" style="padding: 5px 11px; border: 0; border-radius: 4px; cursor: pointer; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; font-weight: {{ $timeRange === $val ? '500' : '400' }}; color: {{ $timeRange === $val ? '#0C0A09' : '#78716C' }}; background: {{ $timeRange === $val ? '#F5F5F4' : 'transparent' }}; transition: all 150ms ease-out;">{{ $lbl }}</button>
                @endforeach
            </div>

            {{-- Country switcher --}}
            <livewire:country-switcher />

            {{-- Primary CTA --}}
            <a href="/admin/leads/create" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; background: #0C0A09; color: #FFFFFF; border-radius: 6px; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12.5px; font-weight: 500; text-decoration: none; letter-spacing: -0.005em; transition: opacity 150ms ease-out;" onmouseover="this.style.opacity='.86'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo lead
            </a>
        </div>
    </div>
</div>

{{-- LOADING OVERLAY --}}
<div style="position: relative;">
    <div wire:loading.flex wire:target="setTimeRange, setVariant, select" style="position: absolute; inset: 0; z-index: 10; background: rgba(250, 250, 249, 0.85); backdrop-filter: blur(2px); align-items: center; justify-content: center;">
        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 6px;">
            <svg style="width: 14px; height: 14px; color: #1E3A8A; animation: alg-spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/>
            </svg>
            <span style="font-family: ui-monospace,'SF Mono',Menlo,monospace; font-size: 11px; color: #292524; letter-spacing: 0.06em; text-transform: uppercase;">Actualizando</span>
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
