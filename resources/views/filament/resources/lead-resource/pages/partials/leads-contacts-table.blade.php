{{-- Contactos view — tabla CRM-style.
     Expects: $leads (Collection), $statuses, $selectedId, $selectedLeadIds (bulk)
     Click on row body cells → wire:click="selectLead($id)" abre slide-over derecho.
     Click on checkbox → wire:click.stop="toggleSelectedLead($id)" para bulk.
     Click on status pill → dropdown Alpine para cambio inline. --}}
{{-- Hover quick-actions: hide date, show 3 action buttons (pin/contacted/open) --}}
<style>
    tbody tr:hover .alg-row-date { display: none !important; }
    tbody tr:hover .alg-row-hover-actions { display: inline-flex !important; }
</style>
@php
    use App\Filament\Resources\LeadResource\Pages\ListLeads;
    $statusColors = [
        'new'         => ['var(--alg-accent-soft)', 'var(--alg-accent)'],
        'contacted'   => ['var(--alg-surface-2)',   'var(--alg-ink-3)'],
        'qualified'   => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
        'proposal'    => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
        'negotiation' => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
        'won'         => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
        'lost'        => ['var(--alg-neg-soft)',    'var(--alg-neg)'],
    ];
@endphp

<div style="flex:1;overflow-y:auto;background:var(--alg-bg);">
    @if($leads->isEmpty())
        <div style="padding:64px 20px;text-align:center;">
            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-3);margin:0 0 6px;">Sin contactos en este filtro</p>
            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá filtros o cambiá el rango de fechas.</p>
        </div>
    @else
        @php
            $allOnPageIds = $leads->pluck('id')->all();
            $allSelectedOnPage = ! empty($allOnPageIds) && empty(array_diff($allOnPageIds, $selectedLeadIds ?? []));
        @endphp
        <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;background:var(--alg-surface);">
            <thead>
                <tr style="background:var(--alg-bg);position:sticky;top:0;z-index:1;">
                    <th style="width:32px;padding:9px 4px 9px 18px;border-bottom:1px solid var(--alg-line);">
                        <input type="checkbox"
                               wire:click="{{ $allSelectedOnPage ? 'clearSelectedLeads' : 'selectAllVisible' }}"
                               {{ $allSelectedOnPage ? 'checked' : '' }}
                               title="{{ $allSelectedOnPage ? 'Deseleccionar todos' : 'Seleccionar todos los visibles' }}"
                               style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                    </th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Contacto</th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Empresa</th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Teléfono</th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Estado</th>
                    <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Score</th>
                    <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">País</th>
                    <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Creado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leads as $l)
                    @php
                        $av = ListLeads::avatarFor($l->email, $l->name);
                        [$pillBg, $pillFg] = $statusColors[$l->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
                        $isActive = $selectedId === $l->id;
                        $isChecked = in_array($l->id, $selectedLeadIds ?? [], true);
                        $score = $l->score ?? 0;
                        $temp = $score >= 80 ? ['var(--alg-warn-soft)', 'var(--alg-warn)', '★ HOT'] : ($score >= 50 ? ['var(--alg-accent-soft)', 'var(--alg-accent)', $score] : ['var(--alg-surface-2)', 'var(--alg-ink-4)', $score ?: '—']);
                        $rowBg = ($isChecked || $isActive) ? 'var(--alg-accent-soft)' : 'transparent';
                        // Stalled = no activity in 14+ days AND status is in pipeline (not won/lost)
                        // latest_activity_at viene de withMax() en getViewData. Si null, usa created_at como fallback.
                        $lastTouch = $l->latest_activity_at ? \Carbon\Carbon::parse($l->latest_activity_at) : $l->created_at;
                        $daysSince = $lastTouch ? $lastTouch->diffInDays(now()) : 0;
                        $isStalled = $daysSince >= 14 && in_array($l->status, ['contacted', 'qualified', 'proposal', 'negotiation'], true);
                    @endphp
                    <tr style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                        onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                        onmouseout="this.style.background='{{ $rowBg }}'"
                        @if($isActive || $isChecked) data-locked="1" @endif>
                        {{-- Bulk checkbox (stops propagation so it doesn't open the slide-over) --}}
                        <td style="padding:11px 4px 11px 18px;text-align:center;" onclick="event.stopPropagation()">
                            <input type="checkbox"
                                   wire:click="toggleSelectedLead({{ $l->id }})"
                                   {{ $isChecked ? 'checked' : '' }}
                                   style="cursor:pointer;width:14px;height:14px;accent-color:var(--alg-accent);">
                        </td>
                        {{-- Contact: avatar + name + email --}}
                        <td style="padding:11px 12px;" wire:click="selectLead({{ $l->id }})">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:600;letter-spacing:0;flex-shrink:0;">{{ $av['initials'] }}</span>
                                <div style="min-width:0;">
                                    <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;">{{ $l->name }}</div>
                                    <div style="font-size:11.5px;color:var(--alg-ink-4);margin-top:1px;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px;font-family:ui-monospace,'SF Mono',Menlo,monospace;">{{ $l->email ?: '—' }}</div>
                                </div>
                            </div>
                        </td>
                        {{-- Company --}}
                        <td style="padding:11px 12px;color:var(--alg-ink-2);" wire:click="selectLead({{ $l->id }})">
                            {{ $l->company ?: '—' }}
                        </td>
                        {{-- Phone --}}
                        <td style="padding:11px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-ink-3);" wire:click="selectLead({{ $l->id }})">
                            {{ $l->phone ?: '—' }}
                        </td>
                        {{-- Status — click pill to change inline (no slide-over) --}}
                        <td style="padding:11px 12px;" onclick="event.stopPropagation()">
                            <div style="display:flex;align-items:center;gap:5px;">
                                <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;display:inline-block;">
                                    <button type="button" @click="open = !open"
                                            style="display:inline-block;font-size:9.5px;font-weight:500;color:{{ $pillFg }};background:{{ $pillBg }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;border:none;cursor:pointer;">{{ $statuses[$l->status] ?? $l->status }} ▾</button>
                                <div x-show="open" x-cloak x-transition.opacity
                                     style="position:absolute;top:calc(100% + 3px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:3px;z-index:20;display:flex;flex-direction:column;gap:1px;min-width:140px;">
                                    @foreach($statuses as $stKey => $stLabel)
                                        @php $cs = $statusColors[$stKey] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)']; @endphp
                                        <button type="button" wire:click="setLeadStatus({{ $l->id }}, '{{ $stKey }}')" @click="open = false"
                                                style="display:flex;align-items:center;gap:6px;padding:4px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                                onmouseover="this.style.background='var(--alg-surface-2)'"
                                                onmouseout="this.style.background='transparent'">
                                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $cs[1] }};"></span>{{ $stLabel }}
                                        </button>
                                    @endforeach
                                    </div>
                                </div>
                                @if($isStalled)
                                    {{-- Stalled badge — usamos SVG inline en vez de emoji ⏸ que no
                                         renderiza bien en muchos browsers (se veía como "!!"). --}}
                                    <span title="Sin actividad hace {{ (int) $daysSince }} días"
                                          style="display:inline-flex;align-items:center;gap:3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:8.5px;font-weight:600;background:var(--alg-neg-soft);color:var(--alg-neg);padding:2px 5px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">
                                        <svg width="8" height="8" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="3" y="3" width="3.5" height="10" rx="0.5"/><rect x="9.5" y="3" width="3.5" height="10" rx="0.5"/></svg>
                                        Stalled {{ (int) $daysSince }}d
                                    </span>
                                @endif
                            </div>
                        </td>
                        {{-- Score --}}
                        <td style="padding:11px 12px;text-align:right;" wire:click="selectLead({{ $l->id }})">
                            <span style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:600;background:{{ $temp[0] }};color:{{ $temp[1] }};padding:2px 7px;border-radius:2px;letter-spacing:.04em;">{{ $temp[2] }}</span>
                        </td>
                        {{-- Country --}}
                        <td style="padding:11px 12px;" wire:click="selectLead({{ $l->id }})">
                            @if($l->country)
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:1px 5px;border-radius:2px;letter-spacing:.04em;">{{ strtoupper($l->country->code) }}</span>
                            @else
                                <span style="color:var(--alg-ink-5);">—</span>
                            @endif
                        </td>
                        {{-- Created at + hover quick-actions --}}
                        <td style="padding:11px 18px 11px 12px;text-align:right;white-space:nowrap;position:relative;" onclick="event.stopPropagation()">
                            @php $isPinned = in_array($l->id, $pinnedIds ?? [], true); @endphp
                            <div class="alg-row-hover-actions"
                                 style="display:none;align-items:center;gap:4px;justify-content:flex-end;">
                                <button type="button" wire:click="quickTogglePin({{ $l->id }})"
                                        title="{{ $isPinned ? 'Quitar pin' : 'Pin' }}"
                                        style="border:1px solid var(--alg-line);background:var(--alg-surface);color:{{ $isPinned ? 'var(--alg-warn)' : 'var(--alg-ink-4)' }};cursor:pointer;width:24px;height:24px;border-radius:3px;font-size:11px;line-height:1;">📌</button>
                                @if($l->status !== 'contacted' && $l->status !== 'won' && $l->status !== 'lost')
                                    <button type="button" wire:click="quickMarkContacted({{ $l->id }})"
                                            title="Marcar como contactado"
                                            style="border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-pos);cursor:pointer;width:24px;height:24px;border-radius:3px;font-size:11px;line-height:1;">✓</button>
                                @endif
                                <button type="button" wire:click="selectLead({{ $l->id }})"
                                        title="Abrir detalle"
                                        style="border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-3);cursor:pointer;width:24px;height:24px;border-radius:3px;font-size:11px;line-height:1;">→</button>
                            </div>
                            <span class="alg-row-date" style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">{{ $l->created_at->format('d M') }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
