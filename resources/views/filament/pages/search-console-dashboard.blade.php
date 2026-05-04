<x-filament-panels::page>
    @php
        $deltaPct = function ($curr, $prev, $invert = false) {
            if ($prev == 0 && $curr == 0) return ['pct' => 0, 'dir' => 'flat'];
            if ($prev == 0) return ['pct' => 100, 'dir' => $invert ? 'down' : 'up'];
            $pct = round((($curr - $prev) / $prev) * 100, 1);
            $dir = $pct > 0 ? ($invert ? 'down' : 'up') : ($pct < 0 ? ($invert ? 'up' : 'down') : 'flat');
            return ['pct' => abs($pct), 'dir' => $dir];
        };
        $clickDelta = $deltaPct($totals->clicks, $prev->clicks);
        $imprDelta  = $deltaPct($totals->impressions, $prev->impressions);
        $ctrDelta   = $deltaPct($totals->avg_ctr, $prev->avg_ctr);
        $posDelta   = $deltaPct($totals->avg_position, $prev->avg_position, true); // pos lower = better

        // Helper to color CTR cell — heat map green/yellow/red
        $ctrColor = function (float $ctr): string {
            return match (true) {
                $ctr >= 8.0 => 'var(--alg-pos)',
                $ctr >= 3.0 => 'var(--alg-warn)',
                $ctr > 0    => 'var(--alg-neg)',
                default     => 'var(--alg-ink-5)',
            };
        };
        // Position color — top 3 green, 4-10 ok, 11-20 warning, 21+ red
        $posColor = function (float $pos): string {
            return match (true) {
                $pos === 0.0 => 'var(--alg-ink-5)',
                $pos <= 3    => 'var(--alg-pos)',
                $pos <= 10   => 'var(--alg-ink-2)',
                $pos <= 20   => 'var(--alg-warn)',
                default      => 'var(--alg-neg)',
            };
        };
        // Max impressions for inline bar viz scaling
        $maxImpr = max(1, $rows->max('impressions') ?? 0);
    @endphp

    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- ─── Header + period selector ─── --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Rendimiento — Resultados de búsqueda</h2>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:4px 0 0;letter-spacing:.04em;">{{ $startDate }} — {{ $endDate }} · Web · vs período anterior</p>
                @if($keywordFilter !== '')
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-accent);margin:4px 0 0;letter-spacing:.04em;">
                        Filtrado: <strong>{{ $keywordFilter }}</strong>
                        <a href="/admin/search-console" style="color:var(--alg-ink-4);text-decoration:none;margin-left:8px;">× quitar filtro</a>
                    </p>
                @endif
            </div>
            <div style="display:inline-flex;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;letter-spacing:.04em;">
                @foreach(['7d'=>'7 d','28d'=>'28 d','3m'=>'3 meses','6m'=>'6 meses','12m'=>'12 meses','16m'=>'16 meses'] as $key=>$label)
                    <button type="button"
                            wire:click="setPeriod('{{ $key }}')"
                            style="padding:7px 12px;border:none;border-right:1px solid var(--alg-line);cursor:pointer;background:{{ $period === $key ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $period === $key ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ─── Two-column layout: MAIN (2fr) | INSIGHTS SIDEBAR (1fr) ─── --}}
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;align-items:flex-start;">

            {{-- ════════════════════════ MAIN COLUMN ════════════════════════ --}}
            <div style="display:flex;flex-direction:column;gap:18px;min-width:0;">

                {{-- 4 KPI tiles con sparklines + delta arrows --}}
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
                    @foreach([
                        ['Clics totales',     (int)$totals->clicks,        0, '',  $clickDelta, 'rgb(30, 58, 138)'],
                        ['Impresiones',       (int)$totals->impressions,   0, '',  $imprDelta,  'rgb(124, 58, 237)'],
                        ['CTR promedio',      (float)$totals->avg_ctr,     1, '%', $ctrDelta,   'rgb(34, 197, 94)'],
                        ['Posición promedio', (float)$totals->avg_position,1, '',  $posDelta,   'rgb(234, 88, 12)'],
                    ] as [$label, $value, $decimals, $suffix, $delta, $color])
                        <div class="alg-hover-lift" style="padding:16px 18px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $color }};"></span>
                                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);font-weight:500;letter-spacing:-0.005em;">{{ $label }}</span>
                            </div>
                            <span data-count-to="{{ $value }}"
                                  data-count-decimals="{{ $decimals }}"
                                  data-count-suffix="{{ $suffix }}"
                                  style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0{{ $suffix }}</span>
                            @if($delta['dir'] !== 'flat')
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $delta['dir'] === 'up' ? 'var(--alg-pos)' : 'var(--alg-neg)' }};font-weight:500;">
                                    {{ $delta['dir'] === 'up' ? '▴' : '▾' }} {{ $delta['pct'] }}%
                                </span>
                            @else
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">·</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Time-series chart --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:16px 20px 8px;">
                    <div style="display:flex;align-items:center;gap:18px;margin-bottom:10px;">
                        <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);">
                            <span style="width:14px;height:2px;background:rgb(30, 58, 138);"></span> Clics
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);">
                            <span style="width:14px;height:2px;background:rgb(124, 58, 237);"></span> Impresiones
                        </span>
                    </div>
                    <div style="height:200px;width:100%;">
                        {!! \App\Support\DashboardCharts::multiSeriesSvg(
                            ['clicks' => $clicksSeries, 'impressions' => $impressionsSeries],
                            $labels,
                            ['#1E3A8A', '#7C3AED'],
                            900, 200, 'line',
                            ['t' => 8, 'r' => 12, 'b' => 28, 'l' => 36]
                        ) !!}
                    </div>
                </div>

                {{-- Tabs (Consultas / Páginas / Países / Fechas) + tabla con bar viz + heat map --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);">
                    <div style="border-bottom:1px solid var(--alg-line);display:flex;">
                        @foreach(['queries'=>'CONSULTAS','pages'=>'PÁGINAS','countries'=>'PAÍSES','dates'=>'FECHAS'] as $key=>$label)
                            <button type="button"
                                    wire:click="setTab('{{ $key }}')"
                                    style="padding:13px 20px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;letter-spacing:.04em;color:{{ $tab === $key ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};border-bottom:2px solid {{ $tab === $key ? 'var(--alg-accent)' : 'transparent' }};margin-bottom:-1px;">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;">
                            <thead>
                                <tr style="background:var(--alg-surface-2);">
                                    <th style="text-align:left;padding:10px 16px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">
                                        @if($tab==='queries') Consulta principal
                                        @elseif($tab==='pages') Página
                                        @elseif($tab==='countries') País
                                        @else Fecha @endif
                                    </th>
                                    <th style="text-align:right;padding:10px 16px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);width:60px;">Clics</th>
                                    <th style="text-align:right;padding:10px 16px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">Impresiones</th>
                                    <th style="text-align:right;padding:10px 16px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);width:70px;">CTR</th>
                                    <th style="text-align:right;padding:10px 16px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);width:70px;">Pos.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $r)
                                    @php
                                        $imprPct = (int) $r->impressions / $maxImpr * 100;
                                        $rowLabel = $tab === 'dates' ? \Carbon\Carbon::parse($r->label)->format('d M Y') : $r->label;
                                    @endphp
                                    <tr class="alg-row-link" style="border-bottom:1px solid var(--alg-line);transition:background 120ms;" onmouseover="this.style.background='var(--alg-surface-2)'" onmouseout="this.style.background='transparent'">
                                        <td style="padding:10px 16px;color:var(--alg-ink);max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $rowLabel }}</td>
                                        <td style="padding:10px 16px;text-align:right;font-variant-numeric:tabular-nums;color:var(--alg-ink);font-weight:500;">{{ number_format((int)$r->clicks) }}</td>
                                        <td style="padding:10px 16px;text-align:right;font-variant-numeric:tabular-nums;color:var(--alg-ink-2);position:relative;min-width:140px;">
                                            {{-- Inline bar viz: subtle background bar proportional to max impressions --}}
                                            <div style="position:absolute;right:16px;top:50%;transform:translateY(-50%);height:18px;width:{{ max(4, $imprPct) }}%;max-width:120px;background:linear-gradient(to right, rgba(124,58,237,0.04), rgba(124,58,237,0.18));border-radius:2px;"></div>
                                            <span style="position:relative;z-index:1;">{{ number_format((int)$r->impressions) }}</span>
                                        </td>
                                        <td style="padding:10px 16px;text-align:right;font-variant-numeric:tabular-nums;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:{{ $ctrColor((float)$r->ctr) }};font-weight:500;">{{ number_format((float)$r->ctr, 1) }}%</td>
                                        <td style="padding:10px 16px;text-align:right;font-variant-numeric:tabular-nums;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:{{ $posColor((float)$r->position) }};font-weight:500;">{{ number_format((float)$r->position, 1) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" style="padding:48px;text-align:center;color:var(--alg-ink-4);font-size:13px;">Sin datos en este período. Sincronizá Search Console o cambiá el rango.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════ INSIGHTS SIDEBAR ════════════════════════ --}}
            <aside style="display:flex;flex-direction:column;gap:14px;min-width:0;position:sticky;top:14px;">

                {{-- Position distribution viz --}}
                @php
                    $bucketLabels = ['top3' => 'Top 3', 'page1' => '4–10', 'page2' => '11–20', 'beyond' => '21+'];
                    $bucketColors = ['top3' => 'var(--alg-pos)', 'page1' => 'var(--alg-accent)', 'page2' => 'var(--alg-warn)', 'beyond' => 'var(--alg-neg)'];
                @endphp
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:14px 16px;">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:10px;">
                        <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:600;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">📊 Distribución de posiciones</h3>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);">{{ $totalQueries }} queries</span>
                    </div>
                    @if($totalQueries > 0)
                        {{-- Stacked bar --}}
                        <div style="display:flex;height:10px;border-radius:3px;overflow:hidden;background:var(--alg-surface-2);margin-bottom:8px;">
                            @foreach($positionBuckets as $key => $count)
                                @if($count > 0)
                                    @php $pct = ($count / $totalQueries) * 100; @endphp
                                    <div style="width:{{ $pct }}%;background:{{ $bucketColors[$key] }};" title="{{ $bucketLabels[$key] }}: {{ $count }}"></div>
                                @endif
                            @endforeach
                        </div>
                        {{-- Legend --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;">
                            @foreach($positionBuckets as $key => $count)
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="width:8px;height:8px;border-radius:2px;background:{{ $bucketColors[$key] }};"></span>
                                    <span style="color:var(--alg-ink-3);">{{ $bucketLabels[$key] }}</span>
                                    <span style="color:var(--alg-ink);font-weight:500;font-variant-numeric:tabular-nums;margin-left:auto;">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;">Sin queries en este período.</p>
                    @endif
                </div>

                {{-- 🔥 Winners --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:14px 16px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:600;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">🔥 Top winners</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);margin:0 0 10px;">queries que más subieron en clics vs período anterior</p>
                    @forelse($winners as $w)
                        <a href="/admin/search-console?kw={{ urlencode($w['query']) }}"
                           class="alg-row-link"
                           style="display:grid;grid-template-columns:1fr auto;gap:8px;padding:6px 8px;margin:0 -8px;text-decoration:none;color:inherit;align-items:center;">
                            <span style="font-size:12px;color:var(--alg-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $w['query'] }}</span>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-pos);font-weight:600;">▴ +{{ $w['delta'] }}</span>
                        </a>
                    @empty
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;">Ninguna query subió en este período.</p>
                    @endforelse
                </div>

                {{-- 📉 Losers --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:14px 16px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:600;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">📉 Top losers</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);margin:0 0 10px;">queries que cayeron — revisar cambio de SERP o intent</p>
                    @forelse($losers as $l)
                        <a href="/admin/search-console?kw={{ urlencode($l['query']) }}"
                           class="alg-row-link"
                           style="display:grid;grid-template-columns:1fr auto;gap:8px;padding:6px 8px;margin:0 -8px;text-decoration:none;color:inherit;align-items:center;">
                            <span style="font-size:12px;color:var(--alg-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $l['query'] }}</span>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-neg);font-weight:600;">▾ {{ $l['delta'] }}</span>
                        </a>
                    @empty
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;">Ninguna query cayó. ✨</p>
                    @endforelse
                </div>

                {{-- 💎 CTR opportunities --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:14px 16px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:600;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">💎 Oportunidades CTR</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);margin:0 0 10px;">muchas impresiones, poco click — mejora título/snippet</p>
                    @forelse($opportunities as $o)
                        <a href="/admin/search-console?kw={{ urlencode($o['query']) }}"
                           class="alg-row-link"
                           style="display:flex;flex-direction:column;gap:2px;padding:6px 8px;margin:0 -8px;text-decoration:none;color:inherit;">
                            <span style="font-size:12px;color:var(--alg-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $o['query'] }}</span>
                            <div style="display:flex;gap:10px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);">
                                <span>{{ number_format($o['impressions']) }} impr.</span>
                                <span style="color:var(--alg-neg);">CTR {{ $o['ctr'] }}%</span>
                                <span>pos {{ $o['position'] }}</span>
                            </div>
                        </a>
                    @empty
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;">Sin oportunidades obvias en este período.</p>
                    @endforelse
                </div>

                {{-- 🎯 Quick wins (page 2 → page 1) --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:14px 16px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:600;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.005em;">🎯 Quick wins (página 2)</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);margin:0 0 10px;">posición 11–20 con impresiones — push SEO = page 1</p>
                    @forelse($quickWins as $q)
                        <a href="/admin/search-console?kw={{ urlencode($q['query']) }}"
                           class="alg-row-link"
                           style="display:flex;flex-direction:column;gap:2px;padding:6px 8px;margin:0 -8px;text-decoration:none;color:inherit;">
                            <span style="font-size:12px;color:var(--alg-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $q['query'] }}</span>
                            <div style="display:flex;gap:10px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);">
                                <span style="color:var(--alg-warn);">pos {{ $q['position'] }}</span>
                                <span>{{ number_format($q['impressions']) }} impr.</span>
                                <span>{{ $q['clicks'] }} clics</span>
                            </div>
                        </a>
                    @empty
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;">Sin queries cerca del top 10. Sigue trabajando SEO.</p>
                    @endforelse
                </div>

            </aside>
        </div>

    </div>
</x-filament-panels::page>
