{{-- Reusable list rows partial — used both by grouped & ungrouped list views.
     Expects: $rows. Reads: $selectedIds (from parent component) for checkbox state.
     Color/label helpers come from ListTasks static methods. --}}
@php use App\Filament\Resources\TaskResource\Pages\ListTasks; @endphp
<table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
    <colgroup>
        <col style="width:30px;">                  {{-- Bulk checkbox --}}
        <col style="width:42px;">                  {{-- Priority chip --}}
        <col>                                       {{-- Title --}}
        <col style="width:90px;">                   {{-- Category --}}
        <col style="width:110px;">                  {{-- Status --}}
        <col style="width:80px;">                   {{-- Due --}}
        <col style="width:140px;">                  {{-- Country / assignee --}}
    </colgroup>
    <tbody>
        @foreach($rows as $t)
            @php
                $pc = ListTasks::priorityColor($t->priority);
                $sc = ListTasks::statusColor($t->status);
                $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
            @endphp
            @php $isChecked = in_array($t->id, $selectedIds ?? [], true); @endphp
            <tr data-task-id="{{ $t->id }}"
                style="border-bottom:1px solid var(--alg-line);transition:background 120ms;{{ $isChecked ? 'background:var(--alg-accent-soft);' : '' }}"
                onmouseover="if(this.style.background.indexOf('accent-soft')<0) this.style.background='var(--alg-surface-2)'"
                onmouseout="this.style.background='{{ $isChecked ? 'var(--alg-accent-soft)' : 'transparent' }}'">
                <td style="padding:8px 0 8px 12px;text-align:center;">
                    <input type="checkbox"
                           wire:click="toggleSelected({{ $t->id }})"
                           {{ $isChecked ? 'checked' : '' }}
                           style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                </td>
                <td style="padding:8px 12px;">
                    {{-- Priority chip — click to change inline --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;display:inline-block;">
                        <button type="button" @click="open = !open"
                                style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:700;color:{{ $pc['fg'] }};background:{{ $pc['bg'] }};padding:2px 6px;border-radius:2px;letter-spacing:.04em;border:none;cursor:pointer;">{{ $t->priority }}</button>
                        <div x-show="open" x-cloak x-transition.opacity
                             style="position:absolute;top:calc(100% + 3px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:10;display:flex;flex-direction:column;gap:1px;">
                            @foreach(['P0','P1','P2','P3'] as $pri)
                                @php $cc = ListTasks::priorityColor($pri); @endphp
                                <button type="button" wire:click="setPriority({{ $t->id }}, '{{ $pri }}')" @click="open = false"
                                        style="display:flex;align-items:center;gap:6px;padding:3px 8px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                        onmouseover="this.style.background='var(--alg-surface-2)'"
                                        onmouseout="this.style.background='transparent'">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:{{ $cc['fg'] }};"></span>{{ $pri }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </td>
                <td style="padding:8px 6px;">
                    {{-- Title click → slide-over (not edit page) --}}
                    <button type="button" wire:click="selectTask({{ $t->id }})"
                            style="color:var(--alg-ink);text-decoration:none;font-weight:500;letter-spacing:-0.005em;background:transparent;border:none;text-align:left;padding:0;cursor:pointer;font-family:inherit;font-size:inherit;">{{ $t->title }}</button>
                    @if($t->description)
                        <div style="font-size:11px;color:var(--alg-ink-4);margin-top:2px;line-height:1.4;max-width:540px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $t->description }}</div>
                    @endif
                </td>
                <td style="padding:8px 6px;">
                    @if($t->category)
                        <span style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 6px;border-radius:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $t->category }}</span>
                    @endif
                </td>
                <td style="padding:8px 6px;">
                    {{-- Status badge — click to change inline --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;display:inline-block;">
                        <button type="button" @click="open = !open"
                                style="display:inline-block;font-size:10px;font-weight:500;color:{{ $sc['fg'] }};background:{{ $sc['bg'] }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;border:none;cursor:pointer;">{{ ListTasks::statusLabel($t->status) }}</button>
                        <div x-show="open" x-cloak x-transition.opacity
                             style="position:absolute;top:calc(100% + 3px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:10;display:flex;flex-direction:column;gap:1px;min-width:130px;">
                            @foreach(['pending','in_progress','blocked','done'] as $st)
                                @php $cs = ListTasks::statusColor($st); @endphp
                                <button type="button" wire:click="moveTaskStatus({{ $t->id }}, '{{ $st }}')" @click="open = false"
                                        style="display:flex;align-items:center;gap:6px;padding:3px 8px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:10.5px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                        onmouseover="this.style.background='var(--alg-surface-2)'"
                                        onmouseout="this.style.background='transparent'">
                                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $cs['fg'] }};"></span>{{ ListTasks::statusLabel($st) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </td>
                <td style="padding:8px 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:{{ $isOverdue ? 'var(--alg-neg)' : 'var(--alg-ink-3)' }};">
                    @if($t->due_date)
                        {{ $t->due_date->format('d M') }}
                    @else
                        <span style="color:var(--alg-ink-5);">—</span>
                    @endif
                </td>
                <td style="padding:8px 12px 8px 6px;text-align:right;">
                    <div style="display:inline-flex;align-items:center;gap:6px;">
                        @if($t->country)
                            <span style="background:var(--alg-surface-2);padding:1px 5px;border-radius:2px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);">{{ strtoupper($t->country->code) }}</span>
                        @endif
                        @if($t->assignee)
                            @php $av = \App\Filament\Resources\TaskResource\Pages\ListTasks::avatarFor($t->assignee); @endphp
                            <span title="{{ $t->assignee }}"
                                  style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:9.5px;font-weight:600;letter-spacing:0;">{{ $av['initials'] }}</span>
                        @else
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:var(--alg-surface-2);color:var(--alg-ink-5);font-size:11px;" title="Sin asignar">○</span>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
