{{-- Kanban view body — 4 cols by status, DnD via SortableJS.
     Expects: $kanbanColumns
     Static helpers from ListTasks used directly. --}}
@php
    use App\Filament\Resources\TaskResource\Pages\ListTasks;
@endphp
{{-- SortableJS — loaded once; @@1 escapes Blade's @ directive parser --}}
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
                            // Optimistic UI: Sortable already moved the DOM. If the
                            // backend write fails, roll back via try/catch around the
                            // promise and re-insert the node into evt.from.
                            const wireRoot = evt.to.closest('[wire\\:id]');
                            const wireId = wireRoot?.getAttribute('wire:id');
                            if (!wireId) return;
                            const promise = window.Livewire.find(wireId).call('moveTaskStatus', taskId, newStatus);
                            // Promise rejected → server failed → rollback DOM
                            (promise?.catch ? promise : Promise.resolve(promise)).catch(err => {
                                console.warn('moveTaskStatus failed, rolling back DnD', err);
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                            });
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
                        $pc = ListTasks::priorityColor($t->priority);
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
                                $av = ListTasks::avatarFor($t->assignee);
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
