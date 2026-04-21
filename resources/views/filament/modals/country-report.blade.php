@php
    $country = $record->country;
    $typeLabels = ['seo' => 'SEO & Analytics', 'marketing' => 'Marketing', 'sales' => 'Sales'];
    $typeColors = ['seo' => '#3B82F6', 'marketing' => '#8B5CF6', 'sales' => '#10B981'];
    $typeLabel = $typeLabels[$record->type] ?? $record->type;
    $typeColor = $typeColors[$record->type] ?? '#6B7280';
    $kpis = $record->kpis ?? [];
    $findings = $record->findings ?? [];
    $opportunities = $record->opportunities ?? [];
    $ga4 = $record->ga4_data ?? [];
    $gsc = $record->gsc_data ?? [];
    $impactColors = ['+' => '#6B7280', '++' => '#F59E0B', '+++' => '#EF4444'];
    $impactLabels = ['+' => 'BAJO', '++' => 'MEDIO', '+++' => 'ALTO'];
@endphp

<div style="font-family:'Inter',sans-serif;color:#E5E7EB;background:#111827;border-radius:8px;overflow:hidden;">

    {{-- ── REPORT HEADER ── --}}
    <div style="
        background:linear-gradient(135deg,rgba(59,130,246,0.15) 0%,rgba(17,24,39,1) 60%);
        border-bottom:1px solid rgba(255,255,255,0.07);
        padding:24px 28px 20px;
        display:flex;align-items:flex-start;justify-content:space-between;gap:20px;
    ">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="
                    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;
                    color:#6B7280;
                ">Informe de País</span>
                <span style="
                    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                    padding:2px 8px;border-radius:4px;
                    background:{{ $typeColor }}22;color:{{ $typeColor }};border:1px solid {{ $typeColor }}44;
                ">{{ $typeLabel }}</span>
            </div>
            <h2 style="font-size:26px;font-weight:800;color:#F9FAFB;margin:0 0 4px;letter-spacing:-0.02em;">
                {{ $country?->name ?? '—' }}
            </h2>
            <p style="font-size:13px;color:#9CA3AF;margin:0;">Período: {{ $record->period }}</p>
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <p style="font-size:10px;color:#4B5563;margin:0 0 3px;text-transform:uppercase;letter-spacing:.08em;">Generado</p>
            <p style="font-size:13px;font-weight:600;color:#6B7280;margin:0;">
                {{ $record->updated_at?->format('d M Y') ?? '—' }}
            </p>
            @if($country)
            <div style="
                margin-top:8px;display:inline-flex;align-items:center;gap:6px;
                padding:4px 10px;border-radius:6px;
                background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);
            ">
                <span style="font-size:11px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.06em;">
                    {{ strtoupper($country->code) }}
                </span>
            </div>
            @endif
        </div>
    </div>

    <div style="padding:24px 28px;display:flex;flex-direction:column;gap:22px;">

        {{-- ── EXECUTIVE SUMMARY ── --}}
        @if($record->summary)
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <div style="width:3px;height:14px;background:#3B82F6;border-radius:2px;flex-shrink:0;"></div>
                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#6B7280;">
                    Resumen Ejecutivo
                </span>
            </div>
            <p style="
                font-size:14px;line-height:1.65;color:#D1D5DB;
                margin:0;padding:14px 16px;
                background:rgba(255,255,255,0.02);border-radius:8px;
                border-left:3px solid rgba(59,130,246,0.4);
            ">{{ $record->summary }}</p>
        </div>
        @endif

        {{-- ── KPIs ── --}}
        @if(!empty($kpis))
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div style="width:3px;height:14px;background:#10B981;border-radius:2px;flex-shrink:0;"></div>
                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#6B7280;">
                    Indicadores Clave (KPIs)
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                @foreach($kpis as $metric => $value)
                <div style="
                    background:rgba(255,255,255,0.03);
                    border:1px solid rgba(255,255,255,0.07);
                    border-radius:8px;padding:12px 14px;
                ">
                    <p style="font-size:10px;color:#6B7280;margin:0 0 4px;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">
                        {{ $metric }}
                    </p>
                    <p style="font-size:20px;font-weight:800;color:#F9FAFB;margin:0;letter-spacing:-0.01em;">
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
                    <div style="width:3px;height:14px;background:#F59E0B;border-radius:2px;flex-shrink:0;"></div>
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#6B7280;">
                        Hallazgos ({{ count($findings) }})
                    </span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($findings as $i => $finding)
                    <div style="
                        background:rgba(255,255,255,0.025);
                        border:1px solid rgba(255,255,255,0.06);
                        border-radius:8px;padding:12px 14px;
                    ">
                        <div style="display:flex;align-items:flex-start;gap:8px;">
                            <span style="
                                flex-shrink:0;width:18px;height:18px;border-radius:4px;
                                background:rgba(245,158,11,0.15);color:#F59E0B;
                                font-size:9px;font-weight:800;
                                display:flex;align-items:center;justify-content:center;
                            ">{{ $i + 1 }}</span>
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:13px;font-weight:600;color:#F3F4F6;margin:0 0 4px;line-height:1.3;">
                                    {{ $finding['title'] ?? '' }}
                                </p>
                                @if(!empty($finding['detail']))
                                <p style="font-size:12px;color:#9CA3AF;margin:0;line-height:1.5;">
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
                    <div style="width:3px;height:14px;background:#8B5CF6;border-radius:2px;flex-shrink:0;"></div>
                    <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#6B7280;">
                        Oportunidades ({{ count($opportunities) }})
                    </span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($opportunities as $i => $opp)
                    @php
                        $impact = $opp['impact'] ?? '';
                        $impColor = $impactColors[$impact] ?? '#6B7280';
                        $impLabel = $impactLabels[$impact] ?? '';
                    @endphp
                    <div style="
                        background:rgba(139,92,246,0.04);
                        border:1px solid rgba(139,92,246,0.12);
                        border-radius:8px;padding:12px 14px;
                    ">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:13px;font-weight:600;color:#F3F4F6;margin:0 0 4px;line-height:1.3;">
                                    {{ $opp['title'] ?? '' }}
                                </p>
                                @if(!empty($opp['detail']))
                                <p style="font-size:12px;color:#9CA3AF;margin:0;line-height:1.5;">
                                    {{ $opp['detail'] }}
                                </p>
                                @endif
                            </div>
                            @if($impLabel)
                            <span style="
                                flex-shrink:0;font-size:9px;font-weight:800;
                                text-transform:uppercase;letter-spacing:.06em;
                                padding:2px 7px;border-radius:4px;white-space:nowrap;
                                background:{{ $impColor }}22;color:{{ $impColor }};
                                border:1px solid {{ $impColor }}44;
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
            background:rgba(255,255,255,0.02);
            border:1px solid rgba(255,255,255,0.06);
            border-radius:8px;overflow:hidden;
        ">
            <summary style="
                padding:12px 16px;cursor:pointer;
                font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#6B7280;
                display:flex;align-items:center;gap:8px;list-style:none;
            ">
                <div style="width:3px;height:12px;background:#4B5563;border-radius:2px;flex-shrink:0;"></div>
                Datos en bruto (GA4 + GSC)
            </summary>
            <div style="padding:0 16px 16px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                @if(!empty($ga4))
                <div>
                    <p style="font-size:10px;font-weight:700;color:#6B7280;text-transform:uppercase;margin:0 0 8px;">GA4</p>
                    @foreach($ga4 as $k => $v)
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                        <span style="font-size:12px;color:#9CA3AF;">{{ $k }}</span>
                        <span style="font-size:12px;font-weight:600;color:#E5E7EB;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                @if(!empty($gsc))
                <div>
                    <p style="font-size:10px;font-weight:700;color:#6B7280;text-transform:uppercase;margin:0 0 8px;">GSC</p>
                    @foreach($gsc as $k => $v)
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                        <span style="font-size:12px;color:#9CA3AF;">{{ $k }}</span>
                        <span style="font-size:12px;font-weight:600;color:#E5E7EB;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </details>
        @endif

    </div>
</div>
