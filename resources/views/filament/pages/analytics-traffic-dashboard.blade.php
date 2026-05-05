<x-filament-panels::page>
    @php
        // Delta calc — color-aware: para `bounce_rate`, "down" es bueno (verde); para todo lo demás "up" es bueno.
        $deltaPct = function ($curr, $prev, bool $invertColor = false) {
            if ($prev == 0 && $curr == 0) return ['pct' => 0, 'dir' => 'flat', 'color' => 'flat'];
            if ($prev == 0) return ['pct' => 100, 'dir' => 'up', 'color' => $invertColor ? 'down' : 'up'];
            $pct = round((($curr - $prev) / $prev) * 100, 1);
            $dir = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat');
            // Color semantics: si invertColor, up=neg / down=pos
            $color = $invertColor ? ($dir === 'up' ? 'down' : ($dir === 'down' ? 'up' : 'flat')) : $dir;
            return ['pct' => abs($pct), 'dir' => $dir, 'color' => $color];
        };

        // Mini-sparkline SVG inline (16px height, simple polyline)
        $sparkSvg = function (array $data, string $color = 'var(--alg-accent)') {
            if (empty($data) || max($data) === 0) return '';
            $max = max(1, max($data));
            $w = 80; $h = 18; $n = count($data);
            $pts = [];
            foreach ($data as $i => $v) {
                $x = $n > 1 ? round(($i / ($n - 1)) * ($w - 2) + 1, 1) : $w / 2;
                $y = $h - 2 - round(($v / $max) * ($h - 4), 1);
                $pts[] = "{$x},{$y}";
            }
            $points = implode(' ', $pts);
            return '<svg viewBox="0 0 '.$w.' '.$h.'" preserveAspectRatio="none" width="80" height="18" style="display:block;opacity:.7;">'
                .'<polyline points="'.$points.'" fill="none" stroke="'.$color.'" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'
                .'</svg>';
        };

        $usersDelta    = $prev ? $deltaPct($totals->users, $prev->users) : null;
        $sessionsDelta = $prev ? $deltaPct($totals->sessions, $prev->sessions) : null;
        $pvDelta       = $prev ? $deltaPct($totals->page_views, $prev->page_views) : null;
        $convDelta     = $prev ? $deltaPct($totals->conversions, $prev->conversions) : null;
        // Bounce rate: invertColor (bajar es bueno)
        $bounceDelta   = $prev ? $deltaPct((float) $totals->bounce_rate, (float) $prev->bounce_rate, true) : null;

        $fmtDuration = function ($seconds) {
            $m = (int) floor($seconds / 60);
            $s = (int) ($seconds % 60);
            return sprintf('%d:%02d', $m, $s);
        };

        $channelData = [
            'organic'  => ['Búsqueda orgánica', (int)$channels->organic, '#1E3A8A'],
            'direct'   => ['Directo',           (int)$channels->direct,  '#57534E'],
            'referral' => ['Referido',          (int)$channels->referral,'#A8A29E'],
            'social'   => ['Social',            (int)$channels->social,  '#7C3AED'],
            'paid'     => ['Pagado',            (int)$channels->paid,    '#EA580C'],
        ];

        // KPI drilldown URLs
        $snapshotsBase = '/admin/analytics-snapshots';
        $kpiHref = fn () => $snapshotsBase
            . '?tableFilters[date][from]=' . urlencode($startDateRaw)
            . '&tableFilters[date][until]=' . urlencode($endDateRaw);

        $hasAnyFilter = $channel !== '' || $currentCountry !== '' || $compareMode !== 'prev' || $period !== '28d' || $customFrom !== '';

        // Goal progress
        $goalPct = $monthlyGoal > 0 ? min(100, round(($monthSessions / $monthlyGoal) * 100)) : 0;
        $goalColor = $goalPct >= 100 ? 'var(--alg-pos)' : ($goalPct >= 70 ? 'var(--alg-accent)' : 'var(--alg-warn)');
    @endphp

    <style>
        /* Mobile responsive: KPI grid col se rompe en 2 cols < 800px */
        @media (max-width: 900px) {
            .alg-analytics-kpis { grid-template-columns: repeat(2, 1fr) !important; }
            .alg-analytics-kpis > div { border-bottom: 1px solid var(--alg-line); }
            .alg-analytics-2col { grid-template-columns: 1fr !important; }
            .alg-analytics-toolbar { flex-direction: column; align-items: stretch !important; }
        }
        .alg-chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border: 1px solid var(--alg-line);
            background: var(--alg-surface); color: var(--alg-ink-3);
            font-family: 'Geist', ui-sans-serif, system-ui, sans-serif;
            font-size: 11.5px; font-weight: 500;
            border-radius: 4px; cursor: pointer;
            transition: all 100ms ease;
        }
        .alg-chip:hover { background: var(--alg-surface-2); color: var(--alg-ink); }
        .alg-chip.is-active { background: var(--alg-accent-soft); color: var(--alg-accent); border-color: var(--alg-accent); }
    </style>

    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- ═══════════ Toolbar 1: Period + Compare + Saved Views ═══════════ --}}
        <div class="alg-analytics-toolbar" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Tráfico</h2>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $startDate }} — {{ $endDate }}</span>

                {{-- Period pills (now visible y prominentes) --}}
                <div style="display:inline-flex;background:var(--alg-surface-2);border:1px solid var(--alg-line);border-radius:5px;padding:1px;">
                    @foreach(['7d'=>'7d','28d'=>'28d','90d'=>'90d','12m'=>'12m'] as $key=>$label)
                        @php $isActive = $period === $key && $customFrom === ''; @endphp
                        <button type="button" wire:click="setPeriod('{{ $key }}')"
                                style="padding:4px 10px;border:none;background:{{ $isActive ? 'var(--alg-surface)' : 'transparent' }};color:{{ $isActive ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};border-radius:4px;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;letter-spacing:-0.005em;">{{ $label }}</button>
                    @endforeach
                </div>

                {{-- Custom date range picker (Alpine dropdown) --}}
                <div x-data="{ open: false, from: @js($customFrom), to: @js($customTo) }" @click.outside="open = false" style="position:relative;">
                    <button type="button" @click="open = !open"
                            title="Rango personalizado"
                            style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border:1px solid var(--alg-line);background:var(--alg-surface);color:{{ $customFrom !== '' ? 'var(--alg-accent)' : 'var(--alg-ink-4)' }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;cursor:pointer;border-radius:4px;">📅 Custom</button>
                    <div x-show="open" x-cloak x-transition.opacity
                         style="position:absolute;top:calc(100% + 4px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:6px;padding:10px;z-index:30;box-shadow:0 4px 14px rgba(0,0,0,0.10);min-width:260px;">
                        <div style="display:flex;flex-direction:column;gap:8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;">
                            <label style="color:var(--alg-ink-3);">Desde
                                <input type="date" x-model="from" style="display:block;margin-top:3px;width:100%;padding:5px 8px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:inherit;font-size:11.5px;border-radius:3px;">
                            </label>
                            <label style="color:var(--alg-ink-3);">Hasta
                                <input type="date" x-model="to" style="display:block;margin-top:3px;width:100%;padding:5px 8px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:inherit;font-size:11.5px;border-radius:3px;">
                            </label>
                            <button type="button" @click="$wire.set('customFrom', from); $wire.set('customTo', to); open = false"
                                    style="margin-top:4px;padding:6px 10px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:inherit;font-size:11.5px;font-weight:500;border-radius:4px;">Aplicar</button>
                        </div>
                    </div>
                </div>

                {{-- Compare toggle --}}
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-left:6px;">Comparar:</span>
                @foreach(['prev' => 'vs anterior', 'yoy' => 'vs año anterior', 'none' => 'sin comparar'] as $key => $lbl)
                    <button type="button" wire:click="setCompareMode('{{ $key }}')"
                            class="alg-chip {{ $compareMode === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
                @endforeach
            </div>

            <div style="display:flex;align-items:center;gap:8px;">
                {{-- Freshness indicator --}}
                @if($freshness)
                    <span title="Fecha más reciente con data en analytics_snapshots"
                          style="display:inline-flex;align-items:center;gap:5px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $freshness->isToday() ? 'var(--alg-pos)' : 'var(--alg-warn)' }};"></span>
                        Última sync: {{ $freshness->diffForHumans() }}
                    </span>
                @endif

                {{-- Saved views dropdown --}}
                <div x-data="{ open: false, name: '' }" @click.outside="open = false" style="position:relative;">
                    <button type="button" @click="open = !open"
                            title="Vistas guardadas"
                            style="display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-2);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;border-radius:4px;">
                        💾 Vistas
                        @if(count($savedViews) > 0)<span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);">· {{ count($savedViews) }}</span>@endif
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity
                         style="position:absolute;top:calc(100% + 4px);right:0;min-width:240px;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:6px;box-shadow:0 6px 20px rgba(0,0,0,0.10);padding:6px;z-index:30;">
                        @foreach($savedViews as $idx => $sv)
                            <div style="display:flex;align-items:center;gap:6px;padding:5px 8px;border-radius:4px;font-size:12px;color:var(--alg-ink-2);"
                                 onmouseover="this.style.background='var(--alg-surface-2)'"
                                 onmouseout="this.style.background='transparent'">
                                <span style="color:var(--alg-ink-4);font-size:11px;">📌</span>
                                <button type="button" wire:click="loadAnalyticsView({{ $idx }})" @click="open = false"
                                        style="flex:1;border:none;background:transparent;text-align:left;color:inherit;cursor:pointer;padding:0;font-family:inherit;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sv['name'] }}</button>
                                <button type="button" wire:click="deleteAnalyticsView({{ $idx }})"
                                        wire:confirm="¿Eliminar la vista &quot;{{ $sv['name'] }}&quot;?"
                                        style="border:none;background:transparent;color:var(--alg-ink-5);cursor:pointer;padding:0 2px;font-size:11px;">✕</button>
                            </div>
                        @endforeach
                        @if(count($savedViews) > 0)<div style="height:1px;background:var(--alg-line);margin:5px 0;"></div>@endif
                        <div style="display:flex;gap:4px;padding:4px 8px 2px;">
                            <input x-model="name" type="text" placeholder="Guardar vista actual…" maxlength="40"
                                   x-on:keydown.enter="$wire.saveCurrentView(name); name=''"
                                   style="flex:1;padding:5px 8px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:inherit;font-size:11.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ Toolbar 2: Channel + Country chips ═══════════ --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:5px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 14px;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Canal:</span>
            <button type="button" wire:click="setChannel('')" class="alg-chip {{ $channel === '' ? 'is-active' : '' }}">Todos</button>
            @foreach(['organic'=>'Orgánico','direct'=>'Directo','referral'=>'Referido','social'=>'Social','paid'=>'Pagado'] as $key=>$lbl)
                <button type="button" wire:click="setChannel('{{ $key }}')"
                        class="alg-chip {{ $channel === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach

            <span style="width:1px;height:20px;background:var(--alg-line);margin:0 8px;"></span>

            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">País:</span>
            <button type="button" wire:click="setCountry('')" class="alg-chip {{ $currentCountry === '' ? 'is-active' : '' }}">Global</button>
            @foreach(['SV','GT','HN','NI','CR','PA'] as $code)
                <button type="button" wire:click="setCountry('{{ $code }}')"
                        class="alg-chip {{ $currentCountry === $code ? 'is-active' : '' }}">{{ $code }}</button>
            @endforeach

            @if($hasAnyFilter)
                <button type="button" wire:click="clearAllFilters"
                        style="margin-left:auto;padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">× limpiar todo</button>
            @endif
        </div>

        {{-- ═══════════ Anomaly banner ═══════════ --}}
        @if($anomaly)
            <div style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:4px;background:{{ $anomaly['level'] === 'critical' ? 'var(--alg-neg-soft)' : 'var(--alg-pos-soft)' }};border:1px solid {{ $anomaly['level'] === 'critical' ? 'var(--alg-neg)' : 'var(--alg-pos)' }};color:{{ $anomaly['level'] === 'critical' ? 'var(--alg-neg)' : 'var(--alg-pos)' }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;">
                <span style="font-size:14px;">{{ $anomaly['level'] === 'critical' ? '📉' : '📈' }}</span>
                <span style="flex:1;">{{ $anomaly['message'] }}</span>
            </div>
        @endif

        {{-- ═══════════ KPI tiles con sparklines + clickable ═══════════ --}}
        <div class="alg-analytics-kpis" style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @php
                $kpis = [
                    ['Usuarios totales', (int)$totals->users, $usersDelta, $sparks['users'] ?? [], '#1E3A8A', $kpiHref()],
                    ['Usuarios nuevos', (int)$totals->new_users, null, [], '#A8A29E', $kpiHref()],
                    ['Sesiones', (int)$totals->sessions, $sessionsDelta, $sparks['sessions'] ?? [], '#1E3A8A', $kpiHref()],
                    ['Vistas de página', (int)$totals->page_views, $pvDelta, $sparks['page_views'] ?? [], '#7C3AED', $kpiHref()],
                    ['Duración media', $fmtDuration((float)$totals->avg_duration), null, [], null, $kpiHref()],
                    ['Conversiones', (int)$totals->conversions, $convDelta, [], 'var(--alg-pos)', $kpiHref()],
                ];
            @endphp
            @foreach($kpis as [$label, $value, $delta, $spark, $color, $href])
                <a href="{{ $href }}" class="alg-hover-lift"
                   title="Ver datos crudos en analytics_snapshots filtrados al período"
                   style="text-decoration:none;color:inherit;padding:14px 16px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:4px;cursor:pointer;transition:background 100ms ease;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-3);letter-spacing:-0.005em;line-height:1.3;">{{ $label }}</span>
                    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px;">
                        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:24px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ is_int($value) ? number_format($value) : $value }}</span>
                        @if($color && ! empty($spark))
                            <div style="margin-bottom:2px;">{!! $sparkSvg($spark, $color) !!}</div>
                        @endif
                    </div>
                    @if($delta && $delta['dir'] !== 'flat')
                        @php
                            $deltaColor = match($delta['color']) {
                                'up'   => 'var(--alg-pos)',
                                'down' => 'var(--alg-neg)',
                                default=> 'var(--alg-ink-5)',
                            };
                        @endphp
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $deltaColor }};font-weight:500;">{{ $delta['dir'] === 'up' ? '▴' : '▾' }} {{ $delta['pct'] }}%</span>
                    @else
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">·</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Bounce rate row separado (color invertido — bajar es bueno) --}}
        @if($prev && $bounceDelta)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:var(--alg-surface);border:1px solid var(--alg-line);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);">
                <span><strong>Tasa de rebote:</strong> {{ number_format((float)$totals->bounce_rate, 1) }}%</span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:{{ $bounceDelta['color'] === 'up' ? 'var(--alg-pos)' : ($bounceDelta['color'] === 'down' ? 'var(--alg-neg)' : 'var(--alg-ink-5)') }};">
                    {{ $bounceDelta['dir'] === 'up' ? '▴' : '▾' }} {{ $bounceDelta['pct'] }}%
                    <span style="color:var(--alg-ink-5);">({{ $bounceDelta['color'] === 'up' ? 'mejor' : ($bounceDelta['color'] === 'down' ? 'peor' : '') }})</span>
                </span>
            </div>
        @endif

        {{-- Goal banner si hay meta configurada --}}
        @if($monthlyGoal > 0)
            <div style="display:flex;align-items:center;gap:14px;padding:10px 14px;background:var(--alg-surface);border:1px solid var(--alg-line);">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);">Goal mensual de sesiones:</span>
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;color:var(--alg-ink);">{{ number_format($monthSessions) }} / {{ number_format($monthlyGoal) }} ({{ $goalPct }}%)</span>
                <div style="flex:1;max-width:300px;height:6px;background:var(--alg-surface-2);border-radius:3px;overflow:hidden;">
                    <div style="height:100%;width:{{ $goalPct }}%;background:{{ $goalColor }};transition:width 300ms ease;"></div>
                </div>
                <button type="button" x-data
                        x-on:click="const g = prompt('Nueva meta mensual de sesiones:', '{{ $monthlyGoal }}'); if (g !== null) $wire.setMonthlyGoal(parseInt(g) || 0)"
                        title="Cambiar meta"
                        style="border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-4);cursor:pointer;padding:3px 8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;border-radius:3px;">⚙ Editar</button>
            </div>
        @else
            <div style="display:flex;align-items:center;gap:10px;padding:8px 14px;background:var(--alg-bg);border:1px dashed var(--alg-line);">
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">Sin meta mensual definida.</span>
                <button type="button" x-data
                        x-on:click="const g = prompt('Define meta mensual de sesiones:', '1000'); if (g !== null) $wire.setMonthlyGoal(parseInt(g) || 0)"
                        style="border:none;background:transparent;color:var(--alg-accent);cursor:pointer;padding:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;text-decoration:underline;">+ Definir goal</button>
            </div>
        @endif

        {{-- ═══════════ Big chart ═══════════ --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px 12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
                <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Usuarios — últimos {{ count($labels) }} días</h3>
                <div style="display:flex;align-items:center;gap:18px;">
                    <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);">
                        <span style="width:14px;height:2px;background:#1E3A8A;"></span> Período actual
                    </span>
                    @if($compareMode !== 'none')
                        <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);">
                            <span style="width:14px;height:1px;background:var(--alg-ink-5);border-top:1px dashed var(--alg-ink-5);"></span> {{ $compareMode === 'yoy' ? 'Año anterior' : 'Período anterior' }}
                        </span>
                    @endif
                </div>
            </div>
            <div style="height:240px;width:100%;">
                {!! \App\Support\DashboardCharts::multiSeriesSvg(
                    $compareMode !== 'none'
                        ? ['users' => $usersSeries, 'previous' => $previousSeries]
                        : ['users' => $usersSeries],
                    $labels,
                    $compareMode !== 'none' ? ['#1E3A8A', '#A8A29E'] : ['#1E3A8A'],
                    1100, 240, 'line',
                    ['t' => 8, 'r' => 12, 'b' => 28, 'l' => 36]
                ) !!}
            </div>
        </div>

        {{-- ═══════════ 2-col: Channels + Countries (o Engagement) ═══════════ --}}
        <div class="alg-analytics-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

            {{-- Channels — clickable rows --}}
            <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
                <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">¿De dónde vienen tus usuarios?</h3>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);margin:0 0 14px;letter-spacing:.04em;">Por canal · click para filtrar</p>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($channelData as $ch => $data)
                        @php [$cName, $cCount, $cColor] = $data; @endphp
                        @php $pct = round(($cCount / $channelTotal) * 100, 1); $isActiveChannel = $channel === $ch; @endphp
                        <button type="button" wire:click="setChannel('{{ $isActiveChannel ? '' : $ch }}')"
                                style="display:block;text-align:left;background:{{ $isActiveChannel ? 'var(--alg-accent-soft)' : 'transparent' }};border:none;padding:6px 8px;margin:0 -8px;cursor:pointer;border-radius:4px;width:calc(100% + 16px);"
                                onmouseover="if(!this.dataset.active)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $isActiveChannel ? 'var(--alg-accent-soft)' : 'transparent' }}'"
                                @if($isActiveChannel) data-active="1" @endif>
                            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:5px;">
                                <span style="display:flex;align-items:center;gap:8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-2);">
                                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $cColor }};"></span>{{ $cName }}
                                </span>
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ number_format($cCount) }}</span>
                            </div>
                            <div style="height:5px;background:var(--alg-surface-2);border-radius:3px;overflow:hidden;">
                                <div style="height:100%;width:{{ $pct }}%;background:{{ $cColor }};"></div>
                            </div>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);margin-top:3px;display:block;">{{ $pct }}%</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- By country (Global) o Engagement (per-country) --}}
            @if($isGlobal)
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">Usuarios por país</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);margin:0 0 14px;letter-spacing:.04em;">Top 8 · click para filtrar</p>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @php $maxCountry = max(1, $byCountry->max('users')); @endphp
                        @forelse($byCountry as $c)
                            <button type="button" wire:click="setCountry('{{ strtoupper($c->code) }}')"
                                    style="display:block;text-align:left;background:transparent;border:none;padding:5px 8px;margin:0 -8px;cursor:pointer;border-radius:4px;width:calc(100% + 16px);"
                                    onmouseover="this.style.background='var(--alg-surface-2)'"
                                    onmouseout="this.style.background='transparent'">
                                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:3px;">
                                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);">
                                        <span style="color:var(--alg-ink-4);font-size:10px;letter-spacing:.04em;font-family:ui-monospace,'SF Mono',Menlo,monospace;margin-right:8px;">{{ strtoupper($c->code) }}</span>{{ $c->name }}
                                    </span>
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ number_format($c->users) }}</span>
                                </div>
                                <div style="height:4px;background:var(--alg-surface-2);">
                                    <div style="height:100%;width:{{ round(($c->users / $maxCountry) * 100, 1) }}%;background:var(--alg-accent);"></div>
                                </div>
                            </button>
                        @empty
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-4);">Sin datos por país en este período.</p>
                        @endforelse
                    </div>
                </div>
            @else
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 14px;letter-spacing:-0.005em;">Engagement</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                        <div>
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);text-transform:uppercase;letter-spacing:.14em;margin:0 0 4px;">Tasa de rebote</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:26px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.02em;line-height:1;font-variant-numeric:tabular-nums;margin:0;">{{ number_format((float)$totals->bounce_rate, 1) }}<span style="font-size:16px;color:var(--alg-ink-3);">%</span></p>
                        </div>
                        <div>
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);text-transform:uppercase;letter-spacing:.14em;margin:0 0 4px;">Páginas / sesión</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:26px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.02em;line-height:1;font-variant-numeric:tabular-nums;margin:0;">{{ $totals->sessions > 0 ? number_format($totals->page_views / $totals->sessions, 1) : '0.0' }}</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ═══════════ Funnel — Sessions → PageViews → Conversions ═══════════ --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
            <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">Funnel del período</h3>
            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);margin:0 0 14px;letter-spacing:.04em;">Sesiones → Vistas → Conversiones — drop-offs en cada paso</p>
            @php
                $funnelSteps = [
                    ['Sesiones', (int) $totals->sessions, 'var(--alg-accent)'],
                    ['Vistas de página', (int) $totals->page_views, 'var(--alg-accent-2)'],
                    ['Conversiones', (int) $totals->conversions, 'var(--alg-pos)'],
                ];
                $funnelMax = max(1, ...array_column($funnelSteps, 1));
            @endphp
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($funnelSteps as $i => [$name, $val, $color])
                    @php
                        $pct = round(($val / $funnelMax) * 100, 1);
                        $prevVal = $i > 0 ? $funnelSteps[$i - 1][1] : null;
                        $dropoff = $prevVal && $prevVal > 0 ? round((($val - $prevVal) / $prevVal) * 100, 1) : null;
                    @endphp
                    <div>
                        <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:4px;">
                            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);font-weight:500;">
                                {{ $name }}
                                @if($dropoff !== null && $dropoff !== 0.0)
                                    <span style="margin-left:8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $dropoff < 0 ? 'var(--alg-ink-4)' : 'var(--alg-pos)' }};">
                                        {{ $dropoff > 0 ? '+' : '' }}{{ $dropoff }}% vs paso anterior
                                    </span>
                                @endif
                            </span>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;color:var(--alg-ink);font-variant-numeric:tabular-nums;font-weight:500;">{{ number_format($val) }}</span>
                        </div>
                        <div style="height:8px;background:var(--alg-surface-2);border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};transition:width 300ms ease;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Out-of-scope note --}}
        <div style="padding:10px 14px;background:var(--alg-bg);border:1px dashed var(--alg-line);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">
            <strong style="color:var(--alg-ink-3);">Pendiente (requiere migration):</strong>
            Top pages · Top referrers · Heatmap de horas. La tabla <code style="background:var(--alg-surface-2);padding:1px 4px;border-radius:2px;">analytics_snapshots</code> guarda datos diarios agregados — no per-página ni per-hora. Habría que crear <code>page_metrics</code> + ingestar de GA4 API.
        </div>

    </div>
</x-filament-panels::page>
