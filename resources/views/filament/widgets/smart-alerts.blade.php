<x-filament-widgets::widget>
@if(count($alerts) > 0)
<div style="display:flex;flex-direction:column;">
@foreach($alerts as $index => $alert)
    @php
        $borderColors = ['danger'=>'#C4314B','warning'=>'#D4880F','info'=>'#00243D'];
        $bc = $borderColors[$alert['type']] ?? '#00243D';
        $isLast = $index === count($alerts) - 1;
    @endphp
    <div style="display:flex;align-items:center;gap:14px;padding:14px 22px;border-left:2px solid {{ $bc }};{{ !$isLast ? 'border-bottom:1px solid #E2E5EA;' : '' }}background:transparent;">
        <svg style="width:14px;height:14px;color:{{ $bc }};flex-shrink:0;opacity:0.8;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            @if($alert['icon'] === 'fire')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/>
            @elseif($alert['icon'] === 'clock')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @elseif($alert['icon'] === 'arrow-trending-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/>
            @else
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
            @endif
        </svg>
        <span style="font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:13px;font-weight:400;color:#4A5568;flex:1;letter-spacing:-0.005em;">{{ $alert['text'] }}</span>
    </div>
@endforeach
</div>
@endif
</x-filament-widgets::widget>
