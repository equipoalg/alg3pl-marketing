{{-- Slide-over derecho — abre al click en una fila de Contactos/Empresas.
     Expects: $selected (Lead model with country/tags), $statuses (array)
     Wire methods: saveLead, closeLead, selectLead --}}
@php
    use App\Filament\Resources\LeadResource\Pages\ListLeads;
    $av = ListLeads::avatarFor($selected->email, $selected->name);
    $statusColors = [
        'new'         => ['var(--alg-accent-soft)', 'var(--alg-accent)'],
        'contacted'   => ['var(--alg-surface-2)',   'var(--alg-ink-3)'],
        'qualified'   => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
        'proposal'    => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
        'negotiation' => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
        'won'         => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
        'lost'        => ['var(--alg-neg-soft)',    'var(--alg-neg)'],
    ];
    [$pillBg, $pillFg] = $statusColors[$selected->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
    $temp = $selected->score ?? 0;
@endphp
<aside style="width:420px;flex-shrink:0;border-left:1px solid var(--alg-line);background:var(--alg-surface);display:flex;flex-direction:column;min-height:0;overflow:hidden;">

    {{-- Header --}}
    <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:{{ $av['bg'] }};color:{{ $av['fg'] }};font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:600;flex-shrink:0;">{{ $av['initials'] }}</span>
            <div style="min-width:0;">
                <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13.5px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->name }}</div>
                <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · creado {{ $selected->created_at->diffForHumans() }}</div>
            </div>
        </div>
        <button type="button" wire:click="closeLead"
                title="Cerrar (Esc)"
                style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
    </div>

    {{-- Body --}}
    <div style="flex:1;overflow-y:auto;padding:16px 18px;">
        {{-- Status badge + score chip --}}
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
            <span style="display:inline-block;font-size:10px;font-weight:500;color:{{ $pillFg }};background:{{ $pillBg }};padding:3px 8px;border-radius:2px;letter-spacing:.04em;text-transform:uppercase;">{{ $statuses[$selected->status] ?? $selected->status }}</span>
            <span style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:600;background:{{ $temp >= 80 ? 'var(--alg-warn-soft)' : 'var(--alg-surface-2)' }};color:{{ $temp >= 80 ? 'var(--alg-warn)' : 'var(--alg-ink-3)' }};padding:3px 8px;border-radius:2px;letter-spacing:.04em;">Score {{ $temp }}{{ $temp >= 80 ? ' · HOT' : '' }}</span>
            @if($selected->country)
                <span style="display:inline-block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);background:var(--alg-surface-2);padding:3px 8px;border-radius:2px;letter-spacing:.04em;">{{ strtoupper($selected->country->code) }}</span>
            @endif
        </div>

        {{-- Editable form fields --}}
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div>
                <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Nombre</label>
                <input type="text" wire:model.live.debounce.500ms="editName"
                       style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
            </div>
            <div>
                <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Email</label>
                <input type="email" wire:model.live.debounce.500ms="editEmail"
                       style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Teléfono</label>
                    <input type="tel" wire:model.live.debounce.500ms="editPhone"
                           style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);outline:none;border-radius:3px;">
                </div>
                <div>
                    <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Estado</label>
                    <select wire:model.live="editStatus"
                            style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;cursor:pointer;">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Empresa</label>
                <input type="text" wire:model.live.debounce.500ms="editCompany"
                       style="width:100%;padding:7px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);outline:none;border-radius:3px;">
            </div>
            <div>
                <label style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);margin-bottom:4px;">Notas</label>
                <textarea wire:model.live.debounce.700ms="editNotes" rows="5"
                          style="width:100%;padding:8px 10px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:3px;resize:vertical;line-height:1.5;"></textarea>
            </div>
        </div>

        {{-- Read-only meta --}}
        <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--alg-line);display:grid;grid-template-columns:auto 1fr;gap:8px 14px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">
            @if($selected->source)
                <span>Fuente</span>
                <span style="color:var(--alg-ink-3);">{{ $selected->source }}{{ $selected->source_detail ? ' · ' . $selected->source_detail : '' }}</span>
            @endif
            @if($selected->utm_source)
                <span>UTM</span>
                <span style="color:var(--alg-ink-3);">{{ $selected->utm_source }}{{ $selected->utm_campaign ? ' / ' . $selected->utm_campaign : '' }}</span>
            @endif
            @if($selected->estimated_value)
                <span>Valor est.</span>
                <span style="color:var(--alg-ink-3);">${{ number_format($selected->estimated_value, 0) }}</span>
            @endif
        </div>
    </div>

    {{-- Footer actions --}}
    <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
        <button type="button" wire:click="saveLead"
                style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
            Guardar
        </button>
        <a href="/admin/leads?view=inbox&selected={{ $selected->id }}"
           style="padding:7px 12px;background:var(--alg-surface);color:var(--alg-ink-2);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
            Abrir bandeja
        </a>
        <div style="flex:1;"></div>
        <a href="{{ \App\Filament\Resources\LeadResource::getUrl('edit', ['record' => $selected]) }}"
           title="Editor completo"
           style="padding:7px 10px;background:transparent;color:var(--alg-ink-4);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;border-radius:4px;">
            Editor →
        </a>
    </div>
</aside>
