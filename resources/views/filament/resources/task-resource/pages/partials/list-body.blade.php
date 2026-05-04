{{-- List view body — grouped table or flat rows.
     Expects: $tasks, $grouped, $groupBy, $selectedIds
     Static helpers from ListTasks used inside task-list-rows partial. --}}
@php
    use App\Filament\Resources\TaskResource\Pages\ListTasks;
@endphp
<div style="background:var(--alg-surface);border:1px solid var(--alg-line);">
    @if($tasks->isEmpty())
        <div style="padding:48px;text-align:center;color:var(--alg-ink-4);font-size:13px;">
            No hay tareas en este filtro.
        </div>
    @elseif($groupBy === 'none')
        @include('filament.resources.task-resource.pages.partials.task-list-rows', ['rows' => $tasks, 'selectedIds' => $selectedIds])
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
                    'status' => ListTasks::statusLabel($groupKey),
                    default  => $groupKey,
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
                @include('filament.resources.task-resource.pages.partials.task-list-rows', ['rows' => $rows, 'selectedIds' => $selectedIds])
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
