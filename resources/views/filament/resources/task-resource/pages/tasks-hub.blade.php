<x-filament-panels::page>
    {{-- Filament wraps in its own .fi-page; we add a 2-col layout INSIDE.
         Color helpers and labels now live as static methods on ListTasks
         (App\Filament\Resources\TaskResource\Pages\ListTasks); each partial
         imports them directly. No more inline closure duplication. --}}
    <style>
        /* Suppress Filament's default page header so our toolbar is the only chrome */
        .fi-page > .fi-header { display: none !important; }
    </style>

    {{-- Grid: sidebar 220px | main flex | (right pane 420px when a task is selected) --}}
    {{-- Alpine wrapper: keyboard shortcuts + showShortcutsHelp modal toggle.
         Shortcut grammar:
           - Single keys: /, ?, c, j, k, Enter, 1-4, Esc
           - Vim-style "g" prefix:  g l → list view,  g k → kanban view
             (we keep the conflict-free single-key model; "k" alone is up-nav) --}}
    <div x-data="{
            showHelp: false,
            focusedIdx: -1,
            gWaiting: false,
            gTimer: null,
            isTyping(e) {
                const t = e.target;
                return t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable;
            },
            visibleRows() {
                // Document-ordered selectable elements. Used by j/k navigation.
                return Array.from(document.querySelectorAll('tr[data-task-id]'));
            },
            applyFocus() {
                const rows = this.visibleRows();
                rows.forEach((r, i) => {
                    if (i === this.focusedIdx) {
                        r.style.outline = '2px solid var(--alg-accent)';
                        r.style.outlineOffset = '-2px';
                        r.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    } else {
                        r.style.outline = '';
                        r.style.outlineOffset = '';
                    }
                });
            },
            moveFocus(delta) {
                const rows = this.visibleRows();
                if (rows.length === 0) return;
                this.focusedIdx = ((this.focusedIdx < 0 ? -1 : this.focusedIdx) + delta + rows.length) % rows.length;
                this.applyFocus();
            },
            focusedTaskId() {
                const rows = this.visibleRows();
                const r = rows[this.focusedIdx];
                return r ? parseInt(r.dataset.taskId, 10) : null;
            },
            beginGPrefix() {
                this.gWaiting = true;
                clearTimeout(this.gTimer);
                this.gTimer = setTimeout(() => { this.gWaiting = false; }, 800);
            },
            handle(e) {
                if (this.isTyping(e)) return;

                // Two-key 'g' prefix: g l → list, g k → kanban
                if (this.gWaiting) {
                    this.gWaiting = false;
                    clearTimeout(this.gTimer);
                    if (e.key === 'l') { e.preventDefault(); $wire.setViewMode('list'); return; }
                    if (e.key === 'k') { e.preventDefault(); $wire.setViewMode('kanban'); return; }
                    // Unknown 2nd key — fall through to single-key handling
                }
                if (e.key === 'g' && !e.metaKey && !e.ctrlKey && !e.shiftKey) {
                    e.preventDefault();
                    this.beginGPrefix();
                    return;
                }

                // Single-key shortcuts
                if (e.key === '/' && !e.shiftKey) {
                    e.preventDefault();
                    document.querySelector('input[wire\\:model\\.live\\.debounce\\.300ms=\'searchTerm\']')?.focus();
                    return;
                }
                if (e.key === '?') {
                    e.preventDefault();
                    this.showHelp = !this.showHelp;
                    return;
                }
                if (e.key === 'c' && !e.metaKey && !e.ctrlKey) {
                    e.preventDefault();
                    window.location.href = '{{ \App\Filament\Resources\TaskResource::getUrl('create') }}';
                    return;
                }
                if (e.key === 'Escape' && {{ $selected ? 'true' : 'false' }}) {
                    e.preventDefault();
                    $wire.closeDetail();
                    return;
                }

                // j / k row navigation (only meaningful in list view)
                if (e.key === 'j') { e.preventDefault(); this.moveFocus(+1); return; }
                if (e.key === 'k') { e.preventDefault(); this.moveFocus(-1); return; }

                // Enter — open the focused row's slide-over
                if (e.key === 'Enter') {
                    const id = this.focusedTaskId();
                    if (id) { e.preventDefault(); $wire.selectTask(id); return; }
                }

                // 1 / 2 / 3 / 4 — set priority of focused row
                if (['1','2','3','4'].includes(e.key) && !e.metaKey && !e.ctrlKey && !e.shiftKey) {
                    const id = this.focusedTaskId();
                    if (id) {
                        e.preventDefault();
                        const priorities = { '1': 'P0', '2': 'P1', '3': 'P2', '4': 'P3' };
                        $wire.setPriority(id, priorities[e.key]);
                    }
                }
            }
         }"
         x-on:keydown.window="handle($event)"
         x-init="$nextTick(() => applyFocus())"
         x-effect="applyFocus()"
         style="display:grid;grid-template-columns:220px 1fr {{ $selected ? '420px' : '' }};gap:18px;align-items:flex-start;font-family:var(--alg-font);position:relative;">

        {{-- LEFT SIDEBAR — filter presets + saved views --}}
        @include('filament.resources.task-resource.pages.partials.sidebar')

        {{-- RIGHT MAIN COLUMN --}}
        <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

            {{-- Focus banner — what should worry me right now --}}
            @include('filament.resources.task-resource.pages.partials.focus-banner')

            {{-- Toolbar: search · counts · view toggle · group-by · new task --}}
            @include('filament.resources.task-resource.pages.partials.toolbar')

            {{-- Filter chips row: priority / category / due — multi-select AND --}}
            @include('filament.resources.task-resource.pages.partials.filter-chips')

            {{-- Viewport-limit notice — shown only when filtered count > VIEWPORT_LIMIT.
                 Honest disclosure: we truncated. Old code silently dropped past row 500. --}}
            @if($wasLimited)
                <div style="background:var(--alg-warn-soft);border:1px solid var(--alg-warn);color:var(--alg-warn);padding:7px 14px;display:flex;align-items:center;gap:10px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;border-radius:4px;">
                    <span style="font-size:12px;">ⓘ</span>
                    <span style="flex:1;">
                        Mostrando las primeras <strong>{{ $viewportLimit }}</strong> tareas de <strong>{{ $totalAfterFilter }}</strong> que coinciden. Refiná los filtros para ver el resto.
                    </span>
                </div>
            @endif

            {{-- Body: list or kanban --}}
            @if($viewMode === 'list')
                @include('filament.resources.task-resource.pages.partials.list-body')
            @else
                @include('filament.resources.task-resource.pages.partials.kanban-body')
            @endif

        </div>

        {{-- RIGHT PANE — slide-over for selected task --}}
        @if($selected)
            @include('filament.resources.task-resource.pages.partials.task-detail-pane', ['selected' => $selected])
        @endif

        {{-- KEYBOARD SHORTCUTS HELP MODAL --}}
        @include('filament.resources.task-resource.pages.partials.shortcuts-help')

    </div>

    {{-- BULK ACTION BAR (floating bottom) — outside grid so it overlays everything --}}
    @include('filament.resources.task-resource.pages.partials.bulk-bar')
</x-filament-panels::page>
