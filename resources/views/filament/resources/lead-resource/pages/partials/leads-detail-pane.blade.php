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

        {{-- Tags chips — relación tags() ya cargada en getViewData --}}
        <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--alg-line);">
            <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Tags</p>
            <div style="display:flex;flex-wrap:wrap;gap:5px;align-items:center;">
                @forelse($selected->tags as $tag)
                    @php
                        $tagBg = $tag->color ?: 'var(--alg-surface-2)';
                        $tagFg = $tag->color ? '#FFFFFF' : 'var(--alg-ink-2)';
                    @endphp
                    <span style="display:inline-flex;align-items:center;gap:4px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;font-weight:500;padding:2px 7px 2px 8px;border-radius:10px;background:{{ $tagBg }};color:{{ $tagFg }};">
                        {{ $tag->name }}
                        <button type="button" wire:click="detachTagFromSelected({{ $tag->id }})"
                                title="Quitar tag"
                                style="background:transparent;border:none;color:inherit;opacity:0.7;cursor:pointer;padding:0 0 0 2px;font-size:11px;line-height:1;">×</button>
                    </span>
                @empty
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-5);letter-spacing:.04em;">— sin tags —</span>
                @endforelse

                {{-- Add tag dropdown --}}
                @php
                    $currentTagIds = $selected->tags->pluck('id')->all();
                    $addable = ($availableTags ?? collect())->whereNotIn('id', $currentTagIds);
                @endphp
                @if($addable->count() > 0)
                    <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
                        <button type="button" @click="open = !open"
                                style="display:inline-flex;align-items:center;gap:3px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;padding:2px 7px;border:1px dashed var(--alg-line);background:transparent;color:var(--alg-ink-4);border-radius:10px;cursor:pointer;">
                            + tag
                        </button>
                        <div x-show="open" x-cloak x-transition.opacity
                             style="position:absolute;top:calc(100% + 4px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:6px;box-shadow:0 4px 14px rgba(0,0,0,0.10);padding:4px;z-index:10;display:flex;flex-direction:column;gap:1px;min-width:140px;max-height:240px;overflow-y:auto;">
                            @foreach($addable as $tag)
                                <button type="button" wire:click="attachTagToSelected({{ $tag->id }})" @click="open = false"
                                        style="display:flex;align-items:center;gap:6px;padding:5px 9px;border:none;background:transparent;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);text-align:left;border-radius:3px;"
                                        onmouseover="this.style.background='var(--alg-surface-2)'"
                                        onmouseout="this.style.background='transparent'">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $tag->color ?: 'var(--alg-ink-4)' }};"></span>{{ $tag->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Activity timeline — usa la relación activities() ya cargada en getViewData --}}
        @if($selected->activities && $selected->activities->count() > 0)
            <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--alg-line);">
                <p style="margin:0 0 10px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Historial</p>
                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($selected->activities as $i => $activity)
                        @php
                            $iconByType = [
                                'note'         => '📝',
                                'call'         => '📞',
                                'email'        => '✉',
                                'meeting'      => '👥',
                                'status_change'=> '↔',
                                'created'      => '+',
                            ];
                            $icon = $iconByType[$activity->type] ?? '·';
                            $isLast = $i === $selected->activities->count() - 1;
                        @endphp
                        <div style="display:flex;gap:10px;align-items:flex-start;position:relative;">
                            {{-- Vertical timeline line --}}
                            @if(! $isLast)
                                <div style="position:absolute;left:11px;top:24px;bottom:-8px;width:1px;background:var(--alg-line);"></div>
                            @endif
                            {{-- Icon dot --}}
                            <div style="flex-shrink:0;width:22px;height:22px;border-radius:50%;background:var(--alg-surface-2);border:1px solid var(--alg-line);display:flex;align-items:center;justify-content:center;font-size:10px;color:var(--alg-ink-3);z-index:1;">{{ $icon }}</div>
                            {{-- Content --}}
                            <div style="flex:1;padding-bottom:12px;min-width:0;">
                                <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;">
                                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;">
                                        {{ $activity->user?->name ?? 'Sistema' }}
                                        <span style="color:var(--alg-ink-4);font-weight:400;">— {{ $activity->type }}</span>
                                    </span>
                                    <span title="{{ $activity->created_at->toDateTimeString() }}"
                                          style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-5);white-space:nowrap;letter-spacing:.04em;">{{ $activity->created_at->diffForHumans() }}</span>
                                </div>
                                @if($activity->description)
                                    <p style="margin:2px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);line-height:1.4;">{{ $activity->description }}</p>
                                @endif
                                @if($activity->outcome)
                                    <p style="margin:2px 0 0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-3);letter-spacing:.04em;">→ {{ $activity->outcome }}</p>
                                @endif
                                @if($activity->next_action)
                                    <p style="margin:4px 0 0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-warn);">⏰ {{ $activity->next_action }}{{ $activity->next_action_date ? ' · ' . $activity->next_action_date->format('d M') : '' }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

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
    <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);flex-wrap:wrap;">
        <button type="button" wire:click="saveLead"
                style="padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;border:none;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">
            Guardar
        </button>

        {{-- Snooze dropdown — current state shown if snoozed --}}
        <div x-data="{ open: false }" @click.outside="open = false" style="position:relative;">
            @php $isSnoozed = $selected->snoozed_until && $selected->snoozed_until->isFuture(); @endphp
            <button type="button" @click="open = !open"
                    title="{{ $isSnoozed ? 'Snoozed hasta ' . $selected->snoozed_until->translatedFormat('d M H:i') : 'Snooze este lead' }}"
                    style="padding:7px 12px;background:{{ $isSnoozed ? 'var(--alg-warn-soft)' : 'var(--alg-surface)' }};color:{{ $isSnoozed ? 'var(--alg-warn)' : 'var(--alg-ink-2)' }};border:1px solid var(--alg-line);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
                ⏰ {{ $isSnoozed ? 'Snoozed' : 'Snooze' }}
            </button>
            <div x-show="open" x-cloak x-transition.opacity
                 style="position:absolute;bottom:calc(100% + 4px);left:0;background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:6px;box-shadow:0 4px 14px rgba(0,0,0,0.14);padding:4px;z-index:50;display:flex;flex-direction:column;gap:1px;min-width:180px;">
                @foreach(['1h' => 'En 1 hora', '3h' => 'En 3 horas', 'tomorrow' => 'Mañana 9 AM', 'monday' => 'Próximo lunes 9 AM', 'week' => 'En 1 semana'] as $when => $lbl)
                    <button type="button" wire:click="snoozeSelected('{{ $when }}')" @click="open = false"
                            style="padding:6px 11px;border:none;background:transparent;color:var(--alg-ink-2);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;text-align:left;cursor:pointer;border-radius:3px;"
                            onmouseover="this.style.background='var(--alg-surface-2)'"
                            onmouseout="this.style.background='transparent'">{{ $lbl }}</button>
                @endforeach
                @if($isSnoozed)
                    <div style="height:1px;background:var(--alg-line);margin:3px 0;"></div>
                    <button type="button" wire:click="unsnoozeSelected" @click="open = false"
                            style="padding:6px 11px;border:none;background:transparent;color:var(--alg-neg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;text-align:left;cursor:pointer;border-radius:3px;"
                            onmouseover="this.style.background='var(--alg-neg-soft)'"
                            onmouseout="this.style.background='transparent'">Quitar snooze</button>
                @endif
            </div>
        </div>

        <a href="/admin/leads?view=inbox&selected={{ $selected->id }}"
           style="padding:7px 12px;background:var(--alg-surface);color:var(--alg-ink-2);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;">
            Abrir bandeja
        </a>

        {{-- Convert to Cliente — solo visible cuando el lead está won (escalación a post-venta) --}}
        @if($selected->status === 'won')
            <button type="button" wire:click="convertSelectedToClient"
                    wire:confirm="¿Convertir este lead en Cliente? Se creará una Cuenta nueva en /admin/clients."
                    title="Crear Cliente con datos pre-llenados desde este lead"
                    style="padding:7px 12px;background:var(--alg-pos-soft);color:var(--alg-pos);border:1px solid var(--alg-pos);cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:600;border-radius:4px;letter-spacing:-0.005em;">
                🏆 Convertir a Cliente
            </button>
        @endif

        <div style="flex:1;"></div>
        <a href="{{ \App\Filament\Resources\LeadResource::getUrl('edit', ['record' => $selected]) }}"
           title="Editor completo"
           style="padding:7px 10px;background:transparent;color:var(--alg-ink-4);border:1px solid var(--alg-line);text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;border-radius:4px;">
            Editor →
        </a>
    </div>
</aside>
