{{--
    Variant B — "Editorial / remixed" — 1:1 port of dashboard-b.jsx
    Layout: SidebarB (56px dark) | HeroB · PipelineStripB · AnalyticsRoomB · LeadsAndCampaignsB · ActivityB
--}}
@php
    use App\Support\DashboardCharts;
    /** @var array $data */
    /** @var string $chartType */
    $kpis           = $data['kpis'];
    $trafficSeries  = $data['trafficSeries'];
    $fuentes        = $data['fuentes'];
    $keywords       = $data['keywords'];
    $pipelineStages = $data['pipelineStages'];
    $recentLeads    = $data['recentLeads'];
    $campaigns      = $data['campaigns'];
    $activity       = $data['activity'];
    $tasks          = $data['tasks'];
    $byCountry      = $data['byCountry'];

    // 90-day index labels (just numbers per JSX)
    $heroLabels = array_map(fn($i) => (string)$i, range(1, 90));

    $totalPipelineCount = array_sum(array_column($pipelineStages, 'count'));
    $totalTraffic = array_sum($trafficSeries['organic']) + array_sum($trafficSeries['directo']) + array_sum($trafficSeries['referido']);

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
    $btnPrimary = 'display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid var(--ink-1);background:var(--ink-1);font-size:12.5px;color:white;font-weight:500;cursor:pointer;font-family:var(--font-sans);';

    $maxFuente = max(1, max(array_column($fuentes, 'value')));
    $maxByCountry = max(1, max(array_column($byCountry, 'value')));
@endphp
        {{-- ═══════════════════ HERO ═══════════════════ --}}
        <section style="padding:0 0 28px;border-bottom:1px solid var(--border);background:var(--surface);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:24px;flex-wrap:wrap;">
                <div style="flex:1;min-width:280px;">
                    <div style="display:inline-flex;align-items:center;gap:8px;font-size:11px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:10px;">
                        <span style="width:18px;height:1px;background:var(--ink-5);"></span>
                        Panorama · ALG SV · 26 abril 2026
                    </div>
                    <h1 style="margin:0;font-size:32px;font-weight:500;letter-spacing:-0.03em;color:var(--ink-1);line-height:1.1;max-width:720px;">
                        <span style="color:var(--accent);">2,847</span> leads activos generaron <span style="color:var(--accent);">$1.24M</span> en pipeline durante los últimos 30 días.
                    </h1>
                    <p style="margin:12px 0 0;font-size:13.5px;color:var(--ink-3);max-width:640px;">
                        La conversión subió 0.6 puntos a 4.8%, impulsada por la campaña <em style="font-style:normal;color:var(--ink-1);font-weight:500;">"Reactivación leads 2025"</em> y mejor posicionamiento de <em style="font-style:normal;color:var(--ink-1);font-weight:500;">alg el salvador</em> en SERP.
                    </p>
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <button style="{{ $btnGhost }}">@include('alg-dashboard.icon', ['name' => 'calendar', 'size' => 13, 'stroke' => 'var(--ink-3)']) Últimos 30 días</button>
                    <button style="{{ $btnGhost }}">@include('alg-dashboard.icon', ['name' => 'download', 'size' => 13, 'stroke' => 'var(--ink-3)'])</button>
                    <button style="{{ $btnPrimary }}">@include('alg-dashboard.icon', ['name' => 'plus', 'size' => 14, 'stroke' => 'white']) Nuevo lead</button>
                </div>
            </div>

            {{-- 4 big editorial KPIs + traffic strip --}}
            <div style="display:grid;grid-template-columns:auto auto auto auto 1fr;gap:0;align-items:stretch;">
                @foreach([
                    ['Leads totales',    '2,847', '+12.4%', 'pos'],
                    ['Cuentas activas',  '142',   '+3.6%',  'pos'],
                    ['Campañas activas', '8',     '3 prog.','ink'],
                    ['Conversión',       '4.8%',  '+0.6pts','pos'],
                ] as [$lbl, $val, $delta, $deltaColor])
                    <div style="padding:0 28px;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:flex-end;gap:4px;">
                        <span style="font-size:10.5px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.08em;font-weight:500;">{{ $lbl }}</span>
                        <span class="num" style="font-size:30px;font-weight:500;letter-spacing:-0.025em;color:var(--ink-1);line-height:1;">{{ $val }}</span>
                        <span style="font-size:11.5px;color:{{ $deltaColor === 'pos' ? 'var(--pos)' : 'var(--ink-4)' }};font-weight:500;">{{ $delta }}</span>
                    </div>
                @endforeach
                <div style="padding:0 0 0 28px;display:flex;flex-direction:column;justify-content:flex-end;min-width:0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:10.5px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.08em;font-weight:500;">Tráfico orgánico · 90 días</span>
                        <span style="font-size:11px;color:var(--ink-4);">{{ number_format($totalTraffic) }} sesiones</span>
                    </div>
                    <div style="height:60px;">
                        {!! DashboardCharts::multiSeriesSvg(
                            ['organic'=>$trafficSeries['organic'],'directo'=>$trafficSeries['directo'],'referido'=>$trafficSeries['referido']],
                            $heroLabels,
                            ['#1E3A8A','#57534E','#A8A29E'],
                            500, 60, $chartType ?? 'line',
                            ['t' => 4, 'r' => 0, 'b' => 4, 'l' => 0]
                        ) !!}
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════════════════ PIPELINE STRIP ═══════════════════ --}}
        <section style="padding:24px 0;border-bottom:1px solid var(--border);">
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Pipeline</h2>
                    <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">{{ $totalPipelineCount }} leads en movimiento · valor estimado $1.24M USD</p>
                </div>
                <button style="{{ $btnGhost }}">Ver detalle @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</button>
            </div>
            <div style="display:grid;grid-template-columns:repeat({{ count($pipelineStages) }},1fr);gap:8px;">
                @foreach($pipelineStages as $s)
                    @php $clr = $colorMap[$s['color']] ?? 'var(--ink-3)'; @endphp
                    <div style="padding:14px 16px;border:1px solid var(--border);border-radius:6px;background:var(--surface);border-top:2px solid {{ $clr }};display:flex;flex-direction:column;gap:6px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-size:11.5px;color:var(--ink-3);font-weight:500;">{{ $s['label'] }}</span>
                            <span style="font-size:10px;color:var(--ink-5);">{{ round(($s['count'] / $totalPipelineCount) * 100) }}%</span>
                        </div>
                        <div class="num" style="font-size:22px;font-weight:500;letter-spacing:-0.02em;">{{ $s['count'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ═══════════════════ ANALYTICS ROOM ═══════════════════ --}}
        <section style="padding:24px 0;border-bottom:1px solid var(--border);">
            <div style="display:grid;grid-template-columns:1.45fr 1fr;gap:32px;">
                {{-- Left: keywords --}}
                <div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Posicionamiento orgánico</h2>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">Top keywords · Search Console · 30 días</p>
                        </div>
                        <button style="{{ $btnGhost }}">Abrir reporte @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</button>
                    </div>
                    <div>
                        <div style="display:grid;grid-template-columns:1fr 60px 70px 80px;font-size:10.5px;color:var(--ink-5);text-transform:uppercase;letter-spacing:0.06em;padding:8px 0;border-top:1px solid var(--ink-2);border-bottom:1px solid var(--border);">
                            <div>Keyword</div>
                            <div style="text-align:right;">Clicks</div>
                            <div style="text-align:right;">Impr.</div>
                            <div style="text-align:right;">Posición</div>
                        </div>
                        @foreach($keywords as $i => $k)
                            <div style="display:grid;grid-template-columns:1fr 60px 70px 80px;padding:11px 0;{{ $i < count($keywords) - 1 ? 'border-bottom:1px solid var(--border);' : '' }}font-size:13px;align-items:center;">
                                <div style="color:var(--ink-1);font-weight:500;">{{ $k['kw'] }}</div>
                                <div class="num tnum" style="text-align:right;color:var(--accent);font-weight:500;">{{ $k['clicks'] }}</div>
                                <div class="num tnum" style="text-align:right;color:var(--ink-3);">{{ number_format($k['impr']) }}</div>
                                <div style="text-align:right;display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                    <span class="num tnum" style="color:var(--ink-2);">{{ number_format($k['pos'], 1) }}</span>
                                    <span style="font-size:10.5px;color:{{ $k['delta'] > 0 ? 'var(--pos)' : ($k['delta'] < 0 ? 'var(--neg)' : 'var(--ink-5)') }};font-weight:500;">{{ $k['delta'] > 0 ? '↑' : ($k['delta'] < 0 ? '↓' : '·') }}{{ number_format(abs($k['delta']), 1) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: fuentes + by-country --}}
                <div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Fuentes de tráfico</h2>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">Distribución · 30 días</p>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach($fuentes as $f)
                            @php $pct = ($f['value'] / $maxFuente) * 100; @endphp
                            <div style="display:grid;grid-template-columns:84px 1fr 56px;align-items:center;gap:10px;">
                                <div style="font-size:12px;color:var(--ink-3);">{{ $f['label'] }}</div>
                                <div style="position:relative;height:8px;background:var(--surface-2);border-radius:2px;">
                                    <div style="position:absolute;inset:0;width:{{ $pct }}%;background:var(--ink-1);border-radius:2px;"></div>
                                </div>
                                <div class="num tnum" style="font-size:12px;color:var(--ink-2);text-align:right;">{{ number_format($f['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--border);">
                        <h3 style="margin:0;font-size:13px;font-weight:600;letter-spacing:-0.01em;">Por país</h3>
                        <p style="margin:3px 0 12px;font-size:11.5px;color:var(--ink-4);">Sesiones — top 6 mercados</p>
                        <div style="display:flex;align-items:flex-end;gap:12px;height:120px;padding:0 4px;">
                            @foreach($byCountry as $i => $c)
                                @php
                                    $h = ($c['value'] / $maxByCountry) * (120 - 36);
                                    $bg = $i < 2 ? 'var(--accent)' : 'var(--ink-2)';
                                    $opacity = $i < 2 ? 1 : (0.45 - ($i * 0.04));
                                @endphp
                                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
                                    <div class="num tnum" style="font-size:11px;color:var(--ink-4);">{{ $c['value'] >= 1000 ? number_format($c['value']/1000, 1) . 'k' : $c['value'] }}</div>
                                    <div style="width:100%;max-width:36px;height:{{ $h }}px;background:{{ $bg }};opacity:{{ $opacity }};border-radius:2px 2px 0 0;"></div>
                                    <div style="font-size:11px;color:var(--ink-4);">{{ $c['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════════════════ LEADS + CAMPAIGNS ═══════════════════ --}}
        <section style="padding:24px 0;border-bottom:1px solid var(--border);">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
                {{-- Leads --}}
                <div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Leads recientes</h2>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">18 nuevos hoy · sincronizado hace 4 min</p>
                        </div>
                        <button style="{{ $btnGhost }}">Ver todos @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</button>
                    </div>
                    <div style="border-top:1px solid var(--ink-2);">
                        @foreach($recentLeads as $l)
                            @php [$bg, $fg] = $stageColor($l['stage']); @endphp
                            <div style="display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:14px;padding:13px 0;border-bottom:1px solid var(--border);">
                                <div style="min-width:0;">
                                    <div style="font-size:13px;font-weight:500;color:var(--ink-1);">{{ $l['name'] }}</div>
                                    <div style="font-size:11.5px;color:var(--ink-4);margin-top:2px;">{{ $l['company'] }} · {{ $l['country'] }} · {{ $l['time'] }}</div>
                                </div>
                                <span style="font-size:10.5px;padding:3px 8px;border-radius:3px;background:{{ $bg }};color:{{ $fg }};font-weight:500;text-transform:uppercase;letter-spacing:0.04em;">{{ $l['stage'] }}</span>
                                <div class="num tnum" style="font-size:13px;font-weight:500;color:var(--ink-1);min-width:64px;text-align:right;">{{ $l['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Campaigns --}}
                <div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Campañas</h2>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">5 campañas · $8,010 invertidos</p>
                        </div>
                        <button style="{{ $btnGhost }}">Crear @include('alg-dashboard.icon', ['name' => 'plus', 'size' => 12, 'stroke' => 'var(--ink-4)'])</button>
                    </div>
                    <div style="border-top:1px solid var(--ink-2);">
                        @foreach($campaigns as $c)
                            @php
                                $statusColor = match($c['status']) {
                                    'Activa'    => 'var(--pos)',
                                    'Pausada'   => 'var(--ink-4)',
                                    'Programada'=> 'var(--accent)',
                                    default     => 'var(--ink-4)',
                                };
                            @endphp
                            <div style="padding:13px 0;border-bottom:1px solid var(--border);display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;">
                                <div style="min-width:0;">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};"></span>
                                        <span style="font-size:13px;font-weight:500;color:var(--ink-1);">{{ $c['name'] }}</span>
                                    </div>
                                    <div style="font-size:11.5px;color:var(--ink-4);margin-top:4px;display:flex;gap:14px;">
                                        <span>{{ number_format($c['sent']) }} envíos</span>
                                        @if($c['open'] !== null)<span>Open <span class="num tnum" style="color:var(--ink-2);">{{ round($c['open']*100) }}%</span></span>@endif
                                        @if($c['click'] !== null)<span>CTR <span class="num tnum" style="color:var(--ink-2);">{{ number_format($c['click']*100, 1) }}%</span></span>@endif
                                    </div>
                                </div>
                                <div class="num tnum" style="font-size:13px;font-weight:500;text-align:right;">{{ $c['spend'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════════════════ ACTIVITY + TASKS ═══════════════════ --}}
        <section style="padding:24px 0 40px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
                {{-- Activity --}}
                <div>
                    <h2 style="margin:0 0 14px;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Actividad reciente</h2>
                    <div style="border-top:1px solid var(--ink-2);">
                        @foreach($activity as $a)
                            <div style="display:grid;grid-template-columns:1fr auto;gap:14px;padding:11px 0;border-bottom:1px solid var(--border);">
                                <div style="font-size:12.5px;color:var(--ink-2);line-height:1.5;">
                                    <span style="font-weight:500;color:var(--ink-1);">{{ $a['actor'] }}</span> {{ $a['action'] }}
                                </div>
                                <span class="num" style="font-size:11px;color:var(--ink-5);">{{ $a['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tasks --}}
                <div>
                    <h2 style="margin:0 0 14px;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Tareas y seguimiento</h2>
                    <div style="border-top:1px solid var(--ink-2);">
                        @foreach($tasks as $t)
                            @php
                                $prioBg = match($t['priority']) { 'alta' => 'var(--neg-soft)', 'media' => 'var(--warn-soft)', default => 'var(--surface-2)' };
                                $prioFg = match($t['priority']) { 'alta' => 'var(--neg)',      'media' => 'var(--warn)',      default => 'var(--ink-4)' };
                            @endphp
                            <div style="display:grid;grid-template-columns:16px 1fr auto;gap:12px;padding:11px 0;align-items:center;border-bottom:1px solid var(--border);">
                                <span style="width:14px;height:14px;border-radius:3px;border:1.5px solid var(--ink-5);"></span>
                                <div style="font-size:13px;color:var(--ink-1);">{{ $t['title'] }}</div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="font-size:10px;padding:2px 7px;border-radius:3px;background:{{ $prioBg }};color:{{ $prioFg }};text-transform:uppercase;letter-spacing:0.06em;font-weight:500;">{{ $t['priority'] }}</span>
                                    <span style="font-size:11.5px;color:var(--ink-4);">{{ $t['due'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
