{{--
  Dashboard ALG · Variación A — "Estructura clásica refinada"
  Layout: KPI hairline grid (4 cols) → Traffic+Country → Keywords+Pipeline →
  Activity+Tasks → AdMetrics. Reuses existing Filament widgets.
--}}

{{-- KPI HAIRLINE GRID (4 cols) — clickable cards --}}
@php
    $kpiLinks = [
        'leads'    => '/admin/leads',
        'cuentas'  => '/admin/leads',
        'campanas' => '/admin/campaigns',
        'tasa'     => '/admin/leads',
    ];
@endphp
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: #E7E5E4; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
    @foreach($kpis as $kpi)
    <a href="{{ $kpiLinks[$kpi['id']] ?? '/admin' }}" style="background: #FFFFFF; padding: 18px 20px 16px; display: flex; flex-direction: column; gap: 10px; min-height: 124px; text-decoration: none; color: inherit; cursor: pointer; transition: background 150ms ease-out;" onmouseover="this.style.background='#FAFAF9'" onmouseout="this.style.background='#FFFFFF'">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #78716C; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 500;">{{ $kpi['label'] }}</span>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#A8A29E" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
        </div>
        <div style="display: flex; align-items: baseline; gap: 10px;">
            <span style="font-family: 'Geist Mono',ui-monospace,'JetBrains Mono','SF Mono',monospace; font-size: 30px; font-weight: 500; letter-spacing: -0.025em; color: #0C0A09; line-height: 1; font-variant-numeric: tabular-nums;">
                {{ is_numeric($kpi['value']) ? number_format($kpi['value']) : $kpi['value'] }}
            </span>
            @if($kpi['delta'] != 0)
            <span style="font-size: 11.5px; font-weight: 500; display: inline-flex; align-items: center; gap: 2px; color: {{ $kpi['delta'] > 0 ? '#166534' : '#9F1239' }};">
                {{ $kpi['delta'] > 0 ? '↑' : '↓' }}
                <span style="font-family: 'Geist Mono',ui-monospace,monospace; font-variant-numeric: tabular-nums;">{{ abs($kpi['delta']) }}%</span>
            </span>
            @endif
        </div>
        <div style="margin-top: auto; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #78716C;">
            {{ $kpi['sub'] }}
        </div>
    </a>
    @endforeach
</div>

{{-- ROW 1: Traffic chart (wide) + Country breakdown (narrow) --}}
<div style="display: grid; grid-template-columns: 1.55fr 1fr; gap: 16px; margin-bottom: 16px;">
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\TrafficTrendWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\LeadsByCountryWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>

{{-- ROW 2: Top keywords + Sales pipeline --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\TopKeywordsWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\SalesPipelineWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>

{{-- ROW 3: Recent leads (custom, wider) + Activity timeline --}}
<div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; margin-bottom: 16px;">
    {{-- Recent leads card --}}
    <div style="background: #FFFFFF; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden;">
        <div style="padding: 16px 20px 12px; display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13.5px; font-weight: 600; color: #0C0A09; letter-spacing: -0.01em;">Leads recientes</div>
                <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; color: #78716C; margin-top: 4px;">
                    @if($recentLeads->count() > 0)
                        Últimas entradas · {{ $recentLeads->count() }} mostrados
                    @else
                        Sin leads aún · los nuevos aparecerán aquí
                    @endif
                </div>
            </div>
            <a href="/admin/leads" style="display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border: 1px solid #E7E5E4; border-radius: 5px; background: #FFFFFF; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 12px; color: #57534E; text-decoration: none; transition: background 150ms ease-out;" onmouseover="this.style.background='#F5F5F4'" onmouseout="this.style.background='#FFFFFF'">
                Ver todos
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
            </a>
        </div>
        <div style="padding: 0 20px 16px;">
            @forelse($recentLeads as $lead)
            @php
                $stageColors = [
                    'won'        => ['#ECFDF5', '#166534', 'Ganado'],
                    'proposal'   => ['#EFF3FB', '#1E3A8A', 'Propuesta'],
                    'qualified'  => ['#EFF3FB', '#1E3A8A', 'Calificado'],
                    'contacted'  => ['#F5F5F4', '#57534E', 'Contactado'],
                    'new'        => ['#F5F5F4', '#78716C', 'Nuevo'],
                    'lost'       => ['#FEF2F2', '#9F1239', 'Perdido'],
                    'negotiation'=> ['#EFF3FB', '#1E3A8A', 'Negociación'],
                ];
                [$bg, $fg, $stageLabel] = $stageColors[$lead->status] ?? ['#F5F5F4', '#78716C', ucfirst($lead->status ?? 'Nuevo')];
                $initials = strtoupper(substr($lead->name ?? 'NN', 0, 1) . substr(strrchr($lead->name ?? '', ' ') ?: '', 1, 1));
            @endphp
            <a href="/admin/leads/{{ $lead->id }}/edit" style="display: grid; grid-template-columns: 32px 1fr auto auto auto; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #E7E5E4; text-decoration: none; color: inherit; transition: background 150ms ease-out; margin: 0 -8px; padding-left: 8px; padding-right: 8px; border-radius: 4px;" onmouseover="this.style.background='#FAFAF9'" onmouseout="this.style.background='transparent'">
                <div style="width: 30px; height: 30px; border-radius: 50%; background: #F5F5F4; display: grid; place-items: center; font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11px; font-weight: 600; color: #292524;">{{ $initials ?: 'NN' }}</div>
                <div style="min-width: 0;">
                    <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13px; font-weight: 500; color: #0C0A09; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $lead->name ?: 'Sin nombre' }}</div>
                    <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #78716C; margin-top: 1px;">
                        {{ $lead->company ?: '—' }}
                        @if($lead->country) · {{ $lead->country->code ? strtoupper($lead->country->code) : '' }} @endif
                    </div>
                </div>
                <div style="font-family: 'Geist Mono',ui-monospace,monospace; font-size: 12.5px; font-weight: 500; color: #292524; font-variant-numeric: tabular-nums;">
                    @if($lead->estimated_value)${{ number_format($lead->estimated_value) }}@else—@endif
                </div>
                <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11px; padding: 3px 8px; border-radius: 3px; background: {{ $bg }}; color: {{ $fg }}; font-weight: 500;">{{ $stageLabel }}</span>
                <span style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11px; color: #A8A29E;">{{ $lead->created_at?->diffForHumans() ?: '—' }}</span>
            </a>
            @empty
            <div style="padding: 32px 0; text-align: center; color: #78716C;">
                <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 13px; margin-bottom: 4px;">Sin leads en este período.</div>
                <div style="font-family: 'Geist',ui-sans-serif,system-ui,sans-serif; font-size: 11.5px; color: #A8A29E;">Cuando lleguen vía formulario o webhook, aparecerán acá.</div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Activity timeline (Filament widget) --}}
    <x-filament-widgets::widgets
        :widgets="[\App\Filament\Widgets\ContactTimelineWidget::class]"
        :columns="1"
        :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
    />
</div>

{{-- ROW 4: Tasks + Ad metrics --}}
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

{{-- Smart alerts (kept as bottom bar — can be hidden if 0) --}}
<x-filament-widgets::widgets
    :widgets="[\App\Filament\Widgets\SmartAlertsWidget::class]"
    :columns="1"
    :data="['countryFilter' => $countryFilter, 'timeRange' => $timeRange]"
/>
