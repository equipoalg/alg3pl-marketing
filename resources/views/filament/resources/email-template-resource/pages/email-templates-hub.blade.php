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
            .alg-tpl-kpis { grid-template-columns: repeat(2, 1fr) !important; }
            .alg-tpl-layout { grid-template-columns: 1fr !important; }
        }
        .alg-toggle {
            display:inline-flex;align-items:center;width:34px;height:18px;background:var(--alg-line);border-radius:10px;
            position:relative;cursor:pointer;transition:background 120ms ease;border:none;
        }
        .alg-toggle::after {
            content:'';position:absolute;top:2px;left:2px;width:14px;height:14px;background:#FFF;border-radius:50%;
            transition:left 120ms ease;
        }
        .alg-toggle.is-on { background:var(--alg-pos); }
        .alg-toggle.is-on::after { left:18px; }
    </style>

    @php
        $categoryOptions = [
            'welcome'      => 'Welcome',
            'follow_up'    => 'Follow up',
            'nurturing'    => 'Nurturing',
            'quote'        => 'Quote',
            'newsletter'   => 'Newsletter',
            'notification' => 'Notification',
            'custom'       => 'Custom',
        ];
        $hasFilters = $currentSearch !== '' || $currentCategory !== '' || $currentActive !== '' || $currentSort !== 'recent';
    @endphp

    <div class="alg-tpl-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '460px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Toolbar 1 --}}
        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Email Templates</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['total'] }}</span>

            <div style="position:relative;flex:1;min-width:220px;max-width:340px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nombre o subject…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            <select wire:model.live="sortBy" title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="recent">↻ Recién actualizados</option>
                <option value="name">A → Z</option>
                <option value="usage_desc">★ Más usados</option>
            </select>

            <div style="flex:1;"></div>

            <a href="{{ \App\Filament\Resources\EmailTemplateResource::getUrl('create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                Nuevo template
            </a>
        </div>

        {{-- Toolbar 2: Category + Active chips --}}
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:5px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 14px;">
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Categoría:</span>
            <button type="button" wire:click="setCategoryFilter('')" class="alg-chip {{ $currentCategory === '' ? 'is-active' : '' }}">Todas</button>
            @foreach($categoryOptions as $key => $lbl)
                <button type="button" wire:click="setCategoryFilter('{{ $key }}')" class="alg-chip {{ $currentCategory === $key ? 'is-active' : '' }}">{{ $lbl }}</button>
            @endforeach

            <span style="width:1px;height:20px;background:var(--alg-line);margin:0 8px;"></span>

            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;margin-right:4px;">Estado:</span>
            <button type="button" wire:click="setActiveFilter('')" class="alg-chip {{ $currentActive === '' ? 'is-active' : '' }}">Todos</button>
            <button type="button" wire:click="setActiveFilter('yes')" class="alg-chip {{ $currentActive === 'yes' ? 'is-active' : '' }}">Activos</button>
            <button type="button" wire:click="setActiveFilter('no')" class="alg-chip {{ $currentActive === 'no' ? 'is-active' : '' }}">Inactivos</button>

            @if($hasFilters)
                <button type="button" wire:click="clearFilters"
                        style="margin-left:auto;padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">× limpiar</button>
            @endif
        </div>

        {{-- KPI tiles (4) --}}
        <div class="alg-tpl-kpis" style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @php
                $tiles = [
                    ['Total templates', number_format($kpis['total']),    'var(--alg-ink)'],
                    ['Activos',         number_format($kpis['active']),   'var(--alg-pos)'],
                    ['Inactivos',       number_format($kpis['inactive']), 'var(--alg-ink-4)'],
                    ['Σ Usos enviados', number_format($kpis['usage']),    'var(--alg-accent)'],
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
            @if($templates->isEmpty())
                <div style="padding:48px 24px;text-align:center;">
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:500;color:var(--alg-ink);margin:0 0 6px;">Sin templates en este filtro</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0 0 18px;letter-spacing:.04em;">Crea un template para reutilizar copy/HTML en tus campañas.</p>
                    <a href="{{ \App\Filament\Resources\EmailTemplateResource::getUrl('create') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:inherit;font-size:12.5px;font-weight:500;border-radius:4px;">+ Nuevo template</a>
                </div>
            @else
                @php
                    $allOnPageIds = $templates->pluck('id')->all();
                    $allSelectedOnPage = ! empty($allOnPageIds) && empty(array_diff($allOnPageIds, $selectedIds ?? []));
                @endphp
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                    <thead>
                        <tr style="background:var(--alg-bg);">
                            <th style="width:32px;padding:9px 4px 9px 18px;border-bottom:1px solid var(--alg-line);">
                                <input type="checkbox"
                                       wire:click="{{ $allSelectedOnPage ? 'clearSelectedTemplates' : 'selectAllVisible' }}"
                                       {{ $allSelectedOnPage ? 'checked' : '' }}
                                       style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                            </th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Template</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Categoría</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">País</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Usos</th>
                            <th style="text-align:center;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Activo</th>
                            <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Actualizado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $t)
                            @php
                                $info = \App\Filament\Resources\EmailTemplateResource\Pages\ListEmailTemplates::categoryIcon($t->category);
                                $isActive = $selectedId === $t->id;
                                $isChecked = in_array($t->id, $selectedIds ?? [], true);
                                $rowBg = ($isActive || $isChecked) ? 'var(--alg-accent-soft)' : 'transparent';
                            @endphp
                            <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'"
                                @if($isActive || $isChecked) data-locked="1" @endif>
                                <td style="padding:11px 4px 11px 18px;text-align:center;" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:click="toggleSelected({{ $t->id }})"
                                           {{ $isChecked ? 'checked' : '' }}
                                           style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectTemplate({{ $t->id }})">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:4px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:13px;flex-shrink:0;">{{ $info['icon'] }}</span>
                                        <div style="min-width:0;">
                                            <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;">{{ $t->name }}</div>
                                            @if($t->subject)
                                                <div style="font-size:11px;color:var(--alg-ink-4);margin-top:1px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:340px;font-family:ui-monospace,'SF Mono',Menlo,monospace;">{{ $t->subject }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectTemplate({{ $t->id }})">
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:{{ $info['fg'] }};background:{{ $info['bg'] }};padding:1px 6px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $info['label'] }}</span>
                                </td>
                                <td style="padding:11px 12px;" wire:click="selectTemplate({{ $t->id }})">
                                    @if($t->country)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;letter-spacing:.06em;">{{ strtoupper($t->country->code) }}</span>
                                    @else
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);letter-spacing:.04em;">All</span>
                                    @endif
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-2);font-variant-numeric:tabular-nums;" wire:click="selectTemplate({{ $t->id }})">
                                    {{ number_format($t->usage_count ?? 0) }}
                                </td>
                                <td style="padding:11px 12px;text-align:center;" onclick="event.stopPropagation()">
                                    <button type="button" wire:click="toggleActive({{ $t->id }})"
                                            class="alg-toggle {{ $t->is_active ? 'is-on' : '' }}"
                                            title="{{ $t->is_active ? 'Activo (click para desactivar)' : 'Inactivo (click para activar)' }}"></button>
                                </td>
                                <td style="padding:11px 18px 11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;" wire:click="selectTemplate({{ $t->id }})">
                                    {{ $t->updated_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Slide-over con HTML preview --}}
    @if($selected)
        @php $info = \App\Filament\Resources\EmailTemplateResource\Pages\ListEmailTemplates::categoryIcon($selected->category); @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:6px;background:{{ $info['bg'] }};color:{{ $info['fg'] }};font-size:16px;flex-shrink:0;">{{ $info['icon'] }}</span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->name }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $info['label'] }} · {{ $selected->usage_count ?? 0 }} usos</div>
                    </div>
                </div>
                <button type="button" wire:click="closeTemplate" style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Nombre</label>
                        <input type="text" wire:model.live.debounce.500ms="editName"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end;">
                        <div>
                            <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Categoría</label>
                            <select wire:model.live="editCategory" style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                                @foreach($categoryOptions as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <label style="display:flex;align-items:center;gap:8px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);cursor:pointer;padding-bottom:9px;">
                            <input type="checkbox" wire:model.live="editIsActive" style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-pos);">
                            Activo
                        </label>
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Subject</label>
                        <input type="text" wire:model.live.debounce.500ms="editSubject"
                               placeholder="Hola {nombre}, sobre {empresa}…"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        <p style="margin:4px 0 0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-5);letter-spacing:.04em;">Variables: {nombre} · {empresa} · {email} · {pais} · {servicio}</p>
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Body HTML</label>
                        <textarea wire:model.live.debounce.700ms="editBodyHtml" rows="8"
                                  style="width:100%;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink);outline:none;resize:vertical;border-radius:3px;line-height:1.5;"></textarea>
                    </div>

                    {{-- Live preview --}}
                    @if($editBodyHtml)
                        <div>
                            <p style="margin:6px 0 4px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Preview</p>
                            <div style="padding:14px;background:#FFFFFF;border:1px solid var(--alg-line);border-radius:4px;max-height:280px;overflow-y:auto;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:#1A1A1A;line-height:1.5;">
                                {!! $editBodyHtml !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
                <button type="button" wire:click="saveTemplate"
                        style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">Guardar</button>
                <div style="flex:1;"></div>
                <a href="{{ \App\Filament\Resources\EmailTemplateResource::getUrl('edit', ['record' => $selected]) }}"
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
            <button type="button" wire:click="bulkActivate"
                    style="padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">✓ Activar</button>
            <button type="button" wire:click="bulkDeactivate"
                    style="padding:5px 11px;border:none;background:transparent;color:#FCA5A5;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(248,113,113,0.15)'"
                    onmouseout="this.style.background='transparent'">⊘ Desactivar</button>
            <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>
            <button type="button" wire:click="clearSelectedTemplates"
                    style="border:none;background:transparent;color:rgba(255,255,255,0.65);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;border-radius:4px;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">×</button>
        </div>
    @endif
</x-filament-panels::page>
