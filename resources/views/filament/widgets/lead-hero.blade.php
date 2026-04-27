<x-filament-widgets::widget>
<div style="padding:22px 24px 20px;border-bottom:1px solid var(--alg-line);">
@if($count > 0)
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:32px;">
    <div>
        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:var(--alg-ink-3);margin:0 0 8px;">Leads Captados</p>
        <div style="display:flex;align-items:baseline;gap:14px;">
            <span style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:44px;font-weight:400;color:var(--alg-ink);line-height:1;letter-spacing:-0.03em;font-variant-numeric:tabular-nums;">{{ number_format($count) }}</span>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:{{ $delta >= 0 ? 'var(--alg-pos)' : 'var(--alg-neg)' }};">{{ $delta >= 0 ? '▴' : '▾' }} {{ abs($delta) }}%</span>
        </div>
        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:6px 0 0;letter-spacing:.04em;">vs período anterior</p>
    </div>
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:flex-end;height:60px;">
        @php
            $maxVal=max(1,max($spark));$svgW=200;$svgH=40;$n=count($spark);
            $pts=collect($spark)->map(function($v,$i)use($maxVal,$svgW,$svgH,$n){
                $x=$n>1?($i/($n-1))*($svgW-4)+2:$svgW/2;
                $y=$svgH-4-($v/$maxVal)*($svgH-8);
                return round($x,1).",".round($y,1);
            })->implode(" ");
            $lastPair=explode(",",collect(explode(" ",$pts))->last());
        @endphp
        <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width:100%;height:40px;opacity:0.5;" preserveAspectRatio="none">
            <polyline points="{{ $pts }}" fill="none" stroke="var(--alg-ink)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);margin:4px 0 0;text-align:right;letter-spacing:.08em;text-transform:uppercase;">Últimos 7 días</p>
    </div>
    <div style="text-align:right;">
        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.16em;color:var(--alg-ink-3);margin:0 0 8px;">Conversión</p>
        <p style="font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:44px;font-weight:400;color:var(--alg-ink);line-height:1;margin:0;letter-spacing:-0.03em;font-variant-numeric:tabular-nums;">{{ $conversionRate }}<span style="color:var(--alg-ink-3);font-size:22px;">%</span></p>
        <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:6px 0 0;letter-spacing:.04em;">leads / usuarios</p>
    </div>
</div>
<div style="height:1px;background:var(--alg-line);margin:18px 0 14px;"></div>
@php
    $stages=["new"=>["label"=>"Nuevos","color"=>"var(--alg-ink-3)"],"contacted"=>["label"=>"Contactados","color"=>"var(--alg-ink-2)"],"qualified"=>["label"=>"Calificados","color"=>"var(--alg-pos)"],"proposal"=>["label"=>"Propuesta","color"=>"var(--alg-accent)"],"negotiation"=>["label"=>"Negociación","color"=>"var(--alg-ink-2)"],"won"=>["label"=>"Ganados","color"=>"var(--alg-pos)"],"lost"=>["label"=>"Perdidos","color"=>"var(--alg-neg)"]];
    $totalLeads=$statuses->sum();
@endphp
@if($totalLeads>0)
<div style="display:flex;gap:1px;height:3px;overflow:hidden;background:var(--alg-line);">
    @foreach($stages as $key=>$meta)@php $n=$statuses->get($key,0);@endphp@if($n>0)<div style="height:100%;width:{{ round($n/$totalLeads*100,1) }}%;background:{{ $meta["color"] }};"></div>@endif@endforeach
</div>
<div style="display:flex;flex-wrap:wrap;gap:6px 18px;margin-top:10px;">
    @foreach($stages as $key=>$meta)@php $n=$statuses->get($key,0);@endphp@if($n>0)<div style="display:flex;align-items:center;gap:6px;"><span style="width:5px;height:5px;border-radius:50%;background:{{ $meta["color"] }};"></span><span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;color:var(--alg-ink-3);letter-spacing:.08em;text-transform:uppercase;">{{ $meta["label"] }}</span><span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:10px;font-weight:500;color:var(--alg-ink);font-variant-numeric:tabular-nums;">{{ $n }}</span></div>@endif@endforeach
</div>
@endif
@else
<div style="text-align:center;padding:32px 0;">
    <p style="font-size:15px;font-weight:400;color:var(--alg-ink);margin:0 0 6px;letter-spacing:-0.01em;">Sin leads en este período</p>
    <p style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;color:var(--alg-ink-3);margin:0 0 18px;letter-spacing:.04em;">Los leads aparecerán aquí automáticamente.</p>
    <a href="/admin/leads/create" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--alg-ink);color:#FFFFFF;font-size:12px;font-weight:500;text-decoration:none;letter-spacing:-0.005em;">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Crear lead
    </a>
</div>
@endif
</div>
</x-filament-widgets::widget>
