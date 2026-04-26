<div class="fi-country-switcher" wire:key="cs" x-data="{ open: false }" @click.outside="open = false">
    @php $current = $selected ? $countries->firstWhere('id', $selected) : null; @endphp
    <nav style="position:relative;display:flex;align-items:center;gap:8px;">
        <button @click="open = !open" style="display:inline-flex;align-items:center;gap:8px;border-radius:0;padding:6px 10px;font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:400;color:#334155;background:#FFFFFF;border:1px solid #E2E8F0;cursor:pointer;transition:border-color 120ms ease;white-space:nowrap;letter-spacing:.04em;" onmouseover="this.style.borderColor='#CBD5E1'" onmouseout="this.style.borderColor='#E2E8F0'">
            @if($current)
                <span style="width:18px;height:13px;display:inline-block;border:1px solid #E2E8F0;" class="flag-{{ $current->code }}"></span>
                <span>{{ strtoupper($current->code) }}</span>
                <span style="font-size:10px;color:#64748B;">{{ $leadCount }}</span>
                @if($newLeadCount > 0)
                    <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;padding:1px 5px;border:1px solid #CBD5E1;color:#334155;letter-spacing:.06em;">+{{ $newLeadCount }}</span>
                @endif
            @else
                <svg style="width:14px;height:14px;color:#64748B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8M12 3c2 2.4 3 5 3 9s-1 6.6-3 9M12 3c-2 2.4-3 5-3 9s1 6.6 3 9"/></svg>
                <span>Global</span>
                <span style="font-size:10px;color:#64748B;">{{ $leadCount }}</span>
            @endif
            <svg style="width:10px;height:10px;color:#64748B;transition:transform 120ms ease;" :style="open && 'transform:rotate(180deg)'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display:none;position:absolute;left:0;top:100%;z-index:50;margin-top:2px;width:220px;border-radius:0;background:#FFFFFF;padding:0;border:1px solid #CBD5E1;box-shadow:0 4px 16px rgba(0,0,0,0.05);">
            <button wire:click="select()" @click="open=false" style="display:flex;width:100%;align-items:center;gap:10px;border-radius:0;padding:9px 14px;text-align:left;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:400;border:none;border-bottom:1px solid #E2E8F0;cursor:pointer;background:{{ !$selected ? '#F8FAFC' : '#FFFFFF' }};color:#0F172A;transition:background 120ms;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='{{ !$selected ? '#F8FAFC' : '#FFFFFF' }}'">
                <svg style="width:14px;height:14px;color:{{ !$selected ? '#0F172A' : '#64748B' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8M12 3c2 2.4 3 5 3 9s-1 6.6-3 9M12 3c-2 2.4-3 5-3 9s1 6.6 3 9"/></svg>
                <span style="flex:1">Global</span>
                @unless($selected)<svg style="width:12px;height:12px;color:#0F172A" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>@endunless
            </button>
            @foreach($countries as $country)
            <button wire:click="select('{{ $country->id }}')" @click="open=false" style="display:flex;width:100%;align-items:center;gap:10px;border-radius:0;padding:9px 14px;text-align:left;font-family:'Geist',ui-sans-serif,system-ui,sans-serif;font-size:12px;font-weight:400;border:none;{{ !$loop->last ? 'border-bottom:1px solid #E2E8F0;' : '' }}cursor:pointer;background:{{ $selected == $country->id ? '#F8FAFC' : '#FFFFFF' }};color:#0F172A;transition:background 120ms;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='{{ $selected == $country->id ? '#F8FAFC' : '#FFFFFF' }}'">
                <span style="width:18px;height:13px;display:inline-block;border:1px solid #E2E8F0;" class="flag-{{ $country->code }}"></span>
                <span style="flex:1">{{ $country->name }}</span>
                <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:9px;color:#64748B;text-transform:uppercase;letter-spacing:.08em;">{{ $country->code }}</span>
                @if($selected == $country->id)<svg style="width:12px;height:12px;color:#0F172A" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>@endif
            </button>
            @endforeach
        </div>
    </nav>
    <div wire:loading.flex wire:target="select" style="position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(247,245,240,0.85);backdrop-filter:blur(3px);">
        <div style="display:flex;align-items:center;gap:12px;background:#FFFFFF;padding:14px 22px;border:1px solid #CBD5E1;">
            <svg style="width:16px;height:16px;color:#0F172A;" class="animate-spin" fill="none" viewBox="0 0 24 24"><circle style="opacity:0.15" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span style="font-family:ui-monospace,'SF Mono',Menlo,monospace;font-size:11px;font-weight:400;color:#334155;letter-spacing:.06em;text-transform:uppercase;">Cargando...</span>
        </div>
    </div>
</div>
<style>
.flag-sv{background:linear-gradient(180deg,#0F47AF 33.3%,#FFF 33.3% 66.6%,#0F47AF 66.6%)}
.flag-gt{background:linear-gradient(90deg,#4997D0 33.3%,#FFF 33.3% 66.6%,#4997D0 66.6%)}
.flag-hn{background:linear-gradient(180deg,#0073CF 33.3%,#FFF 33.3% 66.6%,#0073CF 66.6%)}
.flag-ni{background:linear-gradient(180deg,#0067C6 33.3%,#FFF 33.3% 66.6%,#0067C6 66.6%)}
.flag-cr{background:linear-gradient(180deg,#002B7F 0 20%,#FFF 20% 35%,#CE1126 35% 65%,#FFF 65% 80%,#002B7F 80%)}
.flag-pa{background:linear-gradient(to right,#FFF 50%,#D21034 50%) top/100% 50% no-repeat,linear-gradient(to right,#0055A4 50%,#FFF 50%) bottom/100% 50% no-repeat}
.flag-us{background:linear-gradient(to right,#002868 42%,transparent 42%) no-repeat,linear-gradient(180deg,#BF0A30 7.7%,#FFF 7.7% 15.4%,#BF0A30 15.4% 23.1%,#FFF 23.1% 30.8%,#BF0A30 30.8% 38.5%,#FFF 38.5% 46.2%,#BF0A30 46.2% 53.8%,#FFF 53.8% 61.5%,#BF0A30 61.5% 69.2%,#FFF 69.2% 76.9%,#BF0A30 76.9% 84.6%,#FFF 84.6% 92.3%,#BF0A30 92.3%)}
</style>
