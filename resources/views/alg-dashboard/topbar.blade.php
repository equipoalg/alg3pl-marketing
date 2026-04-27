{{-- Topbar — port of dashboard-a.jsx Topbar. Breadcrumb resolved from current path. --}}
@php
    $currentPath = '/' . trim(request()->path(), '/');
    $pathToLabel = [
        '/admin/dashboard'            => 'Panorama global',
        '/admin/kanban'               => 'Kanban Board',
        '/admin/clients'              => 'Cuentas',
        '/admin/leads'                => 'Leads',
        '/admin/tags'                 => 'Tags',
        '/admin/tasks'                => 'Seguimiento',
        '/admin/campaigns'            => 'Campañas',
        '/admin/funnels'              => 'Funnels',
        '/admin/email-templates'      => 'Email Templates',
        '/admin/email-campaigns'      => 'Envíos',
        '/admin/analytics-snapshots'  => 'Tráfico',
        '/admin/search-console-data'  => 'Search Console',
        '/admin/country-reports'      => 'Reportes',
        '/admin/scoring-rules'        => 'Reglas de scoring',
        '/admin/segments'             => 'Segmentos',
        '/admin/smartlinks'           => 'Smartlinks',
        '/admin/webhooks'             => 'Webhooks',
        '/admin/ad-metrics'           => 'Métricas de ads',
        '/admin/country-configs'      => 'Config. de país',
    ];
    $label = 'Panorama global';
    $bestLen = 0;
    foreach ($pathToLabel as $p => $lbl) {
        if (str_starts_with($currentPath, $p) && strlen($p) > $bestLen) {
            $label = $lbl;
            $bestLen = strlen($p);
        }
    }

    $currentCountry = null;
    if ($cid = session('country_filter')) {
        $currentCountry = \App\Models\Country::find($cid);
    }
    $countryLabel = $currentCountry ? strtoupper($currentCountry->code) : 'SV';

    // Variant — used to render the A/B toggle. Comes from layout override or controller.
    $variant = $variant ?? session('admin_variant', 'a');
    // Build URLs that preserve the current path + flip variant.
    $currentQs = request()->query();
    $aUrl = '?' . http_build_query(array_merge($currentQs, ['variant' => 'a']));
    $bUrl = '?' . http_build_query(array_merge($currentQs, ['variant' => 'b']));
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:52px;border-bottom:1px solid var(--border);background:var(--bg);flex-shrink:0;">
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:12.5px;color:var(--ink-4);">Cliente</span>
        @include('alg-dashboard.icon', ['name' => 'chevron-right', 'size' => 12, 'stroke' => 'var(--ink-5)'])
        <span style="font-size:12.5px;color:var(--ink-4);">ALG {{ $countryLabel }}</span>
        @include('alg-dashboard.icon', ['name' => 'chevron-right', 'size' => 12, 'stroke' => 'var(--ink-5)'])
        <span style="font-size:12.5px;color:var(--ink-1);font-weight:500;">{{ $label }}</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        {{-- Variant A/B switcher — persists across pages via session --}}
        <div style="display:inline-flex;border:1px solid var(--border);border-radius:6px;padding:2px;background:var(--surface);font-family:var(--font-mono);font-size:11px;font-weight:600;letter-spacing:0.04em;">
            <a href="{{ $aUrl }}" title="Layout clásico" style="text-decoration:none;padding:4px 9px;border-radius:4px;color:{{ $variant === 'a' ? 'var(--ink-1)' : 'var(--ink-4)' }};background:{{ $variant === 'a' ? 'var(--surface-2)' : 'transparent' }};">A</a>
            <a href="{{ $bUrl }}" title="Layout editorial" style="text-decoration:none;padding:4px 9px;border-radius:4px;color:{{ $variant === 'b' ? 'var(--ink-1)' : 'var(--ink-4)' }};background:{{ $variant === 'b' ? 'var(--surface-2)' : 'transparent' }};">B</a>
        </div>
        <button style="display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:6px;border:1px solid var(--border);background:var(--surface);font-size:12px;color:var(--ink-3);cursor:pointer;font-family:var(--font-sans);">
            @include('alg-dashboard.icon', ['name' => 'search', 'size' => 14, 'stroke' => 'var(--ink-3)'])
            <span>Buscar</span>
            <span style="font-family:var(--font-mono);font-size:11px;padding:2px 6px;border:1px solid var(--border);border-radius:4px;background:var(--surface);color:var(--ink-3);margin-left:4px;">⌘K</span>
        </button>
        <button style="width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:var(--surface);display:grid;place-items:center;cursor:pointer;">
            @include('alg-dashboard.icon', ['name' => 'bell', 'size' => 15, 'stroke' => 'var(--ink-3)'])
        </button>
        <a href="/admin/leads/create" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:6px;border:1px solid var(--ink-1);background:var(--ink-1);font-size:12.5px;color:white;font-weight:500;font-family:var(--font-sans);">
            @include('alg-dashboard.icon', ['name' => 'plus', 'size' => 14, 'stroke' => 'white'])
            Nuevo lead
        </a>
    </div>
</div>
