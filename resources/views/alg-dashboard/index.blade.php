<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>ALG Dashboard — {{ $variant === 'b' ? 'Editorial' : 'Panorama global' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    {{-- alg.css already @imports Geist+Geist Mono — no duplicate <link> needed (avoids font load timing diff vs Filament pages) --}}
    <link rel="stylesheet" href="{{ asset('css/alg.css') . '?v=' . (file_exists(public_path('css/alg.css')) ? filemtime(public_path('css/alg.css')) : time()) }}">

    {{-- Alpine.js — needed for the country dropdown + sidebar B expand toggle --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Alpine cloak — hides x-show elements before Alpine initializes (flash prevention). */
        [x-cloak] { display: none !important; }

        /* ═══ Aliases unprefixed → ALG tokens — kept for compatibility with existing inline styles in dashboard partials ═══ */
        :root {
            --bg:            var(--alg-bg);
            --surface:       var(--alg-surface);
            --surface-2:     var(--alg-surface-2);
            --surface-3:     var(--alg-surface-3);
            --border:        var(--alg-line);
            --border-strong: var(--alg-line-2);
            --ink-1:         var(--alg-ink);
            --ink-2:         var(--alg-ink-2);
            --ink-3:         var(--alg-ink-3);
            --ink-4:         var(--alg-ink-4);
            --ink-5:         var(--alg-ink-5);
            --accent:        var(--alg-accent);
            --accent-2:      var(--alg-accent-2);
            --accent-soft:   var(--alg-accent-soft);
            --accent-ink:    var(--alg-accent-ink);
            --pos:           var(--alg-pos);
            --pos-soft:      var(--alg-pos-soft);
            --neg:           var(--alg-neg);
            --neg-soft:      var(--alg-neg-soft);
            --warn:          var(--alg-warn);
            --warn-soft:     var(--alg-warn-soft);
            --font-sans:     var(--alg-font);
            --font-mono:     var(--alg-mono);
        }

        /* Box-sizing, scrollbar, fonts, scrollbar-gutter all live in alg.css now (single source of truth) */
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

    {{-- Variant selector removed — preference now lives in /admin/settings.
         Query string ?variant=a or ?variant=b is still honored for QA/debug. --}}
</body>
</html>
