<x-filament-widgets::widget>
<div style="padding:22px 24px;">
    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:#8B95A5;margin:0 0 18px;">Leads por País</p>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;">
        @foreach($mapData as $code => $d)
        <div style="padding:18px 16px;text-align:center;border-right:1px solid #E2E5EA;border-bottom:1px solid #E2E5EA;{{ $loop->last || ($loop->index % 3 === 2) ? 'border-right:none;' : '' }}{{ $loop->last || $loop->index >= count($mapData)-3 ? 'border-bottom:none;' : '' }}background:{{ $d['selected'] ? '#F0F2F5' : 'transparent' }};{{ $d['selected'] ? 'border-left:2px solid #1A1D21;' : '' }}transition:background 120ms ease;">
            <span style="display:block;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:28px;font-weight:400;color:#1A1D21;margin-bottom:4px;letter-spacing:-0.02em;font-variant-numeric:tabular-nums;">{{ $d['leads'] }}</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:#8B95A5;">{{ strtoupper($code) }}</span>
            <span style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:#8B95A5;margin-top:3px;letter-spacing:.04em;">{{ $d['name'] }}</span>
        </div>
        @endforeach
    </div>
</div>
</x-filament-widgets::widget>
