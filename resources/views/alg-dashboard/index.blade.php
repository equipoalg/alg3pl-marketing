<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>ALG Dashboard — {{ $variant === 'b' ? 'Editorial' : 'Panorama global' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Alpine.js — needed for the country dropdown + sidebar B expand toggle --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Alpine cloak — hides x-show elements before Alpine initializes (flash prevention). */
        [x-cloak] { display: none !important; }
    </style>

    <style>
        /* ═══ ALG Design Tokens — 1:1 from styles.css in the Claude Design bundle ═══ */
        :root {
            --bg:          #FAFAF9;
            --surface:     #FFFFFF;
            --surface-2:   #F5F5F4;
            --surface-3:   #EEEDEB;
            --border:      #E7E5E4;
            --border-strong: #D6D3D1;
            --ink-1:       #0C0A09;
            --ink-2:       #292524;
            --ink-3:       #57534E;
            --ink-4:       #78716C;
            --ink-5:       #A8A29E;

            --accent:      #1E3A8A;
            --accent-2:    #2563EB;
            --accent-soft: #EFF3FB;
            --accent-ink:  #1E3A8A;

            --pos:         #166534;
            --pos-soft:    #ECFDF5;
            --neg:         #9F1239;
            --neg-soft:    #FEF2F2;
            --warn:        #92400E;
            --warn-soft:   #FEF3C7;

            --font-sans:   "Geist", ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            --font-mono:   "Geist Mono", ui-monospace, "JetBrains Mono", "SF Mono", monospace;
        }

        * { box-sizing: border-box; }
        html, body, #root { height: 100%; margin: 0; padding: 0; }
        body {
            font-family: var(--font-sans);
            color: var(--ink-1);
            background: var(--bg);
            font-feature-settings: "ss01", "cv11";
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        button { font-family: inherit; cursor: pointer; }
        a { color: inherit; }

        .num { font-family: var(--font-mono); font-feature-settings: "tnum", "zero"; letter-spacing: -0.01em; }
        .tnum { font-variant-numeric: tabular-nums; }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 10px; border: 2px solid var(--bg); }
        ::-webkit-scrollbar-thumb:hover { background: var(--ink-5); }

        /* Floating variant toggle (top right corner) */
        .variant-switch {
            position: fixed; top: 16px; right: 16px; z-index: 100;
            display: inline-flex; gap: 0; padding: 2px;
            background: var(--surface); border: 1px solid var(--border); border-radius: 6px;
            font-family: var(--font-mono); font-size: 11px; font-weight: 600;
        }
        .variant-switch a {
            padding: 4px 10px; border-radius: 4px; text-decoration: none;
            color: var(--ink-4); letter-spacing: 0.04em;
        }
        .variant-switch a.active { background: var(--surface-2); color: var(--ink-1); }
    </style>
</head>
<body>
    <div id="root">
        @if($variant === 'b')
            @include('alg-dashboard.variant-b')
        @else
            @include('alg-dashboard.variant-a')
        @endif
    </div>

    <div class="variant-switch" title="Cambiar layout">
        <a href="?variant=a" class="{{ $variant === 'a' ? 'active' : '' }}">A</a>
        <a href="?variant=b" class="{{ $variant === 'b' ? 'active' : '' }}">B</a>
    </div>
</body>
</html>
