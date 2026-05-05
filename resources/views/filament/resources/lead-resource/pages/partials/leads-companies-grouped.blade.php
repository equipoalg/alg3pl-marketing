{{-- Empresas view — leads agrupados por la columna freetext `company`.
     Expects: $companies (Collection of arrays) — each: [name, count, latest, leads, statuses, countries, value]
     Click on row → navega a ?view=contacts&q={empresa} para ver las personas. --}}
@php
    use App\Filament\Resources\LeadResource\Pages\ListLeads;
    $statusColorMap = [
        'new'         => 'var(--alg-accent)',
        'contacted'   => 'var(--alg-ink-3)',
        'qualified'   => 'var(--alg-pos)',
        'proposal'    => 'var(--alg-warn)',
        'negotiation' => 'var(--alg-warn)',
        'won'         => 'var(--alg-pos)',
        'lost'        => 'var(--alg-neg)',
    ];
@endphp

<div style="flex:1;overflow-y:auto;background:var(--alg-bg);">
    @if($companies->isEmpty())
        <div style="padding:64px 20px;text-align:center;">
            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-3);margin:0 0 6px;">Sin empresas para mostrar</p>
            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá filtros o cambiá el rango.</p>
        </div>
    @else
        <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;background:var(--alg-surface);">
            <thead>
                <tr style="background:var(--alg-bg);position:sticky;top:0;z-index:1;">
                    <th style="text-align:left;padding:9px 16px 9px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Empresa</th>
                    <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);"># Leads</th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Distribución por estado</th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Países</th>
                    <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Valor estimado</th>
                    <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Último</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $c)
                    @php
                        $av = ListLeads::avatarFor(null, $c['name']);
                        $totalForBar = max(1, $c['count']);
                        // Build a search URL: drilling into a company switches to contacts view + sets ?q=
                        $drillHref = '/admin/leads?view=contacts&q=' . urlencode($c['name']);
                    @endphp
                    <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;"
                        onmouseover="this.style.background='var(--alg-surface-2)'"
                        onmouseout="this.style.background='transparent'"
                        onclick="window.location='{{ $drillHref }}'">
                        {{-- Empresa --}}
                        <td style="padding:13px 16px 13px 18px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:4px;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;flex-shrink:0;">{{ $av['initials'] }}</span>
                                <div style="font-size:13.5px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;">{{ $c['name'] }}</div>
                            </div>
                        </td>
                        {{-- Count --}}
                        <td style="padding:13px 12px;text-align:right;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;">
                            {{ $c['count'] }}
                        </td>
                        {{-- Status breakdown — mini stacked bar --}}
                        <td style="padding:13px 12px;">
                            <div style="display:flex;gap:1px;height:6px;border-radius:2px;overflow:hidden;background:var(--alg-line);max-width:160px;">
                                @foreach($c['statuses'] as $st => $n)
                                    @php $w = round(($n / $totalForBar) * 100, 1); @endphp
                                    <div title="{{ $st }}: {{ $n }}"
                                         style="width:{{ $w }}%;background:{{ $statusColorMap[$st] ?? 'var(--alg-ink-4)' }};"></div>
                                @endforeach
                            </div>
                            <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($c['statuses'] as $st => $n)
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;color:var(--alg-ink-4);letter-spacing:.04em;text-transform:uppercase;">
                                        <span style="display:inline-block;width:5px;height:5px;border-radius:50%;background:{{ $statusColorMap[$st] ?? 'var(--alg-ink-4)' }};vertical-align:middle;margin-right:3px;"></span>{{ $st }} {{ $n }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        {{-- Countries --}}
                        <td style="padding:13px 12px;">
                            @if(! empty($c['countries']))
                                <div style="display:flex;gap:3px;flex-wrap:wrap;">
                                    @foreach($c['countries'] as $code)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 5px;border-radius:2px;letter-spacing:.04em;">{{ strtoupper($code) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:var(--alg-ink-5);">—</span>
                            @endif
                        </td>
                        {{-- Value estimated --}}
                        <td style="padding:13px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;">
                            @if($c['value'] > 0)
                                ${{ $c['value'] >= 1000 ? number_format($c['value'] / 1000, 1) . 'k' : number_format($c['value'], 0) }}
                            @else
                                <span style="color:var(--alg-ink-5);">—</span>
                            @endif
                        </td>
                        {{-- Latest activity --}}
                        <td style="padding:13px 18px 13px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;">
                            {{ $c['latest']?->format('d M') ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
