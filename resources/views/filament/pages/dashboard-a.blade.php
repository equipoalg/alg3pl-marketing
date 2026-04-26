{{--
  Dashboard ALG · Variación A — pixel-perfect rewrite to match
  Claude Design bundle (gyZRBvE14Hz7A7CejDDcWw / dashboard-a.jsx)
  All cards are custom blade — no Filament widgets used here.
--}}
@php
    use App\Support\DashboardCharts;
    $kpiLinks = [
        'leads'    => '/admin/leads',
        'cuentas'  => '/admin/leads',
        'campanas' => '/admin/campaigns',
        'tasa'     => '/admin/leads',
    ];
@endphp

{{-- KPI HAIRLINE GRID (4 cols, exact match dashboard-a.jsx KpiGrid) --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#E2E8F0;border:1px solid #E2E8F0;border-radius:8px;overflow:hidden;margin-bottom:16px;">
    @foreach($kpis as $kpi)
    @php
        $sparkData = $kpiSparklines[$kpi['id']] ?? [];
        $sparkColor = in_array($kpi['id'], ['leads', 'tasa']) ? '#2563EB' : '#64748B';
    @endphp
    <a href="{{ $kpiLinks[$kpi['id']] ?? '/admin' }}" style="background:#FFFFFF;padding:18px 20px 16px;display:flex;flex-direction:column;gap:10px;min-height:124px;text-decoration:none;color:inherit;cursor:pointer;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.06em;font-weight:500;">{{ $kpi['label'] }}</span>
            <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7"/><path d="M10 9v5M10 6.5v0.5"/></svg>
        </div>
        <div style="display:flex;align-items:baseline;gap:10px;">
            <span style="font-family:'Geist Mono',ui-monospace,'JetBrains Mono','SF Mono',monospace;font-size:30px;font-weight:500;letter-spacing:-0.025em;color:#0F172A;line-height:1;font-variant-numeric:tabular-nums;">{{ is_numeric($kpi['value']) ? number_format($kpi['value']) : $kpi['value'] }}</span>
            @if($kpi['delta'] != 0)
            <span style="font-size:11.5px;font-weight:500;display:inline-flex;align-items:center;gap:2px;color:{{ $kpi['delta'] > 0 ? '#166534' : '#9F1239' }};">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    @if($kpi['delta'] > 0) <path d="M10 16V4M5 9l5-5 5 5"/> @else <path d="M10 4v12M5 11l5 5 5-5"/> @endif
                </svg>
                <span style="font-family:'Geist Mono',ui-monospace,monospace;font-variant-numeric:tabular-nums;">{{ abs($kpi['delta']) }}%</span>
            </span>
            @endif
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;">{{ $kpi['sub'] }}</span>
            <div style="opacity:0.85;">{!! DashboardCharts::sparklineSvg($sparkData, $sparkColor, 72, 24) !!}</div>
        </div>
    </a>
    @endforeach
</div>

{{-- ROW 1: TrafficCard (1.55fr) + FuentesCard (1fr) --}}
<div style="display:grid;grid-template-columns:1.55fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- TrafficCard --}}
    @php
        $totalOrganic = array_sum($trafficSeries['organic']);
        $totalDirecto = array_sum($trafficSeries['directo']);
        $totalReferido = array_sum($trafficSeries['referido']);
        $totalTraffic = $totalOrganic + $totalDirecto + $totalReferido;
    @endphp
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 20px 4px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Tráfico orgánico</div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;margin-top:4px;">Sesiones por canal — últimos {{ count($trafficSeries['labels']) }} días · {{ number_format($totalTraffic) }} totales</div>
            </div>
            <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                @foreach([['Orgánico', '#2563EB', $totalOrganic], ['Directo', '#64748B', $totalDirecto], ['Referido', '#CBD5E1', $totalReferido]] as [$lbl, $clr, $val])
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="width:8px;height:8px;border-radius:2px;background:{{ $clr }};"></span>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#64748B;">{{ $lbl }}</span>
                    <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11.5px;color:#334155;font-weight:500;font-variant-numeric:tabular-nums;">{{ number_format($val) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div style="padding:8px 16px 16px;flex:1;">
            {!! DashboardCharts::multiSeriesSvg(
                ['organic' => $trafficSeries['organic'], 'directo' => $trafficSeries['directo'], 'referido' => $trafficSeries['referido']],
                $trafficSeries['labels'],
                ['#2563EB', '#64748B', '#CBD5E1'],
                680, 220
            ) !!}
        </div>
    </div>

    {{-- FuentesCard --}}
    @php
        $maxFuente = max(1, max(array_column($fuentes, 'value')));
        $totalFuentes = array_sum(array_column($fuentes, 'value'));
    @endphp
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px 20px;display:flex;flex-direction:column;">
        <div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Fuentes de tráfico</div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;margin-top:4px;">Distribución y tendencia · período actual</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:18px;">
            @foreach($fuentes as $f)
            @php $pct = $maxFuente > 0 ? ($f['value'] / $maxFuente) * 100 : 0; @endphp
            <div style="display:grid;grid-template-columns:84px 1fr 56px;align-items:center;gap:10px;">
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;">{{ $f['label'] }}</div>
                <div style="position:relative;height:8px;background:#F8FAFC;border-radius:2px;">
                    <div style="position:absolute;inset:0;width:{{ $pct }}%;background:#334155;border-radius:2px;"></div>
                </div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:12px;color:#334155;text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($f['value']) }}</div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;">
            <div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#94A3B8;">Total sesiones</div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:18px;font-weight:500;margin-top:2px;font-variant-numeric:tabular-nums;">{{ number_format($totalFuentes) }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#94A3B8;">Δ vs anterior</div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:#166534;margin-top:2px;">—</div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 2: KeywordsCard + PipelineCard --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- KeywordsCard --}}
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px 20px;display:flex;flex-direction:column;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
            <div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Top keywords</div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;margin-top:4px;">Google Search Console · período actual</div>
            </div>
            <a href="/admin/search-console-data" style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:5px;border:1px solid #E2E8F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;text-decoration:none;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="9" r="5"/><path d="M13 13l4 4"/></svg>
                Ver todos
            </a>
        </div>
        <div style="margin-top:10px;">
            <div style="display:grid;grid-template-columns:1fr 70px 70px 70px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#CBD5E1;text-transform:uppercase;letter-spacing:0.06em;padding:0 0 8px;border-bottom:1px solid #E2E8F0;">
                <div>Keyword</div>
                <div style="text-align:right;">Clicks</div>
                <div style="text-align:right;">Impr.</div>
                <div style="text-align:right;">Pos.</div>
            </div>
            @forelse($keywords as $i => $k)
            <div style="display:grid;grid-template-columns:1fr 70px 70px 70px;padding:8px 0;{{ $i < $keywords->count() - 1 ? 'border-bottom:1px solid #E2E8F0;' : '' }}font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;align-items:center;">
                <div style="color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $k->kw }}</div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#2563EB;font-weight:500;font-variant-numeric:tabular-nums;">{{ number_format($k->clicks) }}</div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#64748B;font-variant-numeric:tabular-nums;">{{ number_format($k->impr) }}</div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#334155;font-variant-numeric:tabular-nums;">{{ number_format($k->pos, 1) }}</div>
            </div>
            @empty
            <div style="padding:32px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Sin datos de Search Console aún. Corre <code style="font-family:'Geist Mono',ui-monospace,monospace;background:#F8FAFC;padding:2px 6px;border-radius:3px;">php artisan analytics:sync --gsc</code></div>
            @endforelse
        </div>
    </div>

    {{-- PipelineCard --}}
    @php
        $maxStage = max(1, max(array_column($pipelineData, 'count')));
        $totalActive = array_sum(array_column($pipelineData, 'count'));
        $estimated = \App\Models\Lead::query()
            ->when($countryFilter, fn($q) => $q->where('country_id', $countryFilter))
            ->whereIn('status', ['qualified', 'proposal', 'negotiation'])
            ->sum('estimated_value');
        $wonCount = collect($pipelineData)->firstWhere('id', 'won')['count'] ?? 0;
        $lostCount = collect($pipelineData)->firstWhere('id', 'lost')['count'] ?? 0;
        $closedCount = $wonCount + $lostCount;
        $winRate = $closedCount > 0 ? round(($wonCount / $closedCount) * 100) : 0;
    @endphp
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px 20px;display:flex;flex-direction:column;">
        <div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Pipeline de leads</div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;margin-top:4px;">{{ number_format($totalActive) }} leads activos · valor estimado ${{ number_format($estimated) }} USD</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
            @foreach($pipelineData as $stage)
            @php
                $pct = $maxStage > 0 ? ($stage['count'] / $maxStage) * 100 : 0;
                $opacity = in_array($stage['id'], ['new']) ? '0.5' : (in_array($stage['id'], ['contacted']) ? '0.7' : '1');
            @endphp
            <a href="/admin/leads?tableFilters[status][value]={{ $stage['id'] }}" style="display:grid;grid-template-columns:92px 1fr 40px;align-items:center;gap:12px;text-decoration:none;color:inherit;padding:2px 4px;border-radius:3px;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;">{{ $stage['label'] }}</div>
                <div style="position:relative;height:22px;background:#F8FAFC;border-radius:3px;">
                    <div style="position:absolute;inset:0;width:{{ $pct }}%;background:{{ $stage['color'] }};border-radius:3px;opacity:{{ $opacity }};"></div>
                </div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:12px;text-align:right;color:#0F172A;font-variant-numeric:tabular-nums;">{{ $stage['count'] }}</div>
            </a>
            @endforeach
        </div>
        <div style="margin-top:18px;padding-top:14px;border-top:1px solid #E2E8F0;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            @php
                $stats = [
                    ['Conversión', ($kpis[3]['value'] ?? '0%'), ($kpis[3]['sub'] ?? '')],
                    ['Pipeline activo', number_format($totalActive), 'leads en movimiento'],
                    ['Win rate', $winRate . '%', 'ganados / cerrados'],
                ];
            @endphp
            @foreach($stats as [$lbl, $val, $note])
            <div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#94A3B8;text-transform:uppercase;letter-spacing:0.05em;">{{ $lbl }}</div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:17px;font-weight:500;margin-top:4px;color:#0F172A;font-variant-numeric:tabular-nums;">{{ $val }}</div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#94A3B8;margin-top:2px;">{{ $note }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ROW 3: LeadsRecentCard (1.4fr) + ActivityCard (1fr) --}}
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- LeadsRecentCard --}}
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px 20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            <div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Leads recientes</div>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;margin-top:4px;">{{ $recentLeads->count() ? "Últimas {$recentLeads->count()} entradas" : 'Sin leads aún' }}</div>
            </div>
            <a href="/admin/leads" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:5px;border:1px solid #E2E8F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                Ver todos
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 14L14 6M7 6h7v7"/></svg>
            </a>
        </div>
        <div style="margin-top:6px;">
            @forelse($recentLeads as $i => $lead)
            @php
                $stageColors = [
                    'won' => ['#ECFDF5', '#166534', 'Ganado'],
                    'proposal' => ['#EFF3FB', '#1E3A8A', 'Propuesta'],
                    'qualified' => ['#EFF3FB', '#1E3A8A', 'Calificado'],
                    'negotiation' => ['#EFF3FB', '#1E3A8A', 'Negociación'],
                    'contacted' => ['#F8FAFC', '#64748B', 'Contactado'],
                    'new' => ['#F8FAFC', '#94A3B8', 'Nuevo'],
                    'lost' => ['#FEF2F2', '#9F1239', 'Perdido'],
                ];
                [$bg, $fg, $stageLabel] = $stageColors[$lead->status] ?? ['#F8FAFC', '#94A3B8', ucfirst($lead->status ?? 'Nuevo')];
                $parts = explode(' ', trim($lead->name ?? ''));
                $initials = strtoupper(substr($parts[0] ?? 'N', 0, 1) . substr(end($parts) ?: '', 0, 1));
            @endphp
            <a href="/admin/leads/{{ $lead->id }}/edit" style="display:grid;grid-template-columns:32px 1fr auto auto auto;align-items:center;gap:12px;padding:10px 0;{{ $i < $recentLeads->count() - 1 ? 'border-bottom:1px solid #E2E8F0;' : '' }}text-decoration:none;color:inherit;transition:background 150ms ease-out;margin:0 -8px;padding-left:8px;padding-right:8px;border-radius:4px;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                <div style="width:30px;height:30px;border-radius:50%;background:#F8FAFC;display:grid;place-items:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;color:#334155;">{{ $initials ?: 'NN' }}</div>
                <div style="min-width:0;">
                    <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:#0F172A;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->name ?: 'Sin nombre' }}</div>
                    <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:#94A3B8;margin-top:1px;">{{ $lead->company ?: '—' }}@if($lead->country) · {{ strtoupper($lead->country->code ?? '') }}@endif</div>
                </div>
                <div style="font-family:'Geist Mono',ui-monospace,monospace;font-size:12.5px;font-weight:500;color:#334155;font-variant-numeric:tabular-nums;">@if($lead->estimated_value)${{ number_format($lead->estimated_value) }}@else—@endif</div>
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;padding:3px 8px;border-radius:3px;background:{{ $bg }};color:{{ $fg }};font-weight:500;">{{ $stageLabel }}</span>
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:#CBD5E1;">{{ $lead->created_at?->diffForHumans() ?: '—' }}</span>
            </a>
            @empty
            <div style="padding:32px 0;text-align:center;color:#94A3B8;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;">
                <div style="font-size:13px;margin-bottom:4px;">Sin leads en este período.</div>
                <div style="font-size:11.5px;color:#CBD5E1;">Cuando lleguen vía formulario o webhook, aparecerán acá.</div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ActivityCard --}}
    <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px 20px;">
        <div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Actividad reciente</div>
        </div>
        <div style="margin-top:6px;">
            @forelse($activity as $i => $a)
            <div style="display:grid;grid-template-columns:auto 1fr auto;align-items:flex-start;gap:10px;padding:10px 0;{{ $i < $activity->count() - 1 ? 'border-bottom:1px solid #E2E8F0;' : '' }}">
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $a->user ? '#1E3A8A' : '#CBD5E1' }};margin-top:6px;"></span>
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:#334155;line-height:1.45;">
                    <span style="font-weight:500;color:#0F172A;">{{ $a->user?->name ?? 'Sistema' }}</span>
                    {{ $a->description ?: $a->type }}
                    @if($a->lead)
                        @ <a href="/admin/leads/{{ $a->lead->id }}/edit" style="color:#1E3A8A;text-decoration:none;">{{ $a->lead->name }}</a>
                    @endif
                </div>
                <span style="font-family:'Geist Mono',ui-monospace,monospace;font-size:11px;color:#CBD5E1;white-space:nowrap;">{{ $a->created_at?->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE, true) ?? '—' }}</span>
            </div>
            @empty
            <div style="padding:32px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Sin actividad reciente.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ROW 4: CampaignsCard (full) --}}
<div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px 20px;margin-bottom:16px;">
    @php $totalSpend = $campaigns->sum(fn($c) => (float) ($c->budget ?? 0)); @endphp
    <div style="display:flex;align-items:flex-start;justify-content:space-between;">
        <div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:#0F172A;letter-spacing:-0.01em;">Campañas activas</div>
            <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;margin-top:4px;">{{ $campaigns->count() }} campañas · ${{ number_format($totalSpend) }} presupuesto total</div>
        </div>
        <a href="/admin/campaigns/create" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:5px;border:1px solid #E2E8F0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#64748B;text-decoration:none;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
            <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4v12M4 10h12"/></svg>
            Crear campaña
        </a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 90px 70px 70px 90px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:#CBD5E1;text-transform:uppercase;letter-spacing:0.06em;padding:8px 0;border-bottom:1px solid #E2E8F0;margin-top:10px;">
        <div>Campaña</div>
        <div style="text-align:right;">Enviados</div>
        <div style="text-align:right;">Open</div>
        <div style="text-align:right;">CTR</div>
        <div style="text-align:right;">Presupuesto</div>
    </div>
    @forelse($campaigns as $i => $c)
    @php
        $statusColors = ['active' => '#166534', 'paused' => '#94A3B8', 'scheduled' => '#1E3A8A', 'draft' => '#CBD5E1', 'completed' => '#64748B'];
        $statusColor = $statusColors[$c->status] ?? '#94A3B8';
        $statusLabels = ['active' => 'Activa', 'paused' => 'Pausada', 'scheduled' => 'Programada', 'draft' => 'Borrador', 'completed' => 'Completada'];
        $sent = (int) ($c->sent_count ?? 0);
        $open = (int) ($c->open_count ?? 0);
        $click = (int) ($c->click_count ?? 0);
        $openRate = $sent > 0 ? round(($open / $sent) * 100) : null;
        $ctr = $sent > 0 ? round(($click / $sent) * 100, 1) : null;
    @endphp
    <a href="/admin/campaigns/{{ $c->id }}/edit" style="display:grid;grid-template-columns:1fr 90px 70px 70px 90px;align-items:center;padding:10px 0;{{ $i < $campaigns->count() - 1 ? 'border-bottom:1px solid #E2E8F0;' : '' }}font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;text-decoration:none;color:inherit;margin:0 -8px;padding-left:8px;padding-right:8px;border-radius:4px;transition:background 150ms ease-out;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
        <div style="display:flex;flex-direction:column;gap:3px;min-width:0;">
            <span style="font-weight:500;color:#0F172A;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c->name }}</span>
            <span style="font-size:10.5px;font-weight:500;color:{{ $statusColor }};display:inline-flex;align-items:center;gap:4px;width:fit-content;">
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};"></span>
                {{ $statusLabels[$c->status] ?? ucfirst($c->status) }}
            </span>
        </div>
        <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#334155;font-variant-numeric:tabular-nums;">{{ number_format($sent) }}</div>
        <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#334155;font-variant-numeric:tabular-nums;">{{ $openRate !== null ? $openRate . '%' : '—' }}</div>
        <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#334155;font-variant-numeric:tabular-nums;">{{ $ctr !== null ? $ctr . '%' : '—' }}</div>
        <div style="font-family:'Geist Mono',ui-monospace,monospace;text-align:right;color:#0F172A;font-weight:500;font-variant-numeric:tabular-nums;">${{ number_format((float) ($c->budget ?? 0)) }}</div>
    </a>
    @empty
    <div style="padding:32px 0;text-align:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:#94A3B8;">Sin campañas creadas. <a href="/admin/campaigns/create" style="color:#1E3A8A;">Crear la primera</a></div>
    @endforelse
</div>
