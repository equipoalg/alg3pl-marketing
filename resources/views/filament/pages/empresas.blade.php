<x-filament-panels::page>
    {{-- Suppress Filament page chrome — toolbar takes its place --}}
    <style>
        .fi-page > .fi-header,
        .fi-page-header { display: none !important; }
        .fi-main-ctn > .fi-page { padding-top: 0 !important; }
        .fi-main { padding-top: 0.5rem !important; }
    </style>

    @php
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

    {{-- Toolbar --}}
    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:var(--alg-font);">
        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;flex-shrink:0;">Empresas</span>
        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;flex-shrink:0;">· {{ $totalCompanies }} {{ $totalCompanies === 1 ? 'empresa' : 'empresas' }} · {{ $totalLeads }} leads</span>

        {{-- Search --}}
        <div style="position:relative;flex:1;min-width:200px;max-width:380px;">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
            </svg>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar empresa…"
                   style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
        </div>

        {{-- Status filter --}}
        <select wire:model.live="statusFilter"
                style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
            <option value="">Cualquier estado</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">Con leads {{ strtolower($label) }}</option>
            @endforeach
        </select>

        @if($search !== '' || $statusFilter !== '')
            <button type="button" wire:click="clearFilters"
                    style="padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">
                × limpiar
            </button>
        @endif

        <div style="flex:1;"></div>

        {{-- Quick link to bandeja --}}
        <a href="/admin/leads?view=contacts"
           style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:var(--alg-surface);border:1px solid var(--alg-line);color:var(--alg-ink-2);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;letter-spacing:-0.005em;border-radius:4px;flex-shrink:0;">
            Ver contactos →
        </a>
    </div>

    {{-- Body table --}}
    <div style="margin-top:10px;background:var(--alg-surface);border:1px solid var(--alg-line);overflow:hidden;font-family:var(--alg-font);">
        @if($companies->isEmpty())
            <div style="padding:64px 20px;text-align:center;">
                <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-3);margin:0 0 6px;">Sin empresas para mostrar</p>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá el filtro o buscá otra cosa.</p>
            </div>
        @else
            <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                <thead>
                    <tr style="background:var(--alg-bg);">
                        <th style="text-align:left;padding:10px 16px 10px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Empresa</th>
                        <th style="text-align:right;padding:10px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);"># Leads</th>
                        <th style="text-align:left;padding:10px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Distribución por estado</th>
                        <th style="text-align:left;padding:10px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Países</th>
                        <th style="text-align:right;padding:10px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Valor estimado</th>
                        <th style="text-align:right;padding:10px 18px 10px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Último</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $c)
                        @php
                            $av = \App\Filament\Resources\LeadResource\Pages\ListLeads::avatarFor(null, $c['name']);
                            $totalForBar = max(1, $c['count']);
                            $drillHref = '/admin/leads?view=contacts&q=' . urlencode($c['name']);
                        @endphp
                        <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;"
                            onmouseover="this.style.background='var(--alg-surface-2)'"
                            onmouseout="this.style.background='transparent'"
                            onclick="window.location='{{ $drillHref }}'">
                            {{-- Empresa: avatar + name + customer/prospect badge --}}
                            <td style="padding:13px 16px 13px 18px;">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:4px;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;flex-shrink:0;">{{ $av['initials'] }}</span>
                                    <div style="min-width:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <div style="font-size:13.5px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;">{{ $c['name'] }}</div>
                                        @if($c['has_won'])
                                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;color:var(--alg-pos);background:var(--alg-pos-soft);padding:1px 6px;border-radius:2px;letter-spacing:.06em;text-transform:uppercase;">Cliente</span>
                                        @elseif($c['has_open'])
                                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;color:var(--alg-accent);background:var(--alg-accent-soft);padding:1px 6px;border-radius:2px;letter-spacing:.06em;text-transform:uppercase;">Prospecto</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- Count --}}
                            <td style="padding:13px 12px;text-align:right;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;">
                                {{ $c['count'] }}
                            </td>
                            {{-- Status breakdown --}}
                            <td style="padding:13px 12px;">
                                <div style="display:flex;gap:1px;height:6px;border-radius:2px;overflow:hidden;background:var(--alg-line);max-width:160px;">
                                    @foreach($c['statuses'] as $st => $n)
                                        @php $w = round(($n / $totalForBar) * 100, 1); @endphp
                                        <div title="{{ $statuses[$st] ?? $st }}: {{ $n }}"
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
                            {{-- Latest --}}
                            <td style="padding:13px 18px 13px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;">
                                {{ $c['latest']?->format('d M') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-filament-panels::page>
