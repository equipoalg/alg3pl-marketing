<x-filament-widgets::widget>
<div style="padding:22px 24px 20px;">

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid #E7E5E4;margin-bottom:20px;">
        <div>
            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:#57534E;margin:0 0 6px;">Rendimiento Publicitario</p>
            <h3 style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:400;color:#0C0A09;margin:0;letter-spacing:-0.02em;">Inversión Publicitaria</h3>
            @if($lastSync)
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:#57534E;margin:4px 0 0;letter-spacing:.04em;">Última sync: {{ $lastSync }}</p>
            @endif
        </div>
        <button
            wire:click="triggerSync"
            style="display:inline-flex;align-items:center;gap:6px;background:#0C0A09;color:#FFFFFF;border:none;border-radius:0;padding:7px 12px;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;cursor:pointer;transition:opacity 120ms ease;letter-spacing:-0.005em;"
            onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:12px;height:12px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Sync
        </button>
    </div>

    @if($hasData)

        {{-- Totals row --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid #E7E5E4;border-bottom:1px solid #E7E5E4;margin-bottom:20px;">
            <div style="padding:14px 18px;border-right:1px solid #E7E5E4;">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#57534E;margin:0 0 6px;">Gasto Total</p>
                <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:#0C0A09;margin:0;letter-spacing:-0.02em;font-variant-numeric:tabular-nums;">${{ number_format($totals['spend'], 2) }}</p>
            </div>
            <div style="padding:14px 18px;border-right:1px solid #E7E5E4;">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#57534E;margin:0 0 6px;">Leads Totales</p>
                <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:#0C0A09;margin:0;letter-spacing:-0.02em;font-variant-numeric:tabular-nums;">{{ number_format($totals['leads']) }}</p>
            </div>
            @if($totals['cpl'])
            <div style="padding:14px 18px;">
                <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.14em;color:#57534E;margin:0 0 6px;">CPL Promedio</p>
                <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:28px;font-weight:400;color:#0C0A09;margin:0;letter-spacing:-0.02em;font-variant-numeric:tabular-nums;">${{ number_format($totals['cpl'], 2) }}</p>
            </div>
            @endif
        </div>

        {{-- Platform columns --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-top:1px solid #E7E5E4;border-bottom:1px solid #E7E5E4;">

            @php
            $platforms = [
                'google'   => ['label'=>'Google Ads',  'color'=>'#1a56db'],
                'meta'     => ['label'=>'Meta Ads',    'color'=>'#5850EC'],
                'linkedin' => ['label'=>'LinkedIn',    'color'=>'#0A66C2'],
            ];
            @endphp

            @foreach($platforms as $key => $platform)
            @php $p = $byPlatform[$key]; @endphp
            <div style="padding:16px 18px;{{ !$loop->last ? 'border-right:1px solid #E7E5E4;' : '' }}">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #E7E5E4;">
                    <div style="width:4px;height:14px;background:{{ $platform['color'] }};"></div>
                    <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:500;color:#0C0A09;letter-spacing:-0.005em;">{{ $platform['label'] }}</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:#57534E;margin:0 0 4px;">Gasto</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:400;color:#0C0A09;margin:0;font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">${{ number_format($p['spend'], 2) }}</p>
                    </div>
                    <div>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:#57534E;margin:0 0 4px;">Leads</p>
                        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:18px;font-weight:400;color:#0C0A09;margin:0;font-variant-numeric:tabular-nums;letter-spacing:-0.02em;">{{ number_format($p['leads']) }}</p>
                    </div>
                    <div>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:#57534E;margin:0 0 4px;">CPL</p>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;font-weight:400;color:#292524;margin:0;font-variant-numeric:tabular-nums;">{{ $p['cpl'] ? '$' . number_format($p['cpl'], 2) : '—' }}</p>
                    </div>
                    <div>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:#57534E;margin:0 0 4px;">CTR</p>
                        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:13px;font-weight:400;color:#292524;margin:0;font-variant-numeric:tabular-nums;">{{ $p['ctr'] }}%</p>
                    </div>
                </div>
            </div>
            @endforeach

        </div>

    @else

        {{-- No data state --}}
        <div style="text-align:center;padding:40px 20px;">
            <p style="font-size:14px;font-weight:400;color:#0C0A09;margin:0 0 6px;letter-spacing:-0.01em;">Sin datos publicitarios</p>
            <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#57534E;margin:0 0 18px;letter-spacing:.04em;">Configura las credenciales de Meta Ads o ingresa datos manualmente.</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <div style="background:#F5F5F4;border:1px solid #A8A29E;padding:6px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#292524;letter-spacing:.04em;">
                    META_PAGE_ACCESS_TOKEN
                </div>
                <div style="background:#F5F5F4;border:1px solid #A8A29E;padding:6px 12px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:#292524;letter-spacing:.04em;">
                    META_AD_ACCOUNT_ID
                </div>
            </div>
        </div>

    @endif

</div>
</x-filament-widgets::widget>
