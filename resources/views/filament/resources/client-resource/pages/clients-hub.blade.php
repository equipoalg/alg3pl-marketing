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
            .alg-clients-kpis { grid-template-columns: repeat(3, 1fr) !important; }
            .alg-clients-layout { grid-template-columns: 1fr !important; }
        }
    </style>

    @php
        // Use FQN inline en vez de `use` (que no es permitido dentro de un Blade component slot)
        $statusColors = [
            'active'   => ['var(--alg-pos-soft)',     'var(--alg-pos)'],
            'prospect' => ['var(--alg-accent-soft)',  'var(--alg-accent)'],
            'inactive' => ['var(--alg-surface-2)',    'var(--alg-ink-4)'],
            'churned'  => ['var(--alg-neg-soft)',     'var(--alg-neg)'],
        ];

        $statusOptions = [
            'prospect' => 'Prospect',
            'active'   => 'Active',
            'inactive' => 'Inactive',
            'churned'  => 'Churned',
        ];

        $tierOptions = [
            'enterprise' => 'Enterprise',
            'mid_market' => 'Mid Market',
            'smb'        => 'SMB',
        ];

        $hasFilters = $currentSearch !== '' || $currentStatus !== '' || $currentTier !== '' || $currentSort !== 'company_name';
    @endphp

    <div class="alg-clients-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '420px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    {{-- ═══════════ Main column ═══════════ --}}
    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Toolbar 1: search · sort · saved views · new --}}
        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Clientes</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['total'] }}</span>

            {{-- Search --}}
            <div style="position:relative;flex:1;min-width:220px;max-width:360px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar nombre, contacto, industria…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            {{-- Sort --}}
            <select wire:model.live="sortBy"
                    title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="company_name">A → Z</option>
                <option value="health_desc">★ Health desc</option>
                <option value="value_desc">$ Annual revenue desc</option>
                <option value="contract_soon">⏰ Contract end más cercano</option>
                <option value="recent">↻ Recién actualizados</option>
            </select>

            {{-- Saved views dropdown --}}
            <div x-data="{ open: false, name: '' }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="display:inline-flex;align-items:center;gap:4px;padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-2);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;border-radius:4px;">
                    💾 Vistas
                    @if(count($savedViews) > 0)<span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);">· {{ count($savedViews) }}</span>@endif
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;top:calc(100% + 4px);right:0;min-width:240px;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:6px;box-shadow:0 6px 20px rgba(0,0,0,0.10);padding:6px;z-index:30;">
                    @foreach($savedViews as $idx => $sv)
                        <div style="display:flex;align-items:center;gap:6px;padding:5px 8px;border-radius:4px;font-size:12px;color:var(--alg-ink-2);"
                             onmouseover="this.style.background='var(--alg-surface-2)'"
                             onmouseout="this.style.background='transparent'">
                            <span style="color:var(--alg-ink-4);font-size:11px;">📌</span>
                            <button type="button" wire:click="loadClientView({{ $idx }})" @click="open = false"
                                    style="flex:1;border:none;background:transparent;text-align:left;color:inherit;cursor:pointer;padding:0;font-family:inherit;">{{ $sv['name'] }}</button>
                            <button type="button" wire:click="deleteClientView({{ $idx }})"
                                    wire:confirm="¿Eliminar la vista &quot;{{ $sv['name'] }}&quot;?"
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

            <a href="{{ \App\Filament\Resources\ClientResource::getUrl('create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                Nuevo cliente
            </a>
        </div>

        {{-- Toolbar 2: status chips + tier chips --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:5px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 14px;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Status:</span>
            <button type="button" wire:click="setStatusFilter('')" class="alg-chip {{ $currentStatus === '' ? 'is-active' : '' }}">Todos</button>
            @foreach($statusOptions as $key => $lbl)
                <button type="button" wire:click="setStatusFilter('{{ $key }}')" class="alg-chip {{ $currentStatus === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach

            <span style="width:1px;height:20px;background:var(--alg-line);margin:0 8px;"></span>

            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Tier:</span>
            <button type="button" wire:click="setTierFilter('')" class="alg-chip {{ $currentTier === '' ? 'is-active' : '' }}">Todos</button>
            @foreach($tierOptions as $key => $lbl)
                <button type="button" wire:click="setTierFilter('{{ $key }}')" class="alg-chip {{ $currentTier === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach

            @if($hasFilters)
                <button type="button" wire:click="clearFilters"
                        style="margin-left:auto;padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">× limpiar</button>
            @endif
        </div>

        {{-- Renewals banner --}}
        @if($renewalsCount > 0)
            <div style="display:flex;align-items:center;gap:10px;padding:9px 14px;background:var(--alg-warn-soft);border:1px solid var(--alg-warn);color:var(--alg-warn);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;border-radius:4px;">
                <span style="font-size:14px;">⏰</span>
                <span style="flex:1;"><strong>{{ $renewalsCount }}</strong> {{ $renewalsCount === 1 ? 'contrato vence' : 'contratos vencen' }} en los próximos 30 días.</span>
                <button type="button" wire:click="setSortBy('contract_soon')"
                        style="padding:4px 11px;border:1px solid currentColor;background:transparent;color:inherit;cursor:pointer;font-family:inherit;font-size:11.5px;font-weight:600;border-radius:3px;">
                    Ver primero →
                </button>
            </div>
        @endif

        {{-- KPI tiles (6) --}}
        <div class="alg-clients-kpis" style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @php
                $tiles = [
                    ['Total',         number_format($kpis['total']),                                                         'var(--alg-ink)'],
                    ['Activos',       number_format($kpis['active']),                                                        'var(--alg-pos)'],
                    ['Prospects',     number_format($kpis['prospect']),                                                      'var(--alg-accent)'],
                    ['Churned',       number_format($kpis['churned']),                                                       'var(--alg-neg)'],
                    ['Σ Revenue',     '$' . ($kpis['revenue'] >= 1000000 ? number_format($kpis['revenue']/1000000, 1) . 'M' : ($kpis['revenue'] >= 1000 ? number_format($kpis['revenue']/1000, 0) . 'k' : number_format($kpis['revenue'], 0))), 'var(--alg-ink)'],
                    ['Health avg',    $kpis['avgHealth'] . '/100',                                                            $kpis['avgHealth'] >= 70 ? 'var(--alg-pos)' : ($kpis['avgHealth'] >= 40 ? 'var(--alg-warn)' : 'var(--alg-neg)')],
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
            @if($clients->isEmpty())
                <div style="padding:64px 20px;text-align:center;">
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-3);margin:0 0 6px;">Sin clientes en este filtro</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá filtros o crea uno nuevo.</p>
                </div>
            @else
                @php
                    $allOnPageIds = $clients->pluck('id')->all();
                    $allSelectedOnPage = ! empty($allOnPageIds) && empty(array_diff($allOnPageIds, $selectedIds ?? []));
                @endphp
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                    <thead>
                        <tr style="background:var(--alg-bg);">
                            <th style="width:32px;padding:9px 4px 9px 18px;border-bottom:1px solid var(--alg-line);">
                                <input type="checkbox"
                                       wire:click="{{ $allSelectedOnPage ? 'clearSelectedClients' : 'selectAllVisible' }}"
                                       {{ $allSelectedOnPage ? 'checked' : '' }}
                                       style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                            </th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Cliente</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Status</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Tier</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Health</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Servicios</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Revenue</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Owner</th>
                            <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Contract end</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $c)
                            @php
                                $av = \App\Filament\Resources\ClientResource\Pages\ListClients::avatarFor($c->company_name);
                                [$pillBg, $pillFg] = $statusColors[$c->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
                                $isActive = $selectedId === $c->id;
                                $isChecked = in_array($c->id, $selectedIds ?? [], true);
                                $rowBg = ($isActive || $isChecked) ? 'var(--alg-accent-soft)' : 'transparent';
                                $health = $c->health_score ?? 0;
                                $healthColor = $health >= 70 ? 'var(--alg-pos)' : ($health >= 40 ? 'var(--alg-warn)' : 'var(--alg-neg)');
                                $contractDaysLeft = $c->contract_end ? round(now()->diffInDays($c->contract_end, false)) : null;
                                $services = is_array($c->services_contracted) ? $c->services_contracted : [];
                            @endphp
                            <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'"
                                @if($isActive || $isChecked) data-locked="1" @endif>
                                {{-- Checkbox --}}
                                <td style="padding:11px 4px 11px 18px;text-align:center;" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:click="toggleSelected({{ $c->id }})"
                                           {{ $isChecked ? 'checked' : '' }}
                                           style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                                </td>
                                {{-- Cliente: avatar + name + industry/country --}}
                                <td style="padding:11px 12px;" wire:click="selectClient({{ $c->id }})">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:4px;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;flex-shrink:0;">{{ $av['initials'] }}</span>
                                        <div style="min-width:0;">
                                            <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;">{{ $c->company_name }}</div>
                                            <div style="font-size:11px;color:var(--alg-ink-4);margin-top:1px;line-height:1.3;font-family:ui-monospace,'SF Mono',Menlo,monospace;letter-spacing:.04em;">
                                                @if($c->industry){{ $c->industry }}@endif
                                                @if($c->industry && $c->country) · @endif
                                                @if($c->country){{ strtoupper($c->country->code) }}@endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                {{-- Status pill (clickable inline) --}}
                                <td style="padding:11px 12px;" onclick="event.stopPropagation()">
                                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;display:inline-block;">
                                        <button type="button" @click="open = !open"
                                                style="display:inline-block;font-size:9.5px;font-weight:500;color:{{ $pillFg }};background:{{ $pillBg }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;border:none;cursor:pointer;">{{ $statusOptions[$c->status] ?? $c->status }} ▾</button>
                                        <div x-show="open" x-cloak x-transition.opacity
                                             style="position:absolute;top:calc(100% + 3px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:20;display:flex;flex-direction:column;gap:1px;min-width:120px;">
                                            @foreach($statusOptions as $key => $lbl)
                                                @php $cs = $statusColors[$key] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)']; @endphp
                                                <button type="button" wire:click="setClientStatus({{ $c->id }}, '{{ $key }}')" @click="open = false"
                                                        style="display:flex;align-items:center;gap:6px;padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                                        onmouseover="this.style.background='var(--alg-surface-2)'"
                                                        onmouseout="this.style.background='transparent'">
                                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $cs[1] }};"></span>{{ $lbl }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                {{-- Tier --}}
                                <td style="padding:11px 12px;" wire:click="selectClient({{ $c->id }})">
                                    @if($c->tier)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $tierOptions[$c->tier] ?? $c->tier }}</span>
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                {{-- Health score con barra --}}
                                <td style="padding:11px 12px;" wire:click="selectClient({{ $c->id }})">
                                    <div style="display:flex;align-items:center;gap:8px;min-width:90px;">
                                        <div style="flex:1;height:5px;background:var(--alg-surface-2);border-radius:2px;overflow:hidden;max-width:60px;">
                                            <div style="height:100%;width:{{ max(0, min(100, $health)) }}%;background:{{ $healthColor }};"></div>
                                        </div>
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:{{ $healthColor }};font-variant-numeric:tabular-nums;font-weight:600;">{{ $health }}</span>
                                    </div>
                                </td>
                                {{-- Services chips (top 2 + count) --}}
                                <td style="padding:11px 12px;" wire:click="selectClient({{ $c->id }})">
                                    @if(! empty($services))
                                        <div style="display:flex;flex-wrap:wrap;gap:3px;">
                                            @foreach(array_slice($services, 0, 2) as $svc)
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 5px;border-radius:2px;letter-spacing:.04em;">{{ $svc }}</span>
                                            @endforeach
                                            @if(count($services) > 2)
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-5);">+{{ count($services) - 2 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                {{-- Annual revenue --}}
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;" wire:click="selectClient({{ $c->id }})">
                                    @if($c->annual_revenue && $c->annual_revenue > 0)
                                        ${{ $c->annual_revenue >= 1000000 ? number_format($c->annual_revenue/1000000, 1) . 'M' : ($c->annual_revenue >= 1000 ? number_format($c->annual_revenue/1000, 0) . 'k' : number_format($c->annual_revenue, 0)) }}
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                                {{-- Owner --}}
                                <td style="padding:11px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);letter-spacing:.04em;" wire:click="selectClient({{ $c->id }})">
                                    {{ $c->assignedUser?->name ?? '—' }}
                                </td>
                                {{-- Contract end (con días restantes color-coded) --}}
                                <td style="padding:11px 18px 11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;white-space:nowrap;letter-spacing:.04em;" wire:click="selectClient({{ $c->id }})">
                                    @if($c->contract_end)
                                        @php
                                            $contractColor = $contractDaysLeft < 0 ? 'var(--alg-neg)' : ($contractDaysLeft <= 30 ? 'var(--alg-warn)' : 'var(--alg-ink-4)');
                                        @endphp
                                        <span style="color:{{ $contractColor }};">{{ $c->contract_end->format('d M Y') }}</span>
                                        @if($contractDaysLeft !== null && $contractDaysLeft <= 60)
                                            <div style="font-size:9px;color:{{ $contractColor }};margin-top:1px;">
                                                {{ $contractDaysLeft < 0 ? 'vencido hace ' . abs($contractDaysLeft) . 'd' : ($contractDaysLeft === 0 ? 'hoy' : 'en ' . $contractDaysLeft . 'd') }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color:var(--alg-ink-5);">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ═══════════ Slide-over derecho ═══════════ --}}
    @if($selected)
        @php
            $selAv = \App\Filament\Resources\ClientResource\Pages\ListClients::avatarFor($selected->company_name);
            [$selPillBg, $selPillFg] = $statusColors[$selected->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
            $selHealth = $selected->health_score ?? 0;
            $selHealthColor = $selHealth >= 70 ? 'var(--alg-pos)' : ($selHealth >= 40 ? 'var(--alg-warn)' : 'var(--alg-neg)');
        @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            {{-- Header --}}
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:4px;background:{{ $selAv['bg'] }};color:{{ $selAv['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;flex-shrink:0;">{{ $selAv['initials'] }}</span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->company_name }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $selected->country?->name ?? 'Sin país' }} · creado {{ $selected->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <button type="button" wire:click="closeClient" title="Cerrar"
                        style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            {{-- Body --}}
            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                {{-- Status + tier + health row --}}
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
                    <span style="display:inline-block;font-size:10px;font-weight:500;color:{{ $selPillFg }};background:{{ $selPillBg }};padding:3px 8px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">{{ $statusOptions[$selected->status] ?? $selected->status }}</span>
                    @if($selected->tier)
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:3px 8px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $tierOptions[$selected->tier] ?? $selected->tier }}</span>
                    @endif
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:{{ $selHealthColor }};background:transparent;border:1px solid {{ $selHealthColor }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;">Health {{ $selHealth }}/100</span>
                </div>

                {{-- Editable fields --}}
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Company name</label>
                        <input type="text" wire:model.live.debounce.500ms="editCompanyName"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Status</label>
                            <select wire:model.live="editStatus"
                                    style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                                @foreach($statusOptions as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Tier</label>
                            <select wire:model.live="editTier"
                                    style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                                @foreach($tierOptions as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Industry</label>
                            <input type="text" wire:model.live.debounce.500ms="editIndustry"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Health (0-100)</label>
                            <input type="number" min="0" max="100" wire:model.live.debounce.500ms="editHealthScore"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                    </div>

                    <div>
                        <p style="margin:0 0 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Contacto principal</p>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <input type="text" wire:model.live.debounce.500ms="editPrimaryContactName" placeholder="Nombre"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                            <input type="email" wire:model.live.debounce.500ms="editPrimaryContactEmail" placeholder="email@empresa.com"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                            <input type="tel" wire:model.live.debounce.500ms="editPrimaryContactPhone" placeholder="Teléfono"
                                   style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                    </div>

                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Notas</label>
                        <textarea wire:model.live.debounce.700ms="editNotes" rows="4"
                                  style="width:100%;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;resize:vertical;border-radius:3px;line-height:1.5;"></textarea>
                    </div>
                </div>

                {{-- Read-only meta --}}
                <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--alg-line);display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">
                    @if($selected->annual_revenue)
                        <span>Annual rev.</span>
                        <span style="color:var(--alg-ink-3);">${{ number_format($selected->annual_revenue, 0) }}</span>
                    @endif
                    @if($selected->monthly_volume)
                        <span>Volumen</span>
                        <span style="color:var(--alg-ink-3);">{{ number_format($selected->monthly_volume, 1) }} CBM/mo</span>
                    @endif
                    @if($selected->contract_start)
                        <span>Contract start</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->contract_start->format('d M Y') }}</span>
                    @endif
                    @if($selected->contract_end)
                        <span>Contract end</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->contract_end->format('d M Y') }}</span>
                    @endif
                    @if($selected->assignedUser)
                        <span>Owner</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->assignedUser->name }}</span>
                    @endif
                    @if(is_array($selected->services_contracted) && count($selected->services_contracted) > 0)
                        <span style="grid-column:1/-1;margin-top:6px;">Servicios contratados</span>
                        <span style="grid-column:1/-1;display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($selected->services_contracted as $svc)
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;letter-spacing:.04em;">{{ $svc }}</span>
                            @endforeach
                        </span>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);flex-wrap:wrap;">
                <button type="button" wire:click="saveClient"
                        style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                    Guardar
                </button>
                <div style="flex:1;"></div>
                <a href="{{ \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $selected]) }}"
                   style="padding:7px 10px;background:transparent;color:var(--alg-ink-4);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;border-radius:4px;">
                    Editor →
                </a>
            </div>
        </aside>
    @endif

    </div> {{-- end layout grid --}}

    {{-- ═══════════ Bulk action bar ═══════════ --}}
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

            <button type="button" wire:click="bulkAssignToMe"
                    style="padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">👤 Asignarme</button>

            <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>

            <button type="button" wire:click="clearSelectedClients"
                    title="Quitar selección"
                    style="border:none;background:transparent;color:rgba(255,255,255,0.65);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;border-radius:4px;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">×</button>
        </div>
    @endif
</x-filament-panels::page>
