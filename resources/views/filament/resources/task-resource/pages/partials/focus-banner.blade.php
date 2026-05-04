{{-- Focus banner — what should worry me right now.
     Expects from parent: $banner (['level' => 'critical'|'warning'|'good', 'overdue' => int, 'dueTodayHigh' => int]) --}}
@if($banner['level'] === 'critical' || $banner['level'] === 'warning')
    <div style="background:{{ $banner['level'] === 'critical' ? 'var(--alg-neg-soft)' : 'var(--alg-warn-soft)' }};border:1px solid {{ $banner['level'] === 'critical' ? 'var(--alg-neg)' : 'var(--alg-warn)' }};color:{{ $banner['level'] === 'critical' ? 'var(--alg-neg)' : 'var(--alg-warn)' }};padding:9px 14px;display:flex;align-items:center;gap:10px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;border-radius:4px;">
        <span style="font-size:14px;">{{ $banner['level'] === 'critical' ? '⚠' : '🔥' }}</span>
        <span style="flex:1;">
            @if($banner['overdue'] > 0)
                Tienes <strong>{{ $banner['overdue'] }}</strong> {{ $banner['overdue'] === 1 ? 'tarea vencida' : 'tareas vencidas' }}{{ $banner['dueTodayHigh'] > 0 ? ' y ' : '' }}{!! $banner['dueTodayHigh'] > 0 ? '<strong>' . $banner['dueTodayHigh'] . '</strong> de alta prioridad para hoy' : '' !!}.
            @else
                Tienes <strong>{{ $banner['dueTodayHigh'] }}</strong> {{ $banner['dueTodayHigh'] === 1 ? 'tarea de alta prioridad' : 'tareas de alta prioridad' }} para hoy.
            @endif
        </span>
        <button type="button"
                wire:click="setFilterPreset('{{ $banner['overdue'] > 0 ? 'overdue' : 'high_priority' }}')"
                style="padding:4px 11px;border:1px solid currentColor;background:transparent;color:inherit;font-family:inherit;font-size:11.5px;font-weight:600;cursor:pointer;border-radius:3px;letter-spacing:-0.005em;">
            Mostrar →
        </button>
    </div>
@elseif($banner['level'] === 'good')
    <div style="background:var(--alg-pos-soft);border:1px solid var(--alg-pos);color:var(--alg-pos);padding:7px 14px;display:flex;align-items:center;gap:10px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
        <span style="font-size:13px;">✓</span>
        <span>Sin tareas vencidas ni alta prioridad para hoy. Buen día.</span>
    </div>
@endif
