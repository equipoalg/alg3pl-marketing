{{-- Floating bulk-action bar — shown when selectedIds is non-empty.
     Also renders the soft-undo toast for the last delete (within the recovery window).
     Expects: $selectedIds, $this->canUndo (computed), $lastDeletedSnapshot, $lastDeletedKind.
     Static helpers used directly. --}}
@php
    use App\Filament\Resources\TaskResource\Pages\ListTasks;
@endphp

{{-- Undo toast — only shown when there's a fresh delete to roll back.
     Stays visible for UNDO_WINDOW_SECONDS; after that the wire prop expires
     server-side and a re-render hides this bar. --}}
@if($this->canUndo)
    <div style="position:fixed;bottom:{{ count($selectedIds) > 0 ? '74px' : '20px' }};left:50%;transform:translateX(-50%);background:var(--alg-surface);border:1px solid var(--alg-line);box-shadow:0 8px 24px rgba(0,0,0,0.18);padding:8px 12px;border-radius:8px;display:flex;align-items:center;gap:10px;z-index:1001;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);">
        <span style="font-size:13px;color:var(--alg-warn);">↶</span>
        <span>
            @if($lastDeletedKind === 'bulk')
                {{ count($lastDeletedSnapshot) }} tareas eliminadas.
            @else
                Tarea eliminada.
            @endif
        </span>
        <button type="button" wire:click="undoLastDelete"
                style="padding:4px 10px;background:var(--alg-ink);color:#FFFFFF;border:none;border-radius:4px;cursor:pointer;font-family:inherit;font-size:11.5px;font-weight:600;letter-spacing:-0.005em;">
            Deshacer
        </button>
        <button type="button" wire:click="clearUndoSnapshot"
                title="Descartar"
                style="border:none;background:transparent;color:var(--alg-ink-5);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;">×</button>
    </div>
@endif

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
                    @php $c = ListTasks::priorityColor($pri); @endphp
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
