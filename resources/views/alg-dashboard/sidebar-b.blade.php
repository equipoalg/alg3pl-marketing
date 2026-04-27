{{-- Sidebar B — dark icon rail (56px collapsed → 224px expanded) — port of dashboard-b.jsx
     SidebarB extended with click-to-expand toggle. State persists in localStorage. --}}
@php
    $currentPath = '/' . trim(request()->path(), '/');
    $userName = auth()->user()->name ?? 'Luis Alonso';
    $nameParts = preg_split('/\s+/', trim($userName));
    $userInitials = strtoupper(substr($nameParts[0] ?? 'L', 0, 1) . substr(end($nameParts) ?: '', 0, 1));
    $navSections = $navSections ?? \App\Support\DashboardMockData::navSections();

    $allCountries = \App\Models\Country::orderBy('name')->get(['id','code','name']);
    $currentCountryId = session('country_filter');
    $currentCountry = $currentCountryId ? $allCountries->firstWhere('id', (int) $currentCountryId) : null;
    $workspaceLabel = $currentCountry ? 'ALG ' . strtoupper($currentCountry->code) : 'ALG Global';
@endphp
<aside
    x-data="{
        expanded: localStorage.getItem('algSidebarBExpanded') === '1',
        toggle() {
            this.expanded = !this.expanded;
            localStorage.setItem('algSidebarBExpanded', this.expanded ? '1' : '0');
        }
    }"
    x-bind:style="expanded ? 'width: 224px' : 'width: 56px'"
    style="flex-shrink:0;background:var(--ink-1);color:white;display:flex;flex-direction:column;height:100%;transition:width 180ms ease;overflow:hidden;"
>
    {{-- Brand + expand toggle --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 12px 10px;flex-shrink:0;">
        <a href="/admin/dashboard" style="text-decoration:none;display:flex;align-items:center;gap:10px;color:white;flex:1;min-width:0;">
            <div style="width:30px;height:30px;border-radius:7px;background:white;color:var(--ink-1);display:grid;place-items:center;font-family:var(--font-mono);font-weight:700;font-size:13px;flex-shrink:0;">A</div>
            <div x-show="expanded" x-transition.opacity style="display:flex;flex-direction:column;line-height:1.1;min-width:0;">
                <span style="font-size:13px;font-weight:600;letter-spacing:-0.01em;white-space:nowrap;">ALG3PL</span>
                <span style="font-size:10.5px;color:rgba(255,255,255,0.55);margin-top:2px;white-space:nowrap;">Producción · Marketing</span>
            </div>
        </a>
        <button
            x-on:click="toggle()"
            x-bind:title="expanded ? 'Colapsar' : 'Expandir'"
            style="border:0;background:transparent;color:rgba(255,255,255,0.55);cursor:pointer;padding:4px;border-radius:4px;display:grid;place-items:center;flex-shrink:0;"
            onmouseover="this.style.background='rgba(255,255,255,0.08)'"
            onmouseout="this.style.background='transparent'"
        >
            <svg x-show="!expanded" width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5-5 5"/></svg>
            <svg x-show="expanded" width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5l-5 5 5 5"/></svg>
        </button>
    </div>

    {{-- Workspace switcher (Alpine dropdown, dark theme, only when expanded) --}}
    <div x-show="expanded" x-data="{ wsOpen: false }" x-transition.opacity style="padding:8px 10px;flex-shrink:0;position:relative;">
        <button
            x-on:click.stop="wsOpen = !wsOpen"
            type="button"
            style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:6px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.10);font-size:12px;color:white;cursor:pointer;font-family:var(--font-sans);"
        >
            <span style="display:flex;align-items:center;gap:8px;min-width:0;">
                @include('alg-dashboard.icon', ['name' => 'globe', 'size' => 14, 'stroke' => 'currentColor'])
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Cliente: {{ $workspaceLabel }}</span>
            </span>
            <span x-bind:style="wsOpen ? 'transform: rotate(180deg)' : ''" style="display:grid;place-items:center;transition:transform 150ms ease;">
                @include('alg-dashboard.icon', ['name' => 'chevron-down', 'size' => 12, 'stroke' => 'currentColor'])
            </span>
        </button>

        <div
            x-show="wsOpen"
            x-on:click.outside="wsOpen = false"
            x-on:keydown.escape.window="wsOpen = false"
            x-cloak
            style="position:absolute;top:calc(100% + 4px);left:10px;right:10px;background:#1C1917;border:1px solid rgba(255,255,255,0.10);border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,0.4);z-index:50;padding:4px;"
        >
            <form action="{{ route('alg.workspace.country') }}" method="POST" style="display:flex;flex-direction:column;gap:1px;">
                @csrf
                <button type="submit" name="country" value=""
                    style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 10px;border:0;background:{{ ! $currentCountryId ? 'rgba(255,255,255,0.08)' : 'transparent' }};border-radius:5px;font-size:12px;color:white;cursor:pointer;font-family:var(--font-sans);text-align:left;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                    onmouseout="this.style.background='{{ ! $currentCountryId ? 'rgba(255,255,255,0.08)' : 'transparent' }}'"
                >
                    <span style="display:flex;align-items:center;gap:8px;">
                        @include('alg-dashboard.icon', ['name' => 'globe', 'size' => 13, 'stroke' => 'rgba(255,255,255,0.55)'])
                        ALG Global
                    </span>
                    @if(! $currentCountryId)
                        @include('alg-dashboard.icon', ['name' => 'check', 'size' => 13, 'stroke' => 'var(--accent-2)'])
                    @endif
                </button>

                <div style="height:1px;background:rgba(255,255,255,0.08);margin:3px 0;"></div>

                @foreach($allCountries as $c)
                    @php $isSelected = (int) $currentCountryId === $c->id; @endphp
                    <button type="submit" name="country" value="{{ $c->id }}"
                        style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 10px;border:0;background:{{ $isSelected ? 'rgba(255,255,255,0.08)' : 'transparent' }};border-radius:5px;font-size:12px;color:white;cursor:pointer;font-family:var(--font-sans);text-align:left;"
                        onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                        onmouseout="this.style.background='{{ $isSelected ? 'rgba(255,255,255,0.08)' : 'transparent' }}'"
                    >
                        <span style="display:flex;align-items:center;gap:8px;">
                            <span class="num tnum" style="font-size:10.5px;color:rgba(255,255,255,0.55);width:22px;text-transform:uppercase;letter-spacing:0.04em;">{{ strtoupper($c->code) }}</span>
                            <span>{{ $c->name }}</span>
                        </span>
                        @if($isSelected)
                            @include('alg-dashboard.icon', ['name' => 'check', 'size' => 13, 'stroke' => 'var(--accent-2)'])
                        @endif
                    </button>
                @endforeach
            </form>
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1;overflow-y:auto;overflow-x:hidden;padding:6px 10px 12px;">
        @foreach($navSections as $sec)
            @if(!empty($sec['label']))
                <div x-show="expanded" x-transition.opacity style="font-size:10px;color:rgba(255,255,255,0.45);text-transform:uppercase;letter-spacing:0.08em;padding:10px 6px 4px;font-weight:500;white-space:nowrap;">{{ $sec['label'] }}</div>
            @endif
            @foreach($sec['items'] as $item)
                @php
                    $itemPath = parse_url($item['href'] ?? '#', PHP_URL_PATH);
                    $isActive = $itemPath && str_starts_with($currentPath, $itemPath);
                @endphp
                <a
                    href="{{ $item['href'] ?? '#' }}"
                    x-bind:title="expanded ? '' : @js($item['label'])"
                    style="display:flex;align-items:center;gap:10px;padding:7px 8px;border-radius:6px;font-size:13px;font-weight:{{ $isActive ? '500' : '400' }};color:{{ $isActive ? 'white' : 'rgba(255,255,255,0.55)' }};background:{{ $isActive ? 'rgba(255,255,255,0.10)' : 'transparent' }};text-decoration:none;margin-bottom:1px;white-space:nowrap;"
                    onmouseover="if(!this.dataset.active)this.style.color='white'"
                    onmouseout="if(!this.dataset.active)this.style.color='rgba(255,255,255,0.55)'"
                    @if($isActive) data-active="1" @endif
                >
                    <span style="width:20px;display:grid;place-items:center;flex-shrink:0;">
                        @include('alg-dashboard.icon', ['name' => $item['icon'], 'size' => 17, 'stroke' => 'currentColor'])
                    </span>
                    <span x-show="expanded" x-transition.opacity style="flex:1;overflow:hidden;text-overflow:ellipsis;">{{ $item['label'] }}</span>
                    @if(isset($item['count']))
                        <span x-show="expanded" x-transition.opacity class="num tnum" style="font-size:11px;color:rgba(255,255,255,0.45);">{{ $item['count'] }}</span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    {{-- Footer user --}}
    <div style="padding:10px 12px;border-top:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:10px;flex-shrink:0;">
        <div style="width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,0.10);display:grid;place-items:center;font-size:11px;font-weight:600;flex-shrink:0;">{{ $userInitials }}</div>
        <div x-show="expanded" x-transition.opacity style="flex:1;line-height:1.2;min-width:0;">
            <div style="font-size:12px;font-weight:500;color:white;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $userName }}</div>
            <div style="font-size:10.5px;color:rgba(255,255,255,0.55);">Marketing manager</div>
        </div>
        <a x-show="expanded" x-transition.opacity href="#" onclick="event.preventDefault();document.getElementById('alg-logout-form-b').submit();" style="color:rgba(255,255,255,0.55);text-decoration:none;display:grid;place-items:center;">
            @include('alg-dashboard.icon', ['name' => 'settings', 'size' => 14, 'stroke' => 'currentColor'])
        </a>
        <form id="alg-logout-form-b" action="/admin/logout" method="POST" style="display:none;">@csrf</form>
    </div>
</aside>
