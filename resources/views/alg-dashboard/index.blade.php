<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>ALG Dashboard — {{ $variant === 'b' ? 'Editorial' : 'Panorama global' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    {{-- View Transitions API hint (Chrome/Edge/Safari 18+) --}}
    <meta name="view-transition" content="same-origin">

    {{-- alg.css already @imports Geist+Geist Mono — no duplicate <link> needed (avoids font load timing diff vs Filament pages) --}}
    <link rel="stylesheet" href="{{ asset('css/alg.css') . '?v=' . (file_exists(public_path('css/alg.css')) ? filemtime(public_path('css/alg.css')) : time()) }}">

    {{-- Alpine.js — needed for the country dropdown + sidebar B expand toggle --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ALG Animation Helpers — count-up KPIs + sparkline draw + top progress bar --}}
    <script>
    (function(){
        // Number formatter (es-ES: "2.847" or "$1.24M")
        const fmtInt = new Intl.NumberFormat('es-ES');
        const ease = t => 1 - Math.pow(1 - t, 3); // out-cubic

        // Generic countUp: el can have data-count-prefix, data-count-suffix, data-count-decimals
        window.algCountUp = function(el, target, duration = 800){
            if (!el || isNaN(target)) return;
            const decimals = parseInt(el.dataset.countDecimals || '0', 10);
            const prefix   = el.dataset.countPrefix || '';
            const suffix   = el.dataset.countSuffix || '';
            const start    = performance.now();
            const tick = (now) => {
                const t = Math.min(1, (now - start) / duration);
                const v = target * ease(t);
                const display = decimals > 0
                    ? v.toFixed(decimals)
                    : fmtInt.format(Math.round(v));
                el.textContent = prefix + display + suffix;
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        };

        function initAnimations() {
            // Count-up: any element with data-count-to
            document.querySelectorAll('[data-count-to]').forEach(el => {
                if (el.dataset.countDone) return;
                el.dataset.countDone = '1';
                const target = parseFloat(el.dataset.countTo) || 0;
                algCountUp(el, target, 900);
            });

            // Sparkline draw: set actual path length so the animation finishes exactly at the end
            document.querySelectorAll('.alg-sparkline-animate').forEach(line => {
                if (line.dataset.drawDone) return;
                line.dataset.drawDone = '1';
                try {
                    const len = line.getTotalLength();
                    line.style.strokeDasharray = len;
                    line.style.strokeDashoffset = len;
                    requestAnimationFrame(() => {
                        line.style.transition = 'stroke-dashoffset 1200ms cubic-bezier(0.22,1,0.36,1)';
                        line.style.strokeDashoffset = 0;
                    });
                } catch(e) { /* SVG not yet measurable — skip */ }
            });
        }

        // Initial run + Livewire navigate re-run
        document.addEventListener('DOMContentLoaded', initAnimations);
        document.addEventListener('livewire:navigated', initAnimations);

        // Top progress bar (NProgress-style)
        let bar = null;
        function ensureBar(){
            if (!bar) {
                bar = document.createElement('div');
                bar.id = 'alg-nprogress';
                document.body.appendChild(bar);
            }
            return bar;
        }
        document.addEventListener('livewire:navigating', () => { ensureBar().className = 'active'; });
        document.addEventListener('livewire:navigated', () => {
            const b = ensureBar();
            b.className = 'active done';
            setTimeout(() => { b.className = ''; }, 500);
        });
    })();
    </script>

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
<body class="alg-body">
    {{-- Same flex shell as Filament's layout/index.blade.php override.
         This guarantees zero navigation jump between dashboard and Filament pages:
         identical sidebar (224 or 56), identical topbar (52px), identical main padding (24/28). --}}
    <div id="root" style="display:flex;height:100vh;background:var(--bg);overflow:hidden;">

        @if($variant === 'b')
            @include('alg-dashboard.sidebar-b', ['navSections' => $data['navSections'] ?? null])
        @else
            @include('alg-dashboard.sidebar-a', ['navSections' => $data['navSections'] ?? null])
        @endif

        <div style="flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden;">
            @include('alg-dashboard.topbar', ['variant' => $variant])
            <main class="alg-main" style="flex:1;overflow:auto;padding:24px 28px;background:var(--bg);">
                @if($variant === 'b')
                    @include('alg-dashboard.variant-b-content')
                @else
                    @include('alg-dashboard.variant-a-content')
                @endif
            </main>
        </div>

    </div>

    {{-- A/B switcher now lives inside the GNOME topbar (see topbar.blade.php).
         The floating .variant-switch was retired so it doesn't break the chrome. --}}
</body>
</html>
