<x-filament-panels::page>
    <style>
        .fi-page > .fi-header,
        .fi-page-header { display: none !important; }
        .fi-main-ctn > .fi-page { padding-top: 0 !important; }
        .fi-main { padding-top: 0.5rem !important; }
        @media (max-width: 900px) {
            .alg-tag-kpis { grid-template-columns: repeat(2, 1fr) !important; }
            .alg-tag-layout { grid-template-columns: 1fr !important; }
        }
    </style>

    <div class="alg-tag-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '380px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Toolbar --}}
        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Tags</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['total'] }}</span>

            <div style="position:relative;flex:1;min-width:220px;max-width:340px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar tag…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            <select wire:model.live="sortBy" title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="name">A → Z</option>
                <option value="usage_desc">★ Más usados</option>
                <option value="recent">↻ Recién creados</option>
            </select>

            <div style="flex:1;"></div>

            <a href="{{ \App\Filament\Resources\TagResource::getUrl('create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                Nuevo tag
            </a>
        </div>

        {{-- KPI tiles (4) --}}
        <div class="alg-tag-kpis" style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @foreach([
                ['Total tags', number_format($kpis['total']),      'var(--alg-ink)'],
                ['Usados',     number_format($kpis['used']),       'var(--alg-pos)'],
                ['Sin uso',    number_format($kpis['unused']),     'var(--alg-ink-4)'],
                ['Σ Leads etiquetados', number_format($kpis['totalLeads']), 'var(--alg-accent)'],
            ] as [$lbl, $val, $color])
                <div style="padding:14px 16px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:4px;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-3);letter-spacing:-0.005em;">{{ $lbl }}</span>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;color:{{ $color }};letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ $val }}</span>
                </div>
            @endforeach
        </div>

        {{-- Body: tags como chips grid (más visual que tabla) --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:18px 20px;">
            @if($tags->isEmpty())
                <div style="padding:48px 20px;text-align:center;">
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:500;color:var(--alg-ink);margin:0 0 6px;">Sin tags</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0 0 18px;letter-spacing:.04em;">Etiquetá leads y clientes para segmentar campañas.</p>
                    <a href="{{ \App\Filament\Resources\TagResource::getUrl('create') }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:inherit;font-size:12.5px;font-weight:500;border-radius:4px;">+ Nuevo tag</a>
                </div>
            @else
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($tags as $t)
                        @php
                            $color = $t->color ?: '#6366F1';
                            $isActive = $selectedId === $t->id;
                            $usageCount = $t->leads_count ?? 0;
                        @endphp
                        <button type="button" wire:click="selectTag({{ $t->id }})"
                                style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid {{ $isActive ? $color : 'var(--alg-line)' }};background:{{ $isActive ? $color . '22' : 'var(--alg-bg)' }};color:var(--alg-ink);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;letter-spacing:-0.005em;border-radius:6px;transition:all 100ms ease;"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $isActive ? $color . '22' : 'var(--alg-bg)' }}'"
                                @if($isActive) data-locked="1" @endif>
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                            <span>{{ $t->name }}</span>
                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);font-weight:400;">{{ $usageCount }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Slide-over --}}
    @if($selected)
        @php $selColor = $selected->color ?: '#6366F1'; @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span style="display:inline-block;width:24px;height:24px;border-radius:6px;background:{{ $selColor }};flex-shrink:0;"></span>
                    <div style="min-width:0;">
                        <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->name }}</div>
                        <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $selected->leads_count ?? 0 }} leads etiquetados</div>
                    </div>
                </div>
                <button type="button" wire:click="closeTag" style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Nombre</label>
                        <input type="text" wire:model.live.debounce.500ms="editName"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Slug</label>
                        <input type="text" wire:model.live.debounce.500ms="editSlug"
                               style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Color</label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="color" wire:model.live.debounce.300ms="editColor"
                                   style="width:42px;height:34px;padding:2px;border:1px solid var(--alg-line);background:transparent;cursor:pointer;border-radius:3px;">
                            <input type="text" wire:model.live.debounce.500ms="editColor"
                                   style="flex:1;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Descripción</label>
                        <textarea wire:model.live.debounce.700ms="editDescription" rows="3"
                                  style="width:100%;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;resize:vertical;border-radius:3px;line-height:1.5;"></textarea>
                    </div>

                    {{-- Preview chip --}}
                    <div style="margin-top:8px;padding:12px;background:var(--alg-bg);border:1px dashed var(--alg-line);border-radius:4px;text-align:center;">
                        <p style="margin:0 0 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;">Preview</p>
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:10px;background:{{ $editColor ?: $selColor }};color:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;">{{ $editName ?: $selected->name }}</span>
                    </div>

                    {{-- Quick link a leads filtrados --}}
                    <a href="/admin/leads?view=contacts" target="_self"
                       style="margin-top:6px;padding:8px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);text-decoration:none;display:flex;align-items:center;justify-content:space-between;border-radius:4px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);">
                        <span>Ver los {{ $selected->leads_count ?? 0 }} leads con este tag →</span>
                    </a>
                </div>
            </div>

            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
                <button type="button" wire:click="saveTag"
                        style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">Guardar</button>
                <div style="flex:1;"></div>
                <a href="{{ \App\Filament\Resources\TagResource::getUrl('edit', ['record' => $selected]) }}"
                   style="padding:7px 10px;background:transparent;color:var(--alg-ink-4);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;border-radius:4px;">Editor →</a>
            </div>
        </aside>
    @endif

    </div>
</x-filament-panels::page>
