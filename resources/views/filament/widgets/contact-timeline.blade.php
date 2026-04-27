<x-filament-widgets::widget>
    @php
        // Map dark-mode color slugs to ALG tokens (light theme)
        $colorMap = [
            'sky'     => ['bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-accent-2)'],
            'emerald' => ['bg' => 'var(--alg-pos-soft)',  'fg' => 'var(--alg-pos)'],
            'amber'   => ['bg' => 'var(--alg-warn-soft)', 'fg' => 'var(--alg-warn)'],
            'violet'  => ['bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-accent)'],
            'blue'    => ['bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-accent)'],
            'rose'    => ['bg' => 'var(--alg-neg-soft)',  'fg' => 'var(--alg-neg)'],
            'orange'  => ['bg' => 'var(--alg-warn-soft)', 'fg' => 'var(--alg-warn)'],
            'gray'    => ['bg' => 'var(--alg-surface-2)', 'fg' => 'var(--alg-ink-3)'],
        ];
    @endphp
    <x-filament::section heading="Activity Timeline" icon="heroicon-o-clock" collapsible>
        <div style="max-height:500px;overflow-y:auto;padding-right:0.5rem;" class="space-y-3">
            @forelse($events as $event)
                @php
                    $colors = $colorMap[$event['color']] ?? $colorMap['gray'];
                @endphp
                <div style="display:flex;gap:0.75rem;align-items:flex-start;padding:10px 0;border-bottom:1px solid var(--alg-line);">
                    <div style="
                        flex-shrink:0;width:32px;height:32px;
                        display:flex;align-items:center;justify-content:center;
                        background:{{ $colors['bg'] }};color:{{ $colors['fg'] }};
                        border:1px solid var(--alg-line);
                    ">
                        <x-dynamic-component :component="'heroicon-o-' . $event['icon']" style="width:16px;height:16px" />
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:13px;font-weight:500;color:var(--alg-ink);letter-spacing:-0.005em;">{{ $event['title'] }}</span>
                            @if($event['user'])
                                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);letter-spacing:.04em;">by {{ $event['user'] }}</span>
                            @endif
                        </div>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;color:var(--alg-ink-3);margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $event['subtitle'] }}</p>
                    </div>
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-4);white-space:nowrap;letter-spacing:.04em;">{{ $event['timestamp']->diffForHumans() }}</span>
                </div>
            @empty
                <div style="text-align:center;padding:2rem 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 0.5rem;color:var(--alg-ink-4);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);letter-spacing:.04em;">No recent activity</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
