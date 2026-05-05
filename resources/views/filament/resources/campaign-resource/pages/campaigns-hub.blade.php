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
            .alg-camp-kpis { grid-template-columns: repeat(3, 1fr) !important; }
            .alg-camp-layout { grid-template-columns: 1fr !important; }
        }
    </style>

    @php
        $statusColors = [
            'draft'     => ['var(--alg-surface-2)',  'var(--alg-ink-4)'],
            'scheduled' => ['var(--alg-accent-soft)','var(--alg-accent)'],
            'active'    => ['var(--alg-pos-soft)',   'var(--alg-pos)'],
            'paused'    => ['var(--alg-warn-soft)',  'var(--alg-warn)'],
            'completed' => ['var(--alg-surface-2)',  'var(--alg-ink-3)'],
        ];
        $statusOptions = [
            'draft'     => 'Draft',
            'scheduled' => 'Scheduled',
            'active'    => 'Active',
            'paused'    => 'Paused',
            'completed' => 'Completed',
        ];
        $typeOptions = [
            'email'    => 'Email',
            'whatsapp' => 'WhatsApp',
            'social'   => 'Social',
            'seo'      => 'SEO',
        ];
        $hasFilters = $currentSearch !== '' || $currentStatus !== '' || $currentType !== '' || $currentSort !== 'recent';
    @endphp

    <div class="alg-camp-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '420px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    {{-- Main column --}}
    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Toolbar 1 --}}
        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Campañas</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['total'] }}</span>

            <div style="position:relative;flex:1;min-width:220px;max-width:340px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar campañas…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            <select wire:model.live="sortBy" title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="recent">↓ Más recientes</option>
                <option value="name">A → Z</option>
                <option value="starts_soon">⏰ Próximas a iniciar</option>
                <option value="budget_desc">$ Mayor budget</option>
            </select>

            <div x-data="{ open: false, name: '' }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-2);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;border-radius:4px;">
                    💾 Vistas
                    @if(count($savedViews) > 0)<span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);">· {{ count($savedViews) }}</span>@endif
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;top:calc(100% + 4px);right:0;min-width:240px;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:6px;box-shadow:0 6px 20px rgba(0,0,0,0.10);padding:6px;z-index:30;">
                    @foreach($savedViews as $idx => $sv)
                        <div style="display:flex;align-items:center;gap:6px;padding:5px 8px;border-radius:4px;font-size:12px;color:var(--alg-ink-2);">
                            <span style="color:var(--alg-ink-4);font-size:11px;">📌</span>
                            <button type="button" wire:click="loadCampaignView({{ $idx }})" @click="open = false"
                                    style="flex:1;border:none;background:transparent;text-align:left;color:inherit;cursor:pointer;padding:0;font-family:inherit;">{{ $sv['name'] }}</button>
                            <button type="button" wire:click="deleteCampaignView({{ $idx }})"
                                    wire:confirm="¿Eliminar?"
                                    style="border:none;background:transparent;color:var(--alg-ink-5);cursor:pointer;padding:0 2px;font-size:11px;">✕</button>
                        </div>
                    @endforeach
                    @if(count($savedViews) > 0)<div style="height:1px;background:var(--alg-line);margin:5px 0;"></div>@endif
                    <div style="display:flex;gap:4px;padding:4px 8px 2px;">
                        <input x-model="name" type="text" placeholder="Guardar vista actual…" maxlength="40"
                               x-on:keydown.enter="$wire.saveCurrentView(name); name=''"
                               style="flex:1;padding:5px 8px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:inherit;font-size:11.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                </div>
            </div>

            <div style="flex:1;"></div>

            <a href="{{ \App\Filament\Resources\CampaignResource::getUrl('create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                Nueva campaña
            </a>
        </div>

        {{-- Toolbar 2: Type + Status chips --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:5px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 14px;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Tipo:</span>
            <button type="button" wire:click="setTypeFilter('')" class="alg-chip {{ $currentType === '' ? 'is-active' : '' }}">Todos</button>
            @foreach($typeOptions as $key => $lbl)
                <button type="button" wire:click="setTypeFilter('{{ $key }}')" class="alg-chip {{ $currentType === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
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
        <div class="alg-camp-kpis" style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @php
                $tiles = [
                    ['Total',     number_format($kpis['total']),      'var(--alg-ink)'],
                    ['Active',    number_format($kpis['active']),     'var(--alg-pos)'],
                    ['Scheduled', number_format($kpis['scheduled']),  'var(--alg-accent)'],
                    ['Paused',    number_format($kpis['paused']),     'var(--alg-warn)'],
                    ['Completed', number_format($kpis['completed']),  'var(--alg-ink-3)'],
                    ['Σ Budget',  '$' . ($kpis['budget'] >= 1000000 ? number_format($kpis['budget']/1000000, 1) . 'M' : ($kpis['budget'] >= 1000 ? number_format($kpis['budget']/1000, 0) . 'k' : number_format($kpis['budget'], 0))), 'var(--alg-ink)'],
                ];
            @endphp
            @foreach($tiles as [$lbl, $val, $color])
                <div style="padding:14px 16px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:4px;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-3);letter-spacing:-0.005em;">{{ $lbl }}</span>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;color:{{ $color }};letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ $val }}</span>
                </div>
            @endforeach
        </div>

        {{-- Body --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);overflow:hidden;">
            @if($campaigns->isEmpty())
                {{-- Empty state con type templates --}}
                <div style="padding:48px 24px;">
                    <div style="text-align:center;margin-bottom:32px;">
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:16px;font-weight:500;color:var(--alg-ink);margin:0 0 6px;letter-spacing:-0.01em;">Aún no hay campañas {{ $currentType ? 'de tipo ' . $typeOptions[$currentType] : '' }}</p>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Crea tu primera campaña según el canal:</p>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;max-width:760px;margin:0 auto;">
                        @foreach($typeOptions as $key => $lbl)
                            @php $info = \App\Filament\Resources\CampaignResource\Pages\ListCampaigns::typeIcon($key); @endphp
                            <a href="{{ \App\Filament\Resources\CampaignResource::getUrl('create') }}?type={{ $key }}"
                               class="alg-hover-lift"
                               style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 14px;border:1px solid var(--alg-line);background:var(--alg-bg);text-decoration:none;border-radius:6px;transition:all 120ms ease;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:8px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:20px;">{{ $info['icon'] }}</span>
                                <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">{{ $info['label'] }}</span>
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.06em;">+ Nueva</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $allOnPageIds = $campaigns->pluck('id')->all();
                    $allSelectedOnPage = ! empty($allOnPageIds) && empty(array_diff($allOnPageIds, $selectedIds ?? []));
                @endphp
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                    <thead>
                        <tr style="background:var(--alg-bg);">
                            <th style="width:32px;padding:9px 4px 9px 18px;border-bottom:1px solid var(--alg-line);">
                                <input type="checkbox"
                                       wire:click="{{ $allSelectedOnPage ? 'clearSelectedCampaigns' : 'selectAllVisible' }}"
                                       {{ $allSelectedOnPage ? 'checked' : '' }}
                                       style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                            </th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Campaña</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Tipo</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Status</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">País</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Período</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Budget</th>
                            <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $c)
                            @php
                                $info = \App\Filament\Resources\CampaignResource\Pages\ListCampaigns::typeIcon($c->type);
                                [$pillBg, $pillFg] = $statusColors[$c->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
                                $isActive = $selectedId === $c->id;
                                $isChecked = in_array($c->id, $selectedIds ?? [], true);
                                $rowBg = ($isActive || $isChecked) ? 'var(--alg-accent-soft)' : 'transparent';
                            @endphp
                            <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'"
                                @if($isActive || $isChecked) data-locked="1" @endif>
                                <td style="padding:11px 4px 11px 18px;text-align:center;" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:click="toggleSelected({{ $c->id }})"
                                           {{ $isChecked ? 'checked' : '' }}
                                           style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectCampaign({{ $c->id }})">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:4px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:14px;flex-shrink:0;">{{ $info['icon'] }}</span>
                                        <div style="min-width:0;">
                                            <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;">{{ $c->name }}</div>
                                            @if($c->description)
                                                <div style="font-size:11px;color:var(--alg-ink-4);margin-top:1px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:340px;">{{ $c->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectCampaign({{ $c->id }})">
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:{{ $info['fg'] }};background:{{ $info['bg'] }};padding:1px 6px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $info['label'] }}</span>
                                </td>
                                <td style="padding:11px 12px;" onclick="event.stopPropagation()">
                                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;display:inline-block;">
                                        <button type="button" @click="open = !open"
                                                style="display:inline-block;font-size:9.5px;font-weight:500;color:{{ $pillFg }};background:{{ $pillBg }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;border:none;cursor:pointer;">{{ $statusOptions[$c->status] ?? $c->status }} ▾</button>
                                        <div x-show="open" x-cloak x-transition.opacity
                                             style="position:absolute;top:calc(100% + 3px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:20;display:flex;flex-direction:column;gap:1px;min-width:130px;">
                                            @foreach($statusOptions as $key => $lbl)
                                                @php $cs = $statusColors[$key] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)']; @endphp
                                                <button type="button" wire:click="setCampaignStatus({{ $c->id }}, '{{ $key }}')" @click="open = false"
                                                        style="display:flex;align-items:center;gap:6px;padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                                        onmouseover="this.style.background='var(--alg-surface-2)'"
                                                        onmouseout="this.style.background='transparent'">
                                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $cs[1] }};"></span>{{ $lbl }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectCampaign({{ $c->id }})">
                                    @if($c->country)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;letter-spacing:.06em;">{{ strtoupper($c->country->code) }}</span>
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                <td style="padding:11px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);letter-spacing:.04em;" wire:click="selectCampaign({{ $c->id }})">
                                    @if($c->start_date)
                                        {{ $c->start_date->format('d M') }}{{ $c->end_date ? ' → ' . $c->end_date->format('d M') : '' }}
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;" wire:click="selectCampaign({{ $c->id }})">
                                    @if($c->budget && $c->budget > 0)
                                        ${{ $c->budget >= 1000 ? number_format($c->budget/1000, 0) . 'k' : number_format($c->budget, 0) }}
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                <td style="padding:11px 18px 11px 12px;text-align:right;" onclick="event.stopPropagation()">
                                    @if($c->type === 'email' && in_array($c->status, ['draft','scheduled','active'], true))
                                        <button type="button" wire:click="sendEmailCampaign({{ $c->id }})"
                                                wire:confirm="¿Encolar emails de esta campaña? Se mandarán a todos los leads elegibles del país."
                                                title="Enviar emails"
                                                style="border:1px solid var(--alg-accent);background:var(--alg-accent-soft);color:var(--alg-accent);cursor:pointer;padding:3px 8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;font-weight:500;border-radius:3px;letter-spacing:-0.005em;">
                                            ✈ Enviar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Slide-over --}}
    @if($selected)
        @php
            $info = \App\Filament\Resources\CampaignResource\Pages\ListCampaigns::typeIcon($selected->type);
            [$pillBg, $pillFg] = $statusColors[$selected->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
        @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:6px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:16px;flex-shrink:0;">{{ $info['icon'] }}</span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->name }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $info['label'] }} · creado {{ $selected->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <button type="button" wire:click="closeCampaign" title="Cerrar"
                        style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
                    <span style="display:inline-block;font-size:10px;font-weight:500;color:{{ $pillFg }};background:{{ $pillBg }};padding:3px 8px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">{{ $statusOptions[$selected->status] ?? $selected->status }}</span>
                    @if($selected->country)
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:3px 8px;border-radius:2px;letter-spacing:.04em;">{{ $selected->country->name }}</span>
                    @endif
                </div>

                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Nombre</label>
                        <input type="text" wire:model.live.debounce.500ms="editName"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Tipo</label>
                            <select wire:model.live="editType" style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                                @foreach($typeOptions as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
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
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">País</label>
                        <select wire:model.live="editCountryId" style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                            @foreach($countries as $co)<option value="{{ $co->id }}">{{ $co->name }} ({{ strtoupper($co->code) }})</option>@endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Inicio</label>
                            <input type="date" wire:model.live="editStartDate"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Fin</label>
                            <input type="date" wire:model.live="editEndDate"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Budget (USD)</label>
                        <input type="number" min="0" step="0.01" wire:model.live.debounce.500ms="editBudget"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Descripción</label>
                        <textarea wire:model.live.debounce.700ms="editDescription" rows="3"
                                  style="width:100%;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;resize:vertical;border-radius:3px;line-height:1.5;"></textarea>
                    </div>
                </div>
            </div>

            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);flex-wrap:wrap;">
                <button type="button" wire:click="saveCampaign"
                        style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                    Guardar
                </button>
                @if($selected->type === 'email' && in_array($selected->status, ['draft','scheduled','active'], true))
                    <button type="button" wire:click="sendEmailCampaign({{ $selected->id }})"
                            wire:confirm="¿Encolar emails ahora?"
                            style="padding:7px 12px;background:var(--alg-accent-soft);color:var(--alg-accent);border:1px solid var(--alg-accent);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                        ✈ Enviar emails
                    </button>
                @endif
                <div style="flex:1;"></div>
                <a href="{{ \App\Filament\Resources\CampaignResource::getUrl('edit', ['record' => $selected]) }}"
                   style="padding:7px 10px;background:transparent;color:var(--alg-ink-4);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;border-radius:4px;">
                    Editor →
                </a>
            </div>
        </aside>
    @endif

    </div>

    {{-- Bulk action bar --}}
    @if(count($selectedIds ?? []) > 0)
        <div style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--alg-ink);color:#FFFFFF;padding:10px 14px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.35);display:flex;align-items:center;gap:12px;z-index:1000;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
            <span style="font-weight:600;">{{ count($selectedIds) }} seleccionada{{ count($selectedIds) > 1 ? 's' : '' }}</span>
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
            <button type="button" wire:click="clearSelectedCampaigns" title="Quitar selección"
                    style="border:none;background:transparent;color:rgba(255,255,255,0.65);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;border-radius:4px;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">×</button>
        </div>
    @endif
</x-filament-panels::page>
