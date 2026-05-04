{{-- Right-pane slide-over for the selected task. Expects: $selected.
     Color helpers come from ListTasks static methods (no longer params). --}}
@php
    use App\Filament\Resources\TaskResource\Pages\ListTasks;
    $pc = ListTasks::priorityColor($selected->priority);
    $sc = ListTasks::statusColor($selected->status);
    $isOverdue = $selected->due_date && $selected->due_date->isPast() && $selected->status !== 'done';
@endphp
<aside style="background:var(--alg-surface);border-left:1px solid var(--alg-line);position:sticky;top:14px;display:flex;flex-direction:column;max-height:calc(100vh - 28px);overflow:hidden;">

    {{-- Header: priority + close --}}
    <div style="padding:12px 16px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:8px;">
        <div style="display:flex;align-items:center;gap:8px;min-width:0;">
            {{-- Priority — click to change inline --}}
            <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:700;color:{{ $pc['fg'] }};background:{{ $pc['bg'] }};padding:3px 8px;border:none;border-radius:3px;letter-spacing:.04em;cursor:pointer;">
                    {{ $selected->priority }} ▾
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;top:calc(100% + 4px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:10;display:flex;flex-direction:column;gap:1px;">
                    @foreach(['P0','P1','P2','P3'] as $pri)
                        @php $c = ListTasks::priorityColor($pri); @endphp
                        <button type="button"
                                wire:click="setPriority({{ $selected->id }}, '{{ $pri }}')"
                                @click="open = false"
                                style="display:flex;align-items:center;gap:6px;padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                onmouseover="this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='transparent'">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $c['fg'] }};"></span>
                            {{ $pri }}
                        </button>
                    @endforeach
                </div>
            </div>
            {{-- Status — click to change inline --}}
            <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="font-size:10px;font-weight:500;color:{{ $sc['fg'] }};background:{{ $sc['bg'] }};padding:3px 8px;border:none;border-radius:3px;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;">
                    {{ ListTasks::statusLabel($selected->status) }} ▾
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;top:calc(100% + 4px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:10;display:flex;flex-direction:column;gap:1px;min-width:140px;">
                    @foreach(['pending','in_progress','blocked','done'] as $st)
                        @php $c = ListTasks::statusColor($st); @endphp
                        <button type="button"
                                wire:click="moveTaskStatus({{ $selected->id }}, '{{ $st }}')"
                                @click="open = false"
                                style="display:flex;align-items:center;gap:6px;padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                onmouseover="this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='transparent'">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $c['fg'] }};"></span>
                            {{ ListTasks::statusLabel($st) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        <button type="button" wire:click="closeDetail"
                title="Cerrar (Esc)"
                style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px;border-radius:3px;font-size:14px;line-height:1;"
                onmouseover="this.style.background='var(--alg-surface-2)'"
                onmouseout="this.style.background='transparent'">×</button>
    </div>

    {{-- Body --}}
    <div style="flex:1;overflow-y:auto;padding:16px;">
        {{-- Title (editable) --}}
        <textarea wire:model.live.debounce.500ms="editTitle"
                  rows="2"
                  placeholder="Título de la tarea"
                  style="width:100%;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:600;color:var(--alg-ink);background:transparent;border:none;outline:none;resize:none;letter-spacing:-0.015em;line-height:1.25;padding:0;margin-bottom:14px;"></textarea>

        {{-- Meta grid --}}
        <div style="display:grid;grid-template-columns:90px 1fr;gap:10px 12px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;margin-bottom:18px;">
            <span style="color:var(--alg-ink-4);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;align-self:center;">Categoría</span>
            <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                <button type="button" @click="open = !open"
                        style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:3px 8px;border:none;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;cursor:pointer;">
                    {{ $selected->category ?? 'seo' }} ▾
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     style="position:absolute;top:calc(100% + 4px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:10;display:flex;flex-direction:column;gap:1px;min-width:130px;">
                    @foreach(['seo','technical','content','ux','marketing','analytics'] as $cat)
                        <button type="button"
                                wire:click="setCategory({{ $selected->id }}, '{{ $cat }}')"
                                @click="open = false"
                                style="padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-2);text-align:left;border-radius:3px;text-transform:uppercase;letter-spacing:.06em;"
                                onmouseover="this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='transparent'">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>

            <span style="color:var(--alg-ink-4);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;align-self:center;">Vence</span>
            <input type="date"
                   wire:model.live="editDueDate"
                   wire:change="saveDetail"
                   style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:{{ $isOverdue ? 'var(--alg-neg)' : 'var(--alg-ink-2)' }};background:transparent;border:1px solid var(--alg-line);border-radius:3px;padding:3px 7px;outline:none;width:fit-content;">

            <span style="color:var(--alg-ink-4);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;align-self:center;">Asignado</span>
            <input type="text"
                   wire:model.lazy="editAssignee"
                   wire:change="saveDetail"
                   placeholder="email@equipo.com"
                   style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-2);background:transparent;border:1px solid var(--alg-line);border-radius:3px;padding:3px 7px;outline:none;">

            <span style="color:var(--alg-ink-4);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;align-self:center;">País</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-3);">
                @if($selected->country){{ $selected->country->name }} ({{ strtoupper($selected->country->code) }})@else— sin país —@endif
            </span>

            @if($selected->effort)
                <span style="color:var(--alg-ink-4);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;align-self:center;">Esfuerzo</span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-3);">{{ $selected->effort }}</span>
            @endif

            @if($selected->impact)
                <span style="color:var(--alg-ink-4);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;text-transform:uppercase;letter-spacing:.08em;align-self:center;">Impacto</span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-3);">{{ $selected->impact }}</span>
            @endif
        </div>

        {{-- Description --}}
        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:0 0 8px;">Descripción</p>
        <textarea wire:model.live.debounce.700ms="editDescription"
                  rows="6"
                  placeholder="Agregar descripción…"
                  style="width:100%;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-2);background:var(--alg-surface-2);border:1px solid var(--alg-line);border-radius:4px;padding:10px 12px;outline:none;resize:vertical;line-height:1.5;"></textarea>

        {{-- Notes --}}
        @if($selected->notes)
            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:18px 0 8px;">Notas</p>
            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-2);background:var(--alg-bg);border-left:2px solid var(--alg-line);padding:8px 12px;margin:0;line-height:1.5;">{{ $selected->notes }}</p>
        @endif

        {{-- Metadata footer --}}
        <div style="margin-top:24px;padding-top:14px;border-top:1px solid var(--alg-line);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);letter-spacing:.04em;">
            <div>Creada {{ $selected->created_at?->diffForHumans() }}</div>
            @if($selected->updated_at && $selected->updated_at->ne($selected->created_at))
                <div>Actualizada {{ $selected->updated_at->diffForHumans() }}</div>
            @endif
            <div>ID #{{ $selected->id }}</div>
        </div>
    </div>

    {{-- Footer: save / delete / open full editor --}}
    <div style="padding:10px 16px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
        <button type="button" wire:click="saveDetail"
                style="padding:6px 12px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
            Guardar
        </button>
        <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $selected]) }}"
           style="padding:6px 12px;background:var(--alg-surface);color:var(--alg-ink-2);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
            Editor completo
        </a>
        <div style="flex:1;"></div>
        <button type="button"
                wire:click="deleteSelected"
                wire:confirm="¿Eliminar esta tarea? No se puede deshacer."
                style="padding:6px 10px;background:transparent;color:var(--alg-neg);border:1px solid var(--alg-line);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
            Eliminar
        </button>
    </div>
</aside>
