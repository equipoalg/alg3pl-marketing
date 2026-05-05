<x-filament-panels::page>
    <style>
        .fi-page > .fi-header,
        .fi-page-header { display: none !important; }
        .fi-main-ctn > .fi-page { padding-top: 0 !important; }
        .fi-main { padding-top: 0.5rem !important; }
        .alg-chip {
            display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
            border:1px solid var(--alg-line);background:var(--alg-surface);color:var(--alg-ink-3);
            font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;
            border-radius:4px;cursor:pointer;transition:all 100ms ease;
        }
        .alg-chip:hover { background: var(--alg-surface-2); color: var(--alg-ink); }
        .alg-chip.is-active { background: var(--alg-accent-soft); color: var(--alg-accent); border-color: var(--alg-accent); }
        @media (max-width: 900px) {
            .alg-ec-kpis { grid-template-columns: repeat(3, 1fr) !important; }
            .alg-ec-layout { grid-template-columns: 1fr !important; }
        }
    </style>

    @php
        $hasFilters = $currentSearch !== '' || $currentPeriod !== '30d' || $currentSort !== 'sent_at_desc';
        // Color helper para rates: open ≥25 verde / 15-25 amarillo / <15 rojo
        $rateColor = function ($rate, $type = 'open') {
            $thresholds = match ($type) {
                'open'   => [25, 15],  // ≥25% bueno, 15-25% medio
                'click'  => [3, 1],
                'bounce' => [2, 5],    // bounce: <2% bueno, 2-5% medio, >5% malo (invertido)
                default  => [10, 5],
            };
            if ($type === 'bounce') {
                return $rate < $thresholds[0] ? 'var(--alg-pos)' : ($rate < $thresholds[1] ? 'var(--alg-warn)' : 'var(--alg-neg)');
            }
            return $rate >= $thresholds[0] ? 'var(--alg-pos)' : ($rate >= $thresholds[1] ? 'var(--alg-warn)' : 'var(--alg-neg)');
        };
    @endphp

    <div class="alg-ec-layout" style="display:grid;grid-template-columns:1fr {{ $selected ? '420px' : '' }};gap:14px;align-items:flex-start;font-family:var(--alg-font);">

    <div style="display:flex;flex-direction:column;gap:14px;min-width:0;">

        {{-- Toolbar --}}
        <div style="display:flex;align-items:center;gap:10px;background:var(--alg-surface);border:1px solid var(--alg-line);padding:9px 14px;flex-wrap:wrap;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;">Envíos</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);letter-spacing:.04em;">· {{ $totalShown }} de {{ $kpis['sends'] }}</span>

            <div style="position:relative;flex:1;min-width:220px;max-width:340px;">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5" stroke-linecap="round" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar subject, campaña, from…"
                       style="width:100%;padding:6px 10px 6px 28px;border:1px solid var(--alg-line);background:var(--alg-bg);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:var(--alg-ink);outline:none;border-radius:4px;">
            </div>

            <div style="display:inline-flex;background:var(--alg-surface-2);border:1px solid var(--alg-line);border-radius:5px;padding:1px;">
                @foreach(['7d'=>'7d','30d'=>'30d','90d'=>'90d','all'=>'Todo'] as $key=>$label)
                    @php $isActive = $currentPeriod === $key; @endphp
                    <button type="button" wire:click="setPeriod('{{ $key }}')"
                            style="padding:4px 10px;border:none;background:{{ $isActive ? 'var(--alg-surface)' : 'transparent' }};color:{{ $isActive ? 'var(--alg-ink)' : 'var(--alg-ink-4)' }};border-radius:4px;cursor:pointer;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;font-weight:500;letter-spacing:-0.005em;">{{ $label }}</button>
                @endforeach
            </div>

            <select wire:model.live="sortBy" title="Ordenar"
                    style="padding:5px 9px;border:1px solid var(--alg-line);background:var(--alg-surface);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11.5px;color:var(--alg-ink-2);cursor:pointer;outline:none;border-radius:4px;">
                <option value="sent_at_desc">↓ Más recientes</option>
                <option value="open_rate_desc">★ Mejor open rate</option>
                <option value="click_rate_desc">↗ Mejor click rate</option>
                <option value="sent_count_desc">📨 Mayor volumen</option>
            </select>

            @if($hasFilters)
                <button type="button" wire:click="clearFilters"
                        style="padding:4px 9px;border:none;background:transparent;color:var(--alg-ink-4);font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;cursor:pointer;text-decoration:underline;">× limpiar</button>
            @endif
        </div>

        {{-- KPI tiles (6) — el corazón de esta página son las rates --}}
        <div class="alg-ec-kpis" style="display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid var(--alg-line);background:var(--alg-surface);">
            @php
                $tiles = [
                    ['Envíos',       number_format($kpis['sends']),                              'var(--alg-ink)'],
                    ['Total enviados', number_format($kpis['sent']),                             'var(--alg-ink)'],
                    ['Open rate',    $kpis['open_rate'] . '%',                                   $rateColor($kpis['open_rate'], 'open')],
                    ['Click rate',   $kpis['click_rate'] . '%',                                  $rateColor($kpis['click_rate'], 'click')],
                    ['Bounce rate',  $kpis['bounce_rate'] . '%',                                 $rateColor($kpis['bounce_rate'], 'bounce')],
                    ['Unsubs',       number_format($kpis['unsub']),                              'var(--alg-ink-3)'],
                ];
            @endphp
            @foreach($tiles as [$lbl, $val, $color])
                <div style="padding:14px 16px;border-right:1px solid var(--alg-line);display:flex;flex-direction:column;gap:4px;">
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:11px;color:var(--alg-ink-3);letter-spacing:-0.005em;">{{ $lbl }}</span>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:22px;font-weight:500;color:{{ $color }};letter-spacing:-0.025em;line-height:1;font-variant-numeric:tabular-nums;">{{ $val }}</span>
                </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div style="background:var(--alg-surface);border:1px solid var(--alg-line);overflow:hidden;">
            @if($sends->isEmpty())
                <div style="padding:64px 24px;text-align:center;">
                    <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:15px;font-weight:500;color:var(--alg-ink);margin:0 0 6px;">Sin envíos en este período</p>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-4);margin:0 0 18px;letter-spacing:.04em;">Los envíos aparecen automáticamente cuando ejecutás una campaña tipo email.</p>
                    <a href="/admin/campaigns?type=email"
                       style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;text-decoration:none;font-family:inherit;font-size:12.5px;font-weight:500;border-radius:4px;">→ Ver campañas email</a>
                </div>
            @else
                <table style="width:100%;border-collapse:collapse;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;">
                    <thead>
                        <tr style="background:var(--alg-bg);">
                            <th style="text-align:left;padding:9px 12px 9px 18px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Subject</th>
                            <th style="text-align:left;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Campaña</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Enviados</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Open</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Click</th>
                            <th style="text-align:right;padding:9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Bounce</th>
                            <th style="text-align:right;padding:9px 18px 9px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.10em;border-bottom:1px solid var(--alg-line);">Enviado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sends as $s)
                            @php
                                $openRate  = $s->sent_count > 0 ? round(($s->open_count / $s->sent_count) * 100, 1) : 0;
                                $clickRate = $s->sent_count > 0 ? round(($s->click_count / $s->sent_count) * 100, 1) : 0;
                                $bounceRate= $s->sent_count > 0 ? round(($s->bounce_count / $s->sent_count) * 100, 1) : 0;
                                $isActive = $selectedId === $s->id;
                                $rowBg = $isActive ? 'var(--alg-accent-soft)' : 'transparent';
                            @endphp
                            <tr wire:click="selectSend({{ $s->id }})"
                                style="cursor:pointer;border-bottom:1px solid var(--alg-line);transition:background 100ms ease;background:{{ $rowBg }};"
                                onmouseover="if(!this.dataset.locked)this.style.background='var(--alg-surface-2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'"
                                @if($isActive) data-locked="1" @endif>
                                <td style="padding:11px 12px 11px 18px;">
                                    <div style="font-size:13px;color:var(--alg-ink);font-weight:500;letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:340px;">{{ $s->subject ?: '— sin subject —' }}</div>
                                    @if($s->from_email)
                                        <div style="font-size:11px;color:var(--alg-ink-4);margin-top:1px;font-family:ui-monospace,'SF Mono',Menlo,monospace;letter-spacing:.04em;">{{ $s->from_email }}</div>
                                    @endif
                                </td>
                                <td style="padding:11px 12px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-2);">
                                    {{ $s->campaign?->name ?? '—' }}
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:12px;color:var(--alg-ink);font-weight:500;font-variant-numeric:tabular-nums;">{{ number_format($s->sent_count ?? 0) }}</td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:{{ $rateColor($openRate, 'open') }};font-variant-numeric:tabular-nums;">
                                    <strong>{{ $openRate }}%</strong>
                                    <span style="color:var(--alg-ink-5);font-size:9.5px;display:block;margin-top:1px;">{{ number_format($s->open_count ?? 0) }}</span>
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:{{ $rateColor($clickRate, 'click') }};font-variant-numeric:tabular-nums;">
                                    <strong>{{ $clickRate }}%</strong>
                                    <span style="color:var(--alg-ink-5);font-size:9.5px;display:block;margin-top:1px;">{{ number_format($s->click_count ?? 0) }}</span>
                                </td>
                                <td style="padding:11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11.5px;color:{{ $rateColor($bounceRate, 'bounce') }};font-variant-numeric:tabular-nums;">
                                    <strong>{{ $bounceRate }}%</strong>
                                    <span style="color:var(--alg-ink-5);font-size:9.5px;display:block;margin-top:1px;">{{ number_format($s->bounce_count ?? 0) }}</span>
                                </td>
                                <td style="padding:11px 18px 11px 12px;text-align:right;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;">
                                    {{ $s->sent_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Slide-over (read-only detail) --}}
    @if($selected)
        @php
            $selOpenRate  = $selected->sent_count > 0 ? round(($selected->open_count / $selected->sent_count) * 100, 1) : 0;
            $selClickRate = $selected->sent_count > 0 ? round(($selected->click_count / $selected->sent_count) * 100, 1) : 0;
            $selBounceRate= $selected->sent_count > 0 ? round(($selected->bounce_count / $selected->sent_count) * 100, 1) : 0;
            $selUnsubRate = $selected->sent_count > 0 ? round(($selected->unsubscribe_count / $selected->sent_count) * 100, 1) : 0;
        @endphp
        <aside style="background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:4px;display:flex;flex-direction:column;max-height:calc(100vh - 100px);overflow:hidden;position:sticky;top:14px;font-family:var(--alg-font);">
            <div style="padding:14px 18px;border-bottom:1px solid var(--alg-line);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="min-width:0;">
                    <div style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:14px;font-weight:600;color:var(--alg-ink);letter-spacing:-0.005em;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $selected->subject ?: '— sin subject —' }}</div>
                    <div style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);margin-top:2px;letter-spacing:.04em;">#{{ $selected->id }} · {{ $selected->campaign?->name ?? 'sin campaña' }} · enviado {{ $selected->sent_at?->diffForHumans() ?? '—' }}</div>
                </div>
                <button type="button" wire:click="closeSend" style="border:none;background:transparent;color:var(--alg-ink-4);cursor:pointer;padding:4px 6px;border-radius:3px;font-size:18px;line-height:1;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 18px;">
                {{-- Performance grid --}}
                <p style="margin:0 0 8px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Performance</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px;">
                    @php
                        $perfTiles = [
                            ['Enviados',  number_format($selected->sent_count ?? 0),    null,             'var(--alg-ink)'],
                            ['Abiertos',  number_format($selected->open_count ?? 0),    $selOpenRate,     $rateColor($selOpenRate, 'open')],
                            ['Clicks',    number_format($selected->click_count ?? 0),   $selClickRate,    $rateColor($selClickRate, 'click')],
                            ['Bounces',   number_format($selected->bounce_count ?? 0),  $selBounceRate,   $rateColor($selBounceRate, 'bounce')],
                            ['Unsubs',    number_format($selected->unsubscribe_count ?? 0), $selUnsubRate, 'var(--alg-ink-4)'],
                        ];
                    @endphp
                    @foreach($perfTiles as [$lbl, $val, $rate, $color])
                        <div style="padding:10px 12px;background:var(--alg-bg);border:1px solid var(--alg-line);border-radius:4px;">
                            <p style="margin:0 0 3px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">{{ $lbl }}</p>
                            <p style="margin:0;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:600;color:{{ $color }};font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ $val }}</p>
                            @if($rate !== null)
                                <p style="margin:2px 0 0;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:{{ $color }};font-variant-numeric:tabular-nums;">{{ $rate }}%</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Meta info --}}
                <p style="margin:0 0 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Detalles</p>
                <div style="display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10.5px;color:var(--alg-ink-4);letter-spacing:.04em;">
                    @if($selected->from_name)
                        <span>From name</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->from_name }}</span>
                    @endif
                    @if($selected->from_email)
                        <span>From email</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->from_email }}</span>
                    @endif
                    @if($selected->template)
                        <span>Template</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->template->name }}</span>
                    @endif
                    @if($selected->variant)
                        <span>Variante A/B</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->variant }}</span>
                    @endif
                    @if($selected->sent_at)
                        <span>Sent at</span>
                        <span style="color:var(--alg-ink-3);">{{ $selected->sent_at->format('d M Y H:i') }}</span>
                    @endif
                </div>

                {{-- Body preview --}}
                @if($selected->body)
                    <p style="margin:18px 0 6px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9.5px;font-weight:600;text-transform:uppercase;letter-spacing:.10em;color:var(--alg-ink-4);">Body</p>
                    <div style="padding:12px;background:#FFFFFF;border:1px solid var(--alg-line);border-radius:4px;max-height:280px;overflow-y:auto;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12.5px;color:#1A1A1A;line-height:1.5;">
                        {!! $selected->body !!}
                    </div>
                @endif
            </div>

            <div style="padding:11px 18px;border-top:1px solid var(--alg-line);display:flex;align-items:center;gap:8px;background:var(--alg-bg);">
                @if($selected->campaign)
                    <a href="/admin/campaigns?selected={{ $selected->campaign_id }}"
                       style="padding:7px 12px;background:var(--alg-ink);color:#FFFFFF;border:none;text-decoration:none;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;border-radius:4px;letter-spacing:-0.005em;">Ver campaña →</a>
                @endif
                <div style="flex:1;"></div>
            </div>
        </aside>
    @endif

    </div>
</x-filament-panels::page>
