{{-- Sidebar A — wide (224px) — port of dashboard-a.jsx Sidebar.
     Active state computed from current request path. --}}
@php
    $currentPath = '/' . trim(request()->path(), '/');
    $userName = auth()->user()->name ?? 'Luis Alonso';
    $nameParts = preg_split('/\s+/', trim($userName));
    $userInitials = strtoupper(substr($nameParts[0] ?? 'L', 0, 1) . substr(end($nameParts) ?: '', 0, 1));

    $allCountries = \App\Models\Country::orderBy('name')->get(['id','code','name']);
    $currentCountryId = session('country_filter');
    $currentCountry = $currentCountryId ? $allCountries->firstWhere('id', (int) $currentCountryId) : null;
    $workspaceLabel = $currentCountry ? 'ALG ' . strtoupper($currentCountry->code) : 'ALG Global';
@endphp
<aside style="width:224px;flex-shrink:0;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;height:100%;">

    {{-- Brand --}}
    <a href="/admin/dashboard" style="text-decoration:none;color:inherit;padding:16px 16px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
        <div style="width:26px;height:26px;border-radius:6px;background:var(--ink-1);color:white;display:grid;place-items:center;font-family:var(--font-mono);font-size:12px;font-weight:600;letter-spacing:-0.02em;">A</div>
        <div style="display:flex;flex-direction:column;line-height:1.1;">
            <span style="font-size:13px;font-weight:600;letter-spacing:-0.01em;">ALG3PL</span>
            <span style="font-size:10.5px;color:var(--ink-4);margin-top:2px;">Producción · Marketing</span>
        </div>
    </a>

    {{-- Workspace switcher (Alpine dropdown) --}}
    <div x-data="{ open: false }" style="padding:10px 12px;position:relative;">
        <button
            x-on:click="open = !open"
            x-bind:aria-expanded="open"
            type="button"
            style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:6px;background:var(--surface-2);border:1px solid var(--border);font-size:12px;color:var(--ink-2);cursor:pointer;font-family:var(--font-sans);"
        >
            <span style="display:flex;align-items:center;gap:8px;min-width:0;">
                @include('alg-dashboard.icon', ['name' => 'globe', 'size' => 14])
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Cliente: {{ $workspaceLabel }}</span>
            </span>
            <span x-bind:style="open ? 'transform: rotate(180deg)' : ''" style="display:grid;place-items:center;transition:transform 150ms ease;">
                @include('alg-dashboard.icon', ['name' => 'chevron-down', 'size' => 12])
            </span>
        </button>

        {{-- Dropdown panel --}}
        <div
            x-show="open"
            x-on:click.outside="open = false"
            x-on:keydown.escape.window="open = false"
            x-transition.opacity.duration.150ms
            x-cloak
            style="position:absolute;top:calc(100% + 4px);left:12px;right:12px;background:var(--surface);border:1px solid var(--border);border-radius:6px;box-shadow:0 8px 24px rgba(12,10,9,0.08);z-index:50;padding:4px;"
        >
            <form action="{{ route('alg.workspace.country') }}" method="POST" style="display:flex;flex-direction:column;gap:1px;">
                @csrf
                {{-- Global option --}}
                <button type="submit" name="country" value=""
                    style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 10px;border:0;background:{{ ! $currentCountryId ? 'var(--surface-2)' : 'transparent' }};border-radius:5px;font-size:12px;color:var(--ink-1);cursor:pointer;font-family:var(--font-sans);text-align:left;"
                    onmouseover="this.style.background='var(--surface-2)'"
                    onmouseout="this.style.background='{{ ! $currentCountryId ? 'var(--surface-2)' : 'transparent' }}'"
                >
                    <span style="display:flex;align-items:center;gap:8px;">
                        @include('alg-dashboard.icon', ['name' => 'globe', 'size' => 13, 'stroke' => 'var(--ink-3)'])
                        ALG Global
                    </span>
                    @if(! $currentCountryId)
                        @include('alg-dashboard.icon', ['name' => 'check', 'size' => 13, 'stroke' => 'var(--accent)'])
                    @endif
                </button>

                <div style="height:1px;background:var(--border);margin:3px 0;"></div>

                @foreach($allCountries as $c)
                    @php $isSelected = (int) $currentCountryId === $c->id; @endphp
                    <button type="submit" name="country" value="{{ $c->id }}"
                        style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:7px 10px;border:0;background:{{ $isSelected ? 'var(--surface-2)' : 'transparent' }};border-radius:5px;font-size:12px;color:var(--ink-1);cursor:pointer;font-family:var(--font-sans);text-align:left;"
                        onmouseover="this.style.background='var(--surface-2)'"
                        onmouseout="this.style.background='{{ $isSelected ? 'var(--surface-2)' : 'transparent' }}'"
                    >
                        <span style="display:flex;align-items:center;gap:8px;">
                            <span class="num tnum" style="font-size:10.5px;color:var(--ink-4);width:22px;text-transform:uppercase;letter-spacing:0.04em;">{{ strtoupper($c->code) }}</span>
                            <span>{{ $c->name }}</span>
                        </span>
                        @if($isSelected)
                            @include('alg-dashboard.icon', ['name' => 'check', 'size' => 13, 'stroke' => 'var(--accent)'])
                        @endif
                    </button>
                @endforeach
            </form>
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1;overflow:auto;padding:4px 8px 16px;">
        @foreach($navSections as $sec)
            <div style="margin-bottom:14px;">
                @if(!empty($sec['label']))
                    <div style="font-size:10px;color:var(--ink-5);text-transform:uppercase;letter-spacing:0.08em;padding:8px 10px 4px;font-weight:500;">{{ $sec['label'] }}</div>
                @endif
                @foreach($sec['items'] as $item)
                    @php
                        $itemPath = parse_url($item['href'] ?? '#', PHP_URL_PATH);
                        $isActive = $itemPath && str_starts_with($currentPath, $itemPath);
                    @endphp
                    <a href="{{ $item['href'] ?? '#' }}" style="display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:5px;font-size:13px;font-weight:{{ $isActive ? '500' : '400' }};color:{{ $isActive ? 'var(--ink-1)' : 'var(--ink-3)' }};background:{{ $isActive ? 'var(--surface-2)' : 'transparent' }};text-decoration:none;margin-bottom:1px;position:relative;">
                        @if($isActive)
                            <span style="position:absolute;left:-8px;top:6px;bottom:6px;width:2px;background:var(--accent);border-radius:1px;"></span>
                        @endif
                        @include('alg-dashboard.icon', ['name' => $item['icon'], 'size' => 15, 'stroke' => $isActive ? 'var(--ink-1)' : 'var(--ink-4)'])
                        <span style="flex:1;">{{ $item['label'] }}</span>
                        @if(isset($item['count']))
                            <span class="num tnum" style="font-size:11px;color:var(--ink-5);">{{ $item['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- Footer user --}}
    <div style="padding:10px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px;">
        <div style="width:26px;height:26px;border-radius:50%;background:var(--accent-soft);color:var(--accent);display:grid;place-items:center;font-size:11px;font-weight:600;">{{ $userInitials }}</div>
        <div style="flex:1;line-height:1.2;min-width:0;">
            <div style="font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $userName }}</div>
            <div style="font-size:10.5px;color:var(--ink-4);">Marketing manager</div>
        </div>
        <a href="/admin/logout" onclick="event.preventDefault(); document.getElementById('alg-logout-form').submit();" style="color:var(--ink-4);text-decoration:none;display:grid;place-items:center;">
            @include('alg-dashboard.icon', ['name' => 'settings', 'size' => 14, 'stroke' => 'var(--ink-4)'])
        </a>
        <form id="alg-logout-form" action="/admin/logout" method="POST" style="display:none;">@csrf</form>
    </div>
</aside>
