<x-filament-widgets::widget>
    <x-filament::section heading="Activity Timeline" icon="heroicon-o-clock" collapsible>
        <div style="max-height: 500px; overflow-y: auto; padding-right: 0.5rem;" class="space-y-3">
            @forelse($events as $event)
                <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                    <div style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center;"
                        @class([
                            'ring-1',
                            'bg-sky-500/10 text-sky-400 ring-sky-500/20' => $event['color'] === 'sky',
                            'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20' => $event['color'] === 'emerald',
                            'bg-amber-500/10 text-amber-400 ring-amber-500/20' => $event['color'] === 'amber',
                            'bg-violet-500/10 text-violet-400 ring-violet-500/20' => $event['color'] === 'violet',
                            'bg-blue-500/10 text-blue-400 ring-blue-500/20' => $event['color'] === 'blue',
                            'bg-rose-500/10 text-rose-400 ring-rose-500/20' => $event['color'] === 'rose',
                            'bg-orange-500/10 text-orange-400 ring-orange-500/20' => $event['color'] === 'orange',
                            'bg-gray-500/10 text-gray-400 ring-gray-500/20' => $event['color'] === 'gray',
                        ])>
                        <x-dynamic-component :component="'heroicon-o-' . $event['icon']" style="width:16px;height:16px" />
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 14px; font-weight: 500; color: #fff;">{{ $event['title'] }}</span>
                            @if($event['user'])
                                <span style="font-size: 10px; color: #6b7280;">by {{ $event['user'] }}</span>
                            @endif
                        </div>
                        <p style="font-size: 12px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $event['subtitle'] }}</p>
                    </div>
                    <span style="font-size: 10px; color: #4b5563; white-space: nowrap;">{{ $event['timestamp']->diffForHumans() }}</span>
                </div>
            @empty
                <div style="text-align: center; padding: 2rem 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 0.5rem;color:#4b5563">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p style="font-size: 14px; color: #6b7280;">No recent activity</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
