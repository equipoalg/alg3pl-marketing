{{-- Floating bulk-action bar — shown when selectedLeadIds is non-empty.
     Expects: $selectedLeadIds, $statuses --}}
@php
    $statusColorMap = [
        'new'         => 'var(--alg-accent)',
        'contacted'   => 'var(--alg-ink-3)',
        'qualified'   => 'var(--alg-pos)',
        'proposal'    => 'var(--alg-warn)',
        'negotiation' => 'var(--alg-warn)',
        'won'         => 'var(--alg-pos)',
        'lost'        => 'var(--alg-neg)',
    ];
@endphp
@if(count($selectedLeadIds ?? []) > 0)
    <div style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--alg-ink);color:#FFFFFF;padding:10px 14px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.35);display:flex;align-items:center;gap:12px;z-index:1000;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
        <span style="font-weight:600;">{{ count($selectedLeadIds) }} seleccionado{{ count($selectedLeadIds) > 1 ? 's' : '' }}</span>
        <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>

        {{-- Cambiar estado en bulk --}}
        <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
            <button type="button" @click="open = !open"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='transparent'">
                ▼ Cambiar estado
            </button>
            <div x-show="open" x-cloak x-transition.opacity
                 style="position:absolute;bottom:calc(100% + 4px);left:0;background:var(--alg-ink);border:1px solid rgba(255,255,255,0.15);border-radius:6px;padding:4px;display:flex;flex-direction:column;gap:1px;min-width:160px;">
                @foreach($statuses as $stKey => $stLabel)
                    <button type="button" wire:click="bulkSetStatus('{{ $stKey }}')" @click="open = false"
                            style="display:flex;align-items:center;gap:6px;padding:5px 10px;border:none;background:transparent;color:#FFFFFF;font-family:inherit;font-size:inherit;text-align:left;cursor:pointer;border-radius:4px;"
                            onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                            onmouseout="this.style.background='transparent'">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $statusColorMap[$stKey] ?? 'var(--alg-ink-4)' }};"></span>{{ $stLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        <button type="button" wire:click="bulkAssignToMe"
                style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border:none;background:transparent;color:#FFFFFF;cursor:pointer;font-family:inherit;font-size:inherit;border-radius:4px;font-weight:500;"
                onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                onmouseout="this.style.background='transparent'">
            👤 Asignarme
        </button>

        <span style="width:1px;height:16px;background:rgba(255,255,255,0.20);"></span>

        <button type="button" wire:click="clearSelectedLeads"
                title="Quitar selección"
                style="border:none;background:transparent;color:rgba(255,255,255,0.65);cursor:pointer;padding:4px 8px;font-size:14px;line-height:1;border-radius:4px;"
                onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                onmouseout="this.style.background='transparent'">×</button>
    </div>
@endif
