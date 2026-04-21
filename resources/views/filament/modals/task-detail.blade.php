@php
    $pColors = ['P0'=>'#C4314B','P1'=>'#D4880F','P2'=>'#2563EB','P3'=>'#8B95A5'];
    $sColors = ['pending'=>'#8B95A5','in_progress'=>'#00243D','done'=>'#0A8F5C','blocked'=>'#C4314B'];
    $sLabels = ['pending'=>'Por Atender','in_progress'=>'En Curso','done'=>'Resuelto','blocked'=>'Bloqueada'];
    $pc = $pColors[$record->priority] ?? '#8B95A5';
    $sc = $sColors[$record->status] ?? '#8B95A5';
    $sl = $sLabels[$record->status] ?? $record->status;
    $checklist = $record->checklist ?? [];
    $totalItems = count($checklist);
    $doneItems = collect($checklist)->where('done', true)->count();
    $checkPct = $totalItems > 0 ? round($doneItems / $totalItems * 100) : 0;
    $isOverdue = $record->due_date && $record->due_date->isPast() && $record->status !== 'done';
    $comments = $record->comments ?? collect();
@endphp

<div style="font-family:Inter,-apple-system,sans-serif;color:#1A1D21;background:#fff;border-radius:12px;">

    {{-- HEADER --}}
    <div style="padding:28px 32px 20px;border-bottom:1px solid #E2E5EA;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <span style="width:8px;height:8px;border-radius:50%;background:{{ $pc }};"></span>
            <span style="font-size:11px;font-weight:700;color:#8B95A5;text-transform:uppercase;letter-spacing:0.06em;">{{ $record->priority }}</span>
            <span style="font-size:11px;font-weight:600;color:{{ $sc }};background:{{ $sc }}0D;padding:3px 10px;border-radius:4px;">{{ $sl }}</span>
            @if($record->category)
            <span style="font-size:10px;font-weight:600;color:#8B95A5;text-transform:uppercase;letter-spacing:0.06em;">{{ $record->category }}</span>
            @endif
            @if($isOverdue)
            <span style="font-size:10px;font-weight:700;color:#C4314B;background:#FEF2F2;padding:3px 8px;border-radius:4px;margin-left:auto;">VENCIDA</span>
            @endif
        </div>
        <h2 style="font-size:22px;font-weight:800;color:#00243D;margin:0;letter-spacing:-0.02em;line-height:1.3;">{{ $record->title }}</h2>
    </div>

    <div style="padding:24px 32px;display:flex;flex-direction:column;gap:24px;">

        {{-- META --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;">
            @foreach([
                ['País', $record->country?->name ?? '—'],
                ['Asignado', $record->assignee ?? '—'],
                ['Fecha límite', $record->due_date?->format('d M Y') ?? '—'],
                ['Esfuerzo', $record->effort ?? '—'],
                ['Impacto', $record->impact ?? '—'],
                ['Creada', $record->created_at?->format('d M Y') ?? '—'],
            ] as [$label, $value])
            <div style="padding:12px 14px;background:#F7F8FA;border-radius:8px;">
                <p style="font-size:10px;color:#8B95A5;margin:0 0 4px;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">{{ $label }}</p>
                <p style="font-size:14px;font-weight:600;color:{{ $label === 'Fecha límite' && $isOverdue ? '#C4314B' : '#1A1D21' }};margin:0;">{{ $value }}</p>
            </div>
            @endforeach
        </div>

        {{-- DESCRIPTION --}}
        @if($record->description)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B95A5;margin:0 0 8px;">Descripción</p>
            <p style="font-size:14px;line-height:1.65;color:#4A5568;margin:0;padding:14px 16px;background:#F7F8FA;border-radius:8px;border-left:3px solid #00243D;">{{ $record->description }}</p>
        </div>
        @endif

        {{-- TIME TRACKING --}}
        @if($record->estimated_hours || $record->actual_hours)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B95A5;margin:0 0 10px;">Tiempo</p>
            <div style="display:flex;gap:16px;">
                @if($record->estimated_hours)
                <div style="flex:1;background:#F7F8FA;border-radius:8px;padding:16px;text-align:center;">
                    <p style="font-size:24px;font-weight:800;color:#00243D;margin:0;">{{ $record->estimated_hours }}h</p>
                    <p style="font-size:11px;color:#8B95A5;margin:4px 0 0;">Estimado</p>
                </div>
                @endif
                @if($record->actual_hours)
                <div style="flex:1;background:#F7F8FA;border-radius:8px;padding:16px;text-align:center;">
                    <p style="font-size:24px;font-weight:800;color:{{ $record->estimated_hours && $record->actual_hours > $record->estimated_hours ? '#D4880F' : '#0A8F5C' }};margin:0;">{{ $record->actual_hours }}h</p>
                    <p style="font-size:11px;color:#8B95A5;margin:4px 0 0;">Real</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- CHECKLIST --}}
        @if($totalItems > 0)
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B95A5;margin:0;">Checklist</p>
                <span style="font-size:11px;font-weight:700;color:#8B95A5;">{{ $doneItems }}/{{ $totalItems }}</span>
            </div>
            <div style="height:3px;background:#E2E5EA;border-radius:3px;margin-bottom:10px;overflow:hidden;">
                <div style="height:100%;width:{{ $checkPct }}%;background:{{ $checkPct === 100 ? '#0A8F5C' : '#00243D' }};border-radius:3px;"></div>
            </div>
            @foreach($checklist as $item)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #EDF0F4;' : '' }}">
                @if($item['done'] ?? false)
                <svg style="width:16px;height:16px;color:#0A8F5C;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span style="font-size:13px;color:#8B95A5;text-decoration:line-through;">{{ $item['item'] ?? '' }}</span>
                @else
                <svg style="width:16px;height:16px;color:#B8C0CC;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/></svg>
                <span style="font-size:13px;color:#1A1D21;">{{ $item['item'] ?? '' }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- NOTES --}}
        @if($record->notes)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B95A5;margin:0 0 8px;">Notas</p>
            <p style="font-size:13px;line-height:1.6;color:#4A5568;margin:0;padding:12px 16px;background:#F7F8FA;border-radius:8px;">{{ $record->notes }}</p>
        </div>
        @endif

        {{-- COMMENTS --}}
        @if($comments->count() > 0)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#8B95A5;margin:0 0 10px;">Actividad ({{ $comments->count() }})</p>
            @foreach($comments->take(10) as $comment)
            <div style="padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #EDF0F4;' : '' }}">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:600;color:#1A1D21;">{{ $comment->user?->name ?? 'Sistema' }}</span>
                    <span style="font-size:11px;color:#B8C0CC;">{{ $comment->created_at?->diffForHumans() }}</span>
                </div>
                <p style="font-size:13px;color:#4A5568;margin:0;line-height:1.5;">{{ $comment->body }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
