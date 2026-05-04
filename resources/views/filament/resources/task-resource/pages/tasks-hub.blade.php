<x-filament-panels::page>
    @php
        // Color helpers
        $priorityColor = fn (?string $p) => match ($p) {
            'P0' => ['bg' => 'var(--alg-neg-soft)',    'fg' => 'var(--alg-neg)'],
            'P1' => ['bg' => 'var(--alg-warn-soft)',   'fg' => 'var(--alg-warn)'],
            'P2' => ['bg' => 'var(--alg-accent-soft)', 'fg' => 'var(--alg-accent)'],
            default => ['bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-4)'],
        };
        $statusColor = fn (?string $s) => match ($s) {
            'done'        => ['bg' => 'var(--alg-pos-soft)',    'fg' => 'var(--alg-pos)'],
            'in_progress' => ['bg' => 'var(--alg-accent-soft)', 'fg' => 'var(--alg-accent)'],
            'blocked'     => ['bg' => 'var(--alg-neg-soft)',    'fg' => 'var(--alg-neg)'],
            default       => ['bg' => 'var(--alg-surface-2)',   'fg' => 'var(--alg-ink-3)'],
        };
        $statusLabel = fn (?string $s) => match ($s) {
            'pending'     => 'Pendiente',
            'in_progress' => 'En progreso',
            'blocked'     => 'Bloqueada',
            'done'        => 'Completada',
            default       => $s,
        };
    @endphp

    {{-- Filament wraps in its own .fi-page; we add a 2-col layout INSIDE --}}
    <style>
        /* Suppress Filament's default page header so our toolbar is the only chrome */
        .fi-page > .fi-header { display: none !important; }
    </style>

    {{-- Grid: sidebar 220px | main flex | (right pane 420px when a task is selected) --}}
    <div style="display:grid;grid-template-columns:220px 1fr {{ $selected ? '420px' : '' }};gap:18px;align-items:flex-start;font-family:var(--alg-font);">

        {{-- ════════════════════════ LEFT SIDEBAR — filter presets ════════════════════════ --}}
        <aside style="position:sticky;top:14px;background:var(--alg-surface);border:1px solid var(--alg-line);">
            <div style="padding:14px 16px 10px;border-bottom:1px solid var(--alg-line);">
                <h3 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Tareas</h3>
                <p style="margin:3px 0 0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);letter-spacing:.04em;">{{ $totalShown }} visibles</p>
            </div>
            <nav style="display:flex;flex-direction:column;padding:6px 0;">
                @foreach($presets as $key => $preset)
                    @php $isActive = $filterPreset === $key; @endphp
                    <button type="button"
                            wire:click="setFilterPreset('{{ $key }}')"
                            style="display:grid;grid-template-columns:18px 1fr auto;align-items:center;gap:8px;border:none;background:{{ $isActive ? 'var(--alg-surface-2)' : 'transparent' }};color:{{ $isActive ? 'var(--alg-ink)' : 'var(--alg-ink-2)' }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:{{ $isActive ? '500' : '400' }};letter-spacing:-0.005em;padding:7px 14px;cursor:pointer;text-align:left;border-left:2px solid {{ $isActive ? 'var(--alg-accent)' : 'transparent' }};">
                        <span style="font-size:11px;color:{{ $isActive ? 'var(--alg-accent)' : 'var(--alg-ink-4)' }};">{{ $preset['icon'] }}</span>
                        <span>{{ $preset['label'] }}</span>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);font-weight:500;">{{ $presetCounts[$key] ?? 0 }}</span>
                    </button>
                @endforeach
            </nav>
        </aside>

        {{-- ════════════════════════ RIGHT MAIN ════════════════════════ --}}
        <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

            {{-- Focus banner — what should worry me right now --}}
            @if($banner['level'] === 'critical' || $banner['level'] === 'warning')
                <div style="background:{{ $banner['level'] === 'critical' ? 'var(--alg-neg-soft)' : 'var(--alg-warn-soft)' }};border:1px solid {{ $banner['level'] === 'critical' ? 'var(--alg-neg)' : 'var(--alg-warn)' }};color:{{ $banner['level'] === 'critical' ? 'var(--alg-neg)' : 'var(--alg-warn)' }};padding:9px 14px;display:flex;align-items:center;gap:10px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;border-radius:4px;">
                    <span style="font-size:14px;">{{ $banner['level'] === 'critical' ? '⚠' : '🔥' }}</span>
                    <span style="flex:1;">
                        @if($banner['overdue'] > 0)
                            Tienes <strong>{{ $banner['overdue'] }}</strong> {{ $banner['overdue'] === 1 ? 'tarea vencida' : 'tareas vencidas' }}{{ $banner['dueTodayHigh'] > 0 ? ' y ' : '' }}{!! $banner['dueTodayHigh'] > 0 ? '<strong>' . $banner['dueTodayHigh'] . '</strong> de alta prioridad para hoy' : '' !!}.
                        @else
                            Tienes <strong>{{ $banner['dueTodayHigh'] }}</strong> {{ $banner['dueTodayHigh'] === 1 ? 'tarea de alta prioridad' : 'tareas de alta prioridad' }} para hoy.
                        @endif
                    </span>
                    <button type="button"
                            wire:click="setFilterPreset('{{ $banner['overdue'] > 0 ? 'overdue' : 'high_priority' }}')"
                            style="padding:4px 11px;border:1px solid currentColor;background:transparent;color:inherit;font-family:inherit;font-size:11.5px;font-weight:600;cursor:pointer;border-radius:3px;letter-spacing:-0.005em;">
                        Mostrar →
                    </button>
                </div>
            @elseif($banner['level'] === 'good')
                <div style="background:var(--alg-pos-soft);border:1px solid var(--alg-pos);color:var(--alg-pos);padding:7px 14px;display:flex;align-items:center;gap:10px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
                    <span style="font-size:13px;">✓</span>
                    <span>Sin tareas vencidas ni alta prioridad para hoy. Buen día.</span>
                </div>
            @endif

            {{-- Toolbar: search · view toggle · group-by · new task --}}
            <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 12px;flex-wrap:wrap;">
                {{-- Search --}}
                <div style="position:relative;flex:1;min-width:200px;max-width:380px;">
                    <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                        <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchTerm"
                           placeholder="Buscar tareas…"
                           style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
                </div>

                {{-- View toggle: Lista | Kanban --}}
                <div style="display:inline-flex;background:var(--alg-surface-2);border:1px solid var(--alg-line);border-radius:5px;padding:1px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;letter-spacing:-0.005em;">
                    <button type="button"
                            wire:click="setViewMode('list')"
                            title="Vista lista"
                            style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:{{ $viewMode === 'list' ? 'var(--alg-surface)' : 'transparent' }};color:{{ $viewMode === 'list' ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};border-radius:4px;cursor:pointer;font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h14M3 10h14M3 14h14"/></svg>
                        Lista
                    </button>
                    <button type="button"
                            wire:click="setViewMode('kanban')"
                            title="Vista Kanban"
                            style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:{{ $viewMode === 'kanban' ? 'var(--alg-surface)' : 'transparent' }};color:{{ $viewMode === 'kanban' ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};border-radius:4px;cursor:pointer;font-family:inherit;font-size:inherit;font-weight:inherit;letter-spacing:inherit;">
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="4" height="14" rx="1"/><rect x="9" y="3" width="4" height="9" rx="1"/><rect x="15" y="3" width="2" height="6" rx="1"/></svg>
                        Kanban
                    </button>
                </div>

                {{-- Group by (only relevant in list mode) --}}
                @if($viewMode === 'list')
                    <select wire:model.live="groupBy"
                            style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                        <option value="status">Agrupar por estado</option>
                        <option value="priority">Agrupar por prioridad</option>
                        <option value="category">Agrupar por categoría</option>
                        <option value="country">Agrupar por país</option>
                        <option value="assignee">Agrupar por asignado</option>
                        <option value="none">Sin agrupar</option>
                    </select>
                @endif

                <div style="flex:1;"></div>

                {{-- New task --}}
                <a href="{{ \App\Filament\Resources\TaskResource::getUrl('create') }}"
                   style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
                    <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
                    Nueva tarea
                </a>
            </div>

            {{-- ─── Filter chips row (priority / category / due) — multi-select AND ─── --}}
            @php
                $priChips = array_filter(explode(',', $priorityFilter));
                $catChips = array_filter(explode(',', $categoryFilter));
                $hasAnyChip = ! empty($priChips) || ! empty($catChips) || $dueFilter !== '';
            @endphp
            <div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 12px;display:flex;flex-wrap:wrap;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;">
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.08em;margin-right:4px;">Prioridad</span>
                @foreach(['P0','P1','P2','P3'] as $pri)
                    @php $isOn = in_array($pri, $priChips, true); $c = $priorityColor($pri); @endphp
                    <button type="button" wire:click="togglePriorityChip('{{ $pri }}')"
                            style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border:1px solid {{ $isOn ? $c['fg'] : 'var(--alg-line)' }};background:{{ $isOn ? $c['bg'] : 'transparent' }};color:{{ $isOn ? $c['fg'] : 'var(--alg-ink-4)' }};font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;font-weight:600;border-radius:3px;cursor:pointer;letter-spacing:.04em;">
                        {{ $pri }}
                    </button>
                @endforeach

                <span style="width:1px;height:18px;background:var(--alg-line);margin:0 4px;"></span>

                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.08em;margin-right:4px;">Categoría</span>
                @foreach(['seo','technical','content','ux','marketing','analytics'] as $cat)
                    @php $isOn = in_array($cat, $catChips, true); @endphp
                    <button type="button" wire:click="toggleCategoryChip('{{ $cat }}')"
                            style="padding:3px 9px;border:1px solid {{ $isOn ? 'var(--alg-accent)' : 'var(--alg-line)' }};background:{{ $isOn ? 'var(--alg-accent-soft)' : 'transparent' }};color:{{ $isOn ? 'var(--alg-accent)' : 'var(--alg-ink-4)' }};font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;border-radius:3px;cursor:pointer;text-transform:uppercase;letter-spacing:.06em;">
                        {{ $cat }}
                    </button>
                @endforeach

                <span style="width:1px;height:18px;background:var(--alg-line);margin:0 4px;"></span>

                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.08em;margin-right:4px;">Vence</span>
                @foreach(['today'=>'Hoy','this_week'=>'Esta semana','this_month'=>'Este mes','overdue'=>'Vencidas'] as $key=>$label)
                    @php $isOn = $dueFilter === $key; @endphp
                    <button type="button" wire:click="toggleDueChip('{{ $key }}')"
                            style="padding:3px 9px;border:1px solid {{ $isOn ? 'var(--alg-warn)' : 'var(--alg-line)' }};background:{{ $isOn ? 'var(--alg-warn-soft)' : 'transparent' }};color:{{ $isOn ? 'var(--alg-warn)' : 'var(--alg-ink-4)' }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;border-radius:3px;cursor:pointer;font-weight:500;">
                        {{ $label }}
                    </button>
                @endforeach

                @if($hasAnyChip)
                    <button type="button" wire:click="clearAllChips"
                            style="margin-left:auto;padding:3px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">
                        × limpiar
                    </button>
                @endif
            </div>

            {{-- ════════════════════════ BODY: List view OR Kanban view ════════════════════════ --}}

            @if($viewMode === 'list')
                {{-- ─────────────── LIST VIEW (grouped table) ─────────────── --}}
                <div style="background:var(--alg-surface);border:1px solid var(--alg-line);">
                    @if($tasks->isEmpty())
                        <div style="padding:48px;text-align:center;color:var(--alg-ink-4);font-size:13px;">
                            No hay tareas en este filtro.
                        </div>
                    @elseif($groupBy === 'none')
                        @include('filament.resources.task-resource.pages.partials.task-list-rows', ['rows' => $tasks, 'priorityColor' => $priorityColor, 'statusColor' => $statusColor, 'statusLabel' => $statusLabel, 'selectedIds' => $selectedIds])
                        {{-- Quick-add at the bottom of ungrouped list --}}
                        <div x-data="{ title: '' }" style="padding:8px 16px;border-top:1px solid var(--alg-line);">
                            <input type="text"
                                   x-model="title"
                                   x-on:keydown.enter="$wire.quickAdd(title, 'pending'); title=''"
                                   placeholder="+ Nueva tarea (Enter para crear)"
                                   style="width:100%;padding:6px 10px;border:1px dashed var(--alg-line);background:transparent;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
                        </div>
                    @else
                        @foreach($grouped as $groupKey => $rows)
                            @php
                                $groupLabel = match ($groupBy) {
                                    'status'   => $statusLabel($groupKey),
                                    default    => $groupKey,
                                };
                                // Map group key → status for quick-add (only when grouping by status,
                                // otherwise default to 'pending' since other groupings don't pre-set status)
                                $quickAddStatus = $groupBy === 'status' ? $groupKey : 'pending';
                            @endphp
                            <details open style="border-bottom:1px solid var(--alg-line);">
                                <summary style="padding:10px 16px;background:var(--alg-surface-2);cursor:pointer;display:flex;align-items:center;gap:10px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;font-weight:600;color:var(--alg-ink-2);text-transform:uppercase;letter-spacing:.08em;">
                                    <span style="display:inline-block;width:0;height:0;border:4px solid transparent;border-left-color:var(--alg-ink-3);transform:rotate(0);transition:transform 120ms;"></span>
                                    <span>{{ $groupLabel }}</span>
                                    <span style="color:var(--alg-ink-4);font-weight:500;">{{ count($rows) }}</span>
                                </summary>
                                @include('filament.resources.task-resource.pages.partials.task-list-rows', ['rows' => $rows, 'priorityColor' => $priorityColor, 'statusColor' => $statusColor, 'statusLabel' => $statusLabel, 'selectedIds' => $selectedIds])
                                {{-- Quick-add inline at the foot of every group — pre-fills status when grouping by status --}}
                                <div x-data="{ title: '' }" style="padding:6px 16px 10px;background:var(--alg-bg);">
                                    <input type="text"
                                           x-model="title"
                                           x-on:keydown.enter="$wire.quickAdd(title, '{{ $quickAddStatus }}'); title=''"
                                           placeholder="+ Nueva tarea en {{ $groupLabel }}"
                                           style="width:100%;padding:5px 10px;border:1px dashed var(--alg-line);background:transparent;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-3);outline:none;border-radius:3px;">
                                </div>
                            </details>
                        @endforeach
                    @endif
                </div>

            @else
                {{-- ─────────────── KANBAN VIEW (4 cols by status, DnD via SortableJS) ─────────────── --}}
                {{-- SortableJS — loaded once for the page, re-init on Livewire morph --}}
                <script src="https://cdn.jsdelivr.net/npm/sortablejs@@1.15.2/Sortable.min.js" defer></script>
                <script>
                    (function(){
                        function initKanbanDnD() {
                            document.querySelectorAll('[data-kanban-column]').forEach(col => {
                                if (col._sortableInit) return;
                                col._sortableInit = true;
                                if (typeof Sortable === 'undefined') return; // not loaded yet — try again
                                Sortable.create(col, {
                                    group: 'kanban-tasks',
                                    animation: 160,
                                    ghostClass: 'alg-kanban-ghost',
                                    dragClass: 'alg-kanban-drag',
                                    onEnd: function (evt) {
                                        const taskId = parseInt(evt.item.dataset.taskId, 10);
                                        const newStatus = evt.to.dataset.kanbanColumn;
                                        const oldStatus = evt.from.dataset.kanbanColumn;
                                        if (taskId && newStatus && newStatus !== oldStatus) {
                                            // Fire Livewire — backend updates DB; UI already moved by Sortable
                                            window.Livewire.find(evt.to.closest('[wire\\:id]').getAttribute('wire:id'))
                                                .call('moveTaskStatus', taskId, newStatus);
                                        }
                                    }
                                });
                            });
                        }
                        // Run on load + after Livewire morph (period button, search, etc.)
                        if (typeof Sortable !== 'undefined') {
                            initKanbanDnD();
                        } else {
                            window.addEventListener('load', initKanbanDnD);
                        }
                        document.addEventListener('livewire:initialized', () => {
                            initKanbanDnD();
                            window.Livewire?.hook('morph.updated', () => {
                                // Reset sortable flag so it can rebind on the new DOM
                                document.querySelectorAll('[data-kanban-column]').forEach(c => c._sortableInit = false);
                                initKanbanDnD();
                            });
                        });
                    })();
                </script>
                <style>
                    .alg-kanban-ghost { opacity: 0.35; background: var(--alg-accent-soft) !important; }
                    .alg-kanban-drag  { cursor: grabbing; transform: rotate(1deg); box-shadow: 0 8px 20px rgba(0,0,0,0.18); }
                    [data-kanban-column] { min-height: 60px; }
                </style>

                <div style="display:grid;grid-template-columns:repeat({{ count($kanbanColumns) }},1fr);gap:12px;align-items:flex-start;">
                    @foreach($kanbanColumns as $statusKey => $col)
                        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);min-height:200px;display:flex;flex-direction:column;">
                            {{-- Column header --}}
                            <div style="padding:10px 14px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;background:var(--alg-surface-2);">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $col['color'] }};"></span>
                                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">{{ $col['label'] }}</span>
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);font-weight:500;">{{ count($col['tasks']) }}</span>
                                </div>
                            </div>

                            {{-- Quick-add input at top of EVERY column (creates with that status pre-set) --}}
                            <div x-data="{ title: '' }" style="padding:8px 10px;border-bottom:1px solid var(--alg-line);">
                                <input type="text"
                                       x-model="title"
                                       x-on:keydown.enter="$wire.quickAdd(title, '{{ $statusKey }}'); title=''"
                                       placeholder="+ Nueva en {{ strtolower($col['label']) }}"
                                       style="width:100%;padding:6px 8px;border:1px dashed var(--alg-line);background:transparent;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                            </div>

                            {{-- Cards container — drag/drop target --}}
                            <div data-kanban-column="{{ $statusKey }}"
                                 style="display:flex;flex-direction:column;gap:6px;padding:8px 10px;flex:1;">
                                @forelse($col['tasks'] as $t)
                                    @php
                                        $pc = $priorityColor($t->priority);
                                        $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                                    @endphp
                                    <div class="alg-hover-lift"
                                         data-task-id="{{ $t->id }}"
                                         style="background:var(--alg-bg);border:1px solid var(--alg-line);border-left:3px solid {{ $pc['fg'] }};border-radius:4px;padding:8px 10px;display:flex;flex-direction:column;gap:5px;cursor:grab;">
                                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;">
                                            {{-- Title click → slide-over (not edit page) --}}
                                            <button type="button" wire:click.stop="selectTask({{ $t->id }})"
                                                    style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);font-weight:500;line-height:1.35;background:transparent;border:none;text-align:left;padding:0;cursor:pointer;letter-spacing:-0.005em;">{{ $t->title }}</button>
                                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:700;color:{{ $pc['fg'] }};background:{{ $pc['bg'] }};padding:1px 5px;border-radius:2px;flex-shrink:0;">{{ $t->priority }}</span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);">
                                            @if($t->category)
                                                <span style="text-transform:uppercase;letter-spacing:.06em;">{{ $t->category }}</span>
                                            @endif
                                            @if($t->due_date)
                                                <span style="color:{{ $isOverdue ? 'var(--alg-neg)' : 'var(--alg-ink-4)' }};">{{ $t->due_date->format('d M') }}</span>
                                            @endif
                                            @if($t->country)
                                                <span style="margin-left:auto;background:var(--alg-surface-2);padding:0 4px;border-radius:2px;color:var(--alg-ink-3);">{{ strtoupper($t->country->code) }}</span>
                                            @endif
                                        </div>
                                        @if($t->assignee)
                                            @php
                                                $av = \App\Filament\Resources\TaskResource\Pages\ListTasks::avatarFor($t->assignee);
                                                $assigneeShort = strtok($t->assignee, '@');
                                            @endphp
                                            <div style="display:flex;align-items:center;gap:5px;margin-top:2px;">
                                                <span title="{{ $t->assignee }}"
                                                      style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:8.5px;font-weight:600;">{{ $av['initials'] }}</span>
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-5);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $assigneeShort }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div style="padding:24px 8px;text-align:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);letter-spacing:.04em;">arrastrá tareas aquí</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- ════════════════════════ RIGHT PANE (slide-over) ════════════════════════ --}}
        @if($selected)
            @include('filament.resources.task-resource.pages.partials.task-detail-pane', [
                'selected'      => $selected,
                'priorityColor' => $priorityColor,
                'statusColor'   => $statusColor,
                'statusLabel'   => $statusLabel,
            ])
        @endif

    </div>

    {{-- ════════════════════════ BULK ACTION BAR (floating bottom) ════════════════════════ --}}
    @if(count($selectedIds) > 0)
        <div style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--alg-ink);color:#FFFFFF;padding:10px 14px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.35);display:flex;align-items:center;gap:12px;z-index:1000;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
            <span style="font-weight:600;">{{ count($selectedIds) }} seleccionada{{ count($selectedIds) > 1 ? 's' : '' }}</span>
            <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>

            <button type="button" wire:click="bulkMarkDone"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">
                ✓ Marcar Done
            </button>

            <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                        onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                        onmouseout="this.style.background='transparent'">
                    ★ Prioridad ▾
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;bottom:calc(100% + 4px);left:0;background:var(--alg-ink);border:1px solid rgba(255,255,255,0.15);border-radius:6px;padding:4px;display:flex;flex-direction:column;gap:1px;min-width:120px;">
                    @foreach(['P0','P1','P2','P3'] as $pri)
                        @php $c = $priorityColor($pri); @endphp
                        <button type="button" wire:click="bulkSetPriority('{{ $pri }}')" @click="open = false"
                                style="display:flex;align-items:center;gap:6px;padding:5px 10px;border:none;background:transparent;color:#FFFFFF;font-family:inherit;font-size:inherit;text-align:left;cursor:pointer;border-radius:4px;"
                                onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                                onmouseout="this.style.background='transparent'">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:{{ $c['fg'] }};"></span>{{ $pri }}
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="button" wire:click="bulkAssignToMe"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">
                👤 Asignarme
            </button>

            <button type="button" wire:click="bulkDelete"
                    wire:confirm="¿Eliminar las {{ count($selectedIds) }} tareas seleccionadas? No se puede deshacer."
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:transparent;color:#FCA5A5;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(248,113,113,0.15)'"
                    onmouseout="this.style.background='transparent'">
                🗑 Eliminar
            </button>

            <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>

            <button type="button" wire:click="clearSelected"
                    title="Quitar selección"
                    style="border:none;background:transparent;color:rgba(255,255,255,0.65);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;border-radius:4px;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">×</button>
        </div>
    @endif
</x-filament-panels::page>
