<x-filament-widgets::widget>
<div style="padding:22px 24px;">
    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:var(--alg-ink-3);margin:0 0 18px;">Leads por País</p>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;">
        @foreach($mapData as $code => $d)
        <div style="padding:18px 16px;text-align:center;border-right:1px solid var(--alg-line);border-bottom:1px solid var(--alg-line);{{ $loop->last || ($loop->index % 3 === 2) ? 'border-right:none;' : '' }}{{ $loop->last || $loop->index >= count($mapData)-3 ? 'border-bottom:none;' : '' }}background:{{ $d['selected'] ? 'var(--alg-surface-2)' : 'transparent' }};{{ $d['selected'] ? 'border-left:2px solid var(--alg-ink);' : '' }}transition:background 120ms ease;">
            <span style="display:block;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:var(--alg-ink);margin-bottom:4px;letter-spacing:-0.02em;font-variant-numeric:tabular-nums;">{{ $d['leads'] }}</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:var(--alg-ink-3);">{{ strtoupper($code) }}</span>
            <span style="display:block;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);margin-top:3px;letter-spacing:.04em;">{{ $d['name'] }}</span>
        </div>
        @endforeach
    </div>
</div>
</x-filament-widgets::widget>
