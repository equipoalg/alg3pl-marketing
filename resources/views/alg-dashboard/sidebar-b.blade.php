{{-- Sidebar B — macOS-inspired dock (64px, SVG icons, magnification on hover) --}}
@php
    $currentPath = '/' . trim(request()->path(), '/');

    /**
     * Dock item: [label, svg-name, href, gradient-from, gradient-to]
     * SVG names map to the inline <symbol>s defined below.
     */
    $dockItems = [
        ['Dashboard',           'i-home',     '/admin/dashboard',       '#3B82F6', '#1E40AF'],
        ['Bandeja de entrada',  'i-inbox',    '/admin/leads',           '#06B6D4', '#0E7490'],
        ['Cuentas',             'i-building', '/admin/clients',         '#8B5CF6', '#5B21B6'],
        ['Kanban Board',        'i-kanban',   '/admin/kanban',          '#F59E0B', '#B45309'],
        ['Seguimiento',         'i-check',    '/admin/tasks',           '#10B981', '#047857'],
        ['Tags',                'i-tag',      '/admin/tags',            '#EC4899', '#9D174D'],
        '---',
        ['Campañas',            'i-megaphone','/admin/campaigns',       '#F97316', '#9A3412'],
        ['Funnels',             'i-funnel',   '/admin/funnels',         '#EF4444', '#991B1B'],
        ['Email Templates',     'i-mail',     '/admin/email-templates', '#6366F1', '#3730A3'],
        ['Envíos',              'i-send',     '/admin/email-campaigns', '#0EA5E9', '#075985'],
        '---',
        ['Tráfico',             'i-chart',    '/admin/analytics',       '#84CC16', '#3F6212'],
        ['Search Console',      'i-search',   '/admin/search-console',  '#14B8A6', '#115E59'],
        ['Reportes',            'i-doc',      '/admin/country-reports', '#A855F7', '#6B21A8'],
    ];
@endphp

{{-- Inline SVG icon library — 16×16, stroke 1.6, all white --}}
<svg width="0" height="0" style="position:absolute;" aria-hidden="true">
    <defs>
        <symbol id="i-home" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l7-6 7 6v8a1 1 0 01-1 1h-3v-6H7v6H4a1 1 0 01-1-1V9z"/>
        </symbol>
        <symbol id="i-inbox" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12l2.5-7h9L17 12M3 12h4l1 2h4l1-2h4M3 12v4a1 1 0 001 1h12a1 1 0 001-1v-4"/>
        </symbol>
        <symbol id="i-building" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="4" y="3" width="12" height="14" rx="1"/><path d="M7 7h2M11 7h2M7 10h2M11 10h2M7 13h2M11 13h2"/>
        </symbol>
        <symbol id="i-kanban" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="4" height="14" rx="1"/><rect x="9" y="3" width="4" height="10" rx="1"/><rect x="15" y="3" width="2" height="6" rx="1"/>
        </symbol>
        <symbol id="i-check" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 10l3 3 8-8M16 4v9a3 3 0 01-3 3H6"/>
        </symbol>
        <symbol id="i-tag" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 3H4v5l8 8 5-5-8-8z"/><circle cx="6.5" cy="6.5" r="0.8" fill="white"/>
        </symbol>
        <symbol id="i-megaphone" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 8v4l9 4V4l-9 4zM12 6v8M3 12h2v3"/>
        </symbol>
        <symbol id="i-funnel" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 4h14l-5 7v5l-4-2v-3L3 4z"/>
        </symbol>
        <symbol id="i-mail" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="5" width="14" height="10" rx="1.5"/><path d="M3 6l7 5 7-5"/>
        </symbol>
        <symbol id="i-send" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3l-7 14-2-6-6-2 15-6z"/>
        </symbol>
        <symbol id="i-chart" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 14l5-5 3 3 6-6M13 6h4v4"/>
        </symbol>
        <symbol id="i-search" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="9" r="5"/><path d="M13 13l4 4"/>
        </symbol>
        <symbol id="i-doc" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 3h7l4 4v10a1 1 0 01-1 1H5z"/><path d="M12 3v4h4M7 11h6M7 14h5"/>
        </symbol>
        <symbol id="i-grid" viewBox="0 0 20 20" fill="none" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="6" height="6" rx="1.2"/><rect x="11" y="3" width="6" height="6" rx="1.2"/><rect x="3" y="11" width="6" height="6" rx="1.2"/><rect x="11" y="11" width="6" height="6" rx="1.2"/>
        </symbol>
    </defs>
</svg>

<aside class="alg-dock"
       style="width:64px;flex-shrink:0;background:#0C0A09;display:flex;flex-direction:column;align-items:center;padding:12px 0 14px;gap:8px;height:100%;font-family:var(--font-sans);">

    {{-- Brand --}}
    <a href="/admin/dashboard" title="ALG3PL"
       class="alg-dock-item"
       style="text-decoration:none;width:42px;height:42px;border-radius:11px;background:linear-gradient(135deg, #1E3A8A, #0C0A09);color:#FFFFFF;display:grid;place-items:center;font-family:var(--font-mono);font-weight:700;font-size:17px;letter-spacing:-0.04em;flex-shrink:0;margin-bottom:4px;border:1px solid rgba(255,255,255,0.10);box-shadow:0 2px 6px rgba(0,0,0,0.30), inset 0 1px 0 rgba(255,255,255,0.10);">A</a>

    {{-- Dock items --}}
    @foreach($dockItems as $item)
        @if(is_string($item) && $item === '---')
            <div style="width:36px;height:1px;background:rgba(255,255,255,0.10);margin:2px 0;"></div>
        @else
            @php
                [$label, $iconId, $href, $gradFrom, $gradTo] = $item;
                $isActive = str_starts_with($currentPath, $href);
            @endphp
            <div class="alg-dock-cell" style="position:relative;display:flex;align-items:center;justify-content:flex-start;width:100%;">

                {{-- Active indicator dot (left of icon) --}}
                <span class="alg-dock-active-dot" style="width:4px;height:{{ $isActive ? '24px' : '0' }};background:#FFFFFF;border-radius:0 3px 3px 0;transition:height 200ms cubic-bezier(0.34,1.56,0.64,1);flex-shrink:0;box-shadow:{{ $isActive ? '0 0 8px rgba(255,255,255,0.40)' : 'none' }};"></span>

                <a href="{{ $href }}"
                   class="alg-dock-item {{ $isActive ? 'is-active' : '' }}"
                   data-tooltip="{{ $label }}"
                   style="text-decoration:none;width:42px;height:42px;border-radius:11px;background:linear-gradient(135deg, {{ $gradFrom }}, {{ $gradTo }});display:grid;place-items:center;flex-shrink:0;margin-left:{{ $isActive ? '8px' : '11px' }};color:#FFFFFF;border:1px solid rgba(255,255,255,0.12);box-shadow:0 2px 6px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.18);">
                    <svg width="18" height="18" style="display:block;"><use href="#{{ $iconId }}"/></svg>
                </a>
            </div>
        @endif
    @endforeach

    <div style="flex:1;"></div>

    {{-- Activities (Launchpad) trigger at bottom — must dispatch on window so the
         topbar (separate Alpine component) catches it via x-on:open-activities.window --}}
    <div class="alg-dock-cell" x-data="{}" style="position:relative;display:flex;align-items:center;justify-content:center;">
        <button type="button"
                class="alg-dock-item"
                data-tooltip="Aplicaciones"
                x-on:click="window.dispatchEvent(new CustomEvent('open-activities', { bubbles: true }))"
                style="border:1px solid rgba(255,255,255,0.12);width:42px;height:42px;border-radius:11px;background:linear-gradient(135deg, #57534E, #1C1917);color:#FFFFFF;cursor:pointer;display:grid;place-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.10);">
            <svg width="18" height="18" style="display:block;"><use href="#i-grid"/></svg>
        </button>
    </div>
</aside>
