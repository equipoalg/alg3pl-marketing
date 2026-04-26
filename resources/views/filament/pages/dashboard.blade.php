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
            {{-- Variant toggle --}}
            <div style="display: inline-flex; border: 1px solid #E2E8F0; border-radius: 6px; padding: 2px; background: #FFFFFF;">
                @foreach(['a' => 'A · Clásica', 'b' => 'B · Editorial'] as $v => $lbl)
                <button wire:click="setVariant('{{ $v }}')" type="button" style="padding: 5px 11px; border: 0; border-radius: 4px; cursor: pointer; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; font-weight: {{ $variant === $v ? '500' : '400' }}; color: {{ $variant === $v ? '#0F172A' : '#94A3B8' }}; background: {{ $variant === $v ? '#F8FAFC' : 'transparent' }}; transition: all 150ms ease-out;">{{ $lbl }}</button>
                @endforeach
            </div>

            {{-- Range tabs --}}
            <div style="display: inline-flex; border: 1px solid #E2E8F0; border-radius: 6px; padding: 2px; background: #FFFFFF;">
                @foreach(['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'ytd' => 'Año'] as $val => $lbl)
                <button wire:click="setTimeRange('{{ $val }}')" type="button" style="padding: 5px 11px; border: 0; border-radius: 4px; cursor: pointer; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; font-weight: {{ $timeRange === $val ? '500' : '400' }}; color: {{ $timeRange === $val ? '#0F172A' : '#94A3B8' }}; background: {{ $timeRange === $val ? '#F8FAFC' : 'transparent' }}; transition: all 150ms ease-out;">{{ $lbl }}</button>
                @endforeach
            </div>

            {{-- Country switcher --}}
            <livewire:country-switcher />

            {{-- Primary CTA --}}
            <a href="/admin/leads/create" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; background: #0F172A; color: #FFFFFF; border-radius: 6px; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12.5px; font-weight: 500; text-decoration: none; letter-spacing: -0.005em; transition: opacity 150ms ease-out;" onmouseover="this.style.opacity='.86'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo lead
            </a>
        </div>
    </div>
</div>

{{-- QUICK LINKS — clickable shortcuts to high-frequency operations --}}
<div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px;">
    @php
        $quickLinks = [
            ['label' => 'Todos los leads', 'href' => '/admin/leads',       'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['label' => 'Crear lead',      'href' => '/admin/leads/create','icon' => 'M12 5v14M5 12h14'],
            ['label' => 'Kanban',          'href' => '/admin/kanban',      'icon' => 'M3 5h4v14H3V5zm7 0h4v9h-4V5zm7 0h4v6h-4V5z'],
            ['label' => 'Campañas',        'href' => '/admin/campaigns',   'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
            ['label' => 'Tareas',          'href' => '/admin/tasks',       'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ];
    @endphp
    @foreach($quickLinks as $link)
    <a href="{{ $link['href'] }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid #E2E8F0; border-radius: 6px; background: #FFFFFF; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; font-weight: 500; color: #334155; text-decoration: none; transition: all 150ms ease-out;" onmouseover="this.style.borderColor='#1E3A8A'; this.style.color='#1E3A8A'; this.style.background='#EFF3FB';" onmouseout="this.style.borderColor='#E2E8F0'; this.style.color='#334155'; this.style.background='#FFFFFF';">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $link['icon'] }}"/></svg>
        {{ $link['label'] }}
    </a>
    @endforeach
</div>

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
