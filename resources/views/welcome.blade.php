<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALG3PL — Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Geist', ui-sans-serif, system-ui, -apple-system, 'Helvetica Neue', sans-serif;
            background: #F7F5F0;
            color: #0E0E0C;
            height: 100vh;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: "ss01","cv11";
            letter-spacing: -0.005em;
        }
        canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
        }
        .overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            position: relative;
            width: 360px;
            background: #FBFAF6;
            border: 1px solid #D7D3C7;
        }
        .card-head {
            padding: 28px 32px 22px;
            border-bottom: 1px solid #E4E0D6;
        }
        .card-body {
            padding: 24px 32px 28px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 0;
        }
        .brand-mark {
            width: 22px;
            height: 22px;
            border: 1px solid #0E0E0C;
            display: grid;
            place-items: center;
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 10px;
            font-weight: 500;
        }
        .brand-name {
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .brand-meta {
            margin-left: auto;
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 10px;
            color: #9A9A92;
        }
        .eyebrow {
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 10px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #9A9A92;
            margin-bottom: 8px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 400;
            letter-spacing: -0.02em;
            color: #0E0E0C;
        }
        .field {
            margin-bottom: 16px;
        }
        .field label {
            display: block;
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9A9A92;
            margin-bottom: 7px;
        }
        .field input {
            width: 100%;
            padding: 10px 12px;
            background: #F7F5F0;
            border: 1px solid #E4E0D6;
            border-radius: 0;
            font-size: 13px;
            font-family: inherit;
            color: #0E0E0C;
            outline: none;
            transition: border-color 120ms ease;
            letter-spacing: -0.005em;
        }
        .field input::placeholder {
            color: #9A9A92;
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 12px;
        }
        .field input:focus {
            border-color: #0E0E0C;
        }
        .submit {
            width: 100%;
            padding: 11px;
            background: #0E0E0C;
            color: #F7F5F0;
            border: none;
            border-radius: 0;
            font-size: 13px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: opacity 120ms ease;
            margin-top: 4px;
            letter-spacing: -0.005em;
        }
        .submit:hover { opacity: 0.84; }
        .submit:active { opacity: 0.70; }
        .error-msg {
            border: 1px solid color-mix(in oklab, oklch(48% 0.12 30) 40%, #D7D3C7);
            padding: 9px 12px;
            font-family: 'Geist Mono', ui-monospace, monospace;
            font-size: 11px;
            color: oklch(48% 0.12 30);
            margin-bottom: 16px;
            letter-spacing: .02em;
        }
        .submit.loading {
            opacity: 0.6;
            pointer-events: none;
        }
        @media (max-width: 420px) {
            .card { width: calc(100% - 32px); }
            .card-head, .card-body { padding-left: 24px; padding-right: 24px; }
        }
    </style>
</head>
<body>

<canvas id="dots"></canvas>

<div class="overlay">
    <div class="card">
        <div class="card-head">
            <div class="brand">
                <div class="brand-mark">A</div>
                <div class="brand-name">ALG3PL</div>
                <div class="brand-meta">Marketing Platform</div>
            </div>
        </div>
        <div class="card-body">
            <div class="eyebrow">Acceso</div>
            <div class="page-title" style="margin-bottom:22px;">Iniciar sesión</div>
            <form action="{{ route('portal.login') }}" method="POST" id="loginForm">
                @csrf
                @if($errors->has('email'))
                <div class="error-msg">{{ $errors->first('email') }}</div>
                @endif
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@alg3pl.com" autocomplete="email" autofocus required>
                </div>
                <div class="field">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                </div>
                <button type="submit" class="submit" id="submitBtn">Entrar</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const canvas = document.getElementById('dots');
    const ctx = canvas.getContext('2d');

    let W, H, cols, rows, dots;
    const GAP = 22;
    const BASE = 1.0;
    const MAX_R = 4;
    const REACH = 100;
    // Subtle olive/warm dots on bone background
    const COLOR = [14, 14, 12];

    let mx = -9999, my = -9999;

    function init() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
        cols = Math.ceil(W / GAP) + 1;
        rows = Math.ceil(H / GAP) + 1;

        dots = [];
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                dots.push({
                    ox: c * GAP,
                    oy: r * GAP,
                    x: c * GAP,
                    y: r * GAP,
                    radius: BASE,
                    alpha: 0.08
                });
            }
        }
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);

        for (let i = 0, len = dots.length; i < len; i++) {
            const d = dots[i];
            const dx = mx - d.ox;
            const dy = my - d.oy;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < REACH) {
                const t = 1 - dist / REACH;
                const ease = t * t;

                d.x += (d.ox - dx * 0.04 - d.x) * 0.12;
                d.y += (d.oy - dy * 0.04 - d.y) * 0.12;
                d.radius += ((BASE + (MAX_R - BASE) * ease) - d.radius) * 0.18;
                d.alpha += ((0.08 + 0.55 * ease) - d.alpha) * 0.18;
            } else {
                d.x += (d.ox - d.x) * 0.07;
                d.y += (d.oy - d.y) * 0.07;
                d.radius += (BASE - d.radius) * 0.07;
                d.alpha += (0.08 - d.alpha) * 0.07;
            }

            ctx.beginPath();
            ctx.arc(d.x, d.y, d.radius, 0, 6.2832);
            ctx.fillStyle = `rgba(${COLOR[0]},${COLOR[1]},${COLOR[2]},${d.alpha})`;
            ctx.fill();
        }

        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', init);
    window.addEventListener('mousemove', function(e) {
        mx = e.clientX;
        my = e.clientY;
    });
    window.addEventListener('mouseleave', function() {
        mx = -9999;
        my = -9999;
    });

    window.addEventListener('touchmove', function(e) {
        mx = e.touches[0].clientX;
        my = e.touches[0].clientY;
    }, { passive: true });
    window.addEventListener('touchend', function() {
        mx = -9999;
        my = -9999;
    });

    init();
    draw();
})();

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.textContent = 'Verificando...';
    btn.classList.add('loading');
});
</script>
</body>
</html>
