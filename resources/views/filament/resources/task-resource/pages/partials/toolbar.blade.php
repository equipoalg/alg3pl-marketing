{{-- Top toolbar — search · counts · view toggle · group-by · shortcuts · new.
     Expects: $viewMode, $totalAfterFilter, $totalUnfiltered --}}
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

    {{-- Mostrando X / Y — honest counter (always visible, even when 0) --}}
    <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;background:var(--alg-surface-2);border:1px solid var(--alg-line);border-radius:4px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);letter-spacing:.02em;white-space:nowrap;"
          title="Tareas que coinciden con tus filtros activos">
        <span style="color:var(--alg-ink);font-weight:600;">{{ $totalAfterFilter }}</span>
        <span style="color:var(--alg-ink-5);">de</span>
        <span>{{ $totalUnfiltered }}</span>
    </span>

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

    {{-- Keyboard shortcuts hint --}}
    <button type="button" @click="showHelp = true"
            title="Atajos de teclado (?)"
            style="border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-4);width:24px;height:24px;border-radius:4px;cursor:pointer;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">?</button>

    {{-- New task --}}
    <a href="{{ \App\Filament\Resources\TaskResource::getUrl('create') }}"
       style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
        <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
        Nueva tarea
    </a>
</div>
