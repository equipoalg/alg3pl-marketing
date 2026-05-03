<x-filament-panels::page>
    @php
        $maxFunnel = max(1, max(array_column($funnelStages, 'count')));
        $rateColor = $rateDelta > 0 ? 'var(--alg-pos)' : ($rateDelta < 0 ? 'var(--alg-neg)' : 'var(--alg-ink-5)');
        $rateGlyph = $rateDelta > 0 ? '▴' : ($rateDelta < 0 ? '▾' : '·');
    @endphp

    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Funnel de conversión</h2>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:4px 0 0;letter-spacing:.04em;">{{ $startDate }} — {{ $endDate }} · vs período anterior</p>
            </div>
            <div style="display:inline-flex;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;letter-spacing:.04em;">
                @foreach(['7d'=>'7 d','30d'=>'30 d','90d'=>'90 d','ytd'=>'Año'] as $key=>$label)
                    <button type="button"
                            wire:click="setPeriod('{{ $key }}')"
                            style="padding:7px 12px;border:none;border-right:1px solid var(--alg-line);cursor:pointer;background:{{ $period === $key ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $period === $key ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- 4 KPI tiles: Conversion rate / Won / Lost / Velocity --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            <div class="alg-hover-lift" style="padding:16px 18px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:6px;">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);letter-spacing:-0.005em;line-height:1.3;">Tasa de conversión</span>
                <span data-count-to="{{ $conversionRate }}"
                      data-count-decimals="1"
                      data-count-suffix="%"
                      style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:30px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0%</span>
                @if($rateDelta !== 0.0)
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $rateColor }};font-weight:500;">
                        {{ $rateGlyph }} {{ abs($rateDelta) }} pts vs período anterior
                    </span>
                @else
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">sin variación</span>
                @endif
            </div>
            <div class="alg-hover-lift" style="padding:16px 18px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:6px;">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);letter-spacing:-0.005em;line-height:1.3;">Leads ganados</span>
                <span data-count-to="{{ $wonLeads }}"
                      style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:30px;font-weight:400;color:var(--alg-pos);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0</span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">de {{ $totalLeads }} totales</span>
            </div>
            <div class="alg-hover-lift" style="padding:16px 18px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:6px;">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);letter-spacing:-0.005em;line-height:1.3;">Leads perdidos</span>
                <span data-count-to="{{ $lostLeads }}"
                      style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:30px;font-weight:400;color:var(--alg-neg);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0</span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">{{ $wonLeads + $lostLeads > 0 ? round(($lostLeads / ($wonLeads + $lostLeads)) * 100, 1) : 0 }}% de los cerrados</span>
            </div>
            <div class="alg-hover-lift" style="padding:16px 18px;display:flex;flex-direction:column;gap:6px;">
                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);letter-spacing:-0.005em;line-height:1.3;">Velocidad media (días)</span>
                @if($velocityDays !== null)
                    <span data-count-to="{{ $velocityDays }}"
                          data-count-decimals="1"
                          style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:30px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0</span>
                @else
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:30px;font-weight:400;color:var(--alg-ink-4);letter-spacing:-0.025em;line-height:1;">—</span>
                @endif
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">{{ $velocityDays !== null ? 'desde captura → ganado' : 'sin datos suficientes' }}</span>
            </div>
        </div>

        {{-- Funnel viz: decreasing bars by stage --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Embudo acumulado</h3>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">leads que alcanzaron al menos cada etapa</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($funnelStages as $i => $s)
                    @php
                        $pct = ($s['count'] / $maxFunnel) * 100;
                        // Drop-off vs previous stage
                        $dropPct = null;
                        if ($i > 0) {
                            $prevCount = $funnelStages[$i - 1]['count'];
                            $dropPct = $prevCount > 0 ? round((1 - $s['count'] / $prevCount) * 100, 1) : 0;
                        }
                        $isWon = $s['key'] === 'won';
                        $barColor = $isWon ? 'var(--alg-pos)' : 'var(--alg-accent)';
                    @endphp
                    <a href="/admin/leads?status={{ $s['key'] }}"
                       style="display:grid;grid-template-columns:130px 1fr 80px 90px;align-items:center;gap:14px;padding:6px 0;text-decoration:none;color:inherit;border-radius:4px;transition:background-color 120ms ease;"
                       title="Ver leads en {{ $s['label'] }}"
                       onmouseover="this.style.background='var(--alg-surface-2)'"
                       onmouseout="this.style.background='transparent'">
                        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-2);letter-spacing:-0.005em;">{{ $s['label'] }}</span>
                        <div style="position:relative;height:14px;background:var(--alg-surface-2);border-radius:2px;overflow:hidden;">
                            <div style="position:absolute;inset:0;width:{{ $pct }}%;background:{{ $barColor }};opacity:0.85;transition:width 600ms cubic-bezier(0.22,1,0.36,1);"></div>
                        </div>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;color:var(--alg-ink);font-weight:500;text-align:right;font-variant-numeric:tabular-nums;">{{ $s['count'] }}</span>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $dropPct !== null && $dropPct > 0 ? 'var(--alg-neg)' : 'var(--alg-ink-5)' }};text-align:right;letter-spacing:.04em;">
                            @if($dropPct !== null)
                                {{ $dropPct > 0 ? '−' : '' }}{{ abs($dropPct) }}%
                            @else
                                <span style="color:var(--alg-ink-5);">—</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Weekly conversion rate chart --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px 12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Tasa de conversión semanal</h3>
                <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);">
                    <span style="width:14px;height:2px;background:rgb(30, 58, 138);"></span> % won / closed (13 semanas)
                </span>
            </div>
            <div style="height:200px;width:100%;">
                {!! \App\Support\DashboardCharts::multiSeriesSvg(
                    ['rate' => $weeklyRate],
                    $weeklyLabels,
                    ['#1E3A8A'],
                    900, 200, 'area',
                    ['t' => 16, 'r' => 8, 'b' => 28, 'l' => 36]
                ) !!}
            </div>
        </div>

        {{-- Two-column breakdown: by source + by country --}}
        <div style="display:grid;grid-template-columns:{{ $isGlobal ? '1fr 1fr' : '1fr' }};gap:18px;">

            {{-- By source --}}
            <div style="background:var(--alg-surface);border:1px solid var(--alg-line);">
                <div style="padding:16px 18px;border-bottom:1px solid var(--alg-line);">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Por origen</h3>
                </div>
                <div style="display:grid;grid-template-columns:1fr 60px 60px 70px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);text-transform:uppercase;letter-spacing:0.06em;padding:8px 18px;border-bottom:1px solid var(--alg-line);background:var(--alg-surface-2);">
                    <div>Origen</div>
                    <div style="text-align:right;">Total</div>
                    <div style="text-align:right;">Ganados</div>
                    <div style="text-align:right;">Tasa</div>
                </div>
                @forelse($bySource as $row)
                    <div style="display:grid;grid-template-columns:1fr 60px 60px 70px;align-items:center;padding:11px 18px;border-bottom:1px solid var(--alg-line);font-size:13px;">
                        <span style="color:var(--alg-ink);font-weight:500;">{{ $row->source_label }}</span>
                        <span style="text-align:right;color:var(--alg-ink-3);font-variant-numeric:tabular-nums;">{{ $row->total }}</span>
                        <span style="text-align:right;color:var(--alg-pos);font-weight:500;font-variant-numeric:tabular-nums;">{{ $row->won }}</span>
                        <span style="text-align:right;color:var(--alg-ink);font-weight:500;font-variant-numeric:tabular-nums;">{{ $row->rate }}%</span>
                    </div>
                @empty
                    <div style="padding:24px 18px;text-align:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">Sin datos por origen.</div>
                @endforelse
            </div>

            @if($isGlobal)
                {{-- By country --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);">
                    <div style="padding:16px 18px;border-bottom:1px solid var(--alg-line);">
                        <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Por país</h3>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 60px 60px 70px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);text-transform:uppercase;letter-spacing:0.06em;padding:8px 18px;border-bottom:1px solid var(--alg-line);background:var(--alg-surface-2);">
                        <div>País</div>
                        <div style="text-align:right;">Total</div>
                        <div style="text-align:right;">Ganados</div>
                        <div style="text-align:right;">Tasa</div>
                    </div>
                    @forelse($byCountry as $row)
                        <div style="display:grid;grid-template-columns:1fr 60px 60px 70px;align-items:center;padding:11px 18px;border-bottom:1px solid var(--alg-line);font-size:13px;">
                            <span style="color:var(--alg-ink);font-weight:500;">{{ $row->name }}</span>
                            <span style="text-align:right;color:var(--alg-ink-3);font-variant-numeric:tabular-nums;">{{ $row->total }}</span>
                            <span style="text-align:right;color:var(--alg-pos);font-weight:500;font-variant-numeric:tabular-nums;">{{ $row->won }}</span>
                            <span style="text-align:right;color:var(--alg-ink);font-weight:500;font-variant-numeric:tabular-nums;">{{ $row->rate }}%</span>
                        </div>
                    @empty
                        <div style="padding:24px 18px;text-align:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">Sin datos por país.</div>
                    @endforelse
                </div>
            @endif

        </div>

    </div>
</x-filament-panels::page>
