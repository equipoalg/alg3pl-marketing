@php
    $pColors = ['P0'=>'#9F1239','P1'=>'#92400E','P2'=>'#2563EB','P3'=>'#64748B'];
    $sColors = ['pending'=>'#64748B','in_progress'=>'#1E3A8A','done'=>'#166534','blocked'=>'#9F1239'];
    $sLabels = ['pending'=>'Por Atender','in_progress'=>'En Curso','done'=>'Resuelto','blocked'=>'Bloqueada'];
    $pc = $pColors[$record->priority] ?? '#64748B';
    $sc = $sColors[$record->status] ?? '#64748B';
    $sl = $sLabels[$record->status] ?? $record->status;
    $checklist = $record->checklist ?? [];
    $totalItems = count($checklist);
    $doneItems = collect($checklist)->where('done', true)->count();
    $checkPct = $totalItems > 0 ? round($doneItems / $totalItems * 100) : 0;
    $isOverdue = $record->due_date && $record->due_date->isPast() && $record->status !== 'done';
    $comments = $record->comments ?? collect();
@endphp

<div style="font-family:Inter,-apple-system,sans-serif;color:#0F172A;background:#fff;border-radius:12px;">

    {{-- HEADER --}}
    <div style="padding:28px 32px 20px;border-bottom:1px solid #E2E8F0;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <span style="width:8px;height:8px;border-radius:50%;background:{{ $pc }};"></span>
            <span style="font-size:11px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">{{ $record->priority }}</span>
            <span style="font-size:11px;font-weight:600;color:{{ $sc }};background:{{ $sc }}0D;padding:3px 10px;border-radius:4px;">{{ $sl }}</span>
            @if($record->category)
            <span style="font-size:10px;font-weight:600;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">{{ $record->category }}</span>
            @endif
            @if($isOverdue)
            <span style="font-size:10px;font-weight:700;color:#9F1239;background:#FEF2F2;padding:3px 8px;border-radius:4px;margin-left:auto;">VENCIDA</span>
            @endif
        </div>
        <h2 style="font-size:22px;font-weight:800;color:#1E3A8A;margin:0;letter-spacing:-0.02em;line-height:1.3;">{{ $record->title }}</h2>
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
            <div style="padding:12px 14px;background:#FFFFFF;border-radius:8px;">
                <p style="font-size:10px;color:#64748B;margin:0 0 4px;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">{{ $label }}</p>
                <p style="font-size:14px;font-weight:600;color:{{ $label === 'Fecha límite' && $isOverdue ? '#9F1239' : '#0F172A' }};margin:0;">{{ $value }}</p>
            </div>
            @endforeach
        </div>

        {{-- DESCRIPTION --}}
        @if($record->description)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748B;margin:0 0 8px;">Descripción</p>
            <p style="font-size:14px;line-height:1.65;color:#334155;margin:0;padding:14px 16px;background:#FFFFFF;border-radius:8px;border-left:3px solid #1E3A8A;">{{ $record->description }}</p>
        </div>
        @endif

        {{-- TIME TRACKING --}}
        @if($record->estimated_hours || $record->actual_hours)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748B;margin:0 0 10px;">Tiempo</p>
            <div style="display:flex;gap:16px;">
                @if($record->estimated_hours)
                <div style="flex:1;background:#FFFFFF;border-radius:8px;padding:16px;text-align:center;">
                    <p style="font-size:24px;font-weight:800;color:#1E3A8A;margin:0;">{{ $record->estimated_hours }}h</p>
                    <p style="font-size:11px;color:#64748B;margin:4px 0 0;">Estimado</p>
                </div>
                @endif
                @if($record->actual_hours)
                <div style="flex:1;background:#FFFFFF;border-radius:8px;padding:16px;text-align:center;">
                    <p style="font-size:24px;font-weight:800;color:{{ $record->estimated_hours && $record->actual_hours > $record->estimated_hours ? '#92400E' : '#166534' }};margin:0;">{{ $record->actual_hours }}h</p>
                    <p style="font-size:11px;color:#64748B;margin:4px 0 0;">Real</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- CHECKLIST --}}
        @if($totalItems > 0)
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748B;margin:0;">Checklist</p>
                <span style="font-size:11px;font-weight:700;color:#64748B;">{{ $doneItems }}/{{ $totalItems }}</span>
            </div>
            <div style="height:3px;background:#E2E8F0;border-radius:3px;margin-bottom:10px;overflow:hidden;">
                <div style="height:100%;width:{{ $checkPct }}%;background:{{ $checkPct === 100 ? '#166534' : '#1E3A8A' }};border-radius:3px;"></div>
            </div>
            @foreach($checklist as $item)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #EDF0F4;' : '' }}">
                @if($item['done'] ?? false)
                <svg style="width:16px;height:16px;color:#166534;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span style="font-size:13px;color:#64748B;text-decoration:line-through;">{{ $item['item'] ?? '' }}</span>
                @else
                <svg style="width:16px;height:16px;color:#CBD5E1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/></svg>
                <span style="font-size:13px;color:#0F172A;">{{ $item['item'] ?? '' }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- NOTES --}}
        @if($record->notes)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748B;margin:0 0 8px;">Notas</p>
            <p style="font-size:13px;line-height:1.6;color:#334155;margin:0;padding:12px 16px;background:#FFFFFF;border-radius:8px;">{{ $record->notes }}</p>
        </div>
        @endif

        {{-- COMMENTS --}}
        @if($comments->count() > 0)
        <div>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748B;margin:0 0 10px;">Actividad ({{ $comments->count() }})</p>
            @foreach($comments->take(10) as $comment)
            <div style="padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #EDF0F4;' : '' }}">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <span style="font-size:13px;font-weight:600;color:#0F172A;">{{ $comment->user?->name ?? 'Sistema' }}</span>
                    <span style="font-size:11px;color:#CBD5E1;">{{ $comment->created_at?->diffForHumans() }}</span>
                </div>
                <p style="font-size:13px;color:#334155;margin:0;line-height:1.5;">{{ $comment->body }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
