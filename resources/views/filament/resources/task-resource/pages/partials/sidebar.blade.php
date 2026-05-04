{{-- Filter preset sidebar — left column of /admin/tasks.
     Expects from parent: $presets, $presetCounts, $filterPreset, $totalUnfiltered, $savedViews --}}
<aside style="position:sticky;top:14px;background:var(--alg-surface);border:1px solid var(--alg-line);">
    <div style="padding:14px 16px 10px;border-bottom:1px solid var(--alg-line);">
        <h3 style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Tareas</h3>
        <p style="margin:3px 0 0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);letter-spacing:.04em;"
           title="Tareas en tu país (sin filtros)">{{ $totalUnfiltered }} en total</p>
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

    {{-- Saved views (per-user, persisted in users.preferences JSON) --}}
    @if(! empty($savedViews))
        <div style="border-top:1px solid var(--alg-line);padding:8px 0;">
            <p style="margin:0 14px 4px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Mis vistas</p>
            @foreach($savedViews as $idx => $view)
                <div style="display:grid;grid-template-columns:18px 1fr auto;align-items:center;gap:8px;padding:5px 14px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);">
                    <span style="color:var(--alg-ink-4);font-size:11px;">📌</span>
                    <button type="button" wire:click="loadView({{ $idx }})"
                            style="border:none;background:transparent;text-align:left;color:inherit;cursor:pointer;padding:0;font-family:inherit;font-size:inherit;letter-spacing:-0.005em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $view['name'] }}</button>
                    <button type="button" wire:click="deleteView({{ $idx }})"
                            wire:confirm="¿Eliminar la vista &quot;{{ $view['name'] }}&quot;?"
                            title="Eliminar"
                            style="border:none;background:transparent;color:var(--alg-ink-5);cursor:pointer;padding:0 2px;font-size:11px;line-height:1;">✕</button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Save current view --}}
    <div x-data="{ open: false, name: '' }" style="border-top:1px solid var(--alg-line);padding:8px 14px;">
        <button type="button" @click="open = !open"
                style="width:100%;border:1px dashed var(--alg-line);background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:5px 8px;border-radius:3px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;letter-spacing:-0.005em;">
            💾 Guardar vista actual
        </button>
        <div x-show="open" x-cloak x-transition.opacity style="margin-top:6px;display:flex;gap:4px;">
            <input x-model="name" type="text" placeholder="Nombre…" maxlength="40"
                   x-on:keydown.enter="$wire.saveCurrentView(name); name=''; open=false"
                   style="flex:1;padding:4px 7px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
        </div>
    </div>
</aside>
