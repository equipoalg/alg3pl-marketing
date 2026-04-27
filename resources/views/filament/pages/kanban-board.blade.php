<x-filament-panels::page>
@php
    $allTasks = collect();
    foreach ($columns as $col) $allTasks = $allTasks->merge($col['tasks']);
    $labelColors = ['red'=>'var(--alg-neg)','orange'=>'var(--alg-warn)','yellow'=>'var(--alg-warn)','green'=>'var(--alg-pos)','blue'=>'var(--alg-accent-2)','purple'=>'#7C3AED','pink'=>'#BE185D','gray'=>'var(--alg-ink-3)'];
    $pColors = ['P0'=>'var(--alg-neg)','P1'=>'var(--alg-warn)','P2'=>'var(--alg-accent-2)','P3'=>'var(--alg-ink-3)'];
    $colColors = ['pending'=>'var(--alg-ink-3)','in_progress'=>'var(--alg-accent)','blocked'=>'var(--alg-neg)','done'=>'var(--alg-pos)'];
    $wipLimit = 5;
@endphp

<div x-data="kanbanBoard()" @keydown.window="handleKey($event)" style="font-family:Inter,-apple-system,sans-serif;">

    {{-- PROGRESS BAR --}}
    @php
        $pPct = $totalTasks > 0 ? round($columns['pending']['tasks']->count() / $totalTasks * 100) : 0;
        $iPct = $totalTasks > 0 ? round($columns['in_progress']['tasks']->count() / $totalTasks * 100) : 0;
        $bPct = $totalTasks > 0 ? round($columns['blocked']['tasks']->count() / $totalTasks * 100) : 0;
        $dPct = $totalTasks > 0 ? 100 - $pPct - $iPct - $bPct : 0;
    @endphp
    <div style="display:flex;height:4px;border-radius:4px;overflow:hidden;background:var(--alg-line);">
        <div style="width:{{ $pPct }}%;background:var(--alg-ink-3);transition:width 0.4s ease-out;"></div>
        <div style="width:{{ $iPct }}%;background:var(--alg-accent);transition:width 0.4s ease-out;"></div>
        <div style="width:{{ $bPct }}%;background:var(--alg-neg);transition:width 0.4s ease-out;"></div>
        <div style="width:{{ $dPct }}%;background:var(--alg-pos);transition:width 0.4s ease-out;"></div>
    </div>

    {{-- TOOLBAR --}}
    <div style="display:flex;align-items:center;gap:10px;padding:12px 0 8px;flex-wrap:wrap;">
        <button wire:click="toggleMyTasks" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid {{ $myTasksOnly ? 'var(--alg-accent)' : 'var(--alg-line)' }};cursor:pointer;background:{{ $myTasksOnly ? 'rgba(0,36,61,0.06)' : '#fff' }};color:{{ $myTasksOnly ? 'var(--alg-accent)' : 'var(--alg-ink-2)' }};transition:all 0.15s ease;">
            {{ $myTasksOnly ? 'Mis Tareas' : 'Todas' }}
        </button>

        <button @click="compact = !compact" style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:500;border:1px solid var(--alg-line);cursor:pointer;background:#fff;color:var(--alg-ink-2);">
            <span x-text="compact ? 'Expandir' : 'Compacto'"></span>
        </button>

        <span style="font-size:12px;color:var(--alg-ink-3);margin-left:auto;">{{ $totalTasks }} tareas</span>
        <a href="/admin/tasks/create" style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;background:var(--alg-ink);color:#fff;text-decoration:none;">
            + Nueva Tarea
        </a>
    </div>

    {{-- COLUMNS --}}
    <div style="display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;min-height:68vh;align-items:flex-start;">
        @foreach($columns as $status => $col)
        @php
            $count = $col['tasks']->count();
            $isWip = $status === 'in_progress' && $count > $wipLimit;
            $cc = $colColors[$status];
        @endphp
        <div
            class="kanban-col"
            data-status="{{ $status }}"
            @dragover.prevent="dragOver($event, '{{ $status }}')"
            @dragleave="dragLeave($event)"
            @drop="drop($event, '{{ $status }}')"
            style="flex:1;min-width:250px;max-width:340px;background:#fff;border:1px solid var(--alg-line);border-radius:12px;display:flex;flex-direction:column;transition:border-color 0.2s ease-out;box-shadow:0 1px 3px rgba(0,0,0,0.04);"
            :style="dropTarget === '{{ $status }}' ? 'border-color:{{ $cc }};' : ''"
        >
            {{-- Column header --}}
            <div style="padding:14px 16px 12px;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--alg-line);">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $cc }};"></span>
                <span style="font-size:12px;font-weight:700;color:var(--alg-ink);text-transform:uppercase;letter-spacing:0.05em;">{{ $col['label'] }}</span>
                @if($isWip)
                <span style="font-size:10px;font-weight:700;color:var(--alg-neg);background:var(--alg-neg-soft);padding:2px 7px;border-radius:4px;">WIP {{ $count }}/{{ $wipLimit }}</span>
                @endif
                <span style="font-size:12px;font-weight:700;color:var(--alg-ink-3);margin-left:auto;">{{ $count }}</span>
            </div>

            {{-- Cards --}}
            <div class="kanban-cards" style="padding:8px;display:flex;flex-direction:column;gap:6px;flex:1;overflow-y:auto;max-height:calc(68vh - 100px);">
                @forelse($col['tasks'] as $task)
                @php
                    $dd = null;
                    if ($task->due_date) {
                        $days = (int) now()->startOfDay()->diffInDays($task->due_date->startOfDay(), false);
                        $ddLabel = $days > 0 ? "en {$days}d" : ($days < 0 ? abs($days)."d atrás" : "hoy");
                        $ddColor = ($days < 0 && $task->status !== 'done') ? 'var(--alg-neg)' : ($days <= 3 ? 'var(--alg-warn)' : 'var(--alg-ink-3)');
                        $dd = ['label' => $ddLabel, 'color' => $ddColor];
                    }
                    $cl = $task->checklist ?? [];
                    $clTotal = count($cl);
                    $clDone = collect($cl)->where('done', true)->count();
                    $taskLabels = $task->labels ?? [];
                    $pc = $pColors[$task->priority] ?? 'var(--alg-ink-3)';
                @endphp
                <div
                    x-show="shouldShowCard({{ json_encode($taskLabels) }})"
                    draggable="true"
                    data-id="{{ $task->id }}"
                    @dragstart="dragStart($event, {{ $task->id }})"
                    @dragend="dragEnd()"
                    @click="selected = (selected === {{ $task->id }} ? null : {{ $task->id }})"
                    @dblclick="window.location.href='/admin/tasks/{{ $task->id }}/edit'"
                    style="background:#fff;border:1px solid var(--alg-line);border-radius:8px;cursor:grab;transition:all 0.15s ease;position:relative;"
                    :style="draggingId === {{ $task->id }} ? 'opacity:0.3;' : (selected === {{ $task->id }} ? 'border-color:var(--alg-accent);box-shadow:0 0 0 1px var(--alg-accent);' : '')"
                    onmouseover="if(!this.style.boxShadow)this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'"
                    onmouseout="if(!this.style.boxShadow.includes('00243D'))this.style.boxShadow=''"
                >
                    {{-- COMPACT --}}
                    <div x-show="compact" style="padding:10px 12px;display:flex;align-items:center;gap:8px;">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $pc }};flex-shrink:0;"></span>
                        <span style="font-size:13px;font-weight:500;color:var(--alg-ink);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $task->title }}</span>
                        @if($dd)<span style="font-size:10px;color:{{ $dd['color'] }};">{{ $dd['label'] }}</span>@endif
                    </div>

                    {{-- EXPANDED --}}
                    <div x-show="!compact" style="padding:14px 16px;">
                        {{-- Priority dot + labels --}}
                        <div style="display:flex;align-items:center;gap:5px;margin-bottom:8px;">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $pc }};flex-shrink:0;"></span>
                            <span style="font-size:10px;font-weight:600;color:var(--alg-ink-3);text-transform:uppercase;">{{ $task->priority }}</span>
                            @foreach($taskLabels as $lbl)
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $labelColors[$lbl] ?? 'var(--alg-ink-3)' }};"></span>
                            @endforeach
                            @if($task->country)<span style="margin-left:auto;font-size:10px;color:var(--alg-ink-5);">{{ strtoupper($task->country->code) }}</span>@endif
                        </div>

                        {{-- Title --}}
                        <p style="font-size:14px;font-weight:600;color:var(--alg-ink);margin:0 0 8px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $task->title }}</p>

                        {{-- Expandable detail on select --}}
                        <div x-show="selected === {{ $task->id }}" x-transition.opacity.duration.200ms style="margin-bottom:10px;">
                            @if($task->description)
                            <p style="font-size:13px;color:var(--alg-ink-2);line-height:1.5;margin:0 0 8px;">{{ Str::limit($task->description, 120) }}</p>
                            @endif
                            <div style="display:flex;gap:6px;">
                                <button wire:click="markDone({{ $task->id }})" style="font-size:11px;padding:4px 10px;border-radius:6px;background:var(--alg-pos-soft);color:var(--alg-pos);border:none;cursor:pointer;font-weight:600;">Completar</button>
                                <a href="/admin/tasks/{{ $task->id }}/edit" style="font-size:11px;padding:4px 10px;border-radius:6px;background:var(--alg-surface-2);color:var(--alg-ink-2);text-decoration:none;font-weight:600;">Editar</a>
                                <button wire:click="duplicateTask({{ $task->id }})" style="font-size:11px;padding:4px 10px;border-radius:6px;background:var(--alg-surface-2);color:var(--alg-ink-3);border:none;cursor:pointer;">Duplicar</button>
                            </div>
                        </div>

                        {{-- Checklist --}}
                        @if($clTotal > 0)
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                            <div style="flex:1;height:2px;background:var(--alg-line);border-radius:2px;overflow:hidden;">
                                <div style="height:100%;width:{{ round($clDone/$clTotal*100) }}%;background:{{ $clDone === $clTotal ? 'var(--alg-pos)' : 'var(--alg-accent)' }};border-radius:2px;"></div>
                            </div>
                            <span style="font-size:10px;color:var(--alg-ink-3);">{{ $clDone }}/{{ $clTotal }}</span>
                        </div>
                        @endif

                        {{-- Footer: assignee + due --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            @if($task->assignee)
                            <span style="font-size:11px;color:var(--alg-ink-3);">{{ Str::before($task->assignee, '@') }}</span>
                            @else <span></span> @endif
                            @if($dd)
                            <span style="font-size:11px;font-weight:500;color:{{ $dd['color'] }};">{{ $dd['label'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding:32px 16px;text-align:center;">
                    <p style="font-size:13px;color:var(--alg-ink-5);">Sin tareas</p>
                </div>
                @endforelse
            </div>

            {{-- QUICK ADD --}}
            <div style="padding:8px;" x-data="{ adding: false, newTitle: '' }">
                <div x-show="!adding" @click="adding = true; $nextTick(() => $refs.qi{{ $loop->index }}.focus())" style="padding:8px 12px;border-radius:8px;cursor:pointer;font-size:12px;color:var(--alg-ink-5);border:1px dashed var(--alg-line);text-align:center;transition:all 0.15s;" onmouseover="this.style.borderColor='var(--alg-ink-3)';this.style.color='var(--alg-ink-2)'" onmouseout="this.style.borderColor='var(--alg-line)';this.style.color='var(--alg-ink-5)'">
                    + Agregar tarea
                </div>
                <div x-show="adding" x-transition.opacity>
                    <input x-ref="qi{{ $loop->index }}" x-model="newTitle"
                        @keydown.enter="if(newTitle.trim()){$wire.quickAddTask(newTitle,'{{ $status }}');newTitle='';}"
                        @keydown.escape="adding=false;newTitle=''"
                        @blur="if(!newTitle)adding=false"
                        placeholder="Titulo y Enter..."
                        style="width:100%;padding:8px 12px;border-radius:8px;font-size:13px;font-family:inherit;color:var(--alg-ink);background:#fff;border:1px solid var(--alg-line);outline:none;"
                        onfocus="this.style.borderColor='var(--alg-accent)'"
                        onblur="this.style.borderColor='var(--alg-line)'"
                    >
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- SHORTCUTS --}}
    <div style="display:flex;gap:16px;padding:6px 0;font-size:11px;color:var(--alg-ink-5);">
        @foreach(['N'=>'Nueva','D'=>'Completar','←→'=>'Mover','Dbl-click'=>'Editar'] as $k => $v)
        <span><kbd style="padding:1px 5px;border-radius:3px;background:var(--alg-surface-2);border:1px solid var(--alg-line);font-size:10px;font-family:monospace;color:var(--alg-ink-3);">{{ $k }}</kbd> {{ $v }}</span>
        @endforeach
    </div>
</div>

<style>
.kanban-col{scrollbar-width:thin;scrollbar-color:var(--alg-line) transparent}
.kanban-col::-webkit-scrollbar{width:4px}
.kanban-col::-webkit-scrollbar-thumb{background:var(--alg-line);border-radius:2px}
</style>

<script>
function kanbanBoard() {
    return {
        draggingId: null, dropTarget: null, selected: null, compact: false, labelFilter: [],
        toggleLabel(l) { var i=this.labelFilter.indexOf(l); i>=0?this.labelFilter.splice(i,1):this.labelFilter.push(l); },
        shouldShowCard(cl) { if(!this.labelFilter.length)return true; if(!cl||!cl.length)return false; for(var i=0;i<this.labelFilter.length;i++)if(cl.indexOf(this.labelFilter[i])>=0)return true; return false; },
        dragStart(e,id) { this.draggingId=id; e.dataTransfer.effectAllowed='move'; e.dataTransfer.setData('text/plain',id); },
        dragEnd() { this.draggingId=null; this.dropTarget=null; },
        dragOver(e,s) { e.preventDefault(); this.dropTarget=s; },
        dragLeave(e) { if(!e.currentTarget.contains(e.relatedTarget))this.dropTarget=null; },
        drop(e,ns) { e.preventDefault(); var id=parseInt(e.dataTransfer.getData('text/plain')); if(!id)return; var cards=e.currentTarget.querySelectorAll('.kanban-cards [draggable]'),pos=0; cards.forEach(function(c,i){var r=c.getBoundingClientRect();if(e.clientY>r.top+r.height/2)pos=i+1;}); this.$wire.moveTask(id,ns,pos); this.draggingId=null; this.dropTarget=null; },
        handleKey(e) { if(['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName))return; switch(e.key.toLowerCase()){ case'n':e.preventDefault();window.location.href='/admin/tasks/create';break; case'd':if(this.selected){e.preventDefault();this.$wire.markDone(this.selected);}break; case'escape':this.selected=null;break; case'arrowright':if(this.selected){e.preventDefault();this.mv(1);}break; case'arrowleft':if(this.selected){e.preventDefault();this.mv(-1);}break; } },
        mv(d) { var c=document.querySelector('[data-id="'+this.selected+'"]'); if(!c)return; var col=c.closest('.kanban-col'); if(!col)return; var o=['pending','in_progress','blocked','done'],i=o.indexOf(col.dataset.status),n=i+d; if(n<0||n>=o.length)return; this.$wire.moveTask(this.selected,o[n],0); }
    };
}
</script>
</x-filament-panels::page>
