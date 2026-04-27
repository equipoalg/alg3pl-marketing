{{--
    Variant A — "Classic refined" — 1:1 port of dashboard-a.jsx
    Layout: Sidebar (224px) | Topbar + PageHeader + KpiGrid + 4 grid rows + CampaignsCard
--}}
@php
    use App\Support\DashboardCharts;
    /** @var array $data */
    /** @var string $chartType */
    $kpis           = $data['kpis'];
    $trafficSeries  = $data['trafficSeries'];
    $trafficLabels  = $data['trafficLabels'];
    $fuentes        = $data['fuentes'];
    $keywords       = $data['keywords'];
    $pipelineStages = $data['pipelineStages'];
    $recentLeads    = $data['recentLeads'];
    $campaigns      = $data['campaigns'];
    $activity       = $data['activity'];

    $totalsTraffic = [
        'organic'  => array_sum($trafficSeries['organic']),
        'directo'  => array_sum($trafficSeries['directo']),
        'referido' => array_sum($trafficSeries['referido']),
    ];
    $totalTraffic = array_sum($totalsTraffic);

    $maxFuente = max(1, max(array_column($fuentes, 'value')));
    $totalFuentes = array_sum(array_column($fuentes, 'value'));

    $maxStage = max(1, max(array_column($pipelineStages, 'count')));

    $colorMap = [
        'ink-5' => 'var(--ink-5)', 'ink-4' => 'var(--ink-4)',
        'accent' => 'var(--accent)', 'pos' => 'var(--pos)', 'neg' => 'var(--neg)',
    ];

    $stageColor = function ($st) {
        return match ($st) {
            'Ganado'                => ['var(--pos-soft)', 'var(--pos)'],
            'Propuesta', 'Calificado' => ['var(--accent-soft)', 'var(--accent)'],
            'Contactado'            => ['var(--surface-2)', 'var(--ink-3)'],
            default                 => ['var(--surface-2)', 'var(--ink-4)'],
        };
    };

    $btnGhost = 'display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:6px;border:1px solid var(--border);background:var(--surface);font-size:12px;color:var(--ink-3);cursor:pointer;font-family:var(--font-sans);';
    $btnIcon = 'width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--surface);display:grid;place-items:center;cursor:pointer;';
    $btnPrimary = 'display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid var(--ink-1);background:var(--ink-1);font-size:12.5px;color:white;font-weight:500;cursor:pointer;font-family:var(--font-sans);';
@endphp
        {{-- ═══════════════════ PAGE HEADER ═══════════════════ --}}
        @php $rangeLabels = ['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'ytd' => 'Año']; @endphp
        <div style="padding:0 0 8px;display:flex;flex-direction:column;gap:16px;">
            <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:24px;">
                <div>
                    <h1 style="margin:0;font-size:22px;font-weight:600;letter-spacing:-0.02em;color:var(--ink-1);">Panorama global</h1>
                    <p style="margin:6px 0 0;font-size:13px;color:var(--ink-4);max-width:560px;">
                        Vista resumen del CRM y desempeño de marketing — últimos {{ strtolower($rangeLabels[$timeRange ?? '30d']) }}, sincronizado hace 4 min.
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="display:flex;border:1px solid var(--border);border-radius:6px;padding:2px;background:var(--surface);">
                        @foreach($rangeLabels as $val => $lbl)
                            @php $isActive = ($timeRange ?? '30d') === $val; @endphp
                            <a href="?variant={{ $variant ?? 'a' }}&range={{ $val }}" style="text-decoration:none;padding:5px 11px;border-radius:4px;font-size:12px;font-weight:{{ $isActive ? '500' : '400' }};color:{{ $isActive ? 'var(--ink-1)' : 'var(--ink-4)' }};background:{{ $isActive ? 'var(--surface-2)' : 'transparent' }};">{{ $lbl }}</a>
                        @endforeach
                    </div>
                    <button style="{{ $btnGhost }}">@include('alg-dashboard.icon', ['name' => 'filter-h', 'size' => 13, 'stroke' => 'var(--ink-3)']) Filtros</button>
                    <button style="{{ $btnGhost }}">@include('alg-dashboard.icon', ['name' => 'download', 'size' => 13, 'stroke' => 'var(--ink-3)']) Exportar</button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════ KPI GRID (4 cells) ═══════════════════ --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border);border:1px solid var(--border);border-radius:8px;overflow:hidden;margin:0 28px;">
            @foreach($kpis as $k)
                @php
                    $sparkColor = $k['sparkColor'] === 'accent' ? 'var(--accent-2)' : 'var(--ink-3)';
                    $valueDisplay = is_numeric($k['value']) ? number_format($k['value']) : $k['value'];
                @endphp
                <div style="background:var(--surface);padding:18px 20px 16px;display:flex;flex-direction:column;gap:10px;min-height:124px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:11.5px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.06em;font-weight:500;">{{ $k['label'] }}</span>
                        @include('alg-dashboard.icon', ['name' => 'info', 'size' => 12, 'stroke' => 'var(--ink-5)'])
                    </div>
                    <div style="display:flex;align-items:baseline;gap:10px;">
                        <span class="num" style="font-size:30px;font-weight:500;letter-spacing:-0.025em;color:var(--ink-1);">{{ $valueDisplay }}</span>
                        @if($k['delta'] != 0)
                            @php $deltaColor = $k['delta'] > 0 ? 'var(--pos)' : 'var(--neg)'; @endphp
                            <span style="font-size:11.5px;font-weight:500;display:inline-flex;align-items:center;gap:2px;color:{{ $deltaColor }};">
                                @include('alg-dashboard.icon', ['name' => $k['delta'] > 0 ? 'arrow-up' : 'arrow-down', 'size' => 11, 'stroke' => 'currentColor'])
                                <span class="num">{{ abs($k['delta']) }}%</span>
                            </span>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
                        <span style="font-size:11.5px;color:var(--ink-4);">{{ $k['sub'] }}</span>
                        {!! DashboardCharts::sparklineSvg($k['series'], $sparkColor, 72, 24, true) !!}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ═══════════════════ BODY GRID ═══════════════════ --}}
        <div style="padding:24px 0;display:flex;flex-direction:column;gap:20px;">

            {{-- Row 1: Traffic (1.55fr) + Fuentes (1fr) --}}
            <div style="display:grid;grid-template-columns:1.55fr 1fr;gap:16px;">
                {{-- TrafficCard --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;display:flex;flex-direction:column;overflow:hidden;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:16px 20px 4px;gap:16px;flex-wrap:wrap;">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Tráfico orgánico</div>
                            <div style="font-size:12px;color:var(--ink-4);margin-top:4px;">Sesiones por canal — últimos 90 días · {{ number_format($totalTraffic) }} totales</div>
                        </div>
                        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                            @foreach([['Orgánico','var(--accent-2)','organic'],['Directo','var(--ink-3)','directo'],['Referido','var(--ink-5)','referido']] as [$lbl,$clr,$key])
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="width:8px;height:8px;border-radius:2px;background:{{ $clr }};"></span>
                                    <span style="font-size:11.5px;color:var(--ink-3);">{{ $lbl }}</span>
                                    <span class="num tnum" style="font-size:11.5px;color:var(--ink-2);font-weight:500;">{{ number_format($totalsTraffic[$key]) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div style="padding:8px 20px 20px;flex:1;">
                        {!! DashboardCharts::multiSeriesSvg(
                            ['organic'=>$trafficSeries['organic'],'directo'=>$trafficSeries['directo'],'referido'=>$trafficSeries['referido']],
                            $trafficLabels,
                            ['#2563EB','#57534E','#A8A29E'],
                            680, 220, $chartType ?? 'line'
                        ) !!}
                    </div>
                </div>

                {{-- FuentesCard --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px 20px;display:flex;flex-direction:column;">
                    <div>
                        <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Fuentes de tráfico</div>
                        <div style="font-size:12px;color:var(--ink-4);margin-top:4px;">Distribución y tendencia 30 días</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:18px;">
                        @foreach($fuentes as $f)
                            @php $pct = ($f['value'] / $maxFuente) * 100; @endphp
                            <div style="display:grid;grid-template-columns:84px 1fr 56px;align-items:center;gap:10px;">
                                <div style="font-size:12px;color:var(--ink-3);">{{ $f['label'] }}</div>
                                <div style="position:relative;height:8px;background:var(--surface-2);border-radius:2px;">
                                    <div style="position:absolute;inset:0;width:{{ $pct }}%;background:var(--ink-2);border-radius:2px;"></div>
                                </div>
                                <div class="num tnum" style="font-size:12px;color:var(--ink-2);text-align:right;">{{ number_format($f['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);display:flex;justify-content:space-between;">
                        <div>
                            <div style="font-size:11px;color:var(--ink-4);">Total sesiones</div>
                            <div class="num" style="font-size:18px;font-weight:500;margin-top:2px;">{{ number_format($totalFuentes) }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px;color:var(--ink-4);">Δ vs anterior</div>
                            <div style="font-size:14px;font-weight:500;color:var(--pos);margin-top:2px;">+11.3%</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Keywords + Pipeline --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                {{-- KeywordsCard --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px 20px;display:flex;flex-direction:column;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Top keywords</div>
                            <div style="font-size:12px;color:var(--ink-4);margin-top:4px;">Google Search Console · últimos 30 días</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;padding:5px 10px;border-radius:5px;border:1px solid var(--border);font-size:12px;color:var(--ink-4);">
                            @include('alg-dashboard.icon', ['name' => 'search', 'size' => 12, 'stroke' => 'var(--ink-5)']) Buscar
                        </div>
                    </div>
                    <div style="margin-top:10px;">
                        <div style="display:grid;grid-template-columns:1fr 70px 70px 70px;font-size:10.5px;color:var(--ink-5);text-transform:uppercase;letter-spacing:0.06em;padding:0 0 8px;border-bottom:1px solid var(--border);">
                            <div>Keyword</div>
                            <div style="text-align:right;">Clicks</div>
                            <div style="text-align:right;">Impr.</div>
                            <div style="text-align:right;">Pos.</div>
                        </div>
                        @foreach(array_slice($keywords, 0, 8) as $i => $k)
                            <div style="display:grid;grid-template-columns:1fr 70px 70px 70px;padding:8px 0;{{ $i < 7 ? 'border-bottom:1px solid var(--border);' : '' }}font-size:12px;align-items:center;">
                                <div style="color:var(--ink-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $k['kw'] }}</div>
                                <div class="num tnum" style="text-align:right;color:var(--accent-2);font-weight:500;">{{ $k['clicks'] }}</div>
                                <div class="num tnum" style="text-align:right;color:var(--ink-3);">{{ number_format($k['impr']) }}</div>
                                <div style="text-align:right;display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                                    <span class="num tnum" style="color:var(--ink-2);">{{ number_format($k['pos'], 1) }}</span>
                                    <span style="font-size:10px;color:{{ $k['delta'] > 0 ? 'var(--pos)' : ($k['delta'] < 0 ? 'var(--neg)' : 'var(--ink-5)') }};">{{ $k['delta'] > 0 ? '↑' : ($k['delta'] < 0 ? '↓' : '·') }}{{ number_format(abs($k['delta']), 1) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- PipelineCard --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px 20px;display:flex;flex-direction:column;">
                    <div>
                        <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Pipeline de leads</div>
                        <div style="font-size:12px;color:var(--ink-4);margin-top:4px;">398 leads activos · valor estimado $1.24M USD</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
                        @foreach($pipelineStages as $s)
                            @php
                                $pct = ($s['count'] / $maxStage) * 100;
                                $clr = $colorMap[$s['color']] ?? 'var(--ink-3)';
                                $opacity = $s['color'] === 'ink-5' ? 0.5 : ($s['color'] === 'ink-4' ? 0.7 : 1);
                            @endphp
                            <div style="display:grid;grid-template-columns:92px 1fr 40px;align-items:center;gap:12px;">
                                <div style="font-size:12px;color:var(--ink-3);">{{ $s['label'] }}</div>
                                <div style="position:relative;height:22px;background:var(--surface-2);border-radius:3px;">
                                    <div style="position:absolute;inset:0;width:{{ $pct }}%;background:{{ $clr }};border-radius:3px;opacity:{{ $opacity }};"></div>
                                </div>
                                <div class="num tnum" style="font-size:12px;text-align:right;color:var(--ink-1);">{{ $s['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border);display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                        @foreach([['Conversión','4.8%','+0.6 pts'],['Velocidad media','11d','lead → propuesta'],['Win rate','31%','ganados / cerrados']] as [$lbl,$val,$note])
                            <div>
                                <div style="font-size:10.5px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.05em;">{{ $lbl }}</div>
                                <div class="num" style="font-size:17px;font-weight:500;margin-top:4px;color:var(--ink-1);">{{ $val }}</div>
                                <div style="font-size:11px;color:var(--ink-4);margin-top:2px;">{{ $note }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Row 3: Recent Leads (1.4fr) + Activity (1fr) --}}
            <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">
                {{-- LeadsRecentCard --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px 20px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                        <div>
                            <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Leads recientes</div>
                            <div style="font-size:12px;color:var(--ink-4);margin-top:4px;">Últimas 6 horas · 18 nuevos hoy</div>
                        </div>
                        <button style="{{ $btnGhost }}">Ver todos @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</button>
                    </div>
                    <div style="margin-top:4px;">
                        @foreach($recentLeads as $i => $l)
                            @php [$bg, $fg] = $stageColor($l['stage']); @endphp
                            <div style="display:grid;grid-template-columns:32px 1fr auto auto auto;align-items:center;gap:12px;padding:10px 0;{{ $i < count($recentLeads) - 1 ? 'border-bottom:1px solid var(--border);' : '' }}">
                                <div style="width:30px;height:30px;border-radius:50%;background:var(--surface-2);display:grid;place-items:center;font-size:11px;font-weight:600;color:var(--ink-2);">{{ $l['initials'] }}</div>
                                <div style="min-width:0;">
                                    <div style="font-size:13px;font-weight:500;color:var(--ink-1);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $l['name'] }}</div>
                                    <div style="font-size:11.5px;color:var(--ink-4);margin-top:1px;">{{ $l['company'] }} · {{ $l['country'] }}</div>
                                </div>
                                <div class="num tnum" style="font-size:12.5px;font-weight:500;color:var(--ink-2);">{{ $l['value'] }}</div>
                                <span style="font-size:11px;padding:3px 8px;border-radius:3px;background:{{ $bg }};color:{{ $fg }};font-weight:500;">{{ $l['stage'] }}</span>
                                <span style="font-size:11px;color:var(--ink-5);">{{ $l['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ActivityCard --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px 20px;">
                    <div>
                        <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Actividad reciente</div>
                    </div>
                    <div style="margin-top:4px;">
                        @foreach($activity as $i => $a)
                            <div style="display:grid;grid-template-columns:auto 1fr auto;align-items:flex-start;gap:10px;padding:10px 0;{{ $i < count($activity) - 1 ? 'border-bottom:1px solid var(--border);' : '' }}">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $a['actor'] === 'Sistema' ? 'var(--ink-5)' : 'var(--accent)' }};margin-top:6px;"></span>
                                <div style="font-size:12.5px;color:var(--ink-2);line-height:1.45;">
                                    <span style="font-weight:500;">{{ $a['actor'] }}</span> {{ $a['action'] }}
                                </div>
                                <span class="num" style="font-size:11px;color:var(--ink-5);">{{ $a['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Row 4: Campaigns (full) --}}
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px 20px;">
                <div>
                    <div style="font-size:13.5px;font-weight:600;color:var(--ink-1);letter-spacing:-0.01em;">Campañas activas</div>
                    <div style="font-size:12px;color:var(--ink-4);margin-top:4px;">5 campañas · $8,010 invertidos este mes</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 80px 70px 70px 80px;font-size:10.5px;color:var(--ink-5);text-transform:uppercase;letter-spacing:0.06em;padding:8px 0;border-bottom:1px solid var(--border);margin-top:4px;">
                    <div>Campaña</div>
                    <div style="text-align:right;">Enviados</div>
                    <div style="text-align:right;">Open</div>
                    <div style="text-align:right;">CTR</div>
                    <div style="text-align:right;">Inversión</div>
                </div>
                @foreach($campaigns as $i => $c)
                    @php
                        $statusColor = match($c['status']) {
                            'Activa'    => 'var(--pos)',
                            'Pausada'   => 'var(--ink-4)',
                            'Programada'=> 'var(--accent)',
                            default     => 'var(--ink-4)',
                        };
                    @endphp
                    <div style="display:grid;grid-template-columns:1fr 80px 70px 70px 80px;align-items:center;padding:10px 0;{{ $i < count($campaigns) - 1 ? 'border-bottom:1px solid var(--border);' : '' }}font-size:12px;">
                        <div style="display:flex;flex-direction:column;gap:3px;min-width:0;">
                            <span style="font-weight:500;color:var(--ink-1);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c['name'] }}</span>
                            <span style="font-size:10.5px;font-weight:500;color:{{ $statusColor }};display:inline-flex;align-items:center;gap:4px;width:fit-content;">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};"></span>{{ $c['status'] }}
                            </span>
                        </div>
                        <div class="num tnum" style="text-align:right;color:var(--ink-2);">{{ number_format($c['sent']) }}</div>
                        <div class="num tnum" style="text-align:right;color:var(--ink-2);">{{ $c['open'] !== null ? round($c['open'] * 100) . '%' : '—' }}</div>
                        <div class="num tnum" style="text-align:right;color:var(--ink-2);">{{ $c['click'] !== null ? number_format($c['click'] * 100, 1) . '%' : '—' }}</div>
                        <div class="num tnum" style="text-align:right;color:var(--ink-1);font-weight:500;">{{ $c['spend'] }}</div>
                    </div>
                @endforeach
            </div>

            <div style="height:8px;"></div>
        </div>
