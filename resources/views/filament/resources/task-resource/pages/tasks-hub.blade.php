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
    {{-- Alpine wrapper: keyboard shortcuts (#10) + showShortcutsHelp toggle for the cheatsheet modal --}}
    <div x-data="{
            showHelp: false,
            isTyping(e) {
                const t = e.target;
                return t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable;
            },
            handle(e) {
                if (this.isTyping(e)) return;
                // Single-key shortcuts
                if (e.key === '/' && !e.shiftKey) {
                    e.preventDefault();
                    document.querySelector('input[wire\\:model\\.live\\.debounce\\.300ms=\'searchTerm\']')?.focus();
                } else if (e.key === '?') {
                    e.preventDefault();
                    this.showHelp = !this.showHelp;
                } else if (e.key === 'c' && !e.metaKey && !e.ctrlKey) {
                    e.preventDefault();
                    window.location.href = '{{ \App\Filament\Resources\TaskResource::getUrl('create') }}';
                } else if (e.key === 'l') {
                    e.preventDefault();
                    $wire.setViewMode('list');
                } else if (e.key === 'k') {
                    e.preventDefault();
                    $wire.setViewMode('kanban');
                } else if (e.key === 'Escape' && {{ $selected ? 'true' : 'false' }}) {
                    e.preventDefault();
                    $wire.closeDetail();
                }
            }
         }"
         x-on:keydown.window="handle($event)"
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
