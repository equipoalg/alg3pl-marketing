<x-filament-panels::page>
    @php
        $logo = asset('images/alg-logo.png');
        $icon = asset('images/alg-icon.png');

        $corporateColors = [
            ['name' => 'Azul corporativo', 'pantone' => 'Pantone 295C', 'hex' => '#00447A', 'rgb' => '0 68 122', 'cmyk' => '100 57 0 40'],
            ['name' => 'Rojo corporativo', 'pantone' => 'Pantone 1797C', 'hex' => '#DB001B', 'rgb' => '219 0 27', 'cmyk' => '0 100 99 4'],
            ['name' => 'Gris institucional', 'pantone' => '50% Negro', 'hex' => '#939598', 'rgb' => '147 149 152', 'cmyk' => '0 0 0 50'],
        ];

        $auxiliaryColors = ['#001E3C', '#0072CE', '#5DADE2', '#2D3A4A', '#D9DDE1', '#F2F4F6'];
    @endphp

    <style>
        .alg-brandbook-page {
            --bb-blue: #00447A;
            --bb-red: #DB001B;
            --bb-gray: #939598;
            --bb-ink: #1E2026;
            --bb-muted: #6E737C;
            --bb-line: #D9DDE5;
            --bb-paper: #FBFBFA;
            --bb-soft: #F3F6FA;
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: var(--bb-ink);
        }

        .alg-brandbook-page * {
            box-sizing: border-box;
        }

        .alg-bb-cover {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 14px;
            padding: 18px 2px 4px;
        }

        .alg-bb-cover p,
        .alg-bb-cover h1 {
            margin: 0;
        }

        .alg-bb-kicker {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .11em;
            text-transform: uppercase;
            color: var(--bb-blue);
        }

        .alg-bb-heading {
            margin-top: 6px !important;
            font-size: clamp(26px, 4vw, 42px);
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0;
        }

        .alg-bb-subcopy {
            max-width: 560px;
            margin-top: 8px !important;
            font-size: 13px;
            line-height: 1.55;
            color: var(--bb-muted);
        }

        .alg-bb-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
            min-width: 220px;
        }

        .alg-bb-chip {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border: 1px solid rgba(0, 68, 122, .18);
            border-radius: 999px;
            background: #FFFFFF;
            color: var(--bb-blue);
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .alg-bb-scroll {
            overflow-x: auto;
            padding-bottom: 12px;
        }

        .alg-bb-sheet {
            min-width: 1180px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            background: var(--bb-paper);
            border: 1px solid var(--bb-line);
            box-shadow: 0 22px 55px rgba(15, 23, 42, .08);
        }

        .alg-bb-cell {
            min-height: 350px;
            padding: 26px 28px 24px;
            border-right: 1px solid var(--bb-line);
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-cell:nth-child(3n) {
            border-right: 0;
        }

        .alg-bb-section-title {
            margin: 0 0 22px;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .alg-bb-section-title span {
            color: var(--bb-blue);
        }

        .alg-bb-logo-main {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 112px;
            padding: 10px 12px 24px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-logo-main img {
            width: min(100%, 400px);
            height: auto;
            display: block;
        }

        .alg-bb-small-title {
            margin: 0 0 12px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .alg-bb-versions {
            display: grid;
            grid-template-columns: 1.45fr .75fr .75fr;
            gap: 16px;
            margin-top: 14px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-version-card {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid var(--bb-line);
            padding: 8px;
        }

        .alg-bb-version-card:last-child {
            border-right: 0;
        }

        .alg-bb-version-card img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .alg-bb-version-card.is-horizontal img {
            width: 230px;
        }

        .alg-bb-version-card.is-icon img {
            width: 72px;
        }

        .alg-bb-vertical-mark {
            display: grid;
            justify-items: center;
            gap: 4px;
            text-align: center;
            color: var(--bb-blue);
            font-size: 15px;
            line-height: 1;
            font-weight: 500;
        }

        .alg-bb-vertical-mark img {
            width: 54px;
        }

        .alg-bb-vertical-mark b {
            color: var(--bb-red);
            font-weight: 500;
        }

        .alg-bb-note-grid {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 18px;
            margin-top: 14px;
        }

        .alg-bb-clearspace {
            position: relative;
            min-height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bb-clearspace::before,
        .alg-bb-clearspace::after {
            content: "x";
            position: absolute;
            top: 4px;
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            border: 1px solid var(--bb-line);
            color: var(--bb-muted);
            font-size: 10px;
            background: #FFFFFF;
        }

        .alg-bb-clearspace::before {
            left: 4px;
        }

        .alg-bb-clearspace::after {
            right: 4px;
        }

        .alg-bb-clearspace img {
            width: 190px;
        }

        .alg-bb-minimums {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            align-items: end;
        }

        .alg-bb-minimums div {
            text-align: center;
            font-size: 10px;
            color: var(--bb-muted);
        }

        .alg-bb-minimums img:first-child {
            width: 74px;
            display: block;
            margin: 0 auto 8px;
        }

        .alg-bb-minimums img.icon-only {
            width: 36px;
            display: block;
            margin: 0 auto 8px;
        }

        .alg-bb-measure {
            width: 54px;
            height: 12px;
            border-left: 1px solid var(--bb-muted);
            border-right: 1px solid var(--bb-muted);
            border-bottom: 1px solid var(--bb-muted);
            margin: 0 auto 4px;
        }

        .alg-bb-colors {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .alg-bb-swatch-large {
            width: 84px;
            height: 84px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
            margin-bottom: 18px;
        }

        .alg-bb-color-name {
            margin: 0 0 4px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .alg-bb-color-meta {
            margin: 0 0 14px;
            color: var(--bb-muted);
            font-size: 10px;
            line-height: 1.3;
        }

        .alg-bb-color-spec {
            display: grid;
            grid-template-columns: 38px 1fr;
            gap: 6px;
            margin: 0;
            font-size: 10px;
            line-height: 1.7;
            color: var(--bb-muted);
        }

        .alg-bb-color-spec dt {
            font-weight: 700;
            color: var(--bb-ink);
        }

        .alg-bb-aux {
            margin-top: 34px;
            padding-top: 20px;
            border-top: 1px solid var(--bb-line);
        }

        .alg-bb-aux-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .alg-bb-aux-color {
            display: grid;
            gap: 10px;
            justify-items: center;
            font-size: 10px;
            color: var(--bb-muted);
        }

        .alg-bb-aux-color span:first-child {
            width: 52px;
            height: 52px;
            display: block;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .07);
        }

        .alg-bb-type-row {
            display: grid;
            grid-template-columns: 1fr .9fr;
            gap: 24px;
            align-items: center;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-aa {
            font-size: 110px;
            line-height: .85;
            font-weight: 800;
            letter-spacing: 0;
        }

        .alg-bb-font-name {
            font-size: 13px;
            line-height: 1.6;
        }

        .alg-bb-font-name p {
            margin: 0;
        }

        .alg-bb-font-name .regular {
            color: var(--bb-muted);
            font-weight: 400;
        }

        .alg-bb-font-name .medium {
            font-weight: 600;
        }

        .alg-bb-font-name .bold {
            font-weight: 800;
        }

        .alg-bb-font-name .italic {
            font-style: italic;
        }

        .alg-bb-uses {
            display: grid;
            grid-template-columns: .9fr 1fr;
            gap: 28px;
            padding-top: 22px;
        }

        .alg-bb-use-stack {
            display: grid;
            gap: 22px;
        }

        .alg-bb-use-title {
            margin: 0 0 6px;
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .alg-bb-use-label {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .alg-bb-use-meta {
            margin: 3px 0 0;
            font-size: 10px;
            color: var(--bb-muted);
            line-height: 1.35;
        }

        .alg-bb-body-sample {
            margin: 0;
            font-size: 10px;
            line-height: 1.65;
            color: #3F4450;
        }

        .alg-bb-graphics-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-graphic-item {
            min-height: 124px;
            display: grid;
            grid-template-rows: 18px 1fr;
            gap: 8px;
            border-right: 1px solid var(--bb-line);
            padding-right: 12px;
        }

        .alg-bb-graphic-item:last-child {
            border-right: 0;
            padding-right: 0;
        }

        .alg-bb-graphic-label {
            margin: 0;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #414854;
        }

        .alg-bb-graphic-box {
            position: relative;
            overflow: hidden;
            min-height: 92px;
            background: #FFFFFF;
        }

        .alg-bb-graphic-box svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .alg-bb-patterns {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-top: 20px;
        }

        .alg-bb-pattern {
            height: 72px;
            background: #FFFFFF;
            overflow: hidden;
        }

        .alg-bb-dot-pattern {
            background-image: radial-gradient(var(--bb-line) 1px, transparent 1px);
            background-size: 10px 10px;
        }

        .alg-bb-ring-pattern {
            background-image: repeating-radial-gradient(circle at 0 100%, transparent 0 11px, rgba(0, 68, 122, .22) 12px, transparent 13px);
        }

        .alg-bb-line-pattern {
            background-image: repeating-linear-gradient(110deg, transparent 0 11px, rgba(0, 68, 122, .18) 12px, transparent 13px);
        }

        .alg-bb-grid-pattern {
            background-image: linear-gradient(rgba(0, 68, 122, .12) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 68, 122, .12) 1px, transparent 1px);
            background-size: 18px 18px;
            transform: perspective(180px) rotateX(52deg);
            transform-origin: center bottom;
        }

        .alg-bb-app-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .alg-bb-app-title {
            margin: 0 0 8px;
            font-size: 10px;
            color: var(--bb-muted);
        }

        .alg-bb-business-card,
        .alg-bb-presentation,
        .alg-bb-website,
        .alg-bb-signature {
            background: #FFFFFF;
            border: 1px solid #C9CED8;
            box-shadow: 0 7px 15px rgba(15, 23, 42, .09);
            overflow: hidden;
        }

        .alg-bb-business-card {
            height: 90px;
            display: grid;
            grid-template-columns: 1.15fr .85fr;
        }

        .alg-bb-business-card .card-left {
            padding: 12px;
            font-size: 8px;
            color: #3F4450;
        }

        .alg-bb-business-card .card-left img {
            width: 106px;
            margin-bottom: 12px;
        }

        .alg-bb-business-card .card-right {
            background: linear-gradient(135deg, #00447A, #001E3C);
            display: grid;
            place-items: center;
        }

        .alg-bb-business-card .card-right img {
            width: 64px;
            opacity: .24;
        }

        .alg-bb-presentation {
            height: 90px;
            padding: 12px;
            color: #FFFFFF;
            background: radial-gradient(circle at 92% 35%, rgba(93, 173, 226, .85), transparent 28%), linear-gradient(135deg, #00447A, #001E3C);
        }

        .alg-bb-presentation img {
            width: 112px;
            filter: brightness(0) invert(1);
            opacity: .92;
        }

        .alg-bb-presentation p {
            margin: 18px 0 0;
            max-width: 130px;
            font-size: 11px;
            line-height: 1.2;
            font-weight: 800;
        }

        .alg-bb-website {
            height: 118px;
            border-radius: 8px 8px 0 0;
            background: #071624;
        }

        .alg-bb-browser-bar {
            height: 16px;
            background: #FFFFFF;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-web-hero {
            height: 102px;
            padding: 12px;
            color: #FFFFFF;
            background: linear-gradient(90deg, rgba(0, 30, 60, .9), rgba(0, 68, 122, .5)), url('/images/alg-logo.png');
            background-size: 260px auto;
            background-position: 110% 50%;
            background-repeat: no-repeat;
        }

        .alg-bb-web-hero img {
            width: 100px;
            filter: brightness(0) invert(1);
            opacity: .95;
        }

        .alg-bb-web-hero p {
            margin: 18px 0 0;
            max-width: 140px;
            font-size: 12px;
            line-height: 1.12;
            font-weight: 800;
        }

        .alg-bb-signature {
            height: 118px;
            padding: 15px;
            font-size: 9px;
            color: #3F4450;
        }

        .alg-bb-signature img {
            width: 132px;
            margin: 12px 0 8px;
        }

        .alg-bb-bg-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bb-bg-sample {
            height: 88px;
            display: grid;
            place-items: center;
            border: 1px solid #C9CED8;
            background: #FFFFFF;
        }

        .alg-bb-bg-sample.is-dark {
            background: linear-gradient(135deg, #00447A, #001E3C);
        }

        .alg-bb-bg-sample img {
            width: 205px;
            max-width: 82%;
        }

        .alg-bb-bg-sample.is-dark img {
            filter: brightness(0) invert(1);
        }

        .alg-bb-forbidden {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 18px;
        }

        .alg-bb-bad-mark {
            display: grid;
            gap: 7px;
            text-align: center;
            color: #4A4F59;
            font-size: 9px;
        }

        .alg-bb-bad-box {
            position: relative;
            height: 76px;
            display: grid;
            place-items: center;
            border: 1px solid #D8DCE4;
            background: #FFFFFF;
            overflow: hidden;
        }

        .alg-bb-bad-box::after {
            content: "";
            position: absolute;
            width: 2px;
            height: 132%;
            background: var(--bb-red);
            transform: rotate(42deg);
        }

        .alg-bb-bad-box img {
            width: 112px;
            max-width: 82%;
        }

        .alg-bb-bad-box.is-color img {
            filter: hue-rotate(86deg) saturate(1.5);
        }

        .alg-bb-bad-box.is-stretch img {
            transform: scaleX(1.38);
        }

        .alg-bb-bad-box.is-rotate img {
            transform: rotate(15deg);
        }

        .alg-bb-bad-box.is-type {
            font-family: Georgia, serif;
            font-size: 14px;
            color: #111827;
        }

        .alg-bb-footer {
            grid-column: 1 / -1;
            min-height: 74px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 16px 28px;
            color: #FFFFFF;
            background: linear-gradient(90deg, #00447A, #002B55);
        }

        .alg-bb-footer img {
            width: 220px;
            max-width: 35vw;
            filter: brightness(0) invert(1);
        }

        .alg-bb-footer-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 18px;
            font-size: 13px;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .95;
        }

        .alg-bb-footer-meta span + span::before {
            content: "|";
            margin-right: 18px;
            opacity: .6;
        }

        @media (max-width: 760px) {
            .alg-bb-cover {
                display: grid;
            }

            .alg-bb-actions {
                justify-content: flex-start;
                min-width: 0;
            }

            .alg-bb-scroll {
                overflow: visible;
            }

            .alg-bb-sheet {
                min-width: 0;
                grid-template-columns: 1fr;
            }

            .alg-bb-cell,
            .alg-bb-cell:nth-child(3n) {
                border-right: 0;
            }

            .alg-bb-cell {
                padding: 22px 18px;
            }

            .alg-bb-versions,
            .alg-bb-note-grid,
            .alg-bb-colors,
            .alg-bb-type-row,
            .alg-bb-uses,
            .alg-bb-graphics-row,
            .alg-bb-app-grid,
            .alg-bb-bg-row,
            .alg-bb-forbidden {
                grid-template-columns: 1fr;
            }

            .alg-bb-version-card,
            .alg-bb-graphic-item {
                border-right: 0;
                border-bottom: 1px solid var(--bb-line);
                padding-bottom: 14px;
            }

            .alg-bb-aux-grid,
            .alg-bb-patterns {
                grid-template-columns: repeat(2, 1fr);
            }

            .alg-bb-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .alg-bb-footer img {
                max-width: 100%;
            }

            .alg-bb-footer-meta {
                justify-content: flex-start;
                gap: 10px;
                font-size: 10px;
            }

            .alg-bb-footer-meta span + span::before {
                margin-right: 10px;
            }
        }
    </style>

    <div class="alg-brandbook-page">
        <section class="alg-bb-cover">
            <div>
                <p class="alg-bb-kicker">Sistema de identidad visual</p>
                <h1 class="alg-bb-heading">Brandbook America Logistics Group</h1>
                <p class="alg-bb-subcopy">Una guia visual para que el dashboard, presentaciones, firmas, reportes y materiales comerciales mantengan la misma presencia de marca.</p>
            </div>
            <div class="alg-bb-actions" aria-label="Resumen de marca">
                <span class="alg-bb-chip">Version 1.0</span>
                <span class="alg-bb-chip">Mayo 2024</span>
                <span class="alg-bb-chip">ALG Global</span>
            </div>
        </section>

        <div class="alg-bb-scroll">
            <section class="alg-bb-sheet" aria-label="Brandbook America Logistics Group">
                <article class="alg-bb-cell">
                    <h2 class="alg-bb-section-title"><span>01.</span> Logotipo principal</h2>

                    <div class="alg-bb-logo-main">
                        <img src="{{ $logo }}" alt="America Logistics Group">
                    </div>

                    <div style="margin-top: 14px;">
                        <p class="alg-bb-small-title">Versiones</p>
                        <div class="alg-bb-versions">
                            <div>
                                <p class="alg-bb-color-meta">Horizontal</p>
                                <div class="alg-bb-version-card is-horizontal"><img src="{{ $logo }}" alt="Version horizontal"></div>
                            </div>
                            <div>
                                <p class="alg-bb-color-meta">Vertical</p>
                                <div class="alg-bb-version-card">
                                    <div class="alg-bb-vertical-mark">
                                        <img src="{{ $icon }}" alt="Isotipo ALG">
                                        <span>America</span>
                                        <b>Logistics</b>
                                        <span>Group</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="alg-bb-color-meta">Isotipo</p>
                                <div class="alg-bb-version-card is-icon"><img src="{{ $icon }}" alt="Isotipo ALG"></div>
                            </div>
                        </div>
                    </div>

                    <div class="alg-bb-note-grid">
                        <div>
                            <p class="alg-bb-small-title">Area de seguridad</p>
                            <div class="alg-bb-clearspace"><img src="{{ $logo }}" alt="Area de seguridad"></div>
                        </div>
                        <div>
                            <p class="alg-bb-small-title">Tamano minimo</p>
                            <div class="alg-bb-minimums">
                                <div><img src="{{ $logo }}" alt="Logo minimo"><div class="alg-bb-measure"></div>25 mm</div>
                                <div><img class="icon-only" src="{{ $icon }}" alt="Icono minimo"><div class="alg-bb-measure" style="width:34px;"></div>12 mm</div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="alg-bb-cell">
                    <h2 class="alg-bb-section-title"><span>02.</span> Colores corporativos</h2>

                    <div class="alg-bb-colors">
                        @foreach($corporateColors as $color)
                            <div>
                                <div class="alg-bb-swatch-large" style="background: {{ $color['hex'] }};"></div>
                                <p class="alg-bb-color-name">{{ $color['name'] }}</p>
                                <p class="alg-bb-color-meta">{{ $color['pantone'] }}</p>
                                <dl class="alg-bb-color-spec">
                                    <dt>CMYK</dt><dd>{{ $color['cmyk'] }}</dd>
                                    <dt>RGB</dt><dd>{{ $color['rgb'] }}</dd>
                                    <dt>HEX</dt><dd>{{ $color['hex'] }}</dd>
                                </dl>
                            </div>
                        @endforeach
                    </div>

                    <div class="alg-bb-aux">
                        <p class="alg-bb-small-title">Colores auxiliares</p>
                        <div class="alg-bb-aux-grid">
                            @foreach($auxiliaryColors as $auxColor)
                                <div class="alg-bb-aux-color"><span style="background: {{ $auxColor }};"></span><span>{{ $auxColor }}</span></div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <article class="alg-bb-cell">
                    <h2 class="alg-bb-section-title"><span>03.</span> Tipografia corporativa</h2>

                    <div class="alg-bb-type-row">
                        <div class="alg-bb-aa">Aa</div>
                        <div class="alg-bb-font-name">
                            <p>Helvetica Neue</p>
                            <p class="alg-bb-color-meta" style="margin-top: 4px;">es nuestra tipografia corporativa.</p>
                            <p class="regular">Helvetica Neue Regular</p>
                            <p class="medium">Helvetica Neue Medium</p>
                            <p class="bold">Helvetica Neue Bold</p>
                            <p class="italic">Helvetica Neue Italic</p>
                        </div>
                    </div>

                    <div class="alg-bb-uses">
                        <div class="alg-bb-use-stack">
                            <div>
                                <p class="alg-bb-use-title">Titulo principal</p>
                                <p class="alg-bb-use-meta">Helvetica Neue Bold<br>Tamano sugerido: 36/44 pt</p>
                            </div>
                            <div>
                                <p class="alg-bb-use-label">Subtitulo</p>
                                <p class="alg-bb-use-meta">Helvetica Neue Medium<br>Tamano sugerido: 18/24 pt</p>
                            </div>
                            <div>
                                <p class="alg-bb-use-label">Texto corrido</p>
                                <p class="alg-bb-use-meta">Helvetica Neue Regular<br>Tamano sugerido: 10/14 pt</p>
                            </div>
                        </div>
                        <div>
                            <p class="alg-bb-body-sample">Soluciones logisticas que conectan America con rutas aereas, procesos comerciales y seguimiento claro para cada oportunidad.</p>
                            <p class="alg-bb-body-sample" style="margin-top: 26px; font-style: italic;">Enfasis o citas importantes<br>Helvetica Neue Italic</p>
                        </div>
                    </div>
                </article>

                <article class="alg-bb-cell">
                    <h2 class="alg-bb-section-title"><span>04.</span> Sistema grafico</h2>

                    <div class="alg-bb-graphics-row">
                        <div class="alg-bb-graphic-item">
                            <p class="alg-bb-graphic-label">Red y conexion</p>
                            <div class="alg-bb-graphic-box">
                                <svg viewBox="0 0 120 90" role="img" aria-label="Mapa de conexiones">
                                    <path d="M10 48 C28 35, 46 36, 65 46 S96 59, 112 42" fill="none" stroke="#00447A" stroke-width="1.2" opacity=".45"/>
                                    <path d="M16 60 C36 46, 56 52, 78 34 S101 22, 114 28" fill="none" stroke="#DB001B" stroke-width="1.1" opacity=".75"/>
                                    <g fill="#00447A"><circle cx="18" cy="59" r="2"/><circle cx="49" cy="51" r="2"/><circle cx="80" cy="33" r="2"/><circle cx="112" cy="28" r="2"/></g>
                                    <g fill="#DB001B"><circle cx="30" cy="42" r="2"/><circle cx="67" cy="46" r="2"/><circle cx="101" cy="44" r="2"/></g>
                                    <path d="M8 40 h104 M14 30 h92 M20 68 h80" stroke="#D9DDE1" stroke-width="1" opacity=".7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="alg-bb-graphic-item">
                            <p class="alg-bb-graphic-label">Rutas</p>
                            <div class="alg-bb-graphic-box">
                                <svg viewBox="0 0 120 90" role="img" aria-label="Rutas">
                                    <path d="M10 75 C34 40, 64 76, 108 17" fill="none" stroke="#00447A" stroke-width="1.5"/>
                                    <path d="M16 28 C42 54, 72 21, 112 42" fill="none" stroke="#DB001B" stroke-width="1.3"/>
                                    <path d="M18 70 C50 55, 77 67, 111 55" fill="none" stroke="#939598" stroke-width="1" opacity=".5"/>
                                    <circle cx="108" cy="17" r="2" fill="#00447A"/><circle cx="112" cy="42" r="2" fill="#DB001B"/>
                                </svg>
                            </div>
                        </div>
                        <div class="alg-bb-graphic-item">
                            <p class="alg-bb-graphic-label">Nodos</p>
                            <div class="alg-bb-graphic-box">
                                <svg viewBox="0 0 120 90" role="img" aria-label="Nodos">
                                    @for($y = 18; $y <= 72; $y += 13)
                                        @for($x = 22; $x <= 98; $x += 15)
                                            <circle cx="{{ $x }}" cy="{{ $y }}" r="1.7" fill="{{ ($x + $y) % 5 === 0 ? '#DB001B' : '#00447A' }}" opacity=".9"/>
                                        @endfor
                                    @endfor
                                </svg>
                            </div>
                        </div>
                        <div class="alg-bb-graphic-item">
                            <p class="alg-bb-graphic-label">Trazabilidad</p>
                            <div class="alg-bb-graphic-box">
                                <svg viewBox="0 0 120 90" role="img" aria-label="Trazabilidad">
                                    <circle cx="60" cy="45" r="34" fill="none" stroke="#00447A" stroke-width="1" opacity=".45"/>
                                    <circle cx="60" cy="45" r="23" fill="none" stroke="#00447A" stroke-width="1" opacity=".55"/>
                                    <circle cx="60" cy="45" r="12" fill="none" stroke="#DB001B" stroke-width="1.2"/>
                                    <path d="M60 8 V82 M22 45 H98" stroke="#D9DDE1" stroke-width="1"/>
                                    <circle cx="92" cy="45" r="2" fill="#00447A"/><circle cx="72" cy="24" r="2" fill="#DB001B"/>
                                </svg>
                            </div>
                        </div>
                        <div class="alg-bb-graphic-item">
                            <p class="alg-bb-graphic-label">Movimiento</p>
                            <div class="alg-bb-graphic-box">
                                <svg viewBox="0 0 120 90" role="img" aria-label="Movimiento">
                                    <path d="M5 78 L116 18" stroke="#00447A" stroke-width="1" opacity=".45"/>
                                    <path d="M5 66 L116 10" stroke="#00447A" stroke-width="1" opacity=".8"/>
                                    <path d="M5 56 L116 36" stroke="#DB001B" stroke-width="1.3"/>
                                    <path d="M5 44 L116 28" stroke="#939598" stroke-width="1" opacity=".45"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <p class="alg-bb-small-title">Texturas y patrones</p>
                        <div class="alg-bb-patterns">
                            <div class="alg-bb-pattern alg-bb-dot-pattern"></div>
                            <div class="alg-bb-pattern alg-bb-ring-pattern"></div>
                            <div class="alg-bb-pattern alg-bb-line-pattern"></div>
                            <div class="alg-bb-pattern"><div class="alg-bb-grid-pattern" style="height: 110px;"></div></div>
                        </div>
                    </div>
                </article>

                <article class="alg-bb-cell">
                    <h2 class="alg-bb-section-title"><span>05.</span> Aplicaciones</h2>

                    <div class="alg-bb-app-grid">
                        <div>
                            <p class="alg-bb-app-title">Tarjeta de presentacion</p>
                            <div class="alg-bb-business-card">
                                <div class="card-left">
                                    <img src="{{ $logo }}" alt="Logo tarjeta">
                                    <strong>Nombre Apellido</strong><br>Cargo<br><br>+00 000 000 000<br>nombre@alg.com
                                </div>
                                <div class="card-right"><img src="{{ $icon }}" alt="Marca de agua"></div>
                            </div>
                        </div>
                        <div>
                            <p class="alg-bb-app-title">Presentacion corporativa</p>
                            <div class="alg-bb-presentation">
                                <img src="{{ $logo }}" alt="Logo presentacion">
                                <p>Soluciones logisticas que conectan America.</p>
                            </div>
                        </div>
                        <div>
                            <p class="alg-bb-app-title">Sitio web</p>
                            <div class="alg-bb-website">
                                <div class="alg-bb-browser-bar"></div>
                                <div class="alg-bb-web-hero">
                                    <img src="{{ $logo }}" alt="Logo sitio web">
                                    <p>Conectamos negocios. Entregamos confianza.</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="alg-bb-app-title">Firma de correo</p>
                            <div class="alg-bb-signature">
                                <strong>Nombre Apellido</strong><br>Cargo
                                <img src="{{ $logo }}" alt="Logo firma">
                                <br>+00 000 000 000 &nbsp; | &nbsp; nombre@alg.com &nbsp; | &nbsp; www.alg.com
                            </div>
                        </div>
                    </div>
                </article>

                <article class="alg-bb-cell">
                    <h2 class="alg-bb-section-title"><span>06.</span> Usos sobre fondos</h2>

                    <div class="alg-bb-bg-row">
                        <div>
                            <p class="alg-bb-app-title">Fondo claro</p>
                            <div class="alg-bb-bg-sample"><img src="{{ $logo }}" alt="Uso sobre fondo claro"></div>
                        </div>
                        <div>
                            <p class="alg-bb-app-title">Fondo oscuro</p>
                            <div class="alg-bb-bg-sample is-dark"><img src="{{ $logo }}" alt="Uso sobre fondo oscuro"></div>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <h2 class="alg-bb-section-title" style="margin-bottom: 14px;"><span>07.</span> No permitido</h2>
                        <div class="alg-bb-forbidden">
                            <div class="alg-bb-bad-mark"><div class="alg-bb-bad-box is-color"><img src="{{ $logo }}" alt="Color incorrecto"></div>Cambiar colores</div>
                            <div class="alg-bb-bad-mark"><div class="alg-bb-bad-box is-stretch"><img src="{{ $logo }}" alt="Distorsion incorrecta"></div>Distorsionar</div>
                            <div class="alg-bb-bad-mark"><div class="alg-bb-bad-box is-rotate"><img src="{{ $logo }}" alt="Rotacion incorrecta"></div>Rotar</div>
                            <div class="alg-bb-bad-mark"><div class="alg-bb-bad-box is-type">America Logistics Group</div>Cambiar tipografia</div>
                        </div>
                    </div>
                </article>

                <footer class="alg-bb-footer">
                    <img src="{{ $logo }}" alt="America Logistics Group">
                    <div class="alg-bb-footer-meta">
                        <span>Brandbook</span>
                        <span>Sistema de identidad visual</span>
                        <span>Version 1.0</span>
                        <span>Mayo 2024</span>
                    </div>
                </footer>
            </section>
        </div>
    </div>
</x-filament-panels::page>
