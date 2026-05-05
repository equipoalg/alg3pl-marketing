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

    {{-- Layout: table | slide-over (cuando hay selectedEmpresa) --}}
    <div style="margin-top:10px;display:grid;grid-template-columns:1fr {{ $selected ? '420px' : '' }};gap:10px;align-items:flex-start;">
    {{-- Body table --}}
    <div style="background:var(--alg-surface);border:1px solid var(--alg-line);overflow:hidden;font-family:var(--alg-font);">
        @if($companies->isEmpty())
            <div style="padding:64px 20px;text-align:center;">
                <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-3);margin:0 0 6px;">Sin empresas para mostrar</p>
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá el filtro o buscá otra cosa.</p>
            </div>
        @else
            <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                <thead>
                    <tr style="background:var(--alg-bg);">
                        <th style="width:30px;padding:10px 4px 10px 18px;border-bottom:1px solid var(--alg-line);"></th>
                        <th style="text-align:left;padding:10px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Empresa</th>
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
                            $isExpanded = in_array($c['name'], $expandedEmpresas ?? [], true);
                            $isSelected = $selected && $selected['name'] === $c['name'];
                            $rowBg = $isSelected ? 'var(--alg-accent-soft)' : 'transparent';
                        @endphp
                        <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                            onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                            onmouseout="this.style.background='{{ $rowBg }}'"
                            @if($isSelected) data-locked="1" @endif>
                            {{-- Chevron expand toggle (no abre slide-over, solo expande inline) --}}
                            <td style="padding:13px 4px 13px 18px;text-align:center;" onclick="event.stopPropagation()">
                                <button type="button" wire:click="toggleExpand('{{ addslashes($c['name']) }}')"
                                        title="{{ $isExpanded ? 'Contraer' : 'Expandir contactos' }}"
                                        style="border:none;background:transparent;color:var(--alg-ink-3);cursor:pointer;padding:2px 4px;font-size:10px;line-height:1;transform:rotate({{ $isExpanded ? '90' : '0' }}deg);transition:transform 120ms;">▸</button>
                            </td>
                            {{-- Empresa: avatar + name + customer/prospect badge --}}
                            <td style="padding:13px 12px;" wire:click="selectEmpresa('{{ addslashes($c['name']) }}')">
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
                            <td style="padding:13px 12px;text-align:right;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;" wire:click="selectEmpresa('{{ addslashes($c['name']) }}')">
                                {{ $c['count'] }}
                            </td>
                            {{-- Status breakdown --}}
                            <td style="padding:13px 12px;" wire:click="selectEmpresa('{{ addslashes($c['name']) }}')">
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
                            <td style="padding:13px 12px;" wire:click="selectEmpresa('{{ addslashes($c['name']) }}')">
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
                            <td style="padding:13px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;" wire:click="selectEmpresa('{{ addslashes($c['name']) }}')">
                                @if($c['value'] > 0)
                                    ${{ $c['value'] >= 1000 ? number_format($c['value'] / 1000, 1) . 'k' : number_format($c['value'], 0) }}
                                @else
                                    <span style="color:var(--alg-ink-5);">—</span>
                                @endif
                            </td>
                            {{-- Latest --}}
                            <td style="padding:13px 18px 13px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;" wire:click="selectEmpresa('{{ addslashes($c['name']) }}')">
                                {{ $c['latest']?->format('d M') ?? '—' }}
                            </td>
                        </tr>

                        {{-- Expand-row inline: lista de leads dentro de la empresa --}}
                        @if($isExpanded)
                            <tr style="background:var(--alg-bg);border-bottom:1px solid var(--alg-line);">
                                <td></td>
                                <td colspan="6" style="padding:8px 18px 14px 12px;">
                                    <div style="display:flex;flex-direction:column;gap:1px;">
                                        @foreach($c['leads']->take(20) as $lead)
                                            @php
                                                $statusPillBg = match($lead->status) {
                                                    'won'         => 'var(--alg-pos-soft)',
                                                    'lost'        => 'var(--alg-neg-soft)',
                                                    'qualified'   => 'var(--alg-pos-soft)',
                                                    'proposal','negotiation' => 'var(--alg-warn-soft)',
                                                    'new'         => 'var(--alg-accent-soft)',
                                                    default       => 'var(--alg-surface-2)',
                                                };
                                                $statusPillFg = match($lead->status) {
                                                    'won','qualified' => 'var(--alg-pos)',
                                                    'lost'        => 'var(--alg-neg)',
                                                    'proposal','negotiation' => 'var(--alg-warn)',
                                                    'new'         => 'var(--alg-accent)',
                                                    default       => 'var(--alg-ink-3)',
                                                };
                                            @endphp
                                            <a href="/admin/leads?view=contacts&selected={{ $lead->id }}"
                                               style="display:grid;grid-template-columns:1fr auto auto auto;gap:12px;align-items:center;padding:6px 10px;text-decoration:none;color:inherit;border-radius:3px;"
                                               onmouseover="this.style.background='var(--alg-surface-2)'"
                                               onmouseout="this.style.background='transparent'">
                                                <span style="font-size:12px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;">{{ $lead->name }}</span>
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);">{{ $lead->email ?: '—' }}</span>
                                                <span style="display:inline-block;font-size:9px;font-weight:500;color:{{ $statusPillFg }};background:{{ $statusPillBg }};padding:1px 6px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">{{ $statuses[$lead->status] ?? $lead->status }}</span>
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);letter-spacing:.04em;">{{ $lead->created_at->format('d M') }}</span>
                                            </a>
                                        @endforeach
                                        @if($c['leads']->count() > 20)
                                            <a href="{{ $drillHref }}" style="padding:6px 10px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);text-decoration:none;letter-spacing:.04em;">+ {{ $c['leads']->count() - 20 }} más en bandeja →</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Slide-over derecho — detalle de la empresa seleccionada --}}
    @if($selected)
        @php
            $av = \App\Filament\Resources\LeadResource\Pages\ListLeads::avatarFor(null, $selected['name']);
            // Si la empresa ya tiene un Client matching, conseguir su id para el badge linkable
            $existingClient = \App\Models\Client::where('company_name', $selected['name'])->first();
        @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;font-family:var(--alg-font);position:sticky;top:14px;">
            {{-- Header --}}
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:4px;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;flex-shrink:0;">{{ $av['initials'] }}</span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected['name'] }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">{{ $selected['count'] }} {{ $selected['count'] === 1 ? 'lead' : 'leads' }} · último {{ $selected['latest']?->diffForHumans() ?? '—' }}</div>
                    </div>
                </div>
                <button type="button" wire:click="closeEmpresa" title="Cerrar" style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            {{-- Body --}}
            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                {{-- KPI grid --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
                    <div style="padding:10px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:4px;">
                        <p style="margin:0 0 3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Total leads</p>
                        <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ $selected['count'] }}</p>
                    </div>
                    <div style="padding:10px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:4px;">
                        <p style="margin:0 0 3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Valor estimado</p>
                        <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">${{ $selected['value'] >= 1000 ? number_format($selected['value']/1000, 1) . 'k' : number_format($selected['value'], 0) }}</p>
                    </div>
                </div>

                {{-- Status breakdown --}}
                <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Distribución</p>
                <div style="display:flex;flex-direction:column;gap:5px;margin-bottom:18px;">
                    @foreach($selected['statuses'] as $st => $n)
                        @php
                            $stColor = match($st) {
                                'won','qualified' => 'var(--alg-pos)',
                                'lost'            => 'var(--alg-neg)',
                                'proposal','negotiation' => 'var(--alg-warn)',
                                'new'             => 'var(--alg-accent)',
                                default           => 'var(--alg-ink-3)',
                            };
                            $pct = round(($n / max(1, $selected['count'])) * 100);
                        @endphp
                        <div style="display:grid;grid-template-columns:80px 1fr 30px;align-items:center;gap:8px;font-size:11.5px;">
                            <span style="color:var(--alg-ink-3);text-transform:uppercase;letter-spacing:.04em;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;">{{ $st }}</span>
                            <div style="height:6px;background:var(--alg-line);border-radius:2px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;height:100%;background:{{ $stColor }};"></div>
                            </div>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-2);text-align:right;font-variant-numeric:tabular-nums;">{{ $n }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Países --}}
                @if(! empty($selected['countries']))
                    <p style="margin:0 0 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Países</p>
                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:18px;">
                        @foreach($selected['countries'] as $code)
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:2px 7px;border-radius:2px;letter-spacing:.04em;">{{ strtoupper($code) }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Lista de leads (todos) --}}
                <p style="margin:0 0 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Contactos</p>
                <div style="display:flex;flex-direction:column;gap:1px;">
                    @foreach($selected['leads']->sortByDesc('created_at')->take(50) as $lead)
                        <a href="/admin/leads?view=contacts&selected={{ $lead->id }}"
                           style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 8px;text-decoration:none;color:inherit;border-radius:3px;"
                           onmouseover="this.style.background='var(--alg-surface-2)'"
                           onmouseout="this.style.background='transparent'">
                            <div style="min-width:0;flex:1;">
                                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;">{{ $lead->name }}</div>
                                <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->email ?: '—' }}</div>
                            </div>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.04em;">{{ $lead->status }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Footer actions --}}
            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
                <a href="/admin/leads?view=contacts&q={{ urlencode($selected['name']) }}"
                   style="padding:7px 12px;background:var(--alg-ink);color:#FFFFFF;border:none;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                    Ver contactos →
                </a>

                @if($existingClient)
                    {{-- Ya es Cliente: link directo --}}
                    <a href="{{ \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $existingClient]) }}"
                       title="Ver Cliente existente"
                       style="padding:7px 12px;background:var(--alg-pos-soft);color:var(--alg-pos);border:1px solid var(--alg-pos);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
                        🏆 Ya es Cliente →
                    </a>
                @else
                    <button type="button" wire:click="convertEmpresaToClient('{{ addslashes($selected['name']) }}')"
                            wire:confirm="¿Crear Cliente nuevo con este nombre? Se usará el lead más reciente como contacto principal."
                            title="Convertir empresa en Cliente (post-venta)"
                            style="padding:7px 12px;background:var(--alg-surface);color:var(--alg-ink-2);border:1px solid var(--alg-line);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
                        + Convertir a Cliente
                    </button>
                @endif
            </div>
        </aside>
    @endif

    </div> {{-- end layout grid --}}
</x-filament-panels::page>
