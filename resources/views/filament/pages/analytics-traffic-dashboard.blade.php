<x-filament-panels::page>
    @php
        use App\Support\DashboardCharts;

        $deltaPct = function ($curr, $prev) {
            if ($prev == 0 && $curr == 0) return ['pct' => 0, 'dir' => 'flat'];
            if ($prev == 0) return ['pct' => 100, 'dir' => 'up'];
            $pct = round((($curr - $prev) / $prev) * 100, 1);
            $dir = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat');
            return ['pct' => abs($pct), 'dir' => $dir];
        };
        $usersDelta    = $deltaPct($totals->users, $prev->users);
        $sessionsDelta = $deltaPct($totals->sessions, $prev->sessions);
        $pvDelta       = $deltaPct($totals->page_views, $prev->page_views);
        $convDelta     = $deltaPct($totals->conversions, $prev->conversions);

        // Format duration as "MM:SS"
        $fmtDuration = function ($seconds) {
            $m = (int) floor($seconds / 60);
            $s = (int) ($seconds % 60);
            return sprintf('%d:%02d', $m, $s);
        };

        $channelData = [
            ['Búsqueda orgánica', (int)$channels->organic, '#1E3A8A'],
            ['Directo',           (int)$channels->direct,  '#57534E'],
            ['Referido',          (int)$channels->referral,'#A8A29E'],
            ['Social',            (int)$channels->social,  '#7C3AED'],
            ['Pagado',            (int)$channels->paid,    '#EA580C'],
        ];
    @endphp

    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.01em;">Visión general de los informes</h2>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:4px 0 0;letter-spacing:.04em;">{{ $startDate }} — {{ $endDate }} · vs período anterior</p>
            </div>
            <div style="display:inline-flex;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;letter-spacing:.04em;">
                @foreach(['7d'=>'7 d','28d'=>'28 d','90d'=>'90 d','12m'=>'12 meses'] as $key=>$label)
                    <button type="button"
                            wire:click="setPeriod('{{ $key }}')"
                            style="padding:7px 12px;border:none;border-right:1px solid var(--alg-line);cursor:pointer;background:{{ $period === $key ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $period === $key ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- 6 metric tiles tipo GA4 (Users / Nuevos / Sesiones / Vistas / Duración / Conversiones) --}}
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @foreach([
                ['Usuarios totales', (int)$totals->users, 0, '', $usersDelta],
                ['Usuarios nuevos', (int)$totals->new_users, 0, '', null],
                ['Sesiones', (int)$totals->sessions, 0, '', $sessionsDelta],
                ['Vistas de página', (int)$totals->page_views, 0, '', $pvDelta],
                ['Duración media', $fmtDuration((float)$totals->avg_duration), null, '', null],
                ['Conversiones', (int)$totals->conversions, 0, '', $convDelta],
            ] as $tile)
                @php [$label, $value, $decimals, $suffix, $delta] = $tile; @endphp
                <div class="alg-hover-lift" style="padding:16px 18px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:6px;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);letter-spacing:-0.005em;line-height:1.3;">{{ $label }}</span>
                    @if($decimals === null)
                        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:26px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ $value }}</span>
                    @else
                        <span data-count-to="{{ $value }}"
                              data-count-decimals="{{ $decimals }}"
                              data-count-suffix="{{ $suffix }}"
                              style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:26px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">0{{ $suffix }}</span>
                    @endif
                    @if($delta && $delta['dir'] !== 'flat')
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $delta['dir'] === 'up' ? 'var(--alg-pos)' : 'var(--alg-neg)' }};font-weight:500;">
                            {{ $delta['dir'] === 'up' ? '▴' : '▾' }} {{ $delta['pct'] }}%
                        </span>
                    @else
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);">·</span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Big users-over-time chart with comparison line --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px 12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div>
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">Usuarios en los últimos {{ $labels ? count($labels) : 28 }} días</h3>
                </div>
                <div style="display:flex;align-items:center;gap:18px;">
                    <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);">
                        <span style="width:14px;height:2px;background:rgb(30, 58, 138);"></span> Usuarios
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);">
                        <span style="width:14px;height:1px;background:var(--alg-ink-5);border-top:1px dashed var(--alg-ink-5);"></span> Período anterior
                    </span>
                </div>
            </div>
            <div style="height:240px;width:100%;">
                {!! DashboardCharts::multiSeriesSvg(
                    ['users' => $usersSeries, 'previous' => $previousSeries],
                    $labels,
                    ['#1E3A8A', '#A8A29E'],
                    1100, 240,
                    'line',
                    ['t' => 8, 'r' => 12, 'b' => 28, 'l' => 36]
                ) !!}
            </div>
        </div>

        {{-- Two-column: Channel breakdown + (By country if Global) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            {{-- Where do users come from? (channels) --}}
            <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
                <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 14px;letter-spacing:-0.005em;">¿De dónde vienen tus usuarios?</h3>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);margin:-10px 0 14px;letter-spacing:.04em;">Por canal · primera sesión</p>
                <div style="display:flex;flex-direction:column;gap:14px;">
                    @foreach($channelData as [$cName, $cCount, $cColor])
                        @php $pct = round(($cCount / $channelTotal) * 100, 1); @endphp
                        <div>
                            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:6px;">
                                <span style="display:flex;align-items:center;gap:8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-2);">
                                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $cColor }};"></span>
                                    {{ $cName }}
                                </span>
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ number_format($cCount) }}</span>
                            </div>
                            <div style="height:6px;background:var(--alg-surface-2);border-radius:3px;overflow:hidden;">
                                <div data-progress-fill style="height:100%;width:{{ $pct }}%;background:{{ $cColor }};"></div>
                            </div>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:4px;display:block;">{{ $pct }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- By country (Global view) OR Bounce/conversion summary (per-country view) --}}
            @if($isGlobal)
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 14px;letter-spacing:-0.005em;">Usuarios por país</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);margin:-10px 0 14px;letter-spacing:.04em;">Top 8 países por usuarios</p>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @php $maxCountry = max(1, $byCountry->max('users')); @endphp
                        @forelse($byCountry as $c)
                            <div>
                                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:4px;">
                                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);">
                                        <span class="num tnum" style="color:var(--alg-ink-4);font-size:10.5px;letter-spacing:.04em;margin-right:8px;">{{ strtoupper($c->code) }}</span>
                                        {{ $c->name }}
                                    </span>
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ number_format($c->users) }}</span>
                                </div>
                                <div style="height:4px;background:var(--alg-surface-2);">
                                    <div data-progress-fill style="height:100%;width:{{ round(($c->users / $maxCountry) * 100, 1) }}%;background:var(--alg-accent);"></div>
                                </div>
                            </div>
                        @empty
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-4);">Sin datos por país en este período.</p>
                        @endforelse
                    </div>
                </div>
            @else
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
                    <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:500;color:var(--alg-ink);margin:0 0 14px;letter-spacing:-0.005em;">Engagement</h3>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);margin:-10px 0 14px;letter-spacing:.04em;">Promedios del período</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                        <div>
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);text-transform:uppercase;letter-spacing:.14em;margin:0 0 6px;">Tasa de rebote</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.02em;line-height:1;font-variant-numeric:tabular-nums;margin:0;">{{ number_format((float)$totals->bounce_rate, 1) }}<span style="font-size:18px;color:var(--alg-ink-3);">%</span></p>
                        </div>
                        <div>
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);text-transform:uppercase;letter-spacing:.14em;margin:0 0 6px;">Páginas / sesión</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:var(--alg-ink);letter-spacing:-0.02em;line-height:1;font-variant-numeric:tabular-nums;margin:0;">{{ $totals->sessions > 0 ? number_format($totals->page_views / $totals->sessions, 1) : '0.0' }}</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>
</x-filament-panels::page>
