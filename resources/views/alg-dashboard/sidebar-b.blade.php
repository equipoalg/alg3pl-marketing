{{-- Sidebar B — Ubuntu-style dock (64px, icons only, tooltips on hover) --}}
@php
    $currentPath = '/' . trim(request()->path(), '/');

    // Each dock item is a colorful rounded square. Color is the brand for the section.
    $dockItems = [
        ['Dashboard',           '🏠', '/admin/dashboard',       '#1E3A8A'], // navy
        ['Bandeja de entrada',  '📥', '/admin/leads',           '#2563EB'], // blue
        ['Cuentas',             '🏢', '/admin/clients',         '#0EA5E9'], // sky
        ['Kanban Board',        '📋', '/admin/kanban',          '#F59E0B'], // amber
        ['Seguimiento',         '✅', '/admin/tasks',           '#10B981'], // emerald
        ['Tags',                '🏷️', '/admin/tags',            '#A855F7'], // purple
        '---',
        ['Campañas',            '📣', '/admin/campaigns',       '#EC4899'], // pink
        ['Funnels',             '🔻', '/admin/funnels',         '#F97316'], // orange
        ['Email Templates',     '✉️', '/admin/email-templates', '#8B5CF6'], // violet
        ['Envíos',              '✈️', '/admin/email-campaigns', '#06B6D4'], // cyan
        '---',
        ['Tráfico',             '📈', '/admin/analytics',       '#84CC16'], // lime
        ['Search Console',      '🔍', '/admin/search-console',  '#14B8A6'], // teal
        ['Reportes',            '📄', '/admin/country-reports', '#6366F1'], // indigo
    ];
@endphp

<aside class="alg-dock"
       style="width:64px;flex-shrink:0;background:#0C0A09;display:flex;flex-direction:column;align-items:center;padding:10px 0;gap:6px;height:100%;font-family:var(--font-sans);">

    {{-- Brand --}}
    <a href="/admin/dashboard" title="ALG3PL"
       style="text-decoration:none;width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, var(--alg-accent), #1E3A8A);color:#FFFFFF;display:grid;place-items:center;font-family:var(--font-mono);font-weight:700;font-size:16px;letter-spacing:-0.04em;flex-shrink:0;margin-bottom:6px;">A</a>

    {{-- Dock items --}}
    @foreach($dockItems as $item)
        @if(is_string($item) && $item === '---')
            <div style="width:32px;height:1px;background:rgba(255,255,255,0.10);margin:4px 0;"></div>
        @else
            @php
                [$label, $emoji, $href, $color] = $item;
                $isActive = str_starts_with($currentPath, $href);
            @endphp
            <div x-data="{ tipOpen: false }"
                 style="position:relative;display:flex;align-items:center;justify-content:flex-start;width:100%;">

                {{-- Active indicator dot (left) --}}
                <span style="width:3px;height:{{ $isActive ? '24px' : '0' }};background:#FFFFFF;border-radius:0 2px 2px 0;transition:height 150ms ease;flex-shrink:0;"></span>

                <a href="{{ $href }}"
                   x-on:mouseenter="tipOpen = true"
                   x-on:mouseleave="tipOpen = false"
                   title="{{ $label }}"
                   style="text-decoration:none;width:40px;height:40px;border-radius:10px;background:{{ $color }};display:grid;place-items:center;font-size:18px;line-height:1;flex-shrink:0;margin-left:{{ $isActive ? '8px' : '11px' }};box-shadow:{{ $isActive ? '0 0 0 2px rgba(255,255,255,0.20)' : 'none' }};transition:transform 120ms ease, box-shadow 120ms ease;color:#FFFFFF;"
                   onmouseover="this.style.transform='scale(1.06)'"
                   onmouseout="this.style.transform='none'">
                    {{ $emoji }}
                </a>

                {{-- Tooltip --}}
                <span x-show="tipOpen"
                      x-cloak
                      x-transition:enter="transition ease-out duration-100"
                      x-transition:enter-start="opacity-0 -translate-x-1"
                      x-transition:enter-end="opacity-100 translate-x-0"
                      style="position:absolute;left:62px;top:50%;transform:translateY(-50%);background:#1C1917;color:rgba(255,255,255,0.92);font-size:11.5px;font-weight:500;padding:5px 10px;border-radius:6px;border:1px solid rgba(255,255,255,0.10);white-space:nowrap;pointer-events:none;z-index:100;box-shadow:0 4px 12px rgba(0,0,0,0.30);">
                    {{ $label }}
                </span>
            </div>
        @endif
    @endforeach

    {{-- Spacer --}}
    <div style="flex:1;"></div>

    {{-- Activities trigger at bottom (like Show Apps) --}}
    <div x-data="{ tipOpen: false }" style="position:relative;display:flex;align-items:center;justify-content:center;">
        <button type="button"
                x-on:click="$dispatch('open-activities')"
                x-on:mouseenter="tipOpen = true"
                x-on:mouseleave="tipOpen = false"
                title="Actividades"
                style="border:0;width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.08);color:#FFFFFF;cursor:pointer;display:grid;place-items:center;">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="5" r="2"/><circle cx="15" cy="5" r="2"/><circle cx="5" cy="15" r="2"/><circle cx="15" cy="15" r="2"/></svg>
        </button>
        <span x-show="tipOpen" x-cloak
              style="position:absolute;left:62px;top:50%;transform:translateY(-50%);background:#1C1917;color:rgba(255,255,255,0.92);font-size:11.5px;font-weight:500;padding:5px 10px;border-radius:6px;border:1px solid rgba(255,255,255,0.10);white-space:nowrap;pointer-events:none;z-index:100;box-shadow:0 4px 12px rgba(0,0,0,0.30);">
            Actividades
        </span>
    </div>

</aside>
