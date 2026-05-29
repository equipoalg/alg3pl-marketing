<x-filament-panels::page>
    @php
        $logo = asset('images/alg-logo.png');
        $icon = asset('images/alg-icon.png');

        $navSections = [
            ['start', 'Inicio'],
            ['principles', 'Principios'],
            ['logo', 'Logo'],
            ['color', 'Color'],
            ['type', 'Tipografia'],
            ['systems', 'Apps y sistemas'],
            ['comarketing', 'Co-marketing'],
            ['donts', 'No permitido'],
            ['assets', 'Assets'],
        ];

        $colors = [
            ['Azul corporativo', '#00447A', 'Marca, navegacion principal, fondos institucionales'],
            ['Rojo corporativo', '#DB001B', 'Acentos, alertas criticas y energia comercial'],
            ['Gris institucional', '#939598', 'Texto secundario, lineas, soporte editorial'],
            ['Azul profundo', '#001E3C', 'Fondos premium, dashboards ejecutivos'],
            ['Azul digital', '#0072CE', 'Estados activos, enlaces y datos interactivos'],
            ['Azul claro', '#5DADE2', 'Graficas, rutas, mapas y superficies suaves'],
        ];

        $systemRules = [
            ['Navegacion', 'Usar etiquetas cortas, un verbo o sustantivo por item, y mantener el logo como ancla visual del sistema.'],
            ['Datos', 'Priorizar numeros, estado y accion siguiente. La marca debe ordenar la informacion, no decorarla.'],
            ['Estados', 'Azul para activo o informativo, verde para avance, ambar para revision, rojo solo para riesgo o bloqueo.'],
            ['Reportes', 'Abrir con conclusion, luego evidencia. Graficas limpias, maximo dos acentos de color por modulo.'],
            ['Mobile', 'El isotipo puede reemplazar al logo horizontal, pero los textos deben conservar jerarquia y aire.'],
            ['IA y chat', '5PL debe sentirse como una capa operativa: mensajes claros, sin exceso de personalidad visual.'],
        ];

        $coMarketingRules = [
            ['Jerarquia', 'En piezas propias, America Logistics Group lidera. En piezas compartidas, aclarar el rol de cada marca.'],
            ['Autorizacion', 'No usar marcas, badges o claims de terceros sin aprobacion previa y contexto comercial claro.'],
            ['Balance', 'El logo de partner nunca debe competir con el logo ALG ni cambiar sus colores, proporcion o espacio.'],
            ['Lenguaje', 'Evitar frases que sugieran alianza oficial si solo existe referencia, integracion tecnica o canal comercial.'],
        ];
    @endphp

    <style>
        .alg-brand-doc {
            --bb-blue: #00447A;
            --bb-red: #DB001B;
            --bb-ink: #101216;
            --bb-muted: #68707D;
            --bb-line: #D9DEE7;
            --bb-paper: #FFFFFF;
            --bb-soft: #F5F7FA;
            --bb-deep: #001E3C;
            color: var(--bb-ink);
            font-family: "Helvetica Neue", Arial, sans-serif;
        }

        .alg-brand-doc * {
            box-sizing: border-box;
        }

        .alg-bd-layout {
            display: grid;
            grid-template-columns: 188px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .alg-bd-sidebar {
            position: sticky;
            top: 76px;
            min-height: calc(100vh - 96px);
            padding: 18px 14px;
            border-right: 1px solid var(--bb-line);
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(12px);
        }

        .alg-bd-sidebar-logo {
            display: grid;
            gap: 10px;
            margin-bottom: 22px;
        }

        .alg-bd-sidebar-logo img {
            width: 42px;
            height: 42px;
            display: block;
        }

        .alg-bd-sidebar-title {
            margin: 0;
            font-size: 12px;
            line-height: 1.25;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .alg-bd-sidebar-meta {
            margin: 4px 0 0;
            font-size: 10px;
            color: var(--bb-muted);
        }

        .alg-bd-nav {
            display: grid;
            gap: 2px;
        }

        .alg-bd-nav a {
            display: flex;
            align-items: center;
            min-height: 34px;
            padding: 0 10px;
            border-radius: 6px;
            color: #2E3440;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .alg-bd-nav a:hover,
        .alg-bd-nav a:focus {
            color: var(--bb-blue);
            background: #EEF4FA;
            outline: none;
        }

        .alg-bd-downloads {
            display: grid;
            gap: 8px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--bb-line);
        }

        .alg-bd-mini-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            border: 1px solid var(--bb-line);
            border-radius: 6px;
            color: var(--bb-blue);
            background: #FFFFFF;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
        }

        .alg-bd-content {
            display: grid;
            gap: 28px;
            padding-bottom: 40px;
        }

        .alg-bd-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(240px, .65fr);
            min-height: 430px;
            border: 1px solid var(--bb-line);
            background: var(--bb-paper);
        }

        .alg-bd-hero-copy {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 36px;
            padding: clamp(28px, 5vw, 64px);
        }

        .alg-bd-kicker {
            margin: 0 0 18px;
            color: var(--bb-blue);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .alg-bd-title {
            margin: 0;
            max-width: 760px;
            font-size: clamp(42px, 7vw, 96px);
            line-height: .92;
            font-weight: 800;
            letter-spacing: 0;
        }

        .alg-bd-lede {
            max-width: 620px;
            margin: 22px 0 0;
            font-size: clamp(16px, 2vw, 22px);
            line-height: 1.35;
            color: #3E4652;
        }

        .alg-bd-hero-logo {
            display: grid;
            place-items: center;
            padding: 28px;
            color: #FFFFFF;
            background: #05070A;
            overflow: hidden;
        }

        .alg-bd-hero-logo img {
            width: min(300px, 86%);
            height: auto;
            filter: brightness(0) invert(1);
        }

        .alg-bd-hero-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .alg-bd-tag {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border: 1px solid var(--bb-line);
            border-radius: 999px;
            color: #26303B;
            font-size: 11px;
            font-weight: 800;
            background: #FFFFFF;
        }

        .alg-bd-section {
            scroll-margin-top: 96px;
            border-top: 1px solid var(--bb-line);
            padding-top: 28px;
        }

        .alg-bd-section-head {
            display: grid;
            grid-template-columns: minmax(0, .42fr) minmax(0, .58fr);
            gap: 24px;
            margin-bottom: 22px;
        }

        .alg-bd-number {
            margin: 0 0 8px;
            color: var(--bb-blue);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .alg-bd-h2 {
            margin: 0;
            font-size: clamp(26px, 4vw, 54px);
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0;
        }

        .alg-bd-section-copy {
            margin: 0;
            color: #3E4652;
            font-size: 15px;
            line-height: 1.6;
        }

        .alg-bd-panel {
            border: 1px solid var(--bb-line);
            background: var(--bb-paper);
        }

        .alg-bd-panel-pad {
            padding: clamp(18px, 3vw, 32px);
        }

        .alg-bd-grid-2,
        .alg-bd-grid-3 {
            display: grid;
            gap: 14px;
        }

        .alg-bd-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .alg-bd-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .alg-bd-card {
            border: 1px solid var(--bb-line);
            background: var(--bb-soft);
            padding: 18px;
            min-height: 132px;
        }

        .alg-bd-card.is-dark {
            color: #FFFFFF;
            background: #05070A;
            border-color: #05070A;
        }

        .alg-bd-card h3,
        .alg-bd-rule h3 {
            margin: 0 0 10px;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 800;
        }

        .alg-bd-card p,
        .alg-bd-rule p {
            margin: 0;
            color: var(--bb-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .alg-bd-card.is-dark p {
            color: rgba(255, 255, 255, .72);
        }

        .alg-bd-logo-showcase {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(220px, .8fr);
            gap: 0;
        }

        .alg-bd-logo-primary {
            min-height: 250px;
            display: grid;
            place-items: center;
            border-right: 1px solid var(--bb-line);
            padding: 32px;
        }

        .alg-bd-logo-primary img {
            width: min(100%, 520px);
            height: auto;
        }

        .alg-bd-logo-stack {
            display: grid;
            grid-template-rows: 1fr 1fr;
        }

        .alg-bd-logo-variant {
            display: grid;
            place-items: center;
            padding: 24px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bd-logo-variant:last-child {
            border-bottom: 0;
        }

        .alg-bd-logo-variant img {
            max-width: 210px;
            width: 82%;
            height: auto;
        }

        .alg-bd-logo-variant.is-dark {
            background: #05070A;
        }

        .alg-bd-logo-variant.is-dark img {
            filter: brightness(0) invert(1);
        }

        .alg-bd-color-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border: 1px solid var(--bb-line);
        }

        .alg-bd-color {
            min-height: 230px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            color: #FFFFFF;
            border-right: 1px solid rgba(255, 255, 255, .18);
        }

        .alg-bd-color:nth-child(3n) {
            border-right: 0;
        }

        .alg-bd-color.is-light {
            color: var(--bb-ink);
        }

        .alg-bd-color-name {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .alg-bd-color-hex {
            margin: 0;
            font-size: 28px;
            line-height: 1;
            font-weight: 800;
        }

        .alg-bd-type-sample {
            display: grid;
            grid-template-columns: minmax(220px, .8fr) minmax(0, 1.2fr);
            gap: 0;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bd-aa {
            display: grid;
            place-items: center;
            min-height: 280px;
            font-size: clamp(110px, 15vw, 210px);
            line-height: .8;
            font-weight: 800;
            border-right: 1px solid var(--bb-line);
        }

        .alg-bd-type-rules {
            display: grid;
            gap: 0;
        }

        .alg-bd-type-line {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 14px;
            align-items: baseline;
            padding: 18px 22px;
            border-bottom: 1px solid var(--bb-line);
        }

        .alg-bd-type-line:last-child {
            border-bottom: 0;
        }

        .alg-bd-type-line strong {
            font-size: 12px;
            text-transform: uppercase;
        }

        .alg-bd-type-line span {
            font-size: 24px;
            line-height: 1.15;
        }

        .alg-bd-system-frame {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr);
            min-height: 360px;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
            overflow: hidden;
        }

        .alg-bd-app-rail {
            display: grid;
            align-content: start;
            gap: 12px;
            padding: 18px 12px;
            background: #07111F;
        }

        .alg-bd-app-dot,
        .alg-bd-app-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 7px;
            color: #FFFFFF;
            background: rgba(255, 255, 255, .1);
            font-size: 11px;
            font-weight: 900;
        }

        .alg-bd-app-dot {
            background: #FFFFFF;
        }

        .alg-bd-app-dot img {
            width: 28px;
            height: 28px;
        }

        .alg-bd-app-surface {
            padding: 24px;
            background: #F3F6FA;
        }

        .alg-bd-app-topline {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
        }

        .alg-bd-app-title {
            margin: 0;
            font-size: 22px;
            line-height: 1.1;
            font-weight: 900;
        }

        .alg-bd-app-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .alg-bd-kpi {
            min-height: 86px;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
            padding: 14px;
        }

        .alg-bd-kpi span {
            display: block;
            color: var(--bb-muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .alg-bd-kpi strong {
            display: block;
            margin-top: 10px;
            font-size: 26px;
            line-height: 1;
        }

        .alg-bd-app-table {
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bd-app-row {
            display: grid;
            grid-template-columns: 1fr 90px 110px;
            gap: 12px;
            padding: 13px 14px;
            border-bottom: 1px solid var(--bb-line);
            font-size: 12px;
            align-items: center;
        }

        .alg-bd-app-row:last-child {
            border-bottom: 0;
        }

        .alg-bd-status {
            display: inline-flex;
            justify-content: center;
            min-height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            align-items: center;
            font-size: 10px;
            font-weight: 900;
            background: #EAF2FB;
            color: var(--bb-blue);
        }

        .alg-bd-rule-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .alg-bd-rule {
            padding: 18px;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bd-comarketing {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bd-partner-card {
            display: grid;
            align-content: space-between;
            min-height: 330px;
            padding: 28px;
            color: #FFFFFF;
            background: linear-gradient(135deg, #05070A 0%, #001E3C 100%);
        }

        .alg-bd-partner-card img {
            width: 250px;
            max-width: 100%;
            filter: brightness(0) invert(1);
        }

        .alg-bd-partner-title {
            margin: 0;
            max-width: 360px;
            font-size: clamp(28px, 4vw, 48px);
            line-height: .98;
            font-weight: 900;
        }

        .alg-bd-partner-rules {
            display: grid;
            gap: 0;
        }

        .alg-bd-dont-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .alg-bd-dont {
            display: grid;
            gap: 10px;
            color: #3E4652;
            font-size: 12px;
            text-align: center;
            font-weight: 700;
        }

        .alg-bd-dont-box {
            position: relative;
            min-height: 120px;
            display: grid;
            place-items: center;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
            overflow: hidden;
        }

        .alg-bd-dont-box::after {
            content: "";
            position: absolute;
            width: 2px;
            height: 142%;
            background: var(--bb-red);
            transform: rotate(42deg);
        }

        .alg-bd-dont-box img {
            width: 170px;
            max-width: 82%;
        }

        .alg-bd-dont-box.is-stretch img {
            transform: scaleX(1.45);
        }

        .alg-bd-dont-box.is-rotate img {
            transform: rotate(14deg);
        }

        .alg-bd-dont-box.is-recolor img {
            filter: hue-rotate(92deg) saturate(1.6);
        }

        .alg-bd-assets {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .alg-bd-asset {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 92px;
            padding: 18px;
            border: 1px solid var(--bb-line);
            color: var(--bb-ink);
            background: #FFFFFF;
            text-decoration: none;
        }

        .alg-bd-asset span {
            display: block;
            color: var(--bb-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .alg-bd-asset strong {
            display: block;
            margin-top: 4px;
            font-size: 15px;
        }

        .alg-bd-asset img {
            width: 46px;
            height: 46px;
            object-fit: contain;
        }

        @media (max-width: 1120px) {
            .alg-bd-hero,
            .alg-bd-logo-showcase,
            .alg-bd-type-sample,
            .alg-bd-comarketing {
                grid-template-columns: 1fr;
            }

            .alg-bd-logo-primary,
            .alg-bd-aa {
                border-right: 0;
                border-bottom: 1px solid var(--bb-line);
            }
        }

        @media (max-width: 860px) {
            .alg-bd-layout {
                grid-template-columns: 1fr;
            }

            .alg-bd-sidebar {
                position: sticky;
                top: 54px;
                z-index: 5;
                min-height: 0;
                border-right: 0;
                border-bottom: 1px solid var(--bb-line);
                padding: 12px;
            }

            .alg-bd-sidebar-logo,
            .alg-bd-downloads {
                display: none;
            }

            .alg-bd-nav {
                display: flex;
                overflow-x: auto;
                gap: 6px;
                padding-bottom: 4px;
            }

            .alg-bd-nav a {
                flex: 0 0 auto;
                border: 1px solid var(--bb-line);
                background: #FFFFFF;
            }

            .alg-bd-section-head,
            .alg-bd-grid-2,
            .alg-bd-grid-3,
            .alg-bd-color-grid,
            .alg-bd-rule-list,
            .alg-bd-dont-grid,
            .alg-bd-assets {
                grid-template-columns: 1fr;
            }

            .alg-bd-app-kpis,
            .alg-bd-app-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="alg-brand-doc">
        <div class="alg-bd-layout">
            <aside class="alg-bd-sidebar" aria-label="Navegacion del brandbook">
                <div class="alg-bd-sidebar-logo">
                    <img src="{{ $icon }}" alt="America Logistics Group">
                    <div>
                        <p class="alg-bd-sidebar-title">ALG Brand System</p>
                        <p class="alg-bd-sidebar-meta">Version 1.0 · Mayo 2024</p>
                    </div>
                </div>

                <nav class="alg-bd-nav">
                    @foreach($navSections as [$id, $label])
                        <a href="#{{ $id }}">{{ $label }}</a>
                    @endforeach
                </nav>

                <div class="alg-bd-downloads">
                    <a class="alg-bd-mini-link" href="{{ $logo }}" target="_blank" rel="noopener">Logo PNG</a>
                    <a class="alg-bd-mini-link" href="{{ $icon }}" target="_blank" rel="noopener">Isotipo PNG</a>
                </div>
            </aside>

            <main class="alg-bd-content">
                <section id="start" class="alg-bd-hero">
                    <div class="alg-bd-hero-copy">
                        <div>
                            <p class="alg-bd-kicker">Sistema de identidad visual</p>
                            <h1 class="alg-bd-title">America Logistics Group</h1>
                            <p class="alg-bd-lede">Guia digital para implementar la marca en sistemas, aplicaciones, reportes, presentaciones y piezas comerciales sin perder consistencia operativa.</p>
                        </div>
                        <div class="alg-bd-hero-tags">
                            <span class="alg-bd-tag">Brandbook</span>
                            <span class="alg-bd-tag">Producto digital</span>
                            <span class="alg-bd-tag">Co-marketing</span>
                            <span class="alg-bd-tag">5PL</span>
                        </div>
                    </div>
                    <div class="alg-bd-hero-logo">
                        <img src="{{ $logo }}" alt="America Logistics Group">
                    </div>
                </section>

                <section id="principles" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">01 · Principios</p>
                            <h2 class="alg-bd-h2">Una marca para operar.</h2>
                        </div>
                        <p class="alg-bd-section-copy">La identidad no debe sentirse como decoracion. En plataformas de marketing, CRM y analytics, la marca debe ayudar a leer, decidir y actuar con menos friccion.</p>
                    </div>
                    <div class="alg-bd-grid-3">
                        <article class="alg-bd-card is-dark">
                            <h3>Neutralidad con criterio</h3>
                            <p>La interfaz se apoya en Helvetica, espacio y contraste. El sistema debe sentirse preciso antes que ornamental.</p>
                        </article>
                        <article class="alg-bd-card">
                            <h3>Consistencia transversal</h3>
                            <p>Logo, color, tono y jerarquia deben verse coherentes en admin, dashboard, correo, reportes y materiales de venta.</p>
                        </article>
                        <article class="alg-bd-card">
                            <h3>Legibilidad primero</h3>
                            <p>Todo modulo debe funcionar en tamanos pequenos, tablas densas, mobile y presentaciones ejecutivas.</p>
                        </article>
                    </div>
                </section>

                <section id="logo" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">02 · Logo</p>
                            <h2 class="alg-bd-h2">El ancla visual.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Usar el logotipo horizontal cuando haya espacio suficiente. El isotipo funciona para navegacion compacta, favicons, avatares del sistema y estados donde la marca ya esta contextualizada.</p>
                    </div>
                    <div class="alg-bd-panel alg-bd-logo-showcase">
                        <div class="alg-bd-logo-primary">
                            <img src="{{ $logo }}" alt="Logotipo horizontal America Logistics Group">
                        </div>
                        <div class="alg-bd-logo-stack">
                            <div class="alg-bd-logo-variant"><img src="{{ $icon }}" alt="Isotipo America Logistics Group"></div>
                            <div class="alg-bd-logo-variant is-dark"><img src="{{ $logo }}" alt="Logotipo sobre fondo oscuro"></div>
                        </div>
                    </div>
                </section>

                <section id="color" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">03 · Color</p>
                            <h2 class="alg-bd-h2">Color como sistema.</h2>
                        </div>
                        <p class="alg-bd-section-copy">El azul sostiene confianza e infraestructura. El rojo se reserva para energia, enfasis o riesgo. Los grises mantienen la lectura y evitan ruido visual.</p>
                    </div>
                    <div class="alg-bd-color-grid">
                        @foreach($colors as [$name, $hex, $usage])
                            <div class="alg-bd-color {{ in_array($hex, ['#D9DDE1', '#F2F4F6', '#939598']) ? 'is-light' : '' }}" style="background: {{ $hex }};">
                                <p class="alg-bd-color-name">{{ $name }}</p>
                                <div>
                                    <p class="alg-bd-color-hex">{{ $hex }}</p>
                                    <p style="margin: 10px 0 0; font-size: 12px; line-height: 1.45; max-width: 220px;">{{ $usage }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="type" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">04 · Tipografia</p>
                            <h2 class="alg-bd-h2">Helvetica aplicada.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Helvetica Neue aporta claridad y tono institucional. No debe usarse en piloto automatico: cada peso y tamano debe reforzar jerarquia, lectura y accion.</p>
                    </div>
                    <div class="alg-bd-type-sample">
                        <div class="alg-bd-aa">Aa</div>
                        <div class="alg-bd-type-rules">
                            <div class="alg-bd-type-line"><strong>Titulos</strong><span style="font-weight: 800;">Bold / 36-96 px</span></div>
                            <div class="alg-bd-type-line"><strong>Secciones</strong><span style="font-weight: 700;">Medium / 18-28 px</span></div>
                            <div class="alg-bd-type-line"><strong>UI densa</strong><span style="font-size: 16px;">Regular / 12-15 px</span></div>
                            <div class="alg-bd-type-line"><strong>Enfasis</strong><span style="font-size: 16px; font-style: italic;">Italic solo para citas o notas</span></div>
                        </div>
                    </div>
                </section>

                <section id="systems" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">05 · Aplicaciones y sistemas</p>
                            <h2 class="alg-bd-h2">La marca dentro del producto.</h2>
                        </div>
                        <p class="alg-bd-section-copy">La implementacion en aplicaciones debe parecer una herramienta de trabajo: navegacion clara, datos escaneables, estados consistentes y acciones visibles sin convertir cada pantalla en una pieza publicitaria.</p>
                    </div>

                    <div class="alg-bd-system-frame">
                        <div class="alg-bd-app-rail">
                            <div class="alg-bd-app-dot"><img src="{{ $icon }}" alt="ALG"></div>
                            <div class="alg-bd-app-icon">CRM</div>
                            <div class="alg-bd-app-icon">MKT</div>
                            <div class="alg-bd-app-icon">5PL</div>
                        </div>
                        <div class="alg-bd-app-surface">
                            <div class="alg-bd-app-topline">
                                <h3 class="alg-bd-app-title">Pipeline comercial</h3>
                                <span class="alg-bd-status">GLOBAL</span>
                            </div>
                            <div class="alg-bd-app-kpis">
                                <div class="alg-bd-kpi"><span>Leads nuevos</span><strong>178</strong></div>
                                <div class="alg-bd-kpi"><span>Contactados</span><strong>52</strong></div>
                                <div class="alg-bd-kpi"><span>Ganados</span><strong>0</strong></div>
                            </div>
                            <div class="alg-bd-app-table">
                                <div class="alg-bd-app-row"><strong>Lead logistico regional</strong><span>Alta</span><span class="alg-bd-status">Priorizar</span></div>
                                <div class="alg-bd-app-row"><strong>Cuenta sin asignar</strong><span>Media</span><span class="alg-bd-status">Revisar</span></div>
                                <div class="alg-bd-app-row"><strong>Consulta inbound</strong><span>Baja</span><span class="alg-bd-status">Nutrir</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="alg-bd-rule-list" style="margin-top: 14px;">
                        @foreach($systemRules as [$title, $body])
                            <article class="alg-bd-rule">
                                <h3>{{ $title }}</h3>
                                <p>{{ $body }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="comarketing" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">06 · Co-marketing</p>
                            <h2 class="alg-bd-h2">Claridad entre marcas.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Cuando America Logistics Group convive con clientes, partners o plataformas, la pieza debe dejar claro quien comunica, que relacion existe y cual es la accion esperada.</p>
                    </div>
                    <div class="alg-bd-comarketing">
                        <div class="alg-bd-partner-card">
                            <img src="{{ $logo }}" alt="America Logistics Group">
                            <h3 class="alg-bd-partner-title">Soluciones logisticas que conectan America.</h3>
                        </div>
                        <div class="alg-bd-partner-rules">
                            @foreach($coMarketingRules as [$title, $body])
                                <article class="alg-bd-rule" style="border-width: 0 0 1px 0;">
                                    <h3>{{ $title }}</h3>
                                    <p>{{ $body }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="donts" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">07 · No permitido</p>
                            <h2 class="alg-bd-h2">Proteger consistencia.</h2>
                        </div>
                        <p class="alg-bd-section-copy">No alterar colores, proporciones, tipografia o relaciones entre logo e isotipo. En sistemas, no sacrificar legibilidad para hacer mas visible la marca.</p>
                    </div>
                    <div class="alg-bd-dont-grid">
                        <div class="alg-bd-dont"><div class="alg-bd-dont-box is-recolor"><img src="{{ $logo }}" alt="No recolorear"></div>Cambiar colores</div>
                        <div class="alg-bd-dont"><div class="alg-bd-dont-box is-stretch"><img src="{{ $logo }}" alt="No distorsionar"></div>Distorsionar</div>
                        <div class="alg-bd-dont"><div class="alg-bd-dont-box is-rotate"><img src="{{ $logo }}" alt="No rotar"></div>Rotar</div>
                        <div class="alg-bd-dont"><div class="alg-bd-dont-box"><span style="font-family: Georgia, serif; font-size: 22px;">America Logistics Group</span></div>Cambiar tipografia</div>
                    </div>
                </section>

                <section id="assets" class="alg-bd-section">
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">08 · Assets</p>
                            <h2 class="alg-bd-h2">Recursos listos.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Usar estos archivos como fuente base para el producto y materiales comerciales. Cualquier variante nueva debe respetar esta guia.</p>
                    </div>
                    <div class="alg-bd-assets">
                        <a class="alg-bd-asset" href="{{ $logo }}" target="_blank" rel="noopener"><div><span>Principal</span><strong>Logo horizontal PNG</strong></div><img src="{{ $icon }}" alt=""></a>
                        <a class="alg-bd-asset" href="{{ $icon }}" target="_blank" rel="noopener"><div><span>Compacto</span><strong>Isotipo PNG</strong></div><img src="{{ $icon }}" alt=""></a>
                        <a class="alg-bd-asset" href="{{ asset('images/alg-icon.svg') }}" target="_blank" rel="noopener"><div><span>Vector</span><strong>Isotipo SVG</strong></div><img src="{{ $icon }}" alt=""></a>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-filament-panels::page>
