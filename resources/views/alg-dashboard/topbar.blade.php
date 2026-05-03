{{-- Top bar — GNOME-style 32px black bar:
     [Actividades] · clock · [🔔 country flag 🌙 user]
     Activities button opens a fullscreen overlay (Activities Overview).
     Avatar opens Quick Settings dropdown (theme / variant / country / settings / logout).
--}}
@php
    $currentPath = '/' . trim(request()->path(), '/');
    $pathToLabel = [
        '/admin/dashboard'     => 'Dashboard',
        '/admin/leads'         => 'Bandeja de entrada',
        '/admin/kanban'        => 'Kanban',
        '/admin/clients'       => 'Cuentas',
        '/admin/tags'          => 'Tags',
        '/admin/tasks'         => 'Seguimiento',
        '/admin/campaigns'     => 'Campañas',
        '/admin/funnels'       => 'Funnels',
        '/admin/email-templates' => 'Email Templates',
        '/admin/email-campaigns' => 'Envíos',
        '/admin/analytics'     => 'Tráfico',
        '/admin/search-console'=> 'Search Console',
        '/admin/country-reports' => 'Reportes',
        '/admin/settings'      => 'Opciones',
        '/admin/webhooks'      => 'Webhooks',
    ];
    $currentLabel = 'Dashboard';
    $bestLen = 0;
    foreach ($pathToLabel as $p => $lbl) {
        if (str_starts_with($currentPath, $p) && strlen($p) > $bestLen) {
            $currentLabel = $lbl;
            $bestLen = strlen($p);
        }
    }

    $currentCountryId = session('country_filter');
    $allCountries = \App\Models\Country::orderBy('name')->get(['id','code','name']);
    $currentCountry = $currentCountryId ? $allCountries->firstWhere('id', (int) $currentCountryId) : null;
    $countryLabel = $currentCountry ? strtoupper($currentCountry->code) : 'GLOBAL';

    $variant = $variant ?? session('admin_variant', 'b');
    $currentQs = request()->query();
    $aUrl = '?' . http_build_query(array_merge($currentQs, ['variant' => 'a']));
    $bUrl = '?' . http_build_query(array_merge($currentQs, ['variant' => 'b']));

    $userName = auth()->user()->name ?? 'User';
    $userInitial = strtoupper(mb_substr($userName, 0, 1));

    // Country code → flag emoji helper (Latin America)
    $flag = function ($code) {
        return match (strtoupper($code ?? '')) {
            'SV' => '🇸🇻', 'GT' => '🇬🇹', 'CR' => '🇨🇷', 'HN' => '🇭🇳',
            'NI' => '🇳🇮', 'PA' => '🇵🇦', 'US' => '🇺🇸', 'MX' => '🇲🇽',
            default => '🌐',
        };
    };
@endphp

<header class="alg-topbar"
        x-data="{
            qsOpen: false,
            actOpen: false,
            searchOpen: false,
            now: '',
            tickClock() {
                const d = new Date();
                const months = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
                const hh = String(d.getHours()).padStart(2,'0');
                const mm = String(d.getMinutes()).padStart(2,'0');
                this.now = months[d.getMonth()] + ' ' + d.getDate() + '  ' + hh + ':' + mm;
            }
        }"
        x-init="tickClock(); setInterval(() => tickClock(), 30000);"
        x-on:keydown.window.cmd.k.prevent="searchOpen = true"
        x-on:keydown.window.ctrl.k.prevent="searchOpen = true"
        x-on:keydown.escape.window="qsOpen = false; actOpen = false; searchOpen = false;"
        x-on:open-activities.window="actOpen = true"
        x-on:open-search.window="searchOpen = true"
        style="display:flex;align-items:center;justify-content:space-between;height:32px;padding:0 12px;background:#0C0A09;color:rgba(255,255,255,0.92);flex-shrink:0;font-family:var(--font-sans);font-size:12px;position:relative;z-index:50;">

    {{-- Left: Activities button + current page label --}}
    <div style="display:flex;align-items:center;gap:14px;">
        <button type="button"
                x-on:click="actOpen = !actOpen"
                style="border:0;background:transparent;color:rgba(255,255,255,0.92);font-family:inherit;font-size:13px;font-weight:500;cursor:pointer;padding:4px 8px;border-radius:4px;letter-spacing:-0.005em;"
                onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                onmouseout="this.style.background='transparent'">
            Actividades
        </button>
        <span style="font-size:11.5px;color:rgba(255,255,255,0.65);">{{ $currentLabel }}</span>
    </div>

    {{-- Center: clock --}}
    <div style="position:absolute;left:50%;transform:translateX(-50%);font-size:12px;font-weight:500;letter-spacing:-0.005em;">
        <span x-text="now">apr 21 17:44</span>
    </div>

    {{-- Right: system tray --}}
    <div style="display:flex;align-items:center;gap:2px;">
        {{-- Search trigger --}}
        <button type="button"
                x-on:click="searchOpen = true"
                title="Buscar (⌘K)"
                style="border:0;background:transparent;color:rgba(255,255,255,0.85);cursor:pointer;padding:4px 8px;border-radius:4px;display:inline-flex;align-items:center;gap:4px;font-family:inherit;">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/></svg>
        </button>

        {{-- Notifications --}}
        <button type="button" title="Notificaciones"
                style="border:0;background:transparent;color:rgba(255,255,255,0.85);cursor:pointer;padding:4px 8px;border-radius:4px;font-family:inherit;">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13V9a5 5 0 0110 0v4l1 2H4l1-2zM8 17a2 2 0 004 0"/></svg>
        </button>

        {{-- Country flag indicator --}}
        <span title="País: {{ $countryLabel }}"
              style="padding:0 6px;font-size:14px;line-height:1;">{{ $flag($currentCountry?->code) }}</span>

        {{-- Quick Settings (avatar) --}}
        <button type="button"
                x-on:click="qsOpen = !qsOpen"
                title="{{ $userName }}"
                style="border:0;background:transparent;cursor:pointer;padding:0 4px 0 8px;display:inline-flex;align-items:center;gap:6px;font-family:inherit;color:rgba(255,255,255,0.92);">
            <span style="width:20px;height:20px;border-radius:50%;background:var(--alg-accent);color:#FFFFFF;display:grid;place-items:center;font-size:10px;font-weight:600;">{{ $userInitial }}</span>
            <svg width="10" height="10" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 8l5 5 5-5"/></svg>
        </button>

        {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
             Quick Settings dropdown (GNOME-style)
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
        <div x-show="qsOpen"
             x-cloak
             x-on:click.outside="qsOpen = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform translate-y-1"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             style="position:absolute;top:36px;right:8px;width:300px;background:#1C1917;color:rgba(255,255,255,0.92);border:1px solid rgba(255,255,255,0.10);border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,0.40);padding:12px;z-index:60;font-family:var(--font-sans);">

            <div style="display:flex;align-items:center;gap:10px;padding:6px 4px 12px;border-bottom:1px solid rgba(255,255,255,0.10);margin-bottom:10px;">
                <span style="width:32px;height:32px;border-radius:50%;background:var(--alg-accent);color:#FFFFFF;display:grid;place-items:center;font-size:13px;font-weight:600;">{{ $userInitial }}</span>
                <div style="flex:1;min-width:0;">
                    <p style="margin:0;font-size:13px;font-weight:500;line-height:1.2;">{{ $userName }}</p>
                    <p style="margin:1px 0 0;font-size:11px;color:rgba(255,255,255,0.55);">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>

            {{-- Country (workspace) --}}
            <div style="margin-bottom:10px;">
                <p style="margin:0 0 6px;font-size:10px;color:rgba(255,255,255,0.45);text-transform:uppercase;letter-spacing:.12em;font-family:var(--font-mono);font-weight:500;">Workspace</p>
                <form action="{{ route('alg.workspace.country') }}" method="POST" style="display:flex;flex-wrap:wrap;gap:4px;">
                    @csrf
                    <button type="submit" name="country" value=""
                            style="padding:4px 9px;border:1px solid rgba(255,255,255,0.10);background:{{ ! $currentCountryId ? 'rgba(255,255,255,0.10)' : 'transparent' }};color:rgba(255,255,255,0.92);cursor:pointer;font-family:var(--font-sans);font-size:11px;border-radius:6px;">
                        🌐 Global
                    </button>
                    @foreach($allCountries as $c)
                        @php $isSel = (int) $currentCountryId === $c->id; @endphp
                        <button type="submit" name="country" value="{{ $c->id }}"
                                style="padding:4px 9px;border:1px solid rgba(255,255,255,0.10);background:{{ $isSel ? 'rgba(255,255,255,0.10)' : 'transparent' }};color:rgba(255,255,255,0.92);cursor:pointer;font-family:var(--font-sans);font-size:11px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                            <span>{{ $flag($c->code) }}</span>
                            <span>{{ strtoupper($c->code) }}</span>
                        </button>
                    @endforeach
                </form>
            </div>

            {{-- Variant A/B --}}
            <div style="margin-bottom:10px;">
                <p style="margin:0 0 6px;font-size:10px;color:rgba(255,255,255,0.45);text-transform:uppercase;letter-spacing:.12em;font-family:var(--font-mono);font-weight:500;">Densidad</p>
                <div style="display:inline-flex;background:rgba(255,255,255,0.06);border-radius:6px;padding:2px;">
                    <a href="{{ $bUrl }}" style="text-decoration:none;padding:4px 12px;border-radius:4px;font-family:var(--font-mono);font-size:11px;font-weight:600;color:{{ $variant === 'b' ? '#FFFFFF' : 'rgba(255,255,255,0.55)' }};background:{{ $variant === 'b' ? 'rgba(255,255,255,0.12)' : 'transparent' }};">Compacto</a>
                    <a href="{{ $aUrl }}" style="text-decoration:none;padding:4px 12px;border-radius:4px;font-family:var(--font-mono);font-size:11px;font-weight:600;color:{{ $variant === 'a' ? '#FFFFFF' : 'rgba(255,255,255,0.55)' }};background:{{ $variant === 'a' ? 'rgba(255,255,255,0.12)' : 'transparent' }};">Expandido</a>
                </div>
            </div>

            <div style="height:1px;background:rgba(255,255,255,0.10);margin:8px 0;"></div>

            {{-- Actions list --}}
            <a href="/admin/settings" style="display:flex;align-items:center;gap:10px;padding:7px 8px;border-radius:6px;color:rgba(255,255,255,0.92);text-decoration:none;font-size:12.5px;"
               onmouseover="this.style.background='rgba(255,255,255,0.06)'"
               onmouseout="this.style.background='transparent'">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="10" cy="10" r="2.5"/><path d="M10 2v2M10 16v2M2 10h2M16 10h2M4.2 4.2l1.5 1.5M14.3 14.3l1.5 1.5M4.2 15.8l1.5-1.5M14.3 5.7l1.5-1.5"/></svg>
                Configuración
            </a>
            <form method="POST" action="/admin/logout" style="margin:0;">
                @csrf
                <button type="submit" style="display:flex;width:100%;align-items:center;gap:10px;padding:7px 8px;border-radius:6px;background:transparent;border:0;color:rgba(255,255,255,0.92);text-align:left;cursor:pointer;font-family:inherit;font-size:12.5px;"
                        onmouseover="this.style.background='rgba(255,255,255,0.06)'"
                        onmouseout="this.style.background='transparent'">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 5h3a1 1 0 011 1v8a1 1 0 01-1 1h-3M9 14l-4-4 4-4M5 10h11"/></svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         Activities Overview (fullscreen)
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div x-show="actOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position:fixed;inset:0;background:rgba(12,10,9,0.92);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);z-index:55;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px;">

        {{-- Apps grid --}}
        <div style="max-width:880px;width:100%;">
            <p style="text-align:center;margin:0 0 24px;font-family:var(--font-mono);font-size:11px;color:rgba(255,255,255,0.55);text-transform:uppercase;letter-spacing:.16em;">Aplicaciones</p>
            <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:18px;">
                @foreach([
                    ['Dashboard', '🏠', '/admin/dashboard'],
                    ['Bandeja', '📥', '/admin/leads'],
                    ['Cuentas', '🏢', '/admin/clients'],
                    ['Kanban', '📋', '/admin/kanban'],
                    ['Tareas', '✅', '/admin/tasks'],
                    ['Tags', '🏷️', '/admin/tags'],
                    ['Campañas', '📣', '/admin/campaigns'],
                    ['Funnels', '🔻', '/admin/funnels'],
                    ['Emails', '✉️', '/admin/email-templates'],
                    ['Envíos', '✈️', '/admin/email-campaigns'],
                    ['Tráfico', '📈', '/admin/analytics'],
                    ['Search', '🔍', '/admin/search-console'],
                    ['Reportes', '📄', '/admin/country-reports'],
                    ['Webhooks', '🔌', '/admin/webhooks'],
                    ['Opciones', '⚙️', '/admin/settings'],
                ] as [$name, $emoji, $href])
                    <a href="{{ $href }}"
                       x-on:click="actOpen = false"
                       style="text-decoration:none;display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.10);border-radius:14px;color:rgba(255,255,255,0.92);transition:all 150ms;font-family:var(--font-sans);"
                       onmouseover="this.style.background='rgba(255,255,255,0.12)';this.style.transform='translateY(-2px)'"
                       onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.transform='none'">
                        <span style="font-size:32px;line-height:1;">{{ $emoji }}</span>
                        <span style="font-size:11.5px;font-weight:500;">{{ $name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Workspaces (countries) row --}}
        <div style="max-width:880px;width:100%;margin-top:48px;">
            <p style="text-align:center;margin:0 0 16px;font-family:var(--font-mono);font-size:11px;color:rgba(255,255,255,0.55);text-transform:uppercase;letter-spacing:.16em;">Workspaces</p>
            <form action="{{ route('alg.workspace.country') }}" method="POST" style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                @csrf
                <button type="submit" name="country" value="" x-on:click="actOpen = false"
                        style="padding:14px 18px;background:{{ ! $currentCountryId ? 'rgba(255,255,255,0.18)' : 'rgba(255,255,255,0.06)' }};border:1px solid rgba(255,255,255,0.10);border-radius:10px;color:rgba(255,255,255,0.92);cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:4px;font-family:var(--font-sans);min-width:80px;">
                    <span style="font-size:24px;line-height:1;">🌐</span>
                    <span style="font-size:10.5px;font-weight:500;">Global</span>
                </button>
                @foreach($allCountries as $c)
                    @php $isSel = (int) $currentCountryId === $c->id; @endphp
                    <button type="submit" name="country" value="{{ $c->id }}" x-on:click="actOpen = false"
                            style="padding:14px 18px;background:{{ $isSel ? 'rgba(255,255,255,0.18)' : 'rgba(255,255,255,0.06)' }};border:1px solid rgba(255,255,255,0.10);border-radius:10px;color:rgba(255,255,255,0.92);cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:4px;font-family:var(--font-sans);min-width:80px;">
                        <span style="font-size:24px;line-height:1;">{{ $flag($c->code) }}</span>
                        <span style="font-size:10.5px;font-weight:500;">{{ strtoupper($c->code) }}</span>
                    </button>
                @endforeach
            </form>
        </div>

        <p style="margin:48px 0 0;font-family:var(--font-mono);font-size:10.5px;color:rgba(255,255,255,0.45);">esc para cerrar · ⌘K para buscar</p>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         Search overlay (Cmd+K)
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div x-show="searchOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         style="position:fixed;inset:0;background:rgba(12,10,9,0.65);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);z-index:60;display:flex;align-items:flex-start;justify-content:center;padding-top:12vh;"
         x-on:click.self="searchOpen = false">
        <div style="width:min(640px,90vw);background:var(--alg-surface);border:1px solid var(--alg-line);border-radius:14px;box-shadow:0 24px 64px rgba(0,0,0,0.30);overflow:hidden;font-family:var(--font-sans);"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--alg-line);">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="9" r="6"/><path d="m17 17-3.5-3.5"/></svg>
                <input type="text" placeholder="Buscar leads, cuentas, campañas, ajustes…"
                       x-ref="searchInput"
                       x-init="$watch('searchOpen', v => { if (v) $nextTick(() => $refs.searchInput.focus()) })"
                       style="flex:1;border:0;outline:none;font-size:15px;color:var(--alg-ink);background:transparent;font-family:inherit;letter-spacing:-0.01em;">
                <span style="font-family:var(--font-mono);font-size:10px;color:var(--alg-ink-4);background:var(--alg-surface-2);padding:2px 6px;border-radius:4px;border:1px solid var(--alg-line);">esc</span>
            </div>
            <div style="padding:8px;">
                <p style="margin:0 0 4px;padding:6px 10px;font-family:var(--font-mono);font-size:10px;color:var(--alg-ink-4);text-transform:uppercase;letter-spacing:.12em;">Acciones rápidas</p>
                @foreach([
                    ['🏠 Dashboard', '/admin/dashboard'],
                    ['📥 Bandeja de entrada', '/admin/leads'],
                    ['🏢 Cuentas', '/admin/clients'],
                    ['📈 Tráfico (GA4)', '/admin/analytics'],
                    ['🔍 Search Console', '/admin/search-console'],
                    ['⚙️ Opciones', '/admin/settings'],
                ] as [$name, $href])
                    <a href="{{ $href }}" style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:6px;color:var(--alg-ink);text-decoration:none;font-size:13px;"
                       onmouseover="this.style.background='var(--alg-surface-2)'"
                       onmouseout="this.style.background='transparent'">
                        <span>{{ $name }}</span>
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="var(--alg-ink-4)" stroke-width="1.5"><path d="M6 14L14 6M7 6h7v7"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</header>
