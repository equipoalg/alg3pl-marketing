<x-filament-panels::page>
    @php
        $logo = asset('images/alg-logo.png');
        $icon = asset('images/alg-icon.png');
        $iconSvg = asset('images/alg-icon.svg');

        $navSections = [
            ['start', 'Inicio', '00'],
            ['strategy', 'Estrategia', '01'],
            ['logo', 'Logo', '02'],
            ['color', 'Color', '03'],
            ['type', 'Tipografia', '04'],
            ['tokens', 'Tokens', '05'],
            ['systems', 'Apps y sistemas', '06'],
            ['comarketing', 'Co-marketing', '07'],
            ['donts', 'No permitido', '08'],
            ['assets', 'Assets', '09'],
        ];

        $colors = [
            ['Azul corporativo', '#00447A', 'Marca, navegacion principal, fondos institucionales', 'Pantone 295C', 'dark'],
            ['Rojo corporativo', '#DB001B', 'Acentos, alertas criticas y energia comercial', 'Pantone 1797C', 'dark'],
            ['Gris institucional', '#939598', 'Texto secundario, lineas, soporte editorial', '50% Negro', 'light'],
            ['Azul profundo', '#001E3C', 'Fondos premium, dashboards ejecutivos', 'Digital dark', 'dark'],
            ['Azul digital', '#0072CE', 'Estados activos, enlaces y datos interactivos', 'Digital action', 'dark'],
            ['Azul claro', '#5DADE2', 'Graficas, rutas, mapas y superficies suaves', 'Data support', 'light'],
        ];

        $tokens = [
            ['Color', 'Primary', '#00447A', 'Acciones primarias, navegacion activa y elementos de marca.'],
            ['Color', 'Critical', '#DB001B', 'Alertas de riesgo, prohibiciones y senales de accion urgente.'],
            ['Type', 'Display', '42-96px', 'Hero, paginas guia y momentos editoriales de alta jerarquia.'],
            ['Type', 'UI Body', '12-15px', 'Tablas, filtros, formularios, sidebars y textos operativos.'],
            ['Space', 'Module', '24px', 'Separacion base entre bloques editoriales y modulos densos.'],
            ['Radius', 'System', '6px', 'Controles, tarjetas internas y elementos de herramienta.'],
        ];

        $systemRules = [
            ['Navegacion', 'Usar etiquetas cortas y estables. El usuario debe saber donde esta, que puede hacer y que cambio desde la ultima vista.'],
            ['Dashboards', 'Abrir con conclusion y KPI principal. Luego mostrar causa, tendencia y accion siguiente.'],
            ['Tablas y CRM', 'Reducir ruido: nombre, estado, prioridad, responsable y accion. Todo lo demas debe vivir en detalle o filtro.'],
            ['Formularios', 'Agrupar por tarea, no por base de datos. Mensajes de error breves, humanos y accionables.'],
            ['Reportes', 'La marca no debe competir con la data. Usar fondos claros, dos acentos maximo y titulos conclusivos.'],
            ['Mobile', 'Usar isotipo y navegacion compacta. Mantener botones grandes, lectura vertical y estados visibles.'],
            ['IA y 5PL', 'La IA debe parecer capa operativa: respuestas directas, confianza medida, evidencia y siguiente accion.'],
            ['Estados', 'Azul informa, verde confirma avance, ambar pide revision, rojo bloquea o advierte riesgo real.'],
        ];

        $coMarketingRules = [
            ['Marca lider', 'En piezas propias, ALG lidera. El partner aparece como soporte, integracion o contexto comercial.'],
            ['Relacion explicita', 'Nombrar la relacion real: cliente, partner, integracion, canal o caso de uso. Evitar sugerir alianza oficial si no existe.'],
            ['Proporcion', 'El logo secundario no debe superar el 70% del ancho visual del logo ALG en piezas lideradas por ALG.'],
            ['Aprobacion', 'Cualquier marca de tercero, badge, certificacion o claim compartido requiere validacion antes de publicarse.'],
        ];

        $doDont = [
            ['Logo', 'Usar el logo horizontal sobre fondos claros y con area de seguridad.', 'No invertir, estirar, rotar ni aplicar filtros al logotipo oficial.'],
            ['Color', 'Usar azul para estructura, rojo para enfasis controlado y gris para soporte.', 'No convertir todo en azul ni usar rojo como color decorativo permanente.'],
            ['Tipografia', 'Usar Helvetica Neue para claridad, datos y tono institucional.', 'No mezclar fuentes decorativas ni usar italicas como estilo de interfaz.'],
            ['UI', 'Aplicar marca para ordenar informacion y acelerar decisiones.', 'No transformar pantallas operativas en piezas publicitarias.'],
        ];

        $assets = [
            ['Logo horizontal', 'PNG principal', $logo, $icon],
            ['Isotipo', 'PNG compacto', $icon, $icon],
            ['Isotipo vector', 'SVG', $iconSvg, $icon],
        ];
    @endphp

    <style>
        .alg-brand-doc {
            --bb-blue: #00447A;
            --bb-red: #DB001B;
            --bb-deep: #001E3C;
            --bb-ink: #101216;
            --bb-muted: #68707D;
            --bb-line: #D9DEE7;
            --bb-paper: #FFFFFF;
            --bb-soft: #F5F7FA;
            --bb-tint: #EEF4FA;
            color: var(--bb-ink);
            font-family: "Helvetica Neue", Arial, sans-serif;
        }

        .alg-brand-doc * {
            box-sizing: border-box;
        }

        .alg-bd-layout {
            display: grid;
            grid-template-columns: 156px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }

        .alg-bd-sidebar {
            position: sticky;
            top: 76px;
            min-height: calc(100vh - 96px);
            padding: 18px 14px;
            border-right: 1px solid var(--bb-line);
            background: rgba(255, 255, 255, .94);
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

        .alg-bd-sidebar-title,
        .alg-bd-sidebar-meta,
        .alg-bd-release p,
        .alg-bd-kicker,
        .alg-bd-number,
        .alg-bd-label {
            margin: 0;
        }

        .alg-bd-sidebar-title {
            font-size: 12px;
            line-height: 1.25;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .alg-bd-sidebar-meta {
            margin-top: 4px;
            font-size: 10px;
            color: var(--bb-muted);
        }

        .alg-bd-nav {
            display: grid;
            gap: 2px;
        }

        .alg-bd-nav a {
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            align-items: center;
            min-height: 34px;
            padding: 0 8px;
            border-radius: 6px;
            color: #2E3440;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .alg-bd-nav a span:first-child {
            color: var(--bb-muted);
            font-size: 10px;
            font-weight: 900;
        }

        .alg-bd-nav a:hover,
        .alg-bd-nav a:focus,
        .alg-bd-nav a.is-active {
            color: var(--bb-blue);
            background: var(--bb-tint);
            outline: none;
        }

        .alg-bd-nav a.is-active span:first-child {
            color: var(--bb-blue);
        }

        .alg-bd-downloads {
            display: grid;
            gap: 8px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--bb-line);
        }

        .alg-bd-mini-link,
        .alg-bd-copy-button,
        .alg-bd-action {
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
            cursor: pointer;
        }

        .alg-bd-copy-button {
            width: fit-content;
            padding: 0 10px;
        }

        .alg-bd-content {
            display: grid;
            gap: 34px;
            padding-bottom: 40px;
        }

        .alg-bd-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.18fr) minmax(260px, .82fr);
            min-height: 520px;
            border: 1px solid var(--bb-line);
            background: var(--bb-paper);
        }

        .alg-bd-hero-copy {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 38px;
            padding: clamp(28px, 5vw, 70px);
        }

        .alg-bd-kicker {
            margin-bottom: 18px;
            color: var(--bb-blue);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .alg-bd-title {
            margin: 0;
            max-width: 820px;
            font-size: clamp(44px, 7vw, 104px);
            line-height: .9;
            font-weight: 900;
            letter-spacing: 0;
        }

        .alg-bd-lede {
            max-width: 650px;
            margin: 24px 0 0;
            font-size: clamp(16px, 2vw, 23px);
            line-height: 1.36;
            color: #3E4652;
        }

        .alg-bd-release {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            max-width: 560px;
        }

        .alg-bd-release div {
            border-top: 1px solid var(--bb-line);
            padding-top: 10px;
        }

        .alg-bd-release p:first-child {
            color: var(--bb-muted);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .alg-bd-release p:last-child {
            margin-top: 4px;
            font-size: 13px;
            font-weight: 800;
        }

        .alg-bd-hero-logo {
            display: grid;
            align-items: stretch;
            color: #FFFFFF;
            background: #05070A;
            overflow: hidden;
        }

        .alg-bd-logo-stage {
            display: grid;
            place-items: center;
            min-height: 360px;
            padding: 34px;
        }

        .alg-bd-logo-stage-inner {
            width: min(360px, 92%);
            padding: 26px;
            background: #FFFFFF;
        }

        .alg-bd-logo-stage-inner img {
            width: 100%;
            height: auto;
            display: block;
        }

        .alg-bd-hero-note {
            align-self: end;
            padding: 22px;
            border-top: 1px solid rgba(255, 255, 255, .16);
            color: rgba(255, 255, 255, .78);
            font-size: 12px;
            line-height: 1.55;
        }

        .alg-bd-hero-tags,
        .alg-bd-inline-actions {
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
            padding-top: 30px;
        }

        .alg-bd-section-head {
            display: grid;
            grid-template-columns: minmax(0, .42fr) minmax(0, .58fr);
            gap: 24px;
            margin-bottom: 22px;
        }

        .alg-bd-number {
            margin-bottom: 8px;
            color: var(--bb-blue);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .alg-bd-h2 {
            margin: 0;
            font-size: clamp(28px, 4vw, 58px);
            line-height: .98;
            font-weight: 900;
            letter-spacing: 0;
        }

        .alg-bd-section-copy {
            margin: 0;
            color: #3E4652;
            font-size: 15px;
            line-height: 1.62;
        }

        .alg-bd-grid-2,
        .alg-bd-grid-3,
        .alg-bd-grid-4 {
            display: grid;
            gap: 14px;
        }

        .alg-bd-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .alg-bd-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .alg-bd-grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .alg-bd-panel,
        .alg-bd-card,
        .alg-bd-rule,
        .alg-bd-do,
        .alg-bd-token,
        .alg-bd-asset {
            border: 1px solid var(--bb-line);
            background: var(--bb-paper);
        }

        .alg-bd-card,
        .alg-bd-rule,
        .alg-bd-do,
        .alg-bd-token {
            padding: 18px;
        }

        .alg-bd-card {
            min-height: 138px;
            background: var(--bb-soft);
        }

        .alg-bd-card.is-dark {
            color: #FFFFFF;
            background: #05070A;
            border-color: #05070A;
        }

        .alg-bd-card h3,
        .alg-bd-rule h3,
        .alg-bd-do h3,
        .alg-bd-token h3 {
            margin: 0 0 10px;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 900;
        }

        .alg-bd-card p,
        .alg-bd-rule p,
        .alg-bd-do p,
        .alg-bd-token p {
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
        }

        .alg-bd-logo-primary {
            min-height: 260px;
            display: grid;
            place-items: center;
            border-right: 1px solid var(--bb-line);
            padding: 32px;
        }

        .alg-bd-logo-primary img {
            width: min(100%, 540px);
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

        .alg-bd-logo-variant.is-dark .logo-safe {
            padding: 14px;
            background: #FFFFFF;
        }

        .alg-bd-logo-variant.is-dark img {
            display: block;
        }

        .alg-bd-do-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .alg-bd-do {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
        }

        .alg-bd-do-mark {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #FFFFFF;
            background: var(--bb-blue);
            font-weight: 900;
        }

        .alg-bd-do.is-dont .alg-bd-do-mark {
            background: var(--bb-red);
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
            margin: 0 0 10px;
            font-size: 28px;
            line-height: 1;
            font-weight: 900;
        }

        .alg-bd-type-sample {
            display: grid;
            grid-template-columns: minmax(220px, .8fr) minmax(0, 1.2fr);
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bd-aa {
            display: grid;
            place-items: center;
            min-height: 280px;
            font-size: clamp(110px, 15vw, 210px);
            line-height: .8;
            font-weight: 900;
            border-right: 1px solid var(--bb-line);
        }

        .alg-bd-type-rules {
            display: grid;
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

        .alg-bd-token {
            min-height: 150px;
            display: grid;
            gap: 14px;
        }

        .alg-bd-token-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .alg-bd-token code {
            display: inline-flex;
            width: fit-content;
            min-height: 28px;
            align-items: center;
            padding: 0 8px;
            border-radius: 6px;
            background: var(--bb-soft);
            color: var(--bb-blue);
            font-size: 12px;
            font-weight: 900;
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
            font-weight: 900;
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

        .alg-bd-rule-list.is-deep {
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
            min-height: 360px;
            padding: 28px;
            color: #FFFFFF;
            background: linear-gradient(135deg, #05070A 0%, #001E3C 100%);
        }

        .alg-bd-partner-card .logo-safe {
            width: fit-content;
            max-width: 100%;
            padding: 14px;
            background: #FFFFFF;
        }

        .alg-bd-partner-card img {
            width: 250px;
            max-width: 100%;
            display: block;
        }

        .alg-bd-partner-title {
            margin: 0;
            max-width: 360px;
            font-size: clamp(30px, 4vw, 52px);
            line-height: .96;
            font-weight: 900;
        }

        .alg-bd-partner-rules {
            display: grid;
        }

        .alg-bd-lockup-demo {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 18px;
            align-items: center;
            margin-top: 14px;
            padding: 22px;
            border: 1px solid var(--bb-line);
            background: #FFFFFF;
        }

        .alg-bd-lockup-demo img {
            width: min(220px, 100%);
        }

        .alg-bd-partner-placeholder {
            display: grid;
            place-items: center;
            min-height: 80px;
            border: 1px dashed #9AA3AF;
            color: #4B5563;
            font-weight: 900;
            text-transform: uppercase;
        }

        .alg-bd-divider {
            width: 1px;
            height: 72px;
            background: var(--bb-line);
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
            color: var(--bb-ink);
            text-decoration: none;
        }

        .alg-bd-asset span {
            display: block;
            color: var(--bb-muted);
            font-size: 11px;
            font-weight: 800;
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
            .alg-bd-comarketing,
            .alg-bd-section-head,
            .alg-bd-grid-2,
            .alg-bd-grid-3,
            .alg-bd-grid-4,
            .alg-bd-color-grid,
            .alg-bd-rule-list,
            .alg-bd-rule-list.is-deep,
            .alg-bd-dont-grid,
            .alg-bd-assets,
            .alg-bd-app-kpis,
            .alg-bd-app-row {
                grid-template-columns: 1fr;
            }

            .alg-bd-logo-primary,
            .alg-bd-aa {
                border-right: 0;
                border-bottom: 1px solid var(--bb-line);
            }

            .alg-bd-lockup-demo {
                grid-template-columns: 1fr;
            }

            .alg-bd-divider {
                width: 100%;
                height: 1px;
            }
        }

        @media (max-width: 640px) {
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
                grid-template-columns: 1fr;
                border: 1px solid var(--bb-line);
                background: #FFFFFF;
            }

            .alg-bd-nav a span:first-child {
                display: none;
            }

            .alg-bd-release,
            .alg-bd-do-grid,
            .alg-bd-type-line,
            .alg-bd-system-frame {
                grid-template-columns: 1fr;
            }

            .alg-bd-app-rail {
                grid-template-columns: repeat(4, auto);
            }
        }
    </style>

    <div class="alg-brand-doc" data-brandbook>
        <div class="alg-bd-layout">
            <aside class="alg-bd-sidebar" aria-label="Navegacion del brandbook">
                <div class="alg-bd-sidebar-logo">
                    <img src="{{ $icon }}" alt="America Logistics Group">
                    <div>
                        <p class="alg-bd-sidebar-title">ALG Brand System</p>
                        <p class="alg-bd-sidebar-meta">Version 1.1 · Guia operativa</p>
                    </div>
                </div>

                <nav class="alg-bd-nav" data-brand-nav>
                    @foreach($navSections as [$id, $label, $number])
                        <a href="#{{ $id }}" data-target="{{ $id }}"><span>{{ $number }}</span><span>{{ $label }}</span></a>
                    @endforeach
                </nav>

                <div class="alg-bd-downloads">
                    <a class="alg-bd-mini-link" href="{{ $logo }}" target="_blank" rel="noopener">Logo PNG</a>
                    <a class="alg-bd-mini-link" href="{{ $icon }}" target="_blank" rel="noopener">Isotipo PNG</a>
                    <a class="alg-bd-mini-link" href="#assets">Ver assets</a>
                </div>
            </aside>

            <main class="alg-bd-content">
                <section id="start" class="alg-bd-hero" data-section>
                    <div class="alg-bd-hero-copy">
                        <div>
                            <p class="alg-bd-kicker">Sistema de identidad visual</p>
                            <h1 class="alg-bd-title">America Logistics Group</h1>
                            <p class="alg-bd-lede">Guia digital para implementar la marca en productos, CRM, dashboards, reportes, presentaciones y co-marketing B2B con consistencia internacional.</p>
                        </div>
                        <div>
                            <div class="alg-bd-release" aria-label="Metadatos del brandbook">
                                <div><p>Version</p><p>1.1</p></div>
                                <div><p>Audiencia</p><p>Producto, ventas, marketing</p></div>
                                <div><p>Uso</p><p>Sistemas y comunicacion</p></div>
                            </div>
                            <div class="alg-bd-hero-tags" style="margin-top: 18px;">
                                <span class="alg-bd-tag">Brand system</span>
                                <span class="alg-bd-tag">Producto digital</span>
                                <span class="alg-bd-tag">Co-marketing</span>
                                <span class="alg-bd-tag">5PL</span>
                            </div>
                        </div>
                    </div>
                    <div class="alg-bd-hero-logo">
                        <div class="alg-bd-logo-stage">
                            <div class="alg-bd-logo-stage-inner"><img src="{{ $logo }}" alt="America Logistics Group"></div>
                        </div>
                        <p class="alg-bd-hero-note">La marca debe funcionar como infraestructura visual: clara, sobria y util. Cuando el sistema se vuelve complejo, la identidad debe reducir friccion.</p>
                    </div>
                </section>

                <section id="strategy" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">01 · Estrategia</p>
                            <h2 class="alg-bd-h2">Una marca para operar.</h2>
                        </div>
                        <p class="alg-bd-section-copy">ALG es una marca B2B de infraestructura comercial y logistica. Su identidad debe transmitir control, claridad y capacidad operativa, no decoracion. En cada pantalla la marca debe ayudar a decidir.</p>
                    </div>
                    <div class="alg-bd-grid-3">
                        <article class="alg-bd-card is-dark">
                            <h3>Neutralidad con criterio</h3>
                            <p>Helvetica, espacio y contraste sostienen la experiencia. El sistema debe sentirse preciso antes que ornamental.</p>
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

                <section id="logo" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">02 · Logo</p>
                            <h2 class="alg-bd-h2">El ancla visual.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Usar el logotipo horizontal cuando haya espacio suficiente. El isotipo funciona para navegacion compacta, favicons, avatares del sistema y estados donde la marca ya esta contextualizada.</p>
                    </div>
                    <div class="alg-bd-panel alg-bd-logo-showcase">
                        <div class="alg-bd-logo-primary"><img src="{{ $logo }}" alt="Logotipo horizontal America Logistics Group"></div>
                        <div class="alg-bd-logo-stack">
                            <div class="alg-bd-logo-variant"><img src="{{ $icon }}" alt="Isotipo America Logistics Group"></div>
                            <div class="alg-bd-logo-variant is-dark"><div class="logo-safe"><img src="{{ $logo }}" alt="Logotipo protegido sobre fondo oscuro"></div></div>
                        </div>
                    </div>
                    <div class="alg-bd-do-grid">
                        <article class="alg-bd-do"><div class="alg-bd-do-mark">OK</div><div><h3>Uso correcto</h3><p>Conservar proporciones, espacio libre y version full-color sobre superficies claras o protegidas.</p></div></article>
                        <article class="alg-bd-do is-dont"><div class="alg-bd-do-mark">NO</div><div><h3>Evitar</h3><p>No aplicar filtros para invertir el logo. Sobre fondo oscuro, usar una superficie clara o version aprobada.</p></div></article>
                    </div>
                </section>

                <section id="color" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">03 · Color</p>
                            <h2 class="alg-bd-h2">Color como sistema.</h2>
                        </div>
                        <p class="alg-bd-section-copy">El azul sostiene confianza e infraestructura. El rojo se reserva para energia, enfasis o riesgo. Los grises mantienen la lectura y evitan ruido visual.</p>
                    </div>
                    <div class="alg-bd-color-grid">
                        @foreach($colors as [$name, $hex, $usage, $spec, $tone])
                            <div class="alg-bd-color {{ $tone === 'light' ? 'is-light' : '' }}" style="background: {{ $hex }};">
                                <div>
                                    <p class="alg-bd-color-name">{{ $name }}</p>
                                    <p style="margin: 6px 0 0; font-size: 11px; font-weight: 800; opacity: .78;">{{ $spec }}</p>
                                </div>
                                <div>
                                    <p class="alg-bd-color-hex">{{ $hex }}</p>
                                    <button type="button" class="alg-bd-copy-button" data-copy="{{ $hex }}">Copiar HEX</button>
                                    <p style="margin: 12px 0 0; font-size: 12px; line-height: 1.45; max-width: 220px;">{{ $usage }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="alg-bd-do-grid">
                        <article class="alg-bd-do"><div class="alg-bd-do-mark">OK</div><div><h3>Color funcional</h3><p>Usar color para jerarquia, estado y decision. Un modulo debe tener un color dominante y un acento.</p></div></article>
                        <article class="alg-bd-do is-dont"><div class="alg-bd-do-mark">NO</div><div><h3>Color decorativo</h3><p>No usar rojo como relleno permanente ni multiplicar azules hasta perder contraste.</p></div></article>
                    </div>
                </section>

                <section id="type" class="alg-bd-section" data-section>
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
                            <div class="alg-bd-type-line"><strong>Titulos</strong><span style="font-weight: 900;">Bold / 36-96 px</span></div>
                            <div class="alg-bd-type-line"><strong>Secciones</strong><span style="font-weight: 700;">Medium / 18-28 px</span></div>
                            <div class="alg-bd-type-line"><strong>UI densa</strong><span style="font-size: 16px;">Regular / 12-15 px</span></div>
                            <div class="alg-bd-type-line"><strong>Enfasis</strong><span style="font-size: 16px; font-style: italic;">Italic solo para citas o notas</span></div>
                        </div>
                    </div>
                    <div class="alg-bd-do-grid">
                        <article class="alg-bd-do"><div class="alg-bd-do-mark">OK</div><div><h3>Jerarquia visible</h3><p>Usar peso, tamano y espacio para distinguir lectura editorial, UI densa y data.</p></div></article>
                        <article class="alg-bd-do is-dont"><div class="alg-bd-do-mark">NO</div><div><h3>Mezcla tipografica</h3><p>No sumar fuentes decorativas ni usar mayusculas extensas en contenido operativo.</p></div></article>
                    </div>
                </section>

                <section id="tokens" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">05 · Tokens</p>
                            <h2 class="alg-bd-h2">Del manual al producto.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Los tokens traducen identidad a implementacion. Deben guiar componentes, dashboards, reportes, emails y piezas comerciales para que la marca no dependa de interpretaciones aisladas.</p>
                    </div>
                    <div class="alg-bd-grid-3">
                        @foreach($tokens as [$group, $name, $value, $usage])
                            <article class="alg-bd-token">
                                <div class="alg-bd-token-top"><span class="alg-bd-tag">{{ $group }}</span><code>{{ $value }}</code></div>
                                <div><h3>{{ $name }}</h3><p>{{ $usage }}</p></div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="systems" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">06 · Apps y sistemas</p>
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

                    <div class="alg-bd-rule-list is-deep" style="margin-top: 14px;">
                        @foreach($systemRules as [$title, $body])
                            <article class="alg-bd-rule"><h3>{{ $title }}</h3><p>{{ $body }}</p></article>
                        @endforeach
                    </div>
                    <div class="alg-bd-do-grid">
                        <article class="alg-bd-do"><div class="alg-bd-do-mark">OK</div><div><h3>Producto primero</h3><p>Usar marca para ordenar, senalizar y reducir esfuerzo cognitivo.</p></div></article>
                        <article class="alg-bd-do is-dont"><div class="alg-bd-do-mark">NO</div><div><h3>Marketing en exceso</h3><p>No llenar dashboards de slogans, fondos pesados o graficas decorativas.</p></div></article>
                    </div>
                </section>

                <section id="comarketing" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">07 · Co-marketing</p>
                            <h2 class="alg-bd-h2">Claridad entre marcas.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Cuando ALG convive con clientes, partners o plataformas, la pieza debe dejar claro quien comunica, que relacion existe y cual es la accion esperada.</p>
                    </div>
                    <div class="alg-bd-comarketing">
                        <div class="alg-bd-partner-card">
                            <div class="logo-safe"><img src="{{ $logo }}" alt="America Logistics Group"></div>
                            <h3 class="alg-bd-partner-title">Soluciones logisticas que conectan America.</h3>
                        </div>
                        <div class="alg-bd-partner-rules">
                            @foreach($coMarketingRules as [$title, $body])
                                <article class="alg-bd-rule" style="border-width: 0 0 1px 0;"><h3>{{ $title }}</h3><p>{{ $body }}</p></article>
                            @endforeach
                        </div>
                    </div>
                    <div class="alg-bd-lockup-demo">
                        <img src="{{ $logo }}" alt="America Logistics Group">
                        <div class="alg-bd-divider"></div>
                        <div class="alg-bd-partner-placeholder">Partner logo</div>
                    </div>
                </section>

                <section id="donts" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">08 · No permitido</p>
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
                    <div class="alg-bd-rule-list" style="margin-top: 14px;">
                        @foreach($doDont as [$area, $ok, $no])
                            <article class="alg-bd-rule"><h3>{{ $area }}</h3><p><strong>Hacer:</strong> {{ $ok }}</p><p style="margin-top:8px;"><strong>Evitar:</strong> {{ $no }}</p></article>
                        @endforeach
                    </div>
                </section>

                <section id="assets" class="alg-bd-section" data-section>
                    <div class="alg-bd-section-head">
                        <div>
                            <p class="alg-bd-number">09 · Assets</p>
                            <h2 class="alg-bd-h2">Recursos listos.</h2>
                        </div>
                        <p class="alg-bd-section-copy">Usar estos archivos como fuente base para el producto y materiales comerciales. Cualquier variante nueva debe respetar esta guia.</p>
                    </div>
                    <div class="alg-bd-assets">
                        @foreach($assets as [$title, $meta, $url, $preview])
                            <a class="alg-bd-asset" href="{{ $url }}" target="_blank" rel="noopener"><div><span>{{ $meta }}</span><strong>{{ $title }}</strong></div><img src="{{ $preview }}" alt=""></a>
                        @endforeach
                    </div>
                    <div class="alg-bd-inline-actions" style="margin-top: 14px;">
                        <button type="button" class="alg-bd-action" data-copy="#00447A">Copiar azul</button>
                        <button type="button" class="alg-bd-action" data-copy="#DB001B">Copiar rojo</button>
                        <a class="alg-bd-action" href="#start">Volver arriba</a>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-brandbook]');
            if (!root) return;

            const links = Array.from(root.querySelectorAll('[data-brand-nav] a'));
            const sections = links
                .map((link) => document.getElementById(link.dataset.target))
                .filter(Boolean);

            const activate = (id) => {
                links.forEach((link) => link.classList.toggle('is-active', link.dataset.target === id));
            };

            if (sections.length && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    const visible = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                    if (visible) activate(visible.target.id);
                }, { rootMargin: '-18% 0px -68% 0px', threshold: [0.08, 0.18, 0.32] });
                sections.forEach((section) => observer.observe(section));
            }

            activate('start');

            root.querySelectorAll('[data-copy]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const value = button.dataset.copy;
                    try {
                        await navigator.clipboard.writeText(value);
                        const original = button.textContent;
                        button.textContent = 'Copiado';
                        window.setTimeout(() => { button.textContent = original; }, 1200);
                    } catch (error) {
                        button.textContent = value;
                    }
                });
            });
        })();
    </script>
</x-filament-panels::page>
