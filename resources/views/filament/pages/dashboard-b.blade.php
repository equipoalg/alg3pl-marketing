{{--
  Dashboard ALG · Variación B — "Editorial · jerarquía remixada"
  Hero with editorial title + KPI strip → Pipeline horizontal strip →
  Analytics room (keywords + fuentes) → Leads + Campaigns → Activity.
--}}

@php
    $totalLeads = $kpis[0]['value'] ?? 0;
    $conversion = $kpis[3]['value'] ?? '0%';
    $conversionDelta = $kpis[3]['delta'] ?? 0;
    $estimatedValue = \App\Models\Lead::query()
        ->when($countryFilter, fn($q) => $q->where('country_id', $countryFilter))
        ->whereIn('status', ['qualified', 'proposal', 'negotiation'])
        ->sum('estimated_value');
@endphp

{{-- HERO --}}
<section style="padding: 28px 28px 24px; background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; margin-bottom: 16px;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-bottom: 24px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 280px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11px; color: #78716C; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">
                <span style="width: 18px; height: 1px; background: #A8A29E;"></span>
                Panorama · ALG · {{ now()->isoFormat('D MMMM YYYY') }}
            </div>
            <h1 style="margin: 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 28px; font-weight: 500; letter-spacing: -0.03em; color: #0C0A09; line-height: 1.15; max-width: 720px;">
                @if($totalLeads > 0)
                    <span style="color: #1E3A8A;">{{ number_format($totalLeads) }}</span> leads activos generaron
                    <span style="color: #1E3A8A;">${{ number_format($estimatedValue) }}</span>
                    en pipeline durante el período.
                @else
                    Tu panel está esperando datos. Configurá un webhook de leads y los números aparecerán acá.
                @endif
            </h1>
            <p style="margin: 12px 0 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13.5px; color: #57534E; max-width: 640px; line-height: 1.55;">
                @if($totalLeads > 0)
                    Conversión a {{ $conversion }}{{ $conversionDelta != 0 ? ', ' . ($conversionDelta > 0 ? '↑' : '↓') . ' ' . abs($conversionDelta) . ' pts vs período anterior' : '' }}.
                    @if($selectedCountry) Filtro activo: {{ $selectedCountry->name }}. @endif
                @else
                    Una vez que lleguen los primeros leads, vas a ver acá un titular generado automáticamente con los números clave del período.
                @endif
            </p>
        </div>

        <a href="/admin/leads/create" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #0C0A09; color: #FFFFFF; border-radius: 6px; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12.5px; font-weight: 500; text-decoration: none; flex-shrink: 0; transition: opacity 150ms ease-out;" onmouseover="this.style.opacity='.86'" onmouseout="this.style.opacity='1'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo lead
        </a>
    </div>

    {{-- Hero KPI strip --}}
    <div style="display: grid; grid-template-columns: repeat({{ count($kpis) }}, 1fr); gap: 0;">
        @foreach($kpis as $i => $kpi)
        <div style="padding: 16px 24px 0; {{ $i > 0 ? 'border-left: 1px solid #E7E5E4;' : '' }} display: flex; flex-direction: column; gap: 4px;">
            <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 10.5px; color: #78716C; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 500;">{{ $kpi['label'] }}</span>
            <span style="font-family: 'Geist Mono',ui-monospace,'JetBrains Mono','SF Mono',monospace; font-size: 28px; font-weight: 500; letter-spacing: -0.025em; color: #0C0A09; line-height: 1; font-variant-numeric: tabular-nums;">
                {{ is_numeric($kpi['value']) ? number_format($kpi['value']) : $kpi['value'] }}
            </span>
            @if($kpi['delta'] != 0)
            <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: {{ $kpi['delta'] > 0 ? '#166534' : '#9F1239' }}; font-weight: 500;">
                {{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}%
            </span>
            @else
            <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #78716C;">—</span>
            @endif
        </div>
        @endforeach
    </div>
</section>

{{-- PIPELINE STRIP --}}
@php
    $pipelineStages = [
        ['key' => 'new',         'label' => 'Nuevo',       'color' => '#A8A29E'],
        ['key' => 'contacted',   'label' => 'Contactado',  'color' => '#78716C'],
        ['key' => 'qualified',   'label' => 'Calificado',  'color' => '#1E3A8A'],
        ['key' => 'proposal',    'label' => 'Propuesta',   'color' => '#1E3A8A'],
        ['key' => 'negotiation', 'label' => 'Negociación', 'color' => '#2563EB'],
        ['key' => 'won',         'label' => 'Ganado',      'color' => '#166534'],
        ['key' => 'lost',        'label' => 'Perdido',     'color' => '#9F1239'],
    ];
    $stageCounts = \App\Models\Lead::query()
        ->when($countryFilter, fn($q) => $q->where('country_id', $countryFilter))
        ->selectRaw('status, COUNT(*) as c')
        ->groupBy('status')
        ->pluck('c', 'status');
    $totalPipeline = $stageCounts->sum() ?: 1;
@endphp

<section style="padding: 22px 24px; background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; margin-bottom: 16px;">
    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px;">
        <div>
            <h2 style="margin: 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 15px; font-weight: 600; letter-spacing: -0.01em;">Pipeline</h2>
            <p style="margin: 4px 0 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; color: #78716C;">
                {{ $stageCounts->sum() }} leads en movimiento · valor estimado ${{ number_format($estimatedValue) }} USD
            </p>
        </div>
        <a href="/admin/leads" style="display: inline-flex; align-items: center; gap: 4px; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; color: #57534E; text-decoration: none;">
            Ver detalle
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
        </a>
    </div>
    <div style="display: grid; grid-template-columns: repeat({{ count($pipelineStages) }}, 1fr); gap: 8px;">
        @foreach($pipelineStages as $stage)
        @php $count = $stageCounts->get($stage['key'], 0); $pct = $totalPipeline > 0 ? round(($count / $totalPipeline) * 100) : 0; @endphp
        <div style="padding: 12px 14px; border: 1px solid #E7E5E4; border-radius: 6px; background: #FFFFFF; border-top: 2px solid {{ $stage['color'] }}; display: flex; flex-direction: column; gap: 6px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #57534E; font-weight: 500;">{{ $stage['label'] }}</span>
                <span style="font-family: 'Geist Mono',ui-monospace,monospace; font-size: 10px; color: #A8A29E;">{{ $pct }}%</span>
            </div>
            <div style="font-family: 'Geist Mono',ui-monospace,'JetBrains Mono','SF Mono',monospace; font-size: 22px; font-weight: 500; letter-spacing: -0.02em; color: #0C0A09; font-variant-numeric: tabular-nums;">{{ $count }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- ANALYTICS ROOM (Keywords + Country breakdown) --}}
<div style="display: grid; grid-template-columns: 1.45fr 1fr; gap: 16px; margin-bottom: 16px;">
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\TopKeywordsWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\LeadsByCountryWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>

{{-- TRAFFIC FULL-WIDTH --}}
<div style="margin-bottom: 16px;">
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\TrafficTrendWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>

{{-- LEADS + ACTIVITY --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
    {{-- Recent leads (compact for B) --}}
    <div style="background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; padding: 16px 20px;">
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h2 style="margin: 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 15px; font-weight: 600; letter-spacing: -0.01em;">Leads recientes</h2>
                <p style="margin: 4px 0 0; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; color: #78716C;">Últimos {{ $recentLeads->count() }} registros</p>
            </div>
            <a href="/admin/leads" style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; color: #57534E; text-decoration: none;">Ver todos →</a>
        </div>
        <div style="border-top: 1px solid #292524;">
            @forelse($recentLeads as $lead)
            @php
                $stageColors = [
                    'won'        => ['#ECFDF5', '#166534'],
                    'proposal'   => ['#EFF3FB', '#1E3A8A'],
                    'qualified'  => ['#EFF3FB', '#1E3A8A'],
                    'contacted'  => ['#F5F5F4', '#57534E'],
                    'new'        => ['#F5F5F4', '#78716C'],
                    'lost'       => ['#FEF2F2', '#9F1239'],
                ];
                [$bg, $fg] = $stageColors[$lead->status] ?? ['#F5F5F4', '#78716C'];
            @endphp
            <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 12px; padding: 12px 0; border-bottom: 1px solid #E7E5E4; align-items: center;">
                <div style="min-width: 0;">
                    <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13px; font-weight: 500; color: #0C0A09;">{{ $lead->name ?: 'Sin nombre' }}</div>
                    <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #78716C; margin-top: 2px;">
                        {{ $lead->company ?: '—' }}
                        @if($lead->country) · {{ strtoupper($lead->country->code ?? '') }} @endif
                        · {{ $lead->created_at?->diffForHumans() }}
                    </div>
                </div>
                <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 10.5px; padding: 3px 8px; border-radius: 3px; background: {{ $bg }}; color: {{ $fg }}; font-weight: 500; text-transform: uppercase; letter-spacing: 0.04em;">{{ ucfirst($lead->status ?? 'new') }}</span>
                <div style="font-family: 'Geist Mono',ui-monospace,monospace; font-size: 13px; font-weight: 500; color: #0C0A09; text-align: right; min-width: 64px; font-variant-numeric: tabular-nums;">
                    @if($lead->estimated_value)${{ number_format($lead->estimated_value) }}@else—@endif
                </div>
            </div>
            @empty
            <div style="padding: 28px 0; text-align: center; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13px; color: #78716C;">Sin leads aún</div>
            @endforelse
        </div>
    </div>

    {{-- Activity timeline --}}
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\ContactTimelineWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>

{{-- BOTTOM: Tasks + Ad metrics --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\TaskProgressWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\AdMetricsWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>
