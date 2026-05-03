<x-filament-panels::page>
    @php
        use App\Support\DashboardCharts;

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
    @endphp

    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Header con period selector tipo Google Search Console --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Rendimiento — Resultados de búsqueda</h2>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:4px 0 0;letter-spacing:.04em;">{{ $startDate }} — {{ $endDate }} · Web</p>
            </div>
            {{-- Period pill (real GSC has: 7d, 28d, 3m, 6m, 12m, 16m) --}}
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

        {{-- 4 KPI tiles tipo GSC (clicks, impresiones, CTR, posición) --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @foreach([
                ['Clics totales', (int)$totals->clicks, 0, '', $clickDelta, 'rgb(30, 58, 138)'],
                ['Impresiones totales', (int)$totals->impressions, 0, '', $imprDelta, 'rgb(124, 58, 237)'],
                ['CTR promedio', (float)$totals->avg_ctr, 1, '%', $ctrDelta, 'rgb(34, 197, 94)'],
                ['Posición promedio', (float)$totals->avg_position, 1, '', $posDelta, 'rgb(234, 88, 12)'],
            ] as [$label, $value, $decimals, $suffix, $delta, $color])
                <div class="alg-hover-lift" style="padding:18px 20px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $color }};"></span>
                        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-3);font-weight:500;letter-spacing:-0.005em;">{{ $label }}</span>
                    </div>
                    <div style="display:flex;align-items:baseline;gap:10px;">
                        <span data-count-to="{{ $value }}"
                              data-count-decimals="{{ $decimals }}"
                              data-count-suffix="{{ $suffix }}"
                              style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:32px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0{{ $suffix }}</span>
                        @if($delta['dir'] !== 'flat')
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:{{ $delta['dir'] === 'up' ? 'var(--alg-pos)' : 'var(--alg-neg)' }};font-weight:500;">
                                {{ $delta['dir'] === 'up' ? '▴' : '▾' }} {{ $delta['pct'] }}%
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Time-series chart: clicks (line) + impressions (area) --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px 12px;">
            <div style="display:flex;align-items:center;gap:18px;margin-bottom:12px;">
                <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);">
                    <span style="width:14px;height:2px;background:rgb(30, 58, 138);"></span> Clics
                </span>
                <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);">
                    <span style="width:14px;height:2px;background:rgb(124, 58, 237);"></span> Impresiones
                </span>
            </div>
            <div style="height:240px;width:100%;">
                {!! DashboardCharts::multiSeriesSvg(
                    ['clicks' => $clicksSeries, 'impressions' => $impressionsSeries],
                    $labels,
                    ['#1E3A8A', '#7C3AED'],
                    1100, 240,
                    'line',
                    ['t' => 8, 'r' => 12, 'b' => 28, 'l' => 36]
                ) !!}
            </div>
        </div>

        {{-- Tabs (Consultas / Páginas / Países / Fechas) --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);">
            <div style="border-bottom:1px solid var(--alg-line);display:flex;">
                @foreach(['queries'=>'CONSULTAS','pages'=>'PÁGINAS','countries'=>'PAÍSES','dates'=>'FECHAS'] as $key=>$label)
                    <button type="button"
                            wire:click="setTab('{{ $key }}')"
                            style="padding:14px 22px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;letter-spacing:.04em;color:{{ $tab === $key ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};border-bottom:2px solid {{ $tab === $key ? 'var(--alg-accent)' : 'transparent' }};margin-bottom:-1px;">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Detail table --}}
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;">
                    <thead>
                        <tr style="background:var(--alg-surface-2);">
                            <th style="text-align:left;padding:12px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">
                                @if($tab==='queries') Consulta principal
                                @elseif($tab==='pages') Página
                                @elseif($tab==='countries') País
                                @else Fecha @endif
                            </th>
                            <th style="text-align:right;padding:12px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">Clics</th>
                            <th style="text-align:right;padding:12px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">Impresiones</th>
                            <th style="text-align:right;padding:12px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">CTR</th>
                            <th style="text-align:right;padding:12px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);border-bottom:1px solid var(--alg-line);">Posición</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr style="border-bottom:1px solid var(--alg-line);transition:background 120ms;" onmouseover="this.style.background='var(--alg-surface-2)'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px 18px;color:var(--alg-ink);max-width:520px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $tab === 'dates' ? \Carbon\Carbon::parse($r->label)->format('d M Y') : $r->label }}
                                </td>
                                <td style="padding:12px 18px;text-align:right;font-variant-numeric:tabular-nums;color:var(--alg-ink);">{{ number_format((int)$r->clicks) }}</td>
                                <td style="padding:12px 18px;text-align:right;font-variant-numeric:tabular-nums;color:var(--alg-ink-2);">{{ number_format((int)$r->impressions) }}</td>
                                <td style="padding:12px 18px;text-align:right;font-variant-numeric:tabular-nums;color:var(--alg-ink-3);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;">{{ number_format((float)$r->ctr, 1) }}%</td>
                                <td style="padding:12px 18px;text-align:right;font-variant-numeric:tabular-nums;color:var(--alg-ink-3);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;">{{ number_format((float)$r->position, 1) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:48px;text-align:center;color:var(--alg-ink-4);font-size:13px;">Sin datos en este período. Sincronizá Search Console o cambiá el rango.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
