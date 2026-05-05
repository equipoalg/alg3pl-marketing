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
            .alg-fn-kpis { grid-template-columns: repeat(3, 1fr) !important; }
            .alg-fn-layout { grid-template-columns: 1fr !important; }
        }
    </style>

    @php
        $statusColors = [
            'draft'    => ['var(--alg-surface-2)',  'var(--alg-ink-4)'],
            'active'   => ['var(--alg-pos-soft)',   'var(--alg-pos)'],
            'paused'   => ['var(--alg-warn-soft)',  'var(--alg-warn)'],
            'archived' => ['var(--alg-neg-soft)',   'var(--alg-neg)'],
        ];
        $statusOptions = ['draft' => 'Draft', 'active' => 'Active', 'paused' => 'Paused', 'archived' => 'Archived'];
        $triggerOptions = ['page_visit' => 'Page Visit', 'form_submit' => 'Form Submit', 'api_event' => 'API Event', 'manual' => 'Manual'];
        $hasFilters = $currentSearch !== '' || $currentStatus !== '' || $currentTrigger !== '' || $currentSort !== 'recent';
        $convColor = fn ($r) => $r >= 10 ? 'var(--alg-pos)' : ($r >= 3 ? 'var(--alg-warn)' : 'var(--alg-neg)');
    @endphp

    <div class="alg-fn-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '460px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Toolbar 1 --}}
        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Funnels</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['total'] }}</span>

            <div style="position:relative;flex:1;min-width:220px;max-width:340px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar funnels…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            <select wire:model.live="sortBy" title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="recent">↻ Recién actualizados</option>
                <option value="name">A → Z</option>
                <option value="conversion_desc">★ Mejor conv rate</option>
                <option value="entries_desc">📥 Mayor volumen</option>
            </select>

            <div style="flex:1;"></div>

            <a href="{{ \App\Filament\Resources\FunnelResource::getUrl('create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                Nuevo funnel
            </a>
        </div>

        {{-- Toolbar 2: Trigger + Status chips --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:5px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 14px;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Trigger:</span>
            <button type="button" wire:click="setTriggerFilter('')" class="alg-chip {{ $currentTrigger === '' ? 'is-active' : '' }}">Todos</button>
            @foreach($triggerOptions as $key => $lbl)
                <button type="button" wire:click="setTriggerFilter('{{ $key }}')" class="alg-chip {{ $currentTrigger === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach

            <span style="width:1px;height:20px;background:var(--alg-line);margin:0 8px;"></span>

            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Status:</span>
            <button type="button" wire:click="setStatusFilter('')" class="alg-chip {{ $currentStatus === '' ? 'is-active' : '' }}">Todos</button>
            @foreach($statusOptions as $key => $lbl)
                <button type="button" wire:click="setStatusFilter('{{ $key }}')" class="alg-chip {{ $currentStatus === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach

            @if($hasFilters)
                <button type="button" wire:click="clearFilters"
                        style="margin-left:auto;padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">× limpiar</button>
            @endif
        </div>

        {{-- KPI tiles (6) --}}
        <div class="alg-fn-kpis" style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @php
                $tiles = [
                    ['Total funnels', number_format($kpis['total']),       'var(--alg-ink)'],
                    ['Activos',       number_format($kpis['active']),      'var(--alg-pos)'],
                    ['Pausados',      number_format($kpis['paused']),      'var(--alg-warn)'],
                    ['Σ Entries',     number_format($kpis['entries']),     'var(--alg-ink)'],
                    ['Σ Conversions', number_format($kpis['conversions']), 'var(--alg-accent)'],
                    ['Avg conv rate', $kpis['avgConv'] . '%',              $convColor($kpis['avgConv'])],
                ];
            @endphp
            @foreach($tiles as [$lbl, $val, $color])
                <div style="padding:14px 16px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:4px;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-3);letter-spacing:-0.005em;">{{ $lbl }}</span>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;color:{{ $color }};letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ $val }}</span>
                </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);overflow:hidden;">
            @if($funnels->isEmpty())
                <div style="padding:48px 24px;text-align:center;">
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:500;color:var(--alg-ink);margin:0 0 6px;">Sin funnels en este filtro</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0 0 18px;letter-spacing:.04em;">Crea un funnel para trackear conversiones step-by-step.</p>
                    <a href="{{ \App\Filament\Resources\FunnelResource::getUrl('create') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:inherit;font-size:12.5px;font-weight:500;border-radius:4px;">+ Nuevo funnel</a>
                </div>
            @else
                @php
                    $allOnPageIds = $funnels->pluck('id')->all();
                    $allSelectedOnPage = ! empty($allOnPageIds) && empty(array_diff($allOnPageIds, $selectedIds ?? []));
                @endphp
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                    <thead>
                        <tr style="background:var(--alg-bg);">
                            <th style="width:32px;padding:9px 4px 9px 18px;border-bottom:1px solid var(--alg-line);">
                                <input type="checkbox"
                                       wire:click="{{ $allSelectedOnPage ? 'clearSelectedFunnels' : 'selectAllVisible' }}"
                                       {{ $allSelectedOnPage ? 'checked' : '' }}
                                       style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                            </th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Funnel</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Trigger</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Status</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Steps</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Entries</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Conv rate</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">País</th>
                            <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($funnels as $f)
                            @php
                                $info = \App\Filament\Resources\FunnelResource\Pages\ListFunnels::triggerIcon($f->trigger_type);
                                [$pillBg, $pillFg] = $statusColors[$f->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
                                $isActive = $selectedId === $f->id;
                                $isChecked = in_array($f->id, $selectedIds ?? [], true);
                                $rowBg = ($isActive || $isChecked) ? 'var(--alg-accent-soft)' : 'transparent';
                                $convRate = $f->total_entries > 0 ? round(($f->total_conversions / $f->total_entries) * 100, 1) : 0;
                                $stepsCount = $f->steps->count();
                            @endphp
                            <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'"
                                @if($isActive || $isChecked) data-locked="1" @endif>
                                <td style="padding:11px 4px 11px 18px;text-align:center;" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:click="toggleSelected({{ $f->id }})"
                                           {{ $isChecked ? 'checked' : '' }}
                                           style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectFunnel({{ $f->id }})">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:4px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:14px;flex-shrink:0;">{{ $info['icon'] }}</span>
                                        <div style="min-width:0;">
                                            <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;">{{ $f->name }}</div>
                                            @if($f->description)
                                                <div style="font-size:11px;color:var(--alg-ink-4);margin-top:1px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:340px;">{{ $f->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectFunnel({{ $f->id }})">
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:{{ $info['fg'] }};background:{{ $info['bg'] }};padding:1px 6px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $info['label'] }}</span>
                                </td>
                                <td style="padding:11px 12px;" onclick="event.stopPropagation()">
                                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;display:inline-block;">
                                        <button type="button" @click="open = !open"
                                                style="display:inline-block;font-size:9.5px;font-weight:500;color:{{ $pillFg }};background:{{ $pillBg }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;border:none;cursor:pointer;">{{ $statusOptions[$f->status] ?? $f->status }} ▾</button>
                                        <div x-show="open" x-cloak x-transition.opacity
                                             style="position:absolute;top:calc(100% + 3px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:20;display:flex;flex-direction:column;gap:1px;min-width:120px;">
                                            @foreach($statusOptions as $key => $lbl)
                                                @php $cs = $statusColors[$key] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)']; @endphp
                                                <button type="button" wire:click="setFunnelStatus({{ $f->id }}, '{{ $key }}')" @click="open = false"
                                                        style="display:flex;align-items:center;gap:6px;padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                                        onmouseover="this.style.background='var(--alg-surface-2)'"
                                                        onmouseout="this.style.background='transparent'">
                                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $cs[1] }};"></span>{{ $lbl }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);font-variant-numeric:tabular-nums;" wire:click="selectFunnel({{ $f->id }})">
                                    {{ $stepsCount }}
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink);font-weight:500;font-variant-numeric:tabular-nums;" wire:click="selectFunnel({{ $f->id }})">
                                    {{ number_format($f->total_entries ?? 0) }}
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:{{ $convColor($convRate) }};font-variant-numeric:tabular-nums;font-weight:600;" wire:click="selectFunnel({{ $f->id }})">
                                    {{ $convRate }}%
                                    <div style="font-size:9.5px;color:var(--alg-ink-5);font-weight:400;margin-top:1px;">{{ number_format($f->total_conversions ?? 0) }} conv</div>
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectFunnel({{ $f->id }})">
                                    @if($f->country)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;letter-spacing:.06em;">{{ strtoupper($f->country->code) }}</span>
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                <td style="padding:11px 18px 11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;" wire:click="selectFunnel({{ $f->id }})">
                                    {{ $f->updated_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Slide-over con visualización del funnel --}}
    @if($selected)
        @php
            $info = \App\Filament\Resources\FunnelResource\Pages\ListFunnels::triggerIcon($selected->trigger_type);
            [$selPillBg, $selPillFg] = $statusColors[$selected->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
            $selConvRate = $selected->total_entries > 0 ? round(($selected->total_conversions / $selected->total_entries) * 100, 1) : 0;
        @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:6px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:16px;flex-shrink:0;">{{ $info['icon'] }}</span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->name }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $info['label'] }} · {{ $selected->steps->count() }} steps</div>
                    </div>
                </div>
                <button type="button" wire:click="closeFunnel" style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                {{-- Status + KPIs row --}}
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
                    <span style="display:inline-block;font-size:10px;font-weight:500;color:{{ $selPillFg }};background:{{ $selPillBg }};padding:3px 8px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">{{ $statusOptions[$selected->status] ?? $selected->status }}</span>
                    @if($selected->country)
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:3px 8px;border-radius:2px;letter-spacing:.04em;">{{ $selected->country->name }}</span>
                    @endif
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:18px;">
                    <div style="padding:10px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:4px;">
                        <p style="margin:0 0 3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Entries</p>
                        <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:600;color:var(--alg-ink);font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ number_format($selected->total_entries) }}</p>
                    </div>
                    <div style="padding:10px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:4px;">
                        <p style="margin:0 0 3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Conversions</p>
                        <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:600;color:var(--alg-accent);font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ number_format($selected->total_conversions) }}</p>
                    </div>
                    <div style="padding:10px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:4px;">
                        <p style="margin:0 0 3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Conv rate</p>
                        <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:600;color:{{ $convColor($selConvRate) }};font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ $selConvRate }}%</p>
                    </div>
                </div>

                {{-- Editable fields --}}
                <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Nombre</label>
                        <input type="text" wire:model.live.debounce.500ms="editName"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Trigger</label>
                            <select wire:model.live="editTriggerType" style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                                @foreach($triggerOptions as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Status</label>
                            <select wire:model.live="editStatus" style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                                @foreach($statusOptions as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Descripción</label>
                        <textarea wire:model.live.debounce.700ms="editDescription" rows="2"
                                  style="width:100%;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;resize:vertical;border-radius:3px;line-height:1.5;"></textarea>
                    </div>
                </div>

                {{-- Steps visualization --}}
                @if($selected->steps->count() > 0)
                    <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Steps del funnel</p>
                    <div style="display:flex;flex-direction:column;gap:0;">
                        @foreach($selected->steps as $i => $step)
                            <div style="display:flex;gap:10px;align-items:flex-start;position:relative;">
                                @if(! $loop->last)
                                    <div style="position:absolute;left:11px;top:24px;bottom:-4px;width:1px;background:var(--alg-line);"></div>
                                @endif
                                <div style="flex-shrink:0;width:22px;height:22px;border-radius:50%;background:var(--alg-accent-soft);border:1px solid var(--alg-accent);display:flex;align-items:center;justify-content:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10px;font-weight:600;color:var(--alg-accent);z-index:1;">{{ $i + 1 }}</div>
                                <div style="flex:1;padding-bottom:14px;font-size:12.5px;color:var(--alg-ink-2);font-weight:500;letter-spacing:-0.005em;">{{ $step->name }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="margin:0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);letter-spacing:.04em;">— Sin steps configurados —</p>
                @endif
            </div>

            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
                <button type="button" wire:click="saveFunnel"
                        style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">Guardar</button>
                <div style="flex:1;"></div>
                <a href="{{ \App\Filament\Resources\FunnelResource::getUrl('edit', ['record' => $selected]) }}"
                   style="padding:7px 10px;background:transparent;color:var(--alg-ink-4);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;border-radius:4px;">Editor →</a>
            </div>
        </aside>
    @endif

    </div>

    {{-- Bulk bar --}}
    @if(count($selectedIds ?? []) > 0)
        <div style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--alg-ink);color:#FFFFFF;padding:10px 14px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.35);display:flex;align-items:center;gap:12px;z-index:1000;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
            <span style="font-weight:600;">{{ count($selectedIds) }} seleccionado{{ count($selectedIds) > 1 ? 's' : '' }}</span>
            <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>
            <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                        onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                        onmouseout="this.style.background='transparent'">▼ Cambiar status</button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;bottom:calc(100% + 4px);left:0;background:var(--alg-ink);border:1px solid rgba(255,255,255,0.15);border-radius:6px;padding:4px;display:flex;flex-direction:column;gap:1px;min-width:140px;">
                    @foreach($statusOptions as $key => $lbl)
                        <button type="button" wire:click="bulkSetStatus('{{ $key }}')" @click="open = false"
                                style="padding:5px 10px;border:none;background:transparent;color:#FFFFFF;font-family:inherit;font-size:inherit;text-align:left;cursor:pointer;border-radius:4px;"
                                onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                                onmouseout="this.style.background='transparent'">{{ $lbl }}</button>
                    @endforeach
                </div>
            </div>
            <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>
            <button type="button" wire:click="clearSelectedFunnels"
                    style="border:none;background:transparent;color:rgba(255,255,255,0.65);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;border-radius:4px;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">×</button>
        </div>
    @endif
</x-filament-panels::page>
