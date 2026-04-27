@php
    $country = $record->country;
    $typeLabels = ['seo' => 'SEO & Analytics', 'marketing' => 'Marketing', 'sales' => 'Sales'];
    $typeColors = [
        'seo'       => 'var(--alg-accent)',
        'marketing' => 'var(--alg-accent-2)',
        'sales'     => 'var(--alg-pos)',
    ];
    $typeLabel = $typeLabels[$record->type] ?? $record->type;
    $typeColor = $typeColors[$record->type] ?? 'var(--alg-ink-3)';
    $kpis = $record->kpis ?? [];
    $findings = $record->findings ?? [];
    $opportunities = $record->opportunities ?? [];
    $ga4 = $record->ga4_data ?? [];
    $gsc = $record->gsc_data ?? [];
    $impactColors = [
        '+'   => 'var(--alg-ink-3)',
        '++'  => 'var(--alg-warn)',
        '+++' => 'var(--alg-neg)',
    ];
    $impactBgs = [
        '+'   => 'var(--alg-surface-2)',
        '++'  => 'var(--alg-warn-soft)',
        '+++' => 'var(--alg-neg-soft)',
    ];
    $impactLabels = ['+' => 'BAJO', '++' => 'MEDIO', '+++' => 'ALTO'];
@endphp

<div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;color:var(--alg-ink);background:var(--alg-surface);">

    {{-- ── REPORT HEADER ── --}}
    <div style="
        border-bottom:1px solid var(--alg-line);
        padding:22px 24px 18px;
        display:flex;align-items:flex-start;justify-content:space-between;gap:24px;
    ">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="
                    font-family:ui-monospace,'SF Mono',Menlo,monospace;
                    font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                    color:var(--alg-ink-3);
                ">Informe de País</span>
                <span style="
                    font-family:ui-monospace,'SF Mono',Menlo,monospace;
                    font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;
                    padding:2px 8px;
                    background:var(--alg-surface-2);color:{{ $typeColor }};
                    border:1px solid var(--alg-line);
                ">{{ $typeLabel }}</span>
            </div>
            <h2 style="
                font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                font-size:22px;font-weight:600;color:var(--alg-ink);
                margin:0 0 4px;letter-spacing:-0.02em;
            ">
                {{ $country?->name ?? '—' }}
            </h2>
            <p style="
                font-family:ui-monospace,'SF Mono',Menlo,monospace;
                font-size:11px;color:var(--alg-ink-3);margin:0;letter-spacing:.04em;
            ">Período: {{ $record->period }}</p>
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <p style="
                font-family:ui-monospace,'SF Mono',Menlo,monospace;
                font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                color:var(--alg-ink-3);margin:0 0 6px;
            ">Generado</p>
            <p style="
                font-family:ui-monospace,'SF Mono',Menlo,monospace;
                font-size:12px;font-weight:500;color:var(--alg-ink-2);margin:0;letter-spacing:.04em;
            ">
                {{ $record->updated_at?->format('d M Y') ?? '—' }}
            </p>
            @if($country)
            <div style="
                margin-top:8px;display:inline-flex;align-items:center;gap:6px;
                padding:4px 10px;
                background:var(--alg-surface-2);border:1px solid var(--alg-line);
            ">
                <span style="
                    font-family:ui-monospace,'SF Mono',Menlo,monospace;
                    font-size:11px;font-weight:600;color:var(--alg-ink-2);
                    text-transform:uppercase;letter-spacing:.08em;
                ">
                    {{ strtoupper($country->code) }}
                </span>
            </div>
            @endif
        </div>
    </div>

    <div style="padding:22px 24px;display:flex;flex-direction:column;gap:22px;">

        {{-- ── EXECUTIVE SUMMARY ── --}}
        @if($record->summary)
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <div style="width:3px;height:12px;background:var(--alg-accent);flex-shrink:0;"></div>
                <span style="
                    font-family:ui-monospace,'SF Mono',Menlo,monospace;
                    font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                    color:var(--alg-ink-3);
                ">
                    Resumen Ejecutivo
                </span>
            </div>
            <p style="
                font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                font-size:14px;line-height:1.65;color:var(--alg-ink-2);
                margin:0;padding:14px 16px;
                background:var(--alg-surface-2);
                border-left:2px solid var(--alg-accent);
                letter-spacing:-0.005em;
            ">{{ $record->summary }}</p>
        </div>
        @endif

        {{-- ── KPIs ── --}}
        @if(!empty($kpis))
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div style="width:3px;height:12px;background:var(--alg-pos);flex-shrink:0;"></div>
                <span style="
                    font-family:ui-monospace,'SF Mono',Menlo,monospace;
                    font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                    color:var(--alg-ink-3);
                ">
                    Indicadores Clave (KPIs)
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                @foreach($kpis as $metric => $value)
                <div style="
                    background:var(--alg-surface);
                    border:1px solid var(--alg-line);
                    padding:14px 16px;
                ">
                    <p style="
                        font-family:ui-monospace,'SF Mono',Menlo,monospace;
                        font-size:10px;color:var(--alg-ink-3);margin:0 0 6px;
                        text-transform:uppercase;letter-spacing:.14em;font-weight:500;
                    ">
                        {{ $metric }}
                    </p>
                    <p style="
                        font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                        font-size:22px;font-weight:400;color:var(--alg-ink);
                        margin:0;letter-spacing:-0.02em;font-variant-numeric:tabular-nums;
                    ">
                        {{ $value }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── FINDINGS + OPPORTUNITIES (2 columns) ── --}}
        @if(!empty($findings) || !empty($opportunities))
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            {{-- Findings --}}
            @if(!empty($findings))
            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div style="width:3px;height:12px;background:var(--alg-warn);flex-shrink:0;"></div>
                    <span style="
                        font-family:ui-monospace,'SF Mono',Menlo,monospace;
                        font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                        color:var(--alg-ink-3);
                    ">
                        Hallazgos ({{ count($findings) }})
                    </span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($findings as $i => $finding)
                    <div style="
                        background:var(--alg-surface);
                        border:1px solid var(--alg-line);
                        padding:12px 14px;
                    ">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <span style="
                                flex-shrink:0;width:20px;height:20px;
                                background:var(--alg-warn-soft);color:var(--alg-warn);
                                font-family:ui-monospace,'SF Mono',Menlo,monospace;
                                font-size:10px;font-weight:600;
                                display:flex;align-items:center;justify-content:center;
                            ">{{ $i + 1 }}</span>
                            <div style="flex:1;min-width:0;">
                                <p style="
                                    font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                                    font-size:13px;font-weight:500;color:var(--alg-ink);
                                    margin:0 0 4px;line-height:1.4;letter-spacing:-0.005em;
                                ">
                                    {{ $finding['title'] ?? '' }}
                                </p>
                                @if(!empty($finding['detail']))
                                <p style="
                                    font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                                    font-size:12px;color:var(--alg-ink-3);margin:0;line-height:1.55;
                                ">
                                    {{ $finding['detail'] }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Opportunities --}}
            @if(!empty($opportunities))
            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div style="width:3px;height:12px;background:var(--alg-accent-2);flex-shrink:0;"></div>
                    <span style="
                        font-family:ui-monospace,'SF Mono',Menlo,monospace;
                        font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                        color:var(--alg-ink-3);
                    ">
                        Oportunidades ({{ count($opportunities) }})
                    </span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($opportunities as $i => $opp)
                    @php
                        $impact = $opp['impact'] ?? '';
                        $impColor = $impactColors[$impact] ?? 'var(--alg-ink-3)';
                        $impBg = $impactBgs[$impact] ?? 'var(--alg-surface-2)';
                        $impLabel = $impactLabels[$impact] ?? '';
                    @endphp
                    <div style="
                        background:var(--alg-surface);
                        border:1px solid var(--alg-line);
                        padding:12px 14px;
                    ">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                            <div style="flex:1;min-width:0;">
                                <p style="
                                    font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                                    font-size:13px;font-weight:500;color:var(--alg-ink);
                                    margin:0 0 4px;line-height:1.4;letter-spacing:-0.005em;
                                ">
                                    {{ $opp['title'] ?? '' }}
                                </p>
                                @if(!empty($opp['detail']))
                                <p style="
                                    font-family:'Geist',ui-sans-serif,system-ui,sans-serif;
                                    font-size:12px;color:var(--alg-ink-3);margin:0;line-height:1.55;
                                ">
                                    {{ $opp['detail'] }}
                                </p>
                                @endif
                            </div>
                            @if($impLabel)
                            <span style="
                                flex-shrink:0;
                                font-family:ui-monospace,'SF Mono',Menlo,monospace;
                                font-size:9px;font-weight:600;
                                text-transform:uppercase;letter-spacing:.08em;
                                padding:3px 8px;white-space:nowrap;
                                background:{{ $impBg }};color:{{ $impColor }};
                            ">{{ $impLabel }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
        @endif

        {{-- ── RAW DATA (collapsed-style) ── --}}
        @if(!empty($ga4) || !empty($gsc))
        <details style="
            background:var(--alg-surface);
            border:1px solid var(--alg-line);
            overflow:hidden;
        ">
            <summary style="
                padding:12px 16px;cursor:pointer;
                font-family:ui-monospace,'SF Mono',Menlo,monospace;
                font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;
                color:var(--alg-ink-3);
                display:flex;align-items:center;gap:8px;list-style:none;
            ">
                <div style="width:3px;height:12px;background:var(--alg-ink-4);flex-shrink:0;"></div>
                Datos en bruto (GA4 + GSC)
            </summary>
            <div style="padding:0 16px 16px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                @if(!empty($ga4))
                <div>
                    <p style="
                        font-family:ui-monospace,'SF Mono',Menlo,monospace;
                        font-size:10px;font-weight:500;color:var(--alg-ink-3);
                        text-transform:uppercase;letter-spacing:.14em;
                        margin:0 0 8px;padding-bottom:6px;border-bottom:1px solid var(--alg-line);
                    ">GA4</p>
                    @foreach($ga4 as $k => $v)
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--alg-line);">
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);letter-spacing:.04em;">{{ $k }}</span>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;font-weight:500;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                @if(!empty($gsc))
                <div>
                    <p style="
                        font-family:ui-monospace,'SF Mono',Menlo,monospace;
                        font-size:10px;font-weight:500;color:var(--alg-ink-3);
                        text-transform:uppercase;letter-spacing:.14em;
                        margin:0 0 8px;padding-bottom:6px;border-bottom:1px solid var(--alg-line);
                    ">GSC</p>
                    @foreach($gsc as $k => $v)
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--alg-line);">
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);letter-spacing:.04em;">{{ $k }}</span>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;font-weight:500;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </details>
        @endif

    </div>
</div>
