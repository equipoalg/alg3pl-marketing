<x-filament-panels::page>
    <style>
        .fi-page > .fi-header,
        .fi-page-header { display: none !important; }
        .fi-main-ctn > .fi-page { padding-top: 0 !important; }
        .fi-main { padding-top: 0.5rem !important; }
        .alg-chip {
            display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
            border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-3);
            font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;
            border-radius:4px;cursor:pointer;transition:all 100ms ease;
        }
        .alg-chip:hover { background: var(--alg-surface-2); color: var(--alg-ink); }
        .alg-chip.is-active { background: var(--alg-accent-soft); color: var(--alg-accent); border-color: var(--alg-accent); }
        @media (max-width: 900px) {
            .alg-rep-kpis { grid-template-columns: repeat(3, 1fr) !important; }
            .alg-rep-layout { grid-template-columns: 1fr !important; }
        }
    </style>

    @php
        $typeOptions = ['seo' => 'SEO & Analytics', 'marketing' => 'Marketing', 'sales' => 'Sales'];
        $hasFilters = $currentSearch !== '' || $currentType !== '' || $currentCountry !== null || $currentSort !== 'recent';
    @endphp

    <div class="alg-rep-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '460px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Reportes por país</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['total'] }}</span>

            <div style="position:relative;flex:1;min-width:220px;max-width:340px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar período o resumen…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            <select wire:model.live="countryFilter" title="País"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="">Todos los países</option>
                @foreach($countries as $co)<option value="{{ $co->id }}">{{ $co->name }} ({{ strtoupper($co->code) }})</option>@endforeach
            </select>

            <select wire:model.live="sortBy" title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="recent">↻ Recién actualizados</option>
                <option value="period_desc">↓ Período más reciente</option>
                <option value="country">A → Z por país</option>
            </select>

            <div style="flex:1;"></div>

            <a href="{{ \App\Filament\Resources\CountryReportResource::getUrl('create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                Nuevo reporte
            </a>
        </div>

        {{-- Type chips --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:5px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 14px;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Tipo:</span>
            <button type="button" wire:click="setTypeFilter('')" class="alg-chip {{ $currentType === '' ? 'is-active' : '' }}">Todos</button>
            @foreach($typeOptions as $key => $lbl)
                <button type="button" wire:click="setTypeFilter('{{ $key }}')" class="alg-chip {{ $currentType === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach
            @if($hasFilters)
                <button type="button" wire:click="clearFilters"
                        style="margin-left:auto;padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">× limpiar</button>
            @endif
        </div>

        {{-- KPI tiles (5) --}}
        <div class="alg-rep-kpis" style="display:grid;grid-template-columns:repeat(5,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @foreach([
                ['Total reportes', number_format($kpis['total']),     'var(--alg-ink)'],
                ['SEO',            number_format($kpis['seo']),       'var(--alg-warn)'],
                ['Marketing',      number_format($kpis['marketing']), 'var(--alg-accent)'],
                ['Sales',          number_format($kpis['sales']),     'var(--alg-pos)'],
                ['Países cubiertos', number_format($kpis['countries']), 'var(--alg-ink-3)'],
            ] as [$lbl, $val, $color])
                <div style="padding:14px 16px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:4px;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-3);letter-spacing:-0.005em;">{{ $lbl }}</span>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;color:{{ $color }};letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ $val }}</span>
                </div>
            @endforeach
        </div>

        {{-- Body table --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);overflow:hidden;">
            @if($reports->isEmpty())
                <div style="padding:48px 24px;text-align:center;">
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:500;color:var(--alg-ink);margin:0 0 6px;">Sin reportes</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0 0 18px;letter-spacing:.04em;">Crea reportes con KPIs + findings + opportunities por país y período.</p>
                    <a href="{{ \App\Filament\Resources\CountryReportResource::getUrl('create') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:inherit;font-size:12.5px;font-weight:500;border-radius:4px;">+ Nuevo reporte</a>
                </div>
            @else
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                    <thead>
                        <tr style="background:var(--alg-bg);">
                            <th style="text-align:left;padding:9px 12px 9px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Reporte</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Tipo</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">País</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Período</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">KPIs</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Findings</th>
                            <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $r)
                            @php
                                $info = \App\Filament\Resources\CountryReportResource\Pages\ListCountryReports::typeIcon($r->type);
                                $isActive = $selectedId === $r->id;
                                $rowBg = $isActive ? 'var(--alg-accent-soft)' : 'transparent';
                                $kpisCount = is_array($r->kpis) ? count($r->kpis) : 0;
                                $findingsCount = is_array($r->findings) ? count($r->findings) : 0;
                            @endphp
                            <tr wire:click="selectReport({{ $r->id }})"
                                style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'"
                                @if($isActive) data-locked="1" @endif>
                                <td style="padding:11px 12px 11px 18px;">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:4px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:14px;flex-shrink:0;">{{ $info['icon'] }}</span>
                                        <div style="min-width:0;">
                                            <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;">{{ $r->country?->name ?? 'Sin país' }} · {{ $r->period }}</div>
                                            @if($r->summary)
                                                <div style="font-size:11px;color:var(--alg-ink-4);margin-top:1px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:380px;">{{ $r->summary }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 12px;">
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:{{ $info['fg'] }};background:{{ $info['bg'] }};padding:1px 6px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $info['label'] }}</span>
                                </td>
                                <td style="padding:11px 12px;">
                                    @if($r->country)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;letter-spacing:.06em;">{{ strtoupper($r->country->code) }}</span>
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                <td style="padding:11px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);letter-spacing:.04em;">{{ $r->period }}</td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;">{{ $kpisCount }}</td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;">{{ $findingsCount }}</td>
                                <td style="padding:11px 18px 11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;">{{ $r->updated_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Slide-over con KPIs/Findings/Opportunities --}}
    @if($selected)
        @php $info = \App\Filament\Resources\CountryReportResource\Pages\ListCountryReports::typeIcon($selected->type); @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:6px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:16px;flex-shrink:0;">{{ $info['icon'] }}</span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;">{{ $selected->country?->name ?? 'Sin país' }} · {{ $selected->period }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $info['label'] }}</div>
                    </div>
                </div>
                <button type="button" wire:click="closeReport" style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                @if($selected->summary)
                    <p style="margin:0 0 18px;padding:10px 12px;background:var(--alg-bg);border-left:2px solid var(--alg-accent);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);line-height:1.5;">{{ $selected->summary }}</p>
                @endif

                {{-- KPIs --}}
                @if(is_array($selected->kpis) && count($selected->kpis) > 0)
                    <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">KPIs</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:18px;">
                        @foreach($selected->kpis as $key => $value)
                            <div style="padding:8px 10px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:3px;">
                                <p style="margin:0 0 2px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.06em;">{{ $key }}</p>
                                <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Findings --}}
                @if(is_array($selected->findings) && count($selected->findings) > 0)
                    <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Findings</p>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:18px;">
                        @foreach($selected->findings as $f)
                            <div style="padding:8px 12px;background:var(--alg-bg);border-left:2px solid var(--alg-warn);">
                                <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">{{ $f['title'] ?? '' }}</p>
                                @if(! empty($f['detail']))
                                    <p style="margin:3px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);line-height:1.5;">{{ $f['detail'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Opportunities --}}
                @if(is_array($selected->opportunities) && count($selected->opportunities) > 0)
                    <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Opportunities</p>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($selected->opportunities as $o)
                            <div style="padding:8px 12px;background:var(--alg-bg);border-left:2px solid var(--alg-pos);">
                                <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">{{ $o['title'] ?? (is_string($o) ? $o : '') }}</p>
                                @if(! empty($o['detail']))
                                    <p style="margin:3px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-3);line-height:1.5;">{{ $o['detail'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
                <a href="{{ \App\Filament\Resources\CountryReportResource::getUrl('edit', ['record' => $selected]) }}"
                   style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">Editar reporte →</a>
                <div style="flex:1;"></div>
            </div>
        </aside>
    @endif

    </div>
</x-filament-panels::page>
