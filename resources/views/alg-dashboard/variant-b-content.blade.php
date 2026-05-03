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

    // Defensive max() — array_column on an empty array returns [] and max([]) is a
    // PHP 8 ValueError. Falls back to [0] so we always have at least one numeric.
    $fuenteValues   = array_column($fuentes, 'value') ?: [0];
    $countryValues  = array_column($byCountry, 'value') ?: [0];
    $maxFuente      = max(1, max($fuenteValues));
    $maxByCountry   = max(1, max($countryValues));

    /* ───────── Real data wiring for the hero ───────── */
    $byId = collect($kpis)->keyBy('id');
    $kpiLeads      = $byId->get('leads',    ['value' => 0,    'delta' => 0, 'sub' => '']);
    $kpiCuentas    = $byId->get('cuentas',  ['value' => 0,    'delta' => 0, 'sub' => '']);
    $kpiCampanas   = $byId->get('campanas', ['value' => 0,    'delta' => 0, 'sub' => '']);
    // DashboardData::kpis() returns id 'tasa' for "Tasa de conversión" — accept both keys.
    $kpiConversion = $byId->get('tasa', $byId->get('conversion', ['value' => '0%', 'delta' => 0, 'sub' => '']));

    // Convert "4.8%" → numeric for count-up
    $convNumeric = (float) str_replace('%', '', (string) ($kpiConversion['value'] ?? 0));

    // Headline numbers (real)
    $heroTotalLeads = (int) ($kpiLeads['value'] ?? 0);

    // Country label for the overline
    $heroCountryCode = session('country_filter')
        ? strtoupper(\App\Models\Country::find((int) session('country_filter'))?->code ?? 'GLOBAL')
        : 'GLOBAL';
    $heroDate = now()->translatedFormat('d F Y');

    // Range label (matches $timeRange from controller)
    $rangeLabels = ['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'ytd' => 'Año'];
    $rangeLabel  = $rangeLabels[$timeRange ?? '30d'] ?? '30 días';

    $deltaPositive = (float) ($kpiLeads['delta'] ?? 0) >= 0;

    /* ───────── Pipeline summary ───────── */
    // Sum estimated_value of leads in active stages (won + open) for the headline.
    $pipelineUsd = 0;
    try {
        $countryId = session('country_filter') ? (int) session('country_filter') : null;
        $pipelineUsd = (float) \App\Models\Lead::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereNotIn('status', ['lost'])
            ->sum('estimated_value');
    } catch (\Throwable $e) { /* schema mismatch — silently ignore */ }
    $pipelineUsdLabel = $pipelineUsd >= 1_000_000
        ? '$' . number_format($pipelineUsd / 1_000_000, 2) . 'M'
        : ($pipelineUsd >= 1_000
            ? '$' . number_format($pipelineUsd / 1_000, 0) . 'k'
            : '$' . number_format($pipelineUsd, 0));

    /* ───────── Recent leads + campaigns headline counts ───────── */
    $newLeadsToday = 0;
    try {
        $countryId = session('country_filter') ? (int) session('country_filter') : null;
        $newLeadsToday = \App\Models\Lead::query()
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->whereDate('created_at', today())
            ->count();
    } catch (\Throwable $e) {}

    $campaignsCount = count($campaigns ?? []);
    $campaignsSpend = 0.0;
    foreach (($campaigns ?? []) as $c) {
        $raw = (string) ($c['spend'] ?? '0');
        $campaignsSpend += (float) preg_replace('/[^0-9.\-]/', '', $raw);
    }
    $campaignsSpendLabel = '$' . number_format($campaignsSpend, 0);
@endphp
        {{-- ═══════════════════ HERO ═══════════════════ --}}
        <section style="padding:0 0 28px;border-bottom:1px solid var(--border);background:var(--surface);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:24px;flex-wrap:wrap;">
                <div style="flex:1;min-width:280px;">
                    <div style="display:inline-flex;align-items:center;gap:8px;font-size:11px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:10px;">
                        <span style="width:18px;height:1px;background:var(--ink-5);"></span>
                        Panorama · ALG {{ $heroCountryCode }} · {{ $heroDate }}
                    </div>
                    <h1 style="margin:0;font-size:32px;font-weight:500;letter-spacing:-0.03em;color:var(--ink-1);line-height:1.1;max-width:720px;">
                        @if($heroTotalLeads === 0)
                            Sin leads en los últimos {{ $rangeLabel }}. <span style="color:var(--ink-4);">Conectá Fluent Forms o cargá leads para ver el panorama.</span>
                        @else
                            <a href="/admin/leads" style="text-decoration:none;color:var(--accent);border-bottom:2px solid transparent;transition:border-color 150ms var(--alg-ease-out);" onmouseover="this.style.borderBottomColor='var(--accent)'" onmouseout="this.style.borderBottomColor='transparent'" title="Ver bandeja de entrada">
                                <span class="num" data-count-to="{{ $heroTotalLeads }}">{{ number_format($heroTotalLeads) }}</span>
                            </a> {{ $heroTotalLeads === 1 ? 'lead' : 'leads' }} {{ $heroTotalLeads === 1 ? 'captado' : 'captados' }} durante los últimos {{ $rangeLabel }}.
                        @endif
                    </h1>
                    @if($heroTotalLeads > 0)
                        <p style="margin:12px 0 0;font-size:13.5px;color:var(--ink-3);max-width:640px;">
                            @if($kpiLeads['delta'] !== 0)
                                Los leads {{ $deltaPositive ? 'subieron' : 'bajaron' }}
                                <span style="color:{{ $deltaPositive ? 'var(--pos)' : 'var(--neg)' }};font-weight:500;">{{ abs($kpiLeads['delta']) }}%</span>
                                {{ $kpiLeads['sub'] ?? '' }}.
                            @else
                                {{ $kpiLeads['sub'] ?? 'Período actual sin variaciones registradas.' }}
                            @endif
                            La conversión está en
                            <em style="font-style:normal;color:var(--ink-1);font-weight:500;">{{ $kpiConversion['value'] ?? '0%' }}</em>{{ ($kpiConversion['sub'] ?? null) ? ' — ' . $kpiConversion['sub'] : '' }}.
                        </p>
                    @endif
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;align-items:flex-start;">
                    {{-- Period range picker — pushes ?range=7d|30d|90d|ytd to the dashboard --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                        <button type="button"
                                @click="open = !open"
                                style="{{ $btnGhost }}">
                            @include('alg-dashboard.icon', ['name' => 'calendar', 'size' => 13, 'stroke' => 'var(--ink-3)'])
                            {{ $rangeLabel }}
                            <svg width="9" height="9" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 8l5 5 5-5"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition.opacity
                             style="position:absolute;top:calc(100% + 4px);right:0;min-width:170px;background:var(--surface);border:1px solid var(--border);border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,0.10);padding:4px;z-index:30;">
                            @foreach(['7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'ytd' => 'Año en curso'] as $key => $lbl)
                                @php $isCurrent = ($timeRange ?? '30d') === $key; @endphp
                                <a href="?range={{ $key }}{{ ($variant ?? 'b') !== 'b' ? '&variant=' . $variant : '' }}"
                                   style="display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:4px;text-decoration:none;color:var(--ink-2);font-size:12.5px;{{ $isCurrent ? 'background:var(--surface-2);font-weight:500;color:var(--ink-1);' : '' }}">
                                    <span>{{ $lbl }}</span>
                                    @if($isCurrent)
                                        <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="var(--accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 10l3 3 7-7"/></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <a href="/admin/leads/create" style="{{ $btnPrimary }};text-decoration:none;">@include('alg-dashboard.icon', ['name' => 'plus', 'size' => 14, 'stroke' => 'white']) Nuevo lead</a>
                </div>
            </div>

            {{-- 4 big editorial KPIs (real data) + traffic strip --}}
            <div style="display:grid;grid-template-columns:auto auto auto auto 1fr;gap:0;align-items:stretch;">
                @php
                    $tiles = [
                        ['Leads totales',    (int) ($kpiLeads['value'] ?? 0),      0, '',  $kpiLeads['delta']      ?? 0, $kpiLeads['sub']      ?? null, '/admin/kanban',    'Ver pipeline completo en Kanban'],
                        ['Cuentas activas',  (int) ($kpiCuentas['value'] ?? 0),    0, '',  $kpiCuentas['delta']    ?? 0, $kpiCuentas['sub']    ?? null, '/admin/clients',   'Ver cuentas'],
                        ['Campañas activas', (int) ($kpiCampanas['value'] ?? 0),   0, '',  $kpiCampanas['delta']   ?? 0, $kpiCampanas['sub']   ?? null, '/admin/campaigns', 'Ver campañas'],
                        ['Conversión',       $convNumeric,                         1, '%', $kpiConversion['delta'] ?? 0, $kpiConversion['sub'] ?? null, '/admin/conversion', 'Ver análisis de conversión'],
                    ];
                @endphp
                @foreach($tiles as [$lbl, $countTo, $decimals, $suffix, $delta, $sub, $href, $tip])
                    <a href="{{ $href }}"
                       title="{{ $tip }}"
                       class="alg-kpi-tile"
                       style="text-decoration:none;color:inherit;padding:0 28px;border-right:1px solid var(--border);display:flex;flex-direction:column;justify-content:flex-end;gap:4px;cursor:pointer;transition:background-color 150ms var(--alg-ease-out);">
                        <span style="font-size:10.5px;color:var(--ink-4);text-transform:uppercase;letter-spacing:0.08em;font-weight:500;">{{ $lbl }}</span>
                        <span class="num"
                              data-count-to="{{ $countTo }}"
                              data-count-decimals="{{ $decimals }}"
                              data-count-suffix="{{ $suffix }}"
                              style="font-size:30px;font-weight:500;letter-spacing:-0.025em;color:var(--ink-1);line-height:1;">0{{ $suffix }}</span>
                        @if((float) $delta !== 0.0)
                            <span style="font-size:11.5px;color:{{ (float) $delta >= 0 ? 'var(--pos)' : 'var(--neg)' }};font-weight:500;">
                                {{ (float) $delta >= 0 ? '▴' : '▾' }} {{ abs((float) $delta) }}%
                            </span>
                        @elseif($sub)
                            <span style="font-size:11.5px;color:var(--ink-4);font-weight:500;">{{ $sub }}</span>
                        @endif
                    </a>
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
                    <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;"><a href="/admin/kanban" style="text-decoration:none;color:inherit;" title="Abrir Kanban">Pipeline</a></h2>
                    <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">{{ $totalPipelineCount }} {{ $totalPipelineCount === 1 ? 'lead' : 'leads' }} en movimiento{{ $pipelineUsd > 0 ? ' · valor estimado ' . $pipelineUsdLabel . ' USD' : '' }}</p>
                </div>
                <a href="/admin/kanban" style="{{ $btnGhost }};text-decoration:none;">Ver detalle @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</a>
            </div>
            <div style="display:grid;grid-template-columns:repeat({{ count($pipelineStages) }},1fr);gap:8px;">
                @foreach($pipelineStages as $s)
                    @php
                        $clr = $colorMap[$s['color']] ?? 'var(--ink-3)';
                        // Each stage links to the inbox filtered by status. Inbox supports ?status=<id>.
                        $stageHref = '/admin/leads?status=' . urlencode($s['id']);
                    @endphp
                    <a href="{{ $stageHref }}"
                       title="Ver leads en {{ $s['label'] }}"
                       class="alg-hover-lift"
                       style="text-decoration:none;color:inherit;padding:14px 16px;border:1px solid var(--border);border-radius:6px;background:var(--surface);border-top:2px solid {{ $clr }};display:flex;flex-direction:column;gap:6px;cursor:pointer;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-size:11.5px;color:var(--ink-3);font-weight:500;">{{ $s['label'] }}</span>
                            <span style="font-size:10px;color:var(--ink-5);">{{ $totalPipelineCount > 0 ? round(($s['count'] / $totalPipelineCount) * 100) : 0 }}%</span>
                        </div>
                        <div class="num" data-count-to="{{ $s['count'] }}" style="font-size:22px;font-weight:500;letter-spacing:-0.02em;">0</div>
                    </a>
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
                        <a href="/admin/search-console" style="{{ $btnGhost }};text-decoration:none;">Abrir reporte @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</a>
                    </div>
                    <div>
                        <div style="display:grid;grid-template-columns:1fr 60px 70px 80px;font-size:10.5px;color:var(--ink-5);text-transform:uppercase;letter-spacing:0.06em;padding:8px 0;border-top:1px solid var(--ink-2);border-bottom:1px solid var(--border);">
                            <div>Keyword</div>
                            <div style="text-align:right;">Clicks</div>
                            <div style="text-align:right;">Impr.</div>
                            <div style="text-align:right;">Posición</div>
                        </div>
                        @forelse($keywords as $i => $k)
                            <a href="/admin/search-console?kw={{ urlencode($k['kw']) }}"
                               class="alg-row-link"
                               title="Ver desempeño de '{{ $k['kw'] }}' en Search Console"
                               style="display:grid;grid-template-columns:1fr 60px 70px 80px;padding:11px 12px;margin:0 -12px;{{ $i < count($keywords) - 1 ? 'border-bottom:1px solid var(--border);' : '' }}font-size:13px;align-items:center;text-decoration:none;color:inherit;">
                                <div style="color:var(--ink-1);font-weight:500;">{{ $k['kw'] }}</div>
                                <div class="num tnum" style="text-align:right;color:var(--accent);font-weight:500;">{{ $k['clicks'] }}</div>
                                <div class="num tnum" style="text-align:right;color:var(--ink-3);">{{ number_format($k['impr']) }}</div>
                                <div style="text-align:right;display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                    <span class="num tnum" style="color:var(--ink-2);">{{ number_format($k['pos'], 1) }}</span>
                                    <span style="font-size:10.5px;color:{{ $k['delta'] > 0 ? 'var(--pos)' : ($k['delta'] < 0 ? 'var(--neg)' : 'var(--ink-5)') }};font-weight:500;">{{ $k['delta'] > 0 ? '↑' : ($k['delta'] < 0 ? '↓' : '·') }}{{ number_format(abs($k['delta']), 1) }}</span>
                                </div>
                            </a>
                        @empty
                            <div style="padding:32px 0;text-align:center;font-size:12.5px;color:var(--ink-4);">
                                Sin datos de Search Console todavía.
                            </div>
                        @endforelse
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
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        @foreach($fuentes as $f)
                            @php
                                $pct = ($f['value'] / $maxFuente) * 100;
                                $channelKey = $f['key'] ?? strtolower($f['label']);
                            @endphp
                            <a href="/admin/analytics?channel={{ $channelKey }}"
                               class="alg-row-link"
                               title="Ver tráfico de {{ $f['label'] }} en GA4"
                               style="display:grid;grid-template-columns:84px 1fr 56px;align-items:center;gap:10px;padding:6px 10px;margin:0 -10px;text-decoration:none;color:inherit;">
                                <div style="font-size:12px;color:var(--ink-3);">{{ $f['label'] }}</div>
                                <div style="position:relative;height:8px;background:var(--surface-2);border-radius:2px;">
                                    <div style="position:absolute;inset:0;width:{{ $pct }}%;background:var(--ink-1);border-radius:2px;"></div>
                                </div>
                                <div class="num tnum" style="font-size:12px;color:var(--ink-2);text-align:right;">{{ number_format($f['value']) }}</div>
                            </a>
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
                                    $countryCode = $c['code'] ?? $c['label'];
                                @endphp
                                <a href="/admin/analytics?country={{ urlencode($countryCode) }}"
                                   class="alg-row-link"
                                   title="Ver tráfico de {{ $c['label'] }} en GA4"
                                   style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;color:inherit;padding:4px 0;border-radius:4px;">
                                    <div class="num tnum" style="font-size:11px;color:var(--ink-4);">{{ $c['value'] >= 1000 ? number_format($c['value']/1000, 1) . 'k' : $c['value'] }}</div>
                                    <div style="width:100%;max-width:36px;height:{{ $h }}px;background:{{ $bg }};opacity:{{ $opacity }};border-radius:2px 2px 0 0;"></div>
                                    <div style="font-size:11px;color:var(--ink-4);">{{ $c['label'] }}</div>
                                </a>
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
                            <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">{{ $newLeadsToday }} {{ $newLeadsToday === 1 ? 'nuevo hoy' : 'nuevos hoy' }} · actualizado hace un momento</p>
                        </div>
                        <a href="/admin/leads" style="{{ $btnGhost }};text-decoration:none;">Ver todos @include('alg-dashboard.icon', ['name' => 'arrow-up-right', 'size' => 12, 'stroke' => 'var(--ink-4)'])</a>
                    </div>
                    <div style="border-top:1px solid var(--ink-2);">
                        @forelse($recentLeads as $l)
                            @php [$bg, $fg] = $stageColor($l['stage']); @endphp
                            <a href="/admin/leads?selected={{ $l['id'] ?? '' }}"
                               class="alg-row-link"
                               title="Abrir {{ $l['name'] }} en bandeja de entrada"
                               style="display:grid;grid-template-columns:1fr auto auto;align-items:center;gap:14px;padding:13px 12px;margin:0 -12px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;">
                                <div style="min-width:0;">
                                    <div style="font-size:13px;font-weight:500;color:var(--ink-1);">{{ $l['name'] }}</div>
                                    <div style="font-size:11.5px;color:var(--ink-4);margin-top:2px;">{{ $l['company'] }} · {{ $l['country'] }} · {{ $l['time'] }}</div>
                                </div>
                                <span style="font-size:10.5px;padding:3px 8px;border-radius:3px;background:{{ $bg }};color:{{ $fg }};font-weight:500;text-transform:uppercase;letter-spacing:0.04em;">{{ $l['stage'] }}</span>
                                <div class="num tnum" style="font-size:13px;font-weight:500;color:var(--ink-1);min-width:64px;text-align:right;">{{ $l['value'] }}</div>
                            </a>
                        @empty
                            <div style="padding:32px 0;text-align:center;font-size:12.5px;color:var(--ink-4);">
                                Sin leads recientes en este filtro.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Campaigns --}}
                <div>
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <h2 style="margin:0;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Campañas</h2>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--ink-4);">{{ $campaignsCount }} {{ $campaignsCount === 1 ? 'campaña' : 'campañas' }}{{ $campaignsSpend > 0 ? ' · ' . $campaignsSpendLabel . ' invertidos' : '' }}</p>
                        </div>
                        <a href="/admin/campaigns/create" style="{{ $btnGhost }};text-decoration:none;">Crear @include('alg-dashboard.icon', ['name' => 'plus', 'size' => 12, 'stroke' => 'var(--ink-4)'])</a>
                    </div>
                    <div style="border-top:1px solid var(--ink-2);">
                        @forelse($campaigns as $c)
                            @php
                                $statusColor = match($c['status']) {
                                    'Activa'    => 'var(--pos)',
                                    'Pausada'   => 'var(--ink-4)',
                                    'Programada'=> 'var(--accent)',
                                    default     => 'var(--ink-4)',
                                };
                                $campaignHref = isset($c['id']) ? "/admin/campaigns/{$c['id']}/edit" : '/admin/campaigns';
                            @endphp
                            <a href="{{ $campaignHref }}"
                               class="alg-row-link"
                               title="Editar campaña {{ $c['name'] }}"
                               style="padding:13px 12px;margin:0 -12px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;text-decoration:none;color:inherit;">
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
                            </a>
                        @empty
                            <div style="padding:32px 0;text-align:center;font-size:12.5px;color:var(--ink-4);">
                                Aún no hay campañas. <a href="/admin/campaigns/create" style="color:var(--accent);text-decoration:none;font-weight:500;">Crear la primera →</a>
                            </div>
                        @endforelse
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
                        @forelse($activity as $a)
                            @php $leadId = $a['lead_id'] ?? null; @endphp
                            @if($leadId)
                                <a href="/admin/leads?selected={{ $leadId }}"
                                   class="alg-row-link"
                                   title="Abrir lead relacionado"
                                   style="display:grid;grid-template-columns:1fr auto;gap:14px;padding:11px 12px;margin:0 -12px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;">
                                    <div style="font-size:12.5px;color:var(--ink-2);line-height:1.5;">
                                        <span style="font-weight:500;color:var(--ink-1);">{{ $a['actor'] }}</span> {{ $a['action'] }}
                                    </div>
                                    <span class="num" style="font-size:11px;color:var(--ink-5);">{{ $a['time'] }}</span>
                                </a>
                            @else
                                <div style="display:grid;grid-template-columns:1fr auto;gap:14px;padding:11px 0;border-bottom:1px solid var(--border);">
                                    <div style="font-size:12.5px;color:var(--ink-2);line-height:1.5;">
                                        <span style="font-weight:500;color:var(--ink-1);">{{ $a['actor'] }}</span> {{ $a['action'] }}
                                    </div>
                                    <span class="num" style="font-size:11px;color:var(--ink-5);">{{ $a['time'] }}</span>
                                </div>
                            @endif
                        @empty
                            <div style="padding:32px 0;text-align:center;font-size:12.5px;color:var(--ink-4);">
                                Sin actividad reciente.
                            </div>
                        @endforelse
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
                                $taskHref = isset($t['id']) ? "/admin/tasks/{$t['id']}/edit" : '/admin/tasks';
                            @endphp
                            <a href="{{ $taskHref }}"
                               class="alg-row-link"
                               title="Abrir tarea"
                               style="display:grid;grid-template-columns:16px 1fr auto;gap:12px;padding:11px 12px;margin:0 -12px;align-items:center;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;">
                                <span style="width:14px;height:14px;border-radius:3px;border:1.5px solid var(--ink-5);"></span>
                                <div style="font-size:13px;color:var(--ink-1);">{{ $t['title'] }}</div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="font-size:10px;padding:2px 7px;border-radius:3px;background:{{ $prioBg }};color:{{ $prioFg }};text-transform:uppercase;letter-spacing:0.06em;font-weight:500;">{{ $t['priority'] }}</span>
                                    <span style="font-size:11.5px;color:var(--ink-4);">{{ $t['due'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
