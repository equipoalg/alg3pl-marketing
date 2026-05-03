<x-filament-panels::page>

{{-- Suppress Filament page chrome (h1, header padding) for this page only --}}
<style>
    /* Nuke the Filament page header so the inbox is edge-to-edge */
    .fi-page-header,
    .fi-header-heading,
    .fi-header-subheading,
    .fi-page > header.fi-header { display: none !important; }
    /* Pull the inbox tight to the global topbar */
    main.alg-main { padding: 0 !important; background: var(--alg-surface) !important; }
    .fi-page { padding: 0 !important; }
    .fi-main { padding: 0 !important; gap: 0 !important; }
    /* Avoid the wrapper's gap so our inbox container fills */
    .fi-page > * + * { margin-top: 0 !important; }
    /* Inbox container occupies all available height (topbar 52px + safety) */
    .alg-inbox { height: calc(100vh - 52px); }

    /* Date-bucket label */
    .alg-inbox-bucket {
        font-family: ui-monospace,'SF Mono',Menlo,monospace;
        font-size: 9.5px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .12em;
        color: var(--alg-ink-4);
        padding: 10px 16px 6px; background: var(--alg-bg);
        position: sticky; top: 0; z-index: 1;
        border-bottom: 1px solid var(--alg-line);
    }

    /* Row variants — read vs unread */
    .alg-inbox-row { transition: background 100ms ease; }
    .alg-inbox-row:hover { background: var(--alg-surface-2); }
    .alg-inbox-row.is-active { background: var(--alg-surface-2); }
    .alg-inbox-row.is-active .alg-inbox-name { color: var(--alg-ink); }
    .alg-inbox-row.is-read .alg-inbox-name { font-weight: 400; }
    .alg-inbox-row.is-read .alg-inbox-preview { opacity: 0.6; }
    .alg-inbox-row.is-unread .alg-inbox-name { font-weight: 600; color: var(--alg-ink); }
    .alg-inbox-row.is-unread .alg-inbox-dot {
        background: var(--alg-accent-2);
        width: 8px; height: 8px; border-radius: 50%;
        display: inline-block;
    }

    /* Reading toolbar buttons */
    .alg-rp-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 10px; background: transparent; border: none;
        cursor: pointer; color: var(--alg-ink-2);
        font-family: 'Geist',ui-sans-serif,system-ui,sans-serif;
        font-size: 12px; font-weight: 500; letter-spacing: -0.005em;
        border-radius: 4px; transition: background 120ms ease;
    }
    .alg-rp-btn:hover:not(:disabled) { background: var(--alg-surface-2); }
    .alg-rp-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .alg-rp-btn svg { width: 14px; height: 14px; }
</style>

<div class="alg-inbox"
     x-data="{
        focused: 'list',
        focusReply() {
            this.$nextTick(() => {
                const ta = document.getElementById('alg-quick-reply');
                if (ta) ta.focus();
            });
        }
     }"
     x-on:keydown.window="
        // Don't intercept while typing in inputs/textareas
        if (['INPUT','TEXTAREA','SELECT'].includes($event.target.tagName)) return;
        if ($event.key === 'j' || $event.key === 'ArrowDown') { $event.preventDefault(); $wire.nextLead(); }
        else if ($event.key === 'k' || $event.key === 'ArrowUp') { $event.preventDefault(); $wire.prevLead(); }
        else if ($event.key === 'r') { $event.preventDefault(); focusReply(); }
        else if ($event.key === 'p' && @js($selected?->id)) { $event.preventDefault(); $wire.togglePin(@js($selected?->id ?? 0)); }
        else if ($event.key === '/') { $event.preventDefault(); document.getElementById('alg-inbox-search')?.focus(); }
     "
     style="display:flex;flex-direction:column;background:var(--alg-bg);">

    {{-- ════════════════════════════════════════════════
         TOP TOOLBAR (40px) — search + actions
    ════════════════════════════════════════════════ --}}
    <div style="display:flex;align-items:center;gap:10px;padding:0 16px;height:48px;border-bottom:1px solid var(--alg-line);background:var(--alg-surface);flex-shrink:0;">
        <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;flex-shrink:0;">Bandeja de entrada</span>
        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;flex-shrink:0;">· {{ $totalShown }}</span>

        {{-- Search full-width with key hint --}}
        <div style="flex:1;position:relative;max-width:520px;">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
            </svg>
            <input id="alg-inbox-search"
                   type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar… (presiona / para enfocar, j/k para navegar, r para responder)"
                   style="width:100%;padding:6px 30px 6px 30px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            <span style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);background:var(--alg-surface-2);padding:1px 5px;border-radius:3px;border:1px solid var(--alg-line);">/</span>
        </div>

        {{-- Status filter pill --}}
        <select wire:model.live="statusFilter"
                style="padding:5px 8px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);cursor:pointer;outline:none;">
            <option value="">Cualquier estado</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>

        {{-- Actions --}}
        <a href="{{ \App\Filament\Resources\LeadResource::getUrl('create') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;letter-spacing:-0.005em;border-radius:4px;flex-shrink:0;">
            <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 4v12M4 10h12"/></svg>
            Nuevo
        </a>
    </div>

    {{-- ════════════════════════════════════════════════
         BODY: 2 columns (folders+list 380px | reading flex)
    ════════════════════════════════════════════════ --}}
    <div style="flex:1;display:flex;min-height:0;">

        {{-- ───── LEFT: folders + list ───── --}}
        <div style="width:380px;flex-shrink:0;display:flex;flex-direction:column;border-right:1px solid var(--alg-line);background:var(--alg-surface);min-height:0;">

            {{-- Folder pills (Outlook-style: Todos / Sin leer / Hot / Pinned) --}}
            <div style="display:flex;gap:0;padding:0;border-bottom:1px solid var(--alg-line);background:var(--alg-bg);">
                @foreach([
                    'all'    => ['Todos',   $folderCounts['all']],
                    'unread' => ['Sin leer', $folderCounts['unread']],
                    'hot'    => ['Hot',     $folderCounts['hot']],
                    'pinned' => ['Pinned',  $folderCounts['pinned']],
                ] as $key => $tile)
                    @php [$lbl, $count] = $tile; $isActive = $folder === $key; @endphp
                    <button type="button"
                            wire:click="setFolder('{{ $key }}')"
                            style="flex:1;padding:8px 6px;border:none;border-bottom:2px solid {{ $isActive ? 'var(--alg-accent)' : 'transparent' }};background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:{{ $isActive ? '600' : '500' }};color:{{ $isActive ? 'var(--alg-ink)' : 'var(--alg-ink-3)' }};letter-spacing:-0.005em;display:flex;flex-direction:column;align-items:center;gap:2px;">
                        <span>{{ $lbl }}</span>
                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);font-weight:500;">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            {{-- List body — date-grouped --}}
            <div style="flex:1;overflow-y:auto;min-height:0;">

                @php
                    $groups = [
                        'pinned'    => '📌 Anclados',
                        'today'     => 'Hoy',
                        'yesterday' => 'Ayer',
                        'thisWeek'  => 'Esta semana',
                        'older'     => 'Más antiguo',
                    ];
                    $totalRows = collect($grouped)->sum->count();
                @endphp

                @if($totalRows === 0)
                    <div style="padding:48px 20px;text-align:center;">
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-3);margin:0 0 4px;">Sin leads en este filtro</p>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá la búsqueda o cambiá el filtro.</p>
                    </div>
                @endif

                @foreach($groups as $key => $label)
                    @if(count($grouped[$key]) > 0)
                        <div class="alg-inbox-bucket">{{ $label }} <span style="color:var(--alg-ink-5);">({{ count($grouped[$key]) }})</span></div>
                        @foreach($grouped[$key] as $lead)
                            @php
                                $isActive = $selected && $selected->id === $lead->id;
                                $isRead   = in_array($lead->id, $readIds, true);
                                $isPinned = in_array($lead->id, $pinnedIds, true);
                                $rowClasses = 'alg-inbox-row ' . ($isActive ? 'is-active' : '') . ' ' . ($isRead ? 'is-read' : 'is-unread');
                                $initial = strtoupper(mb_substr($lead->name, 0, 1));
                                $statusColors = [
                                    'new'         => ['var(--alg-accent-soft)', 'var(--alg-accent)'],
                                    'contacted'   => ['var(--alg-surface-2)',   'var(--alg-ink-3)'],
                                    'qualified'   => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
                                    'proposal'    => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
                                    'negotiation' => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
                                    'won'         => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
                                    'lost'        => ['var(--alg-neg-soft)',    'var(--alg-neg)'],
                                ];
                                [$pillBg, $pillFg] = $statusColors[$lead->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
                            @endphp
                            <button type="button"
                                    wire:click="selectLead({{ $lead->id }})"
                                    class="{{ $rowClasses }}"
                                    style="display:block;width:100%;text-align:left;padding:10px 14px 10px 8px;border:none;border-bottom:1px solid var(--alg-line);border-left:3px solid {{ $isActive ? 'var(--alg-accent)' : 'transparent' }};background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;">
                                <div style="display:flex;align-items:flex-start;gap:8px;">
                                    {{-- Unread dot --}}
                                    <div style="width:10px;height:10px;display:flex;align-items:center;justify-content:center;margin-top:6px;flex-shrink:0;">
                                        @if(!$isRead)<span class="alg-inbox-dot"></span>@endif
                                    </div>
                                    {{-- Avatar --}}
                                    <div style="width:28px;height:28px;background:var(--alg-surface-3);color:var(--alg-ink-2);border-radius:50%;display:grid;place-items:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;font-weight:600;flex-shrink:0;">{{ $initial }}</div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
                                            <span class="alg-inbox-name" style="font-size:12.5px;color:var(--alg-ink-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;letter-spacing:-0.005em;">
                                                {{ $lead->name }}
                                                @if($isPinned)<span style="font-size:10px;color:var(--alg-warn);margin-left:4px;">📌</span>@endif
                                            </span>
                                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);flex-shrink:0;">{{ $lead->created_at->shortRelativeDiffForHumans() }}</span>
                                        </div>
                                        <div style="font-size:11.5px;color:var(--alg-ink-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:1px;">{{ $lead->email }}</div>
                                        @if($lead->notes)
                                            <div class="alg-inbox-preview" style="font-size:11.5px;color:var(--alg-ink-4);line-height:1.4;margin-top:2px;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;">{{ $lead->notes }}</div>
                                        @endif
                                        <div style="display:flex;gap:4px;margin-top:5px;flex-wrap:wrap;align-items:center;">
                                            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;padding:1px 5px;background:{{ $pillBg }};color:{{ $pillFg }};">{{ $lead->status }}</span>
                                            @if($lead->country)
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;padding:1px 5px;background:var(--alg-surface-2);color:var(--alg-ink-3);">{{ $lead->country->code }}</span>
                                            @endif
                                            @if(($lead->score ?? 0) >= 80)
                                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;letter-spacing:.04em;padding:1px 5px;background:var(--alg-warn-soft);color:var(--alg-warn);">★ HOT</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ───── RIGHT: reading pane ───── --}}
        <div style="flex:1;display:flex;flex-direction:column;min-height:0;min-width:0;background:var(--alg-bg);">
            @if($selected)
                @php
                    $rInitial = strtoupper(mb_substr($selected->name, 0, 1));
                    $isPinned = in_array($selected->id, $pinnedIds, true);
                    $isRead   = in_array($selected->id, $readIds, true);
                @endphp

                {{-- Reading-pane sticky toolbar --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 16px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);flex-shrink:0;">
                    <div style="display:flex;align-items:center;gap:2px;">
                        <button class="alg-rp-btn" wire:click="prevLead" title="Anterior (k)">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5l-5 5 5 5"/></svg>
                        </button>
                        <button class="alg-rp-btn" wire:click="nextLead" title="Siguiente (j)">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 5l5 5-5 5"/></svg>
                        </button>
                        <div style="width:1px;height:18px;background:var(--alg-line);margin:0 6px;"></div>
                        <button class="alg-rp-btn" x-on:click="focusReply()" title="Responder (r)">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 8L4 13l5 5M4 13h10c2.2 0 4-1.8 4-4V5"/></svg>
                            Nota
                        </button>
                        <button class="alg-rp-btn" wire:click="togglePin({{ $selected->id }})" title="Anclar (p)">
                            <svg viewBox="0 0 20 20" fill="{{ $isPinned ? 'var(--alg-warn)' : 'none' }}" stroke="currentColor" stroke-width="1.5"><path d="M10 3v6l-3 3v2h6v-2l-3-3V3M10 14v3"/></svg>
                            {{ $isPinned ? 'Anclado' : 'Anclar' }}
                        </button>
                        <button class="alg-rp-btn" wire:click="markUnread({{ $selected->id }})" title="Marcar como no leído">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6" cy="10" r="2.5" fill="currentColor"/><path d="M3 5h14M3 15h14"/></svg>
                            No leído
                        </button>
                    </div>
                    <div style="display:flex;align-items:center;gap:4px;">
                        @if($selected->phone)
                            <a href="https://wa.me/{{ ltrim($selected->phone, '+') }}" target="_blank" rel="noopener" class="alg-rp-btn" style="text-decoration:none;">
                                WhatsApp
                            </a>
                        @endif
                        <a href="/admin/leads/{{ $selected->id }}/edit" class="alg-rp-btn" style="text-decoration:none;background:var(--alg-ink);color:#FFFFFF;">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17h14M11 4l5 5L7 18l-5 1 1-5 8-9z"/></svg>
                            Editar
                        </a>
                    </div>
                </div>

                {{-- Reading body (scrollable) --}}
                <div style="flex:1;overflow-y:auto;background:var(--alg-bg);">

                    {{-- Header: avatar + name + email --}}
                    <div style="padding:20px 24px 16px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                        <div style="display:flex;align-items:flex-start;gap:14px;">
                            <div style="width:48px;height:48px;background:var(--alg-accent);color:#FFFFFF;border-radius:50%;display:grid;place-items:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:19px;font-weight:500;flex-shrink:0;">{{ $rInitial }}</div>
                            <div style="flex:1;min-width:0;">
                                <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:600;color:var(--alg-ink);margin:0 0 2px;letter-spacing:-0.015em;">{{ $selected->name }}</h2>
                                <a href="mailto:{{ $selected->email }}" style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink-3);text-decoration:none;letter-spacing:.04em;">{{ $selected->email }}</a>
                                @if($selected->phone)
                                    <span style="color:var(--alg-ink-5);margin:0 6px;">·</span>
                                    <a href="tel:{{ $selected->phone }}" style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink-3);text-decoration:none;letter-spacing:.04em;">{{ $selected->phone }}</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 4-col status grid --}}
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                        @foreach([
                            ['Estado', ucfirst($selected->status)],
                            ['Score', $selected->score ?? 0],
                            ['Origen', $selected->source ? ucfirst(str_replace('_',' ',$selected->source)) : '—'],
                            ['País', $selected->country?->name ?? '—'],
                        ] as $i => [$label, $value])
                            <div style="padding:10px 14px;{{ $i < 3 ? 'border-right:1px solid var(--alg-line);' : '' }}">
                                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:0 0 3px;">{{ $label }}</p>
                                <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Empresa --}}
                    @if($selected->company)
                        <div style="padding:14px 24px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:0 0 3px;">Empresa</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">{{ $selected->company }}@if($selected->position)<span style="color:var(--alg-ink-3);"> · {{ $selected->position }}</span>@endif</p>
                        </div>
                    @endif

                    {{-- Mensaje --}}
                    @php
                        // Strip the "(Blog X, Sub #Y)" reference that Fluent Forms imports add
                        // and the "— Importado de Fluent Forms" line that wraps it. We render
                        // the form URL as a separate clickable line below instead.
                        $cleanNotes = $selected->notes;
                        if ($cleanNotes) {
                            $cleanNotes = preg_replace(
                                '/\s*[—–-]\s*Importado\s+de\s+Fluent\s+Forms[^\n]*\(Blog[^)]*\)\s*/iu',
                                '',
                                $cleanNotes
                            );
                            $cleanNotes = preg_replace('/\(Blog\s*\d+,\s*Sub\s*#\s*\d+\)/iu', '', $cleanNotes);
                            $cleanNotes = trim($cleanNotes);
                        }
                    @endphp
                    @if($cleanNotes)
                        <div style="padding:14px 24px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:0 0 8px;">Mensaje</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;line-height:1.6;color:var(--alg-ink);margin:0;white-space:pre-wrap;letter-spacing:-0.005em;">{{ $cleanNotes }}</p>
                        </div>
                    @endif

                    {{-- Origen del formulario (URL) --}}
                    @if($selected->landing_page)
                        <div style="padding:10px 24px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:0 0 4px;">Origen</p>
                            <a href="{{ $selected->landing_page }}" target="_blank" rel="noopener"
                               style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:var(--alg-accent);text-decoration:none;letter-spacing:.04em;display:inline-flex;align-items:center;gap:5px;word-break:break-all;">
                                <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" style="flex-shrink:0;"><path d="M11 4h5v5M16 4l-7 7M11 9v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-5a1 1 0 011-1h6"/></svg>
                                <span>{{ $selected->landing_page }}</span>
                            </a>
                        </div>
                    @endif

                    {{-- UTM --}}
                    @if($selected->utm_source || $selected->utm_campaign)
                        <div style="padding:10px 24px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                            <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:center;">
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);">UTM</span>
                                @foreach(['utm_source'=>'source', 'utm_medium'=>'medium', 'utm_campaign'=>'campaign'] as $field => $label)
                                    @if($selected->{$field})
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);">
                                            <span style="color:var(--alg-ink-5);">{{ $label }}:</span> {{ $selected->{$field} }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Activity timeline --}}
                    <div style="padding:14px 24px;">
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-4);margin:0 0 12px;">Actividad reciente</p>
                        @if($selected->activities->isNotEmpty())
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                @foreach($selected->activities as $activity)
                                    <div style="display:flex;gap:10px;align-items:flex-start;">
                                        <div style="width:6px;height:6px;border-radius:50%;background:var(--alg-accent);margin-top:7px;flex-shrink:0;"></div>
                                        <div style="flex:1;min-width:0;">
                                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-2);margin:0 0 1px;letter-spacing:-0.005em;line-height:1.5;">{{ $activity->description ?? ucfirst($activity->type) }}</p>
                                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">{{ $activity->created_at->format('d M · H:i') }} · {{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Sin actividad registrada todavía.</p>
                        @endif
                    </div>
                </div>

                {{-- Quick reply (sticky bottom) --}}
                <div style="background:var(--alg-surface);border-top:1px solid var(--alg-line);padding:10px 16px;flex-shrink:0;">
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <textarea id="alg-quick-reply"
                                  wire:model.defer="replyText"
                                  placeholder="Agregar nota interna…  (presiona r para enfocar)"
                                  rows="2"
                                  style="flex:1;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;resize:vertical;border-radius:4px;letter-spacing:-0.005em;"></textarea>
                        <button type="button"
                                wire:click="addNote"
                                style="padding:8px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;font-weight:500;letter-spacing:-0.005em;border-radius:4px;flex-shrink:0;align-self:stretch;">
                            Guardar
                        </button>
                    </div>
                </div>

            @else
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:48px;text-align:center;">
                    <div data-empty-icon style="width:48px;height:48px;border-radius:50%;background:var(--alg-surface-2);display:grid;place-items:center;margin-bottom:14px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                        </svg>
                    </div>
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-2);margin:0 0 4px;letter-spacing:-0.005em;">Selecciona un lead</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">El detalle aparecerá aquí.</p>
                </div>
            @endif
        </div>

    </div>
</div>
</x-filament-panels::page>
