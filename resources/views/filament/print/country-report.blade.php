<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALG3PL — {{ $record->country?->name ?? 'Report' }} / {{ $record->period }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #ffffff;
            color: #0F172A;
            font-size: 13px;
            line-height: 1.5;
        }

        /* ---- PRINT BUTTON BAR (screen only) ---- */
        .print-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1E3A8A;
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .print-bar .brand {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .print-bar .actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-print {
            background: #ffffff;
            color: #1E3A8A;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-close {
            background: transparent;
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        /* ---- PAGE WRAPPER ---- */
        .page-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 76px 40px 60px; /* top pad = print-bar height */
        }

        /* ---- REPORT HEADER ---- */
        .report-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 24px;
            border-bottom: 2px solid #1E3A8A;
            margin-bottom: 28px;
        }
        .report-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-mark {
            width: 44px;
            height: 44px;
            background: #1E3A8A;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }
        .logo-text .company {
            font-size: 18px;
            font-weight: 700;
            color: #1E3A8A;
            line-height: 1.2;
        }
        .logo-text .tagline {
            font-size: 11px;
            color: #6B7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .report-meta {
            text-align: right;
        }
        .report-meta .report-title {
            font-size: 20px;
            font-weight: 700;
            color: #1E3A8A;
            line-height: 1.2;
        }
        .report-meta .report-sub {
            font-size: 12px;
            color: #6B7280;
            margin-top: 4px;
        }
        .report-meta .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 8px;
        }
        .badge-seo { background: #EFF6FF; color: #1D4ED8; }
        .badge-marketing { background: #F0FDF4; color: #166534; }
        .badge-sales { background: #FFF7ED; color: #9A3412; }

        /* ---- SECTION HEADINGS ---- */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #1E3A8A;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 8px;
            margin-bottom: 16px;
            margin-top: 28px;
        }

        /* ---- SUMMARY ---- */
        .summary-box {
            background: #FFFFFF;
            border-left: 4px solid #1E3A8A;
            border-radius: 0 6px 6px 0;
            padding: 14px 18px;
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
        }

        /* ---- KPI GRID ---- */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 4px;
        }
        .kpi-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 14px 16px;
        }
        .kpi-card .kpi-label {
            font-size: 11px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }
        .kpi-card .kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #1E3A8A;
            line-height: 1;
        }

        /* ---- FINDINGS / OPPORTUNITIES TABLE ---- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table th {
            background: #1E3A8A;
            color: #fff;
            text-align: left;
            padding: 9px 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .data-table th:first-child { border-radius: 6px 0 0 0; }
        .data-table th:last-child { border-radius: 0 6px 0 0; }
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #E2E8F0;
            color: #374151;
            vertical-align: top;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-table tr:nth-child(even) td {
            background: #FFFFFF;
        }
        .impact-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .impact-high { background: #FEF2F2; color: #991B1B; }
        .impact-medium { background: #FFFBEB; color: #92400E; }
        .impact-low { background: #F0FDF4; color: #166534; }

        /* ---- FOOTER ---- */
        .report-footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #9CA3AF;
            font-size: 11px;
        }

        /* ---- PRINT STYLES ---- */
        @media print {
            .print-bar { display: none !important; }
            .page-wrapper { padding-top: 20px; margin: 0; max-width: 100%; }
            body { font-size: 12px; }
            .kpi-grid { grid-template-columns: repeat(3, 1fr); }
            .data-table { page-break-inside: avoid; }
            .section-title { page-break-after: avoid; }
            @page {
                margin: 1.5cm 2cm;
                size: A4 portrait;
            }
        }

        @media (max-width: 640px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .report-header { flex-direction: column; gap: 16px; }
            .report-meta { text-align: left; }
        }
    </style>
</head>
<body>

{{-- PRINT BAR (hidden on print) --}}
<div class="print-bar">
    <span class="brand">ALG3PL Intelligence — Vista de Impresión</span>
    <div class="actions">
        <a href="javascript:history.back()" class="btn-close">← Volver</a>
        <button class="btn-print" onclick="window.print()">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75H5.25A2.25 2.25 0 013 13.5V9a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 9v4.5a2.25 2.25 0 01-2.25 2.25h-1.5M6.75 15.75v3.75a.75.75 0 00.75.75h9a.75.75 0 00.75-.75v-3.75M6.75 15.75h10.5"/>
            </svg>
            Imprimir / Guardar PDF
        </button>
    </div>
</div>

<div class="page-wrapper">

    {{-- REPORT HEADER --}}
    <div class="report-header">
        <div class="report-logo">
            <div class="logo-mark">A3</div>
            <div class="logo-text">
                <div class="company">ALG3PL</div>
                <div class="tagline">Intelligence Platform</div>
            </div>
        </div>
        <div class="report-meta">
            <div class="report-title">{{ $record->country?->name ?? 'Global' }}</div>
            <div class="report-sub">Período: {{ $record->period }}</div>
            @if($record->type)
            <span class="badge badge-{{ $record->type }}">
                {{ ['seo' => 'SEO & Analytics', 'marketing' => 'Marketing', 'sales' => 'Sales'][$record->type] ?? $record->type }}
            </span>
            @endif
            <div style="font-size:11px;color:#9CA3AF;margin-top:6px;">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- SUMMARY --}}
    @if($record->summary)
    <div class="section-title">Resumen Ejecutivo</div>
    <div class="summary-box">{{ $record->summary }}</div>
    @endif

    {{-- KPIs --}}
    @if($record->kpis && count($record->kpis) > 0)
    <div class="section-title">Indicadores Clave (KPIs)</div>
    <div class="kpi-grid">
        @foreach($record->kpis as $key => $value)
        <div class="kpi-card">
            <div class="kpi-label">{{ $key }}</div>
            <div class="kpi-value">{{ $value }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- FINDINGS --}}
    @if($record->findings && count($record->findings) > 0)
    <div class="section-title">Hallazgos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30%;">Hallazgo</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->findings as $finding)
            <tr>
                <td style="font-weight:600;color:#0F172A;">{{ $finding['title'] ?? '—' }}</td>
                <td>{{ $finding['detail'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- OPPORTUNITIES --}}
    @if($record->opportunities && count($record->opportunities) > 0)
    <div class="section-title">Oportunidades</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30%;">Oportunidad</th>
                <th>Detalle</th>
                <th style="width:100px;">Impacto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->opportunities as $opp)
            <tr>
                <td style="font-weight:600;color:#0F172A;">{{ $opp['title'] ?? '—' }}</td>
                <td>{{ $opp['detail'] ?? '—' }}</td>
                <td>
                    @php
                        $impact = $opp['impact'] ?? null;
                        $impactClass = match($impact) {
                            '+++' => 'impact-high',
                            '++' => 'impact-medium',
                            '+' => 'impact-low',
                            default => '',
                        };
                        $impactLabel = match($impact) {
                            '+++' => 'Alto',
                            '++' => 'Medio',
                            '+' => 'Bajo',
                            default => '—',
                        };
                    @endphp
                    @if($impactClass)
                    <span class="impact-badge {{ $impactClass }}">{{ $impactLabel }}</span>
                    @else
                    {{ $impactLabel }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- GA4 DATA --}}
    @if($record->ga4_data && count($record->ga4_data) > 0)
    <div class="section-title">Datos Google Analytics 4</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:40%;">Métrica</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->ga4_data as $key => $value)
            <tr>
                <td style="font-weight:500;">{{ $key }}</td>
                <td>{{ $value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- GSC DATA --}}
    @if($record->gsc_data && count($record->gsc_data) > 0)
    <div class="section-title">Datos Google Search Console</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:40%;">Métrica</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->gsc_data as $key => $value)
            <tr>
                <td style="font-weight:500;">{{ $key }}</td>
                <td>{{ $value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- FOOTER --}}
    <div class="report-footer">
        <span>ALG3PL Intelligence Platform &mdash; marketing.alg3pl.com</span>
        <span>{{ $record->country?->name ?? 'Global' }} &middot; {{ $record->period }}</span>
    </div>

</div>

<script>
// Auto-open print dialog if ?print=1 is in URL
if (new URLSearchParams(window.location.search).get('print') === '1') {
    window.addEventListener('load', function() {
        setTimeout(function() { window.print(); }, 300);
    });
}
</script>

</body>
</html>
