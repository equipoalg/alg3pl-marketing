{{-- Filter chips row — multi-select AND.
     Expects: $priorityFilter, $categoryFilter, $dueFilter
     Uses static helpers from ListTasks. --}}
@php
    use App\Filament\Resources\TaskResource\Pages\ListTasks;
    $priChips = array_filter(explode(',', $priorityFilter));
    $catChips = array_filter(explode(',', $categoryFilter));
    $hasAnyChip = ! empty($priChips) || ! empty($catChips) || $dueFilter !== '';
@endphp
<div style="background:var(--alg-surface);border:1px solid var(--alg-line);padding:8px 12px;display:flex;flex-wrap:wrap;align-items:center;gap:6px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;">
    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.08em;margin-right:4px;">Prioridad</span>
    @foreach(['P0','P1','P2','P3'] as $pri)
        @php $isOn = in_array($pri, $priChips, true); $c = ListTasks::priorityColor($pri); @endphp
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
