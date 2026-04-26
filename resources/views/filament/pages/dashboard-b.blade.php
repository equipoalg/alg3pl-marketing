{{--
  Dashboard ALG · Variación B — pixel-perfect rewrite to match
  Claude Design bundle (gyZRBvE14Hz7A7CejDDcWw / dashboard-b.jsx)
  Editorial hierarchy with hero + pipeline strip + analytics room.
--}}
@php
    use App\Support\DashboardCharts;

    $totalLeads = $kpis[0]['value'] ?? 0;
    $conversion = $kpis[3]['value'] ?? '0%';
    $conversionDelta = $kpis[3]['delta'] ?? 0;
    $estimated = \App\Models\Lead::query()
        ->when($countryFilter, fn($q) => $q->where('country_id', $countryFilter))
        ->whereIn('status', ['qualified', 'proposal', 'negotiation'])
        ->sum('estimated_value');
    $totalActiveB = array_sum(array_column($pipelineData, 'count'));

    // Traffic data already computed in Dashboard.php
    $totalTrafficB = array_sum($trafficSeries['organic']) + array_sum($trafficSeries['directo']) + array_sum($trafficSeries['referido']);
@endphp

{{-- HERO --}}
<section style="padding:28px 32px 24px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;margin-bottom:16px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="flex:1;min-width:280px;">
            <div style="display:inline-flex;align-items:center;gap:8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:12px;">
                <span style="width:18px;height:1px;background:#CBD5E1;"></span>
                Panorama · ALG{{ $selectedCountry ? ' ' . strtoupper($selectedCountry->code) : '' }} · {{ now()->isoFormat('D MMMM YYYY') }}
            </div>
            <h1 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:32px;font-weight:500;letter-spacing:-0.03em;color:#0F172A;line-height:1.1;max-width:720px;">
                @if($totalLeads > 0)
                    <span style="color:#1E3A8A;">{{ number_format($totalLeads) }}</span> leads activos generaron <span style="color:#1E3A8A;">${{ number_format($estimated) }}</span> en pipeline durante el período.
                @else
                    Tu panel está esperando datos. Configurá un webhook de leads y los números aparecerán acá.
                @endif
            </h1>
            <p style="margin:12px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;color:#64748B;max-width:640px;line-height:1.55;">
                @if($totalLeads > 0)
                    Conversión a {{ $conversion }}{{ $conversionDelta != 0 ? ', ' . ($conversionDelta > 0 ? '↑' : '↓') . ' ' . abs($conversionDelta) . ' pts vs período anterior' : '' }}.
                    @if($selectedCountry) Filtro activo: {{ $selectedCountry->name }}. @endif
                @else
                    Una vez que lleguen los primeros leads, vas a ver acá un titular generado automáticamente con los números clave del período.
                @endif
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <a href="/admin/leads" style="display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid #E2E8F0;background:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v10M5 9l5 4 5-4M3 16h14"/></svg>
                Exportar
            </a>
            <a href="/admin/leads/create" style="display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid #0F172A;background:#0F172A;color:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;text-decoration:none;transition:opacity 150ms ease-out;" onmouseover="this.style.opacity='0.86'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4v12M4 10h12"/></svg>
                Nuevo lead
            </a>
        </div>
    </div>

    {{-- Hero KPI strip + traffic mini chart (5 cols: 4 KPIs + chart) --}}
    @php
        $kpiLinksB = ['leads' => '/admin/leads', 'cuentas' => '/admin/leads', 'campanas' => '/admin/campaigns', 'tasa' => '/admin/leads'];
    @endphp
    <div style="display:grid;grid-template-columns:auto auto auto auto 1fr;gap:0;align-items:stretch;">
        @foreach($kpis as $i => $kpi)
        <a href="{{ $kpiLinksB[$kpi['id']] ?? '/admin' }}" style="padding:0 28px;{{ $i > 0 ? 'border-left:1px solid #E2E8F0;' : '' }}display:flex;flex-direction:column;justify-content:flex-end;gap:4px;text-decoration:none;color:inherit;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.08em;font-weight:500;">{{ $kpi['label'] }}</span>
            <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:30px;font-weight:500;letter-spacing:-0.025em;color:#0F172A;line-height:1;font-variant-numeric:tabular-nums;">{{ is_numeric($kpi['value']) ? number_format($kpi['value']) : $kpi['value'] }}</span>
            @if($kpi['delta'] != 0)
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:{{ $kpi['delta'] > 0 ? '#166534' : '#9F1239' }};font-weight:500;">{{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}%</span>
            @else
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;">{{ $kpi['sub'] }}</span>
            @endif
        </a>
        @endforeach
        <div style="padding:0 0 0 28px;display:flex;flex-direction:column;justify-content:flex-end;min-width:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.08em;font-weight:500;">Tráfico · {{ count($trafficSeries['labels']) }} días</span>
                <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#94A3B8;">{{ number_format($totalTrafficB) }} sesiones</span>
            </div>
            <div style="height:60px;">
                {!! DashboardCharts::multiSeriesSvg(
                    ['organic' => $trafficSeries['organic'], 'directo' => $trafficSeries['directo'], 'referido' => $trafficSeries['referido']],
                    $trafficSeries['labels'],
                    ['#1E3A8A', '#64748B', '#CBD5E1'],
                    500, 60, 'line',
                    ['t' => 4, 'r' => 0, 'b' => 4, 'l' => 0]
                ) !!}
            </div>
        </div>
    </div>
</section>

{{-- PIPELINE STRIP --}}
<section style="padding:22px 24px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;margin-bottom:16px;">
    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
        <div>
            <h2 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Pipeline</h2>
            <p style="margin:4px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">{{ number_format($totalActiveB) }} leads en movimiento · valor estimado ${{ number_format($estimated) }} USD</p>
        </div>
        <a href="/admin/leads" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:5px;border:1px solid #E2E8F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
            Ver detalle
            <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 14L14 6M7 6h7v7"/></svg>
        </a>
    </div>
    <div style="display:grid;grid-template-columns:repeat({{ count($pipelineData) }}, 1fr);gap:8px;">
        @foreach($pipelineData as $stage)
        @php $pct = $totalActiveB > 0 ? round(($stage['count'] / $totalActiveB) * 100) : 0; @endphp
        <a href="/admin/leads?tableFilters[status][value]={{ $stage['id'] }}" style="padding:14px 16px;border:1px solid #E2E8F0;border-radius:6px;background:#FFFFFF;border-top:2px solid {{ $stage['color'] }};display:flex;flex-direction:column;gap:6px;text-decoration:none;color:inherit;transition:all 150ms ease-out;" onmouseover="this.style.background='#F8FAFC';this.style.borderColor='#CBD5E1';this.style.borderTopColor='{{ $stage['color'] }}';" onmouseout="this.style.background='#FFFFFF';this.style.borderColor='#E2E8F0';this.style.borderTopColor='{{ $stage['color'] }}';">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#64748B;font-weight:500;">{{ $stage['label'] }}</span>
                <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:10px;color:#CBD5E1;">{{ $pct }}%</span>
            </div>
            <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:22px;font-weight:500;letter-spacing:-0.02em;color:#0F172A;font-variant-numeric:tabular-nums;">{{ $stage['count'] }}</div>
        </a>
        @endforeach
    </div>
</section>

{{-- ANALYTICS ROOM (Keywords + Fuentes side by side) --}}
<section style="padding:22px 24px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;margin-bottom:16px;">
    <div style="display:grid;grid-template-columns:1.45fr 1fr;gap:32px;">

        {{-- Left: Keywords --}}
        <div>
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Posicionamiento orgánico</h2>
                    <p style="margin:4px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Top keywords · Search Console · período actual</p>
                </div>
                <a href="/admin/search-console-data" style="display:inline-flex;align-items:center;gap:4px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;">
                    Abrir reporte
                    <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 14L14 6M7 6h7v7"/></svg>
                </a>
            </div>
            <div>
                <div style="display:grid;grid-template-columns:1fr 60px 70px 80px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#CBD5E1;text-transform:uppercase;letter-spacing:0.06em;padding:8px 0;border-top:1px solid #334155;border-bottom:1px solid #E2E8F0;">
                    <div>Keyword</div>
                    <div style="text-align:right;">Clicks</div>
                    <div style="text-align:right;">Impr.</div>
                    <div style="text-align:right;">Posición</div>
                </div>
                @forelse($keywords as $i => $k)
                <div style="display:grid;grid-template-columns:1fr 60px 70px 80px;padding:11px 0;{{ $i < $keywords->count() - 1 ? 'border-bottom:1px solid #E2E8F0;' : '' }}font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;align-items:center;">
                    <div style="color:#0F172A;font-weight:500;">{{ $k->kw }}</div>
                    <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#1E3A8A;font-weight:500;font-variant-numeric:tabular-nums;">{{ number_format($k->clicks) }}</div>
                    <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#64748B;font-variant-numeric:tabular-nums;">{{ number_format($k->impr) }}</div>
                    <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#334155;font-variant-numeric:tabular-nums;">{{ number_format($k->pos, 1) }}</div>
                </div>
                @empty
                <div style="padding:32px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Sin datos de Search Console aún.</div>
                @endforelse
            </div>
        </div>

        {{-- Right: Fuentes --}}
        @php
            $maxFuenteB = max(1, max(array_column($fuentes, 'value')));
        @endphp
        <div>
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Fuentes de tráfico</h2>
                    <p style="margin:4px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Distribución · período actual</p>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($fuentes as $f)
                @php $pct = $maxFuenteB > 0 ? ($f['value'] / $maxFuenteB) * 100 : 0; @endphp
                <div style="display:grid;grid-template-columns:84px 1fr 56px;align-items:center;gap:10px;">
                    <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;">{{ $f['label'] }}</div>
                    <div style="position:relative;height:8px;background:#F8FAFC;border-radius:2px;">
                        <div style="position:absolute;inset:0;width:{{ $pct }}%;background:#0F172A;border-radius:2px;"></div>
                    </div>
                    <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:12px;color:#334155;text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($f['value']) }}</div>
                </div>
                @endforeach
            </div>

            {{-- Sub: by country (vertical bars) --}}
            @php
                $byCountry = \App\Models\Lead::query()
                    ->with('country')
                    ->selectRaw('country_id, COUNT(*) as c')
                    ->groupBy('country_id')
                    ->orderByDesc('c')
                    ->limit(6)
                    ->get();
                $maxByCountry = max(1, $byCountry->max('c') ?? 1);
            @endphp
            <div style="margin-top:24px;padding-top:18px;border-top:1px solid #E2E8F0;">
                <h3 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;letter-spacing:-0.01em;">Por país</h3>
                <p style="margin:3px 0 12px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;">Leads — top 6 mercados</p>
                @if($byCountry->count() > 0)
                <div style="display:flex;align-items:flex-end;gap:12px;height:140px;padding:0 4px;">
                    @foreach($byCountry as $i => $cb)
                    @php
                        $h = ($cb->c / $maxByCountry) * 104;
                        $color = $i < 2 ? '#1E3A8A' : '#334155';
                        $opacity = $i < 2 ? '1' : (0.45 - ($i * 0.04));
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#94A3B8;font-variant-numeric:tabular-nums;">{{ $cb->c }}</div>
                        <div style="width:100%;max-width:36px;height:{{ $h }}px;background:{{ $color }};opacity:{{ $opacity }};border-radius:2px 2px 0 0;"></div>
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#94A3B8;">{{ strtoupper($cb->country?->code ?? '?') }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="padding:32px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#94A3B8;">Sin datos por país.</div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- LEADS + CAMPAIGNS --}}
<section style="padding:22px 24px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;margin-bottom:16px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">

        {{-- Recent Leads --}}
        <div>
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Leads recientes</h2>
                    <p style="margin:4px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">{{ $recentLeads->count() }} más recientes</p>
                </div>
                <a href="/admin/leads" style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;">Ver todos →</a>
            </div>
            <div style="border-top:1px solid #334155;">
                @forelse($recentLeads as $i => $lead)
                @php
                    $stageColors = [
                        'won' => ['#ECFDF5', '#166534'],
                        'proposal' => ['#EFF3FB', '#1E3A8A'],
                        'qualified' => ['#EFF3FB', '#1E3A8A'],
                        'negotiation' => ['#EFF3FB', '#1E3A8A'],
                        'contacted' => ['#F8FAFC', '#64748B'],
                        'new' => ['#F8FAFC', '#94A3B8'],
                        'lost' => ['#FEF2F2', '#9F1239'],
                    ];
                    [$bg, $fg] = $stageColors[$lead->status] ?? ['#F8FAFC', '#94A3B8'];
                @endphp
                <a href="/admin/leads/{{ $lead->id }}/edit" style="display:grid;grid-template-columns:1fr auto auto;gap:14px;padding:13px 8px;border-bottom:1px solid #E2E8F0;align-items:center;text-decoration:none;color:inherit;margin:0 -8px;border-radius:4px;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:#0F172A;">{{ $lead->name ?: 'Sin nombre' }}</div>
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;margin-top:2px;">{{ $lead->company ?: '—' }}@if($lead->country) · {{ strtoupper($lead->country->code ?? '') }}@endif · {{ $lead->created_at?->diffForHumans() }}</div>
                    </div>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;padding:3px 8px;border-radius:3px;background:{{ $bg }};color:{{ $fg }};font-weight:500;text-transform:uppercase;letter-spacing:0.04em;">{{ ucfirst($lead->status ?? 'new') }}</span>
                    <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:13px;font-weight:500;color:#0F172A;text-align:right;min-width:64px;font-variant-numeric:tabular-nums;">@if($lead->estimated_value)${{ number_format($lead->estimated_value) }}@else—@endif</div>
                </a>
                @empty
                <div style="padding:28px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:#94A3B8;">Sin leads aún</div>
                @endforelse
            </div>
        </div>

        {{-- Campaigns --}}
        <div>
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Campañas</h2>
                    <p style="margin:4px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">{{ $campaigns->count() }} campañas · ${{ number_format($campaigns->sum(fn($c) => (float) ($c->budget ?? 0))) }} presupuesto</p>
                </div>
                <a href="/admin/campaigns/create" style="display:inline-flex;align-items:center;gap:4px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;">
                    Crear
                    <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4v12M4 10h12"/></svg>
                </a>
            </div>
            <div style="border-top:1px solid #334155;">
                @forelse($campaigns as $i => $c)
                @php
                    $statusColors = ['active' => '#166534', 'paused' => '#94A3B8', 'scheduled' => '#1E3A8A', 'draft' => '#CBD5E1'];
                    $statusColor = $statusColors[$c->status] ?? '#94A3B8';
                    $sent = (int) ($c->sent_count ?? 0);
                    $open = (int) ($c->open_count ?? 0);
                    $click = (int) ($c->click_count ?? 0);
                    $openRate = $sent > 0 ? round(($open / $sent) * 100) : null;
                    $ctr = $sent > 0 ? round(($click / $sent) * 100, 1) : null;
                @endphp
                <a href="/admin/campaigns/{{ $c->id }}/edit" style="padding:13px 8px;border-bottom:1px solid #E2E8F0;display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;text-decoration:none;color:inherit;margin:0 -8px;border-radius:4px;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <div style="min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};"></span>
                            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:#0F172A;">{{ $c->name }}</span>
                        </div>
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;margin-top:4px;display:flex;gap:14px;flex-wrap:wrap;">
                            <span>{{ number_format($sent) }} envíos</span>
                            @if($openRate !== null)<span>Open <span style="font-family:'Geist Mono',ui-monospace,monospace;color:#334155;font-variant-numeric:tabular-nums;">{{ $openRate }}%</span></span>@endif
                            @if($ctr !== null)<span>CTR <span style="font-family:'Geist Mono',ui-monospace,monospace;color:#334155;font-variant-numeric:tabular-nums;">{{ $ctr }}%</span></span>@endif
                        </div>
                    </div>
                    <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:13px;font-weight:500;text-align:right;font-variant-numeric:tabular-nums;">${{ number_format((float) ($c->budget ?? 0)) }}</div>
                </a>
                @empty
                <div style="padding:28px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:#94A3B8;">Sin campañas</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- ACTIVITY + TASKS --}}
<section style="padding:22px 24px 28px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;margin-bottom:16px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">

        {{-- Activity --}}
        <div>
            <h2 style="margin:0 0 14px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Actividad reciente</h2>
            <div style="border-top:1px solid #334155;">
                @forelse($activity as $i => $a)
                <div style="display:grid;grid-template-columns:1fr auto;gap:14px;padding:11px 0;border-bottom:1px solid #E2E8F0;">
                    <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:#334155;line-height:1.5;">
                        <span style="font-weight:500;color:#0F172A;">{{ $a->user?->name ?? 'Sistema' }}</span>
                        {{ $a->description ?: $a->type }}
                        @if($a->lead) en <a href="/admin/leads/{{ $a->lead->id }}/edit" style="color:#1E3A8A;text-decoration:none;">{{ $a->lead->name }}</a> @endif
                    </div>
                    <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#CBD5E1;white-space:nowrap;">{{ $a->created_at?->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE, true) ?? '—' }}</span>
                </div>
                @empty
                <div style="padding:28px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Sin actividad reciente.</div>
                @endforelse
            </div>
        </div>

        {{-- Tasks (next-action from lead activities) --}}
        @php
            $tasks = \App\Models\LeadActivity::query()
                ->with('lead')
                ->whereNotNull('next_action')
                ->where(function($q) { $q->whereNull('next_action_date')->orWhere('next_action_date', '>=', now()); })
                ->latest('next_action_date')
                ->limit(5)
                ->get();
        @endphp
        <div>
            <h2 style="margin:0 0 14px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Tareas y seguimiento</h2>
            <div style="border-top:1px solid #334155;">
                @forelse($tasks as $t)
                @php
                    $due = $t->next_action_date ? \Carbon\Carbon::parse($t->next_action_date) : null;
                    $isOverdue = $due && $due->isPast();
                    $isToday = $due && $due->isToday();
                    $priority = $isOverdue ? 'alta' : ($isToday ? 'media' : 'baja');
                    $prCol = $priority === 'alta' ? ['#FEF2F2', '#9F1239'] : ($priority === 'media' ? ['#FEF3C7', '#92400E'] : ['#F8FAFC', '#94A3B8']);
                @endphp
                <div style="display:grid;grid-template-columns:16px 1fr auto;gap:12px;padding:11px 0;align-items:center;border-bottom:1px solid #E2E8F0;">
                    <span style="width:14px;height:14px;border-radius:3px;border:1.5px solid #CBD5E1;"></span>
                    <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:#0F172A;">
                        {{ $t->next_action }}
                        @if($t->lead) — <a href="/admin/leads/{{ $t->lead->id }}/edit" style="color:#1E3A8A;text-decoration:none;">{{ $t->lead->name }}</a> @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10px;padding:2px 7px;border-radius:3px;background:{{ $prCol[0] }};color:{{ $prCol[1] }};text-transform:uppercase;letter-spacing:0.06em;font-weight:500;">{{ $priority }}</span>
                        @if($due)<span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;">{{ $due->diffForHumans() }}</span>@endif
                    </div>
                </div>
                @empty
                <div style="padding:28px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Sin tareas pendientes.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
