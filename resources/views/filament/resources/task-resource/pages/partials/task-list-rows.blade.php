{{-- Reusable list rows partial — used both by grouped & ungrouped list views.
     Expects: $rows, $priorityColor, $statusColor, $statusLabel  --}}
<table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
    <colgroup>
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
                $pc = $priorityColor($t->priority);
                $sc = $statusColor($t->status);
                $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
            @endphp
            <tr style="border-bottom:1px solid var(--alg-line);transition:background 120ms;" onmouseover="this.style.background='var(--alg-surface-2)'" onmouseout="this.style.background='transparent'">
                <td style="padding:8px 12px;">
                    <span style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:700;color:{{ $pc['fg'] }};background:{{ $pc['bg'] }};padding:2px 6px;border-radius:2px;letter-spacing:.04em;">{{ $t->priority }}</span>
                </td>
                <td style="padding:8px 6px;">
                    <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $t]) }}" style="color:var(--alg-ink);text-decoration:none;font-weight:500;letter-spacing:-0.005em;">{{ $t->title }}</a>
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
                    <span style="display:inline-block;font-size:10px;font-weight:500;color:{{ $sc['fg'] }};background:{{ $sc['bg'] }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">{{ $statusLabel($t->status) }}</span>
                </td>
                <td style="padding:8px 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:{{ $isOverdue ? 'var(--alg-neg)' : 'var(--alg-ink-3)' }};">
                    @if($t->due_date)
                        {{ $t->due_date->format('d M') }}
                    @else
                        <span style="color:var(--alg-ink-5);">—</span>
                    @endif
                </td>
                <td style="padding:8px 12px 8px 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);text-align:right;">
                    @if($t->country)
                        <span style="background:var(--alg-surface-2);padding:1px 5px;border-radius:2px;margin-right:4px;">{{ strtoupper($t->country->code) }}</span>
                    @endif
                    @if($t->assignee)
                        <span style="color:var(--alg-ink-4);">{{ $t->assignee }}</span>
                    @else
                        <span style="color:var(--alg-ink-5);">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
