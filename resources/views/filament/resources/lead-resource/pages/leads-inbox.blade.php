<x-filament-panels::page>
    <div style="display:grid;grid-template-columns:380px 1fr;gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);height:calc(100vh - 200px);min-height:520px;overflow:hidden;">

        {{-- ═══════ LEFT: list ═══════ --}}
        <div style="display:flex;flex-direction:column;border-right:1px solid var(--alg-line);min-width:0;background:var(--alg-surface);">

            {{-- Search + filters --}}
            <div style="padding:14px 16px;border-bottom:1px solid var(--alg-line);background:var(--alg-bg);">
                <div style="position:relative;">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;">
                        <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Buscar nombre, email, empresa…"
                           style="width:100%;padding:8px 10px 8px 32px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;">
                </div>

                {{-- Status chips --}}
                <div style="display:flex;gap:4px;margin-top:10px;flex-wrap:wrap;">
                    @foreach($statuses as $key => $label)
                        <button type="button"
                                wire:click="setStatus('{{ $key }}')"
                                style="padding:3px 8px;border:1px solid var(--alg-line);background:{{ $statusFilter === $key ? 'var(--alg-ink)' : 'var(--alg-surface)' }};color:{{ $statusFilter === $key ? '#FFFFFF' : 'var(--alg-ink-3)' }};cursor:pointer;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Counter --}}
            <div style="padding:8px 16px;border-bottom:1px solid var(--alg-line);background:var(--alg-surface-2);">
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);letter-spacing:.04em;">{{ $leads->count() }} {{ $leads->count() === 1 ? 'lead' : 'leads' }}</span>
            </div>

            {{-- List --}}
            <div style="flex:1;overflow-y:auto;">
                @forelse($leads as $lead)
                    @php
                        $isActive = $selected && $selected->id === $lead->id;
                        $initial = strtoupper(mb_substr($lead->name, 0, 1));
                    @endphp
                    <button type="button"
                            wire:click="selectLead({{ $lead->id }})"
                            style="display:block;width:100%;text-align:left;padding:12px 16px;border:none;border-bottom:1px solid var(--alg-line);border-left:3px solid {{ $isActive ? 'var(--alg-accent)' : 'transparent' }};background:{{ $isActive ? 'var(--alg-surface-2)' : 'transparent' }};cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;transition:background 120ms;"
                            onmouseover="if(!this.style.borderLeftColor||this.style.borderLeftColor==='transparent') this.style.background='var(--alg-surface-2)'"
                            onmouseout="if(!{{ $isActive ? 'true' : 'false' }}) this.style.background='transparent'">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            {{-- Avatar --}}
                            <div style="width:32px;height:32px;background:var(--alg-surface-3);color:var(--alg-ink-2);border-radius:50%;display:grid;place-items:center;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:600;flex-shrink:0;">{{ $initial }}</div>

                            <div style="flex:1;min-width:0;">
                                {{-- Name + time --}}
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:2px;">
                                    <span style="font-size:12.5px;font-weight:500;color:var(--alg-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;letter-spacing:-0.005em;">{{ $lead->name }}</span>
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);flex-shrink:0;">{{ $lead->created_at->shortRelativeDiffForHumans() }}</span>
                                </div>
                                {{-- Email --}}
                                <div style="font-size:11.5px;color:var(--alg-ink-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $lead->email }}</div>
                                {{-- Notes preview --}}
                                @if($lead->notes)
                                    <div style="font-size:11.5px;color:var(--alg-ink-4);line-height:1.45;margin-top:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $lead->notes }}</div>
                                @endif
                                {{-- Badges --}}
                                <div style="display:flex;gap:5px;margin-top:6px;flex-wrap:wrap;">
                                    @php
                                        $statusColors = [
                                            'new'         => ['var(--alg-accent-soft)', 'var(--alg-accent)'],
                                            'contacted'   => ['var(--alg-surface-2)',   'var(--alg-ink-3)'],
                                            'qualified'   => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
                                            'proposal'    => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
                                            'negotiation' => ['var(--alg-warn-soft)',   'var(--alg-warn)'],
                                            'won'         => ['var(--alg-pos-soft)',    'var(--alg-pos)'],
                                            'lost'        => ['var(--alg-neg-soft)',    'var(--alg-neg)'],
                                        ];
                                        [$bg, $fg] = $statusColors[$lead->status] ?? ['var(--alg-surface-2)', 'var(--alg-ink-3)'];
                                    @endphp
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;padding:2px 6px;background:{{ $bg }};color:{{ $fg }};">{{ $lead->status }}</span>
                                    @if($lead->country)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;padding:2px 6px;background:var(--alg-surface-2);color:var(--alg-ink-3);">{{ $lead->country->code }}</span>
                                    @endif
                                    @if($lead->score >= 80)
                                        <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;letter-spacing:.04em;padding:2px 6px;background:var(--alg-warn-soft);color:var(--alg-warn);">★ HOT</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div style="padding:48px 20px;text-align:center;">
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink-3);margin:0 0 4px;">Sin leads en este filtro</p>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Limpiá la búsqueda o cambiá el estado.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════ RIGHT: detail card ═══════ --}}
        <div style="display:flex;flex-direction:column;min-width:0;overflow-y:auto;background:var(--alg-bg);">
            @if($selected)
                @php $rInitial = strtoupper(mb_substr($selected->name, 0, 1)); @endphp

                {{-- Header: avatar + name + actions --}}
                <div style="padding:24px 28px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                    <div style="display:flex;align-items:flex-start;gap:16px;">
                        <div style="width:56px;height:56px;background:var(--alg-accent);color:#FFFFFF;border-radius:50%;display:grid;place-items:center;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;flex-shrink:0;">{{ $rInitial }}</div>
                        <div style="flex:1;min-width:0;">
                            <h2 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;color:var(--alg-ink);margin:0 0 4px;letter-spacing:-0.02em;">{{ $selected->name }}</h2>
                            <a href="mailto:{{ $selected->email }}" style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink-3);text-decoration:none;letter-spacing:.04em;">{{ $selected->email }}</a>
                            @if($selected->phone)
                                <a href="tel:{{ $selected->phone }}" style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12.5px;color:var(--alg-ink-3);text-decoration:none;letter-spacing:.04em;margin-top:2px;">{{ $selected->phone }}</a>
                            @endif
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <a href="/admin/leads/{{ $selected->id }}/edit" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--alg-ink);color:#FFFFFF;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;letter-spacing:-0.005em;">
                                <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17h14M11 4l5 5L7 18l-5 1 1-5 8-9z"/></svg>
                                Editar
                            </a>
                            @if($selected->phone)
                                <a href="https://wa.me/{{ ltrim($selected->phone, '+') }}" target="_blank" rel="noopener" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:transparent;border:1px solid var(--alg-line);color:var(--alg-ink-2);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;letter-spacing:-0.005em;">
                                    WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 4-col status grid --}}
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                    @foreach([
                        ['Estado', ucfirst($selected->status)],
                        ['Score', $selected->score ?? 0],
                        ['Origen', $selected->source ? ucfirst(str_replace('_',' ',$selected->source)) : '—'],
                        ['País', $selected->country?->name ?? '—'],
                    ] as $i => [$label, $value])
                        <div style="padding:14px 18px;{{ $i < 3 ? 'border-right:1px solid var(--alg-line);' : '' }}">
                            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 6px;">{{ $label }}</p>
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Empresa --}}
                @if($selected->company)
                    <div style="padding:18px 28px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 6px;">Empresa</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink);margin:0;letter-spacing:-0.005em;">{{ $selected->company }}</p>
                        @if($selected->position)
                            <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink-3);margin:2px 0 0;">{{ $selected->position }}</p>
                        @endif
                    </div>
                @endif

                {{-- Mensaje (notes) --}}
                @if($selected->notes)
                    <div style="padding:18px 28px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 10px;">Mensaje</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;line-height:1.65;color:var(--alg-ink-2);margin:0;white-space:pre-wrap;letter-spacing:-0.005em;">{{ $selected->notes }}</p>
                    </div>
                @endif

                {{-- UTM info --}}
                @if($selected->utm_source || $selected->utm_campaign)
                    <div style="padding:14px 28px;background:var(--alg-surface);border-bottom:1px solid var(--alg-line);">
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 8px;">UTM</p>
                        <div style="display:flex;gap:14px;flex-wrap:wrap;">
                            @foreach(['utm_source'=>'source', 'utm_medium'=>'medium', 'utm_campaign'=>'campaign'] as $field => $label)
                                @if($selected->{$field})
                                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);">
                                        <span style="color:var(--alg-ink-4);">{{ $label }}:</span> {{ $selected->{$field} }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Activity timeline --}}
                <div style="padding:18px 28px;flex:1;">
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:var(--alg-ink-3);margin:0 0 14px;">Actividad reciente</p>

                    @if($selected->activities->isNotEmpty())
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            @foreach($selected->activities as $activity)
                                <div style="display:flex;gap:12px;align-items:flex-start;">
                                    <div style="width:8px;height:8px;border-radius:50%;background:var(--alg-accent);margin-top:6px;flex-shrink:0;"></div>
                                    <div style="flex:1;min-width:0;">
                                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;color:var(--alg-ink);margin:0 0 2px;letter-spacing:-0.005em;">{{ $activity->description ?? ucfirst($activity->type) }}</p>
                                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">{{ $activity->created_at->format('d M Y · H:i') }} · {{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">Sin actividad registrada todavía.</p>
                    @endif
                </div>

                {{-- Created/updated footer --}}
                <div style="padding:12px 28px;background:var(--alg-surface-2);border-top:1px solid var(--alg-line);">
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">
                        Creado {{ $selected->created_at->format('d M Y H:i') }}
                        @if($selected->updated_at && $selected->updated_at->ne($selected->created_at))
                            · Actualizado {{ $selected->updated_at->diffForHumans() }}
                        @endif
                    </span>
                </div>

            @else
                {{-- Empty state --}}
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:48px;text-align:center;">
                    <div data-empty-icon style="width:48px;height:48px;border-radius:50%;background:var(--alg-surface-2);display:grid;place-items:center;margin-bottom:14px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;color:var(--alg-ink-2);margin:0 0 4px;letter-spacing:-0.005em;">Selecciona un lead</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0;letter-spacing:.04em;">El detalle aparecerá aquí.</p>
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
