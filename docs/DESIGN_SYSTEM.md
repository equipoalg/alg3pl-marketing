# ALG3PL Design System

> **Source of truth** for the visual language of the ALG3PL admin app.
> Origin: `/admin/dashboard` (Claude Design bundle, "Editorial · Aireado · Geist · Stone + Navy").
> All admin pages must conform to the rules below. When in doubt: **look at the dashboard**.

---

## 1. Tokens

All tokens live in `public/css/alg.css` (`:root` block). Two naming conventions coexist:

- **`--alg-*`** — primary names. Use these in CSS files and Filament class overrides.
- **Unprefixed `--*`** — aliases for the bundle's blade partials (`var(--ink-1)`, `var(--surface)`, etc.). Both resolve to the same value.

### 1.1 Color

| Token | Value | Use |
|---|---|---|
| `--alg-bg` / `--bg` | `#FAFAF9` | Page background (warm off-white) |
| `--alg-surface` / `--surface` | `#FFFFFF` | Cards, sidebar, topbar, modal body |
| `--alg-surface-2` / `--surface-2` | `#F5F5F4` | Hover state, alt row, pill toggle bg |
| `--alg-surface-3` / `--surface-3` | `#EEEDEB` | Tertiary, nested |
| `--alg-line` / `--border` | `#E7E5E4` | Hairline borders, dividers, input outlines |
| `--alg-line-2` / `--border-strong` | `#D6D3D1` | Stronger dividers, scrollbar thumb |
| `--alg-ink` / `--ink-1` | `#0C0A09` | Primary text, headings, **primary button bg** |
| `--alg-ink-2` / `--ink-2` | `#292524` | Body text, table values |
| `--alg-ink-3` / `--ink-3` | `#57534E` | Tertiary text, sidebar nav default |
| `--alg-ink-4` / `--ink-4` | `#78716C` | Subtitles, helper labels |
| `--alg-ink-5` / `--ink-5` | `#A8A29E` | Disabled, microcopy, placeholders, low-contrast icons |
| `--alg-accent` / `--accent` | `#1E3A8A` | Navy accent — links, active nav bar, chart series 0 |
| `--alg-accent-2` / `--accent-2` | `#2563EB` | Brighter navy — chart series 1, KPI sparklines, highlights |
| `--alg-accent-soft` / `--accent-soft` | `#EFF3FB` | Navy soft bg — info badges, focus ring tint |
| `--alg-pos` / `--pos` | `#166534` | Success / positive |
| `--alg-pos-soft` / `--pos-soft` | `#ECFDF5` | Success soft bg |
| `--alg-neg` / `--neg` | `#9F1239` | Danger / negative |
| `--alg-neg-soft` / `--neg-soft` | `#FEF2F2` | Danger soft bg |
| `--alg-warn` / `--warn` | `#92400E` | Warning / amber |
| `--alg-warn-soft` / `--warn-soft` | `#FEF3C7` | Warning soft bg |

### 1.2 Typography

| Token | Value |
|---|---|
| `--alg-font` / `--font-sans` | `"Geist", ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif` |
| `--alg-mono` / `--font-mono` | `"Geist Mono", ui-monospace, "JetBrains Mono", "SF Mono", Menlo, monospace` |

Loaded from Google Fonts in `index.blade.php` and `alg.css`. Geist weights used: 300, 400, 500, 600, 700.

### 1.3 Sizes

| Context | Size | Weight | Letter-spacing |
|---|---|---|---|
| Hero KPI value | 30–32px | 500 | -0.025em |
| Page H1 | 22px | 600 | -0.02em |
| Section H2 | 15px | 600 | -0.01em |
| Card H3 | 13.5px | 600 | -0.01em |
| Body / table cell | 12–13px | 400 | normal |
| Subtitle / helper | 12px | 400 | 0 |
| Microcopy / column header | 10.5–11px | 500 | 0.06–0.08em (uppercase) |
| Chart axis label | 10px | 400 | 0 |

### 1.4 Spacing

| Token | Value |
|---|---|
| `--alg-pad-sm` | 12px |
| `--alg-pad` | 16px |
| `--alg-pad-lg` | 20px |

Page main content padding: **24px vertical × 28px horizontal**.
Card interior padding: **16px–20px**.
Gap between cards in grid: **16px**.

### 1.5 Geometry (border-radius)

| Token | Value | Use |
|---|---|---|
| `--alg-radius-xs` | 3px | Badges, dots |
| `--alg-radius-sm` | 5px | Nav items |
| `--alg-radius` | 6px | Buttons, inputs, pills |
| `--alg-radius-lg` | 8px | Cards, modals, table wrappers |

### 1.6 Shadow

**No shadows on cards, buttons, inputs, or any UI surface.** Hairlines (`1px solid var(--alg-line)`) do all the work.

Only exception:
- `--alg-shadow-lg`: `0 8px 24px rgba(12, 10, 9, 0.08)` — used **only** on dropdown panels (workspace switcher, country list).

### 1.7 Motion

| Token | Value |
|---|---|
| `--alg-ease` | `150ms ease-out` (standard) |
| `--alg-ease-fast` | `100ms ease-out` (hover) |

---

## 2. Layout primitives

### 2.1 Page structure

```
┌─────────────────────────────────────────────────────────┐
│ Sidebar 224px │ Topbar 52px                             │
│  (white,      │ ─────────────────────────────────────── │
│   border-     │ Main content                            │
│   right       │ padding: 24px 28px                      │
│   1px line)   │                                         │
│               │   ┌──────────┐ ┌──────────┐             │
│  ALG3PL brand │   │  Card    │ │  Card    │             │
│  Workspace    │   │  white   │ │  white   │             │
│  Nav sections │   │  border  │ │  border  │             │
│  Footer user  │   └──────────┘ └──────────┘             │
└─────────────────────────────────────────────────────────┘
```

- **Sidebar A** (default): 224px, white background, hairline right border. Brand → workspace switcher → nav groups → footer user.
- **Sidebar B** (editorial): 56px collapsed icon-rail dark (`var(--alg-ink)`), expandable to 224px via Alpine + localStorage. Used in dashboard variant B.
- **Topbar**: 52px tall, hairline bottom border, page bg color. Breadcrumb (`Cliente > ALG XX > Page`) | Search "Buscar ⌘K" | Bell | "Nuevo lead" primary button.
- **Main**: `padding: 24px 28px`, scrolls independently of sidebar.

### 2.2 Card pattern

Standard card used everywhere (KPI grid, table wrapper, form section, dashboard widget):

```html
<div style="background: var(--alg-surface);
            border: 1px solid var(--alg-line);
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            flex-direction: column;">
  <!-- card title (Geist 13.5px 600) -->
  <!-- subtitle (Geist 12px ink-4) -->
  <!-- body -->
</div>
```

**Never** `box-shadow` on a card. **Never** rounded corners > 8px.

### 2.3 Hairline divider

`border-bottom: 1px solid var(--alg-line)` — for separating sections inside a card, table rows, or topbar from main.

---

## 3. Component patterns

### 3.1 H1 page heading

```html
<h1 style="margin: 0;
           font-size: 22px;
           font-weight: 600;
           letter-spacing: -0.02em;
           color: var(--alg-ink);">
  Panorama global
</h1>
```

Filament selector that matches: `.fi-header-heading`. See `alg.css` line ~338.

### 3.2 Primary button (filled, ink-1)

The "Nuevo lead" button in the topbar is the canonical pattern. **Always solid `var(--alg-ink)` (warm black) — NOT navy.**

```html
<button style="display: inline-flex;
               align-items: center;
               gap: 6px;
               padding: 6px 11px;
               border-radius: 6px;
               border: 1px solid var(--alg-ink);
               background: var(--alg-ink);
               font-size: 12.5px;
               color: white;
               font-weight: 500;
               font-family: var(--alg-font);
               cursor: pointer;">
  + Nuevo lead
</button>
```

Filament selector: `.fi-color-primary.fi-bg-color-600` and `.fi-btn-color-primary`.

### 3.3 Ghost / secondary button

```html
<button style="display: inline-flex;
               align-items: center;
               gap: 8px;
               padding: 6px 10px;
               border-radius: 6px;
               border: 1px solid var(--alg-line);
               background: var(--alg-surface);
               font-size: 12px;
               color: var(--alg-ink-3);
               font-family: var(--alg-font);
               cursor: pointer;">
  Filtros
</button>
```

Filament selector: `.fi-btn-color-gray`.

### 3.4 Pill toggle group (segmented control)

Used for `7 días | 30 días | 90 días | Año` ranges.

```html
<div style="display: flex;
            border: 1px solid var(--alg-line);
            border-radius: 6px;
            padding: 2px;
            background: var(--alg-surface);">
  <a style="padding: 5px 11px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--alg-ink-1);
            background: var(--alg-surface-2); /* active */
            font-weight: 500;">30 días</a>
  <a style="padding: 5px 11px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--alg-ink-4);
            background: transparent; /* inactive */
            font-weight: 400;">90 días</a>
</div>
```

### 3.5 Table

Pattern from "Top keywords" / "Leads recientes" / "Campañas" cards.

```html
<!-- Card wrapper -->
<div style="background: var(--alg-surface);
            border: 1px solid var(--alg-line);
            border-radius: 8px;
            padding: 16px 20px;">

  <!-- Header row (uppercase microcopy) -->
  <div style="display: grid;
              grid-template-columns: 1fr 70px 70px 70px;
              font-size: 10.5px;
              color: var(--alg-ink-5);
              text-transform: uppercase;
              letter-spacing: 0.06em;
              padding: 0 0 8px;
              border-bottom: 1px solid var(--alg-line);">
    <div>Keyword</div>
    <div style="text-align: right;">Clicks</div>
    ...
  </div>

  <!-- Body row -->
  <div style="display: grid;
              grid-template-columns: 1fr 70px 70px 70px;
              padding: 8px 0;
              border-bottom: 1px solid var(--alg-line);
              font-size: 12px;
              align-items: center;
              color: var(--alg-ink-2);">
    <div>alg el salvador</div>
    <div class="num tnum" style="text-align: right; color: var(--alg-accent-2);">47</div>
    ...
  </div>
</div>
```

Filament selectors: `.fi-ta` / `.fi-ta-ctn` (wrapper), `.fi-ta-header-cell-label` (header), `.fi-ta-cell` (body).

### 3.6 Status / stage badge

```html
<span style="font-size: 11px;
             padding: 3px 8px;
             border-radius: 3px;
             background: var(--alg-pos-soft);
             color: var(--alg-pos);
             font-weight: 500;">
  Ganado
</span>
```

Color map by stage:

| Stage | Background | Foreground |
|---|---|---|
| Ganado | `var(--alg-pos-soft)` | `var(--alg-pos)` |
| Calificado / Propuesta | `var(--alg-accent-soft)` | `var(--alg-accent)` |
| Contactado | `var(--alg-surface-2)` | `var(--alg-ink-3)` |
| Perdido | `var(--alg-neg-soft)` | `var(--alg-neg)` |
| Default / Nuevo | `var(--alg-surface-2)` | `var(--alg-ink-4)` |

Filament selector: `.fi-badge`. See `alg.css` line ~544.

### 3.7 KPI card with sparkline

```html
<div style="background: var(--alg-surface); padding: 18px 20px 16px; min-height: 124px;">
  <span style="font-size: 11.5px;
               color: var(--alg-ink-4);
               text-transform: uppercase;
               letter-spacing: 0.06em;
               font-weight: 500;">Leads totales</span>
  <span class="num" style="font-size: 30px;
                           font-weight: 500;
                           letter-spacing: -0.025em;
                           color: var(--alg-ink);">2,847</span>
  <span style="font-size: 11.5px; color: var(--alg-pos);">+12.4%</span>
  <!-- inline SVG sparkline 72x24 -->
</div>
```

### 3.8 Multi-series chart (SVG)

Generated by `app/Support/DashboardCharts::multiSeriesSvg()`. Always:

- 5 Y-axis ticks, dashed grid `var(--alg-line)`
- 5 X-axis labels (deduped: first / Q1 / mid / Q3 / last)
- Series 0 stroke 1.75px opacity 1
- Series 1+ stroke 1.25px opacity 0.7
- Area mode: fill opacity 0.10
- Default colors: `["var(--alg-accent-2)", "var(--alg-ink-3)", "var(--alg-ink-5)"]`

### 3.9 Sparkline (SVG)

`DashboardCharts::sparklineSvg()` — 72×24, stroke 1.5px, optional 0.10 fill. Use `var(--alg-accent-2)` for positive KPIs, `var(--alg-ink-3)` for neutral.

### 3.10 Form input

```html
<input style="border-radius: 6px;
              border: 1px solid var(--alg-line);
              background: var(--alg-surface);
              font-family: var(--alg-font);
              font-size: 13px;
              color: var(--alg-ink);
              padding: 6px 10px;">
```

Focus state: border `var(--alg-accent)` + 3px shadow `var(--alg-accent-soft)`. Placeholder color `var(--alg-ink-5)`.

Filament selector: `.fi-input`, `.fi-select-input`, `.fi-textarea`.

---

## 4. Critical rules

These are the rules that MUST be followed. Violating them breaks visual consistency.

1. **No gradients, ever.** Solid colors only.
2. **Primary buttons are `var(--alg-ink)` (warm black), NOT navy.** Navy is reserved for accents (links, active state, chart series, badges). The "Nuevo lead" topbar button is the reference.
3. **Borders are hairlines (1px solid `var(--alg-line)`).** No double borders, no thicker borders, no colored borders except for focus states.
4. **No shadows on cards, buttons, inputs.** Only the workspace dropdown panel has a shadow.
5. **Numerics always use `.num` class** (Geist Mono + tabular-nums + tnum/zero feature settings + -0.01em letter-spacing). Right-align numerics in tables.
6. **Active nav state**: `var(--alg-surface-2)` background + 2px `var(--alg-accent)` left border + `var(--alg-ink)` text + weight 500. NOT a fill, NOT navy bg.
7. **Microcopy is uppercase + 0.06em letter-spacing + `var(--alg-ink-5)`.** Used for KPI labels, column headers, section overlines.
8. **Sidebar count badges** are `.num tnum` 11px `var(--alg-ink-5)`. Never colored bg pills.
9. **Tables live inside cards.** Never raw tables on the page background.
10. **Page bg is `var(--alg-bg)` (`#FAFAF9` warm off-white), NOT pure white.** White is reserved for cards/sidebar/topbar.

---

## 5. Filament v5 selector mapping

`public/css/alg.css` is loaded at `HEAD_END` render hook so it wins cascade vs Filament's `app.css`.

| Filament v5 class | What it targets | Token applied | Coverage |
|---|---|---|---|
| `.fi-header-heading` | Page H1 | 22px / 600 / -0.02em / ink-1 | ✓ |
| `.fi-header-subheading` | Page subtitle | 13px / ink-4 / margin-top 4px | ✓ |
| `.fi-color-primary.fi-bg-color-600` | Primary button (Tailwind blue → ink-1) | ink-1 bg, white fg | ✓ |
| `.fi-btn-color-primary` | Older Filament primary class | ink-1 | ✓ |
| `.fi-btn-color-gray` | Ghost/secondary button | surface, line border, ink-3 | ✓ |
| `.fi-btn-color-success` | Green button | pos | ✓ |
| `.fi-btn-color-danger` | Red button | neg | ✓ |
| `.fi-btn-color-warning` | Amber button | warn | ✓ |
| `.fi-btn-color-info` | Info button | accent | ✓ |
| `.fi-ta`, `.fi-ta-ctn` | Table card wrapper | surface bg, line border, radius 8 | ✓ |
| `.fi-ta-header-row` | Table header row | hairline bottom | ✓ |
| `.fi-ta-header-cell-label` | Column header text | 10.5px / uppercase / 0.06em / ink-5 | ✓ |
| `.fi-ta-cell` | Body cell | 12px / ink-2 / hairline border | ✓ |
| `.fi-ta-row:hover` | Row hover | surface-2 bg | ✓ |
| `.fi-empty-state` | Empty table state | sutil icon, 13px heading | ✓ |
| `.fi-input`, `.fi-select-input`, `.fi-textarea` | Form fields | line border, surface bg, accent focus | ✓ |
| `.fi-input::placeholder` | Placeholder text | ink-5 | ✓ |
| `.fi-section` | Form section card | surface, line border, radius 8 | ✓ |
| `.fi-section-header-heading` | Section title | 13.5px / 600 / -0.01em | ✓ |
| `.fi-badge` | Status pill base | 11px / 3px 8px / radius 3 | ✓ |
| `.fi-badge-color-{success,danger,warning,info,primary}` | Badge variants | soft bg + matching ink | ✓ |
| `.fi-sidebar-item-btn.fi-active` | Active nav item | surface-2 bg + 2px accent left bar | ✓ |
| `.fi-breadcrumbs` | Auto breadcrumbs | hidden (custom topbar shows context) | ✓ |
| `.fi-btn-group`, `.fi-btn-group-item` | Segmented control | pill toggle pattern | ✓ |
| `.fi-modal` | Modal wrapper | surface, line border, radius 8 | ✓ |
| `.fi-modal-header`, `.fi-modal-footer` | Modal chrome | hairline, padding | ✓ |
| `.fi-ta-col-sort-button svg` | Sort icon | ink-3 default, accent active | ✓ |
| `.fi-pagination-item` | Pagination button | ghost variant | ✓ |
| `.fi-pagination-item.fi-active` | Active page | accent bg | ✓ |
| `.fi-checkbox-input:checked`, `.fi-radio-input:checked` | Checked controls | accent fill | ✓ |
| `.fi-loading-indicator` | Loading state | accent | ✓ |
| `.fi-wi-stats-overview` | KPI grid widget | line border, no shadow | ✓ |
| `.fi-dropdown-panel` | Dropdown menu | surface, line border, shadow-lg | ✓ |

---

## 6. How to apply this in new code

When you're writing or editing a new admin page or blade:

1. **Inline styles** in custom blades — always use `var(--alg-*)` tokens. Never paste a raw hex.
2. **Filament resource forms / tables** — leave them alone, they pick up the tokens via the selectors above.
3. **Custom blade partials** (modals, widgets) — use the patterns in §3 verbatim.
4. **New components** (e.g. a chart, a stat card) — extend `alg.css` with `.fi-*` selectors when applicable, OR use inline tokens. Update §5 if you add a new selector mapping.
5. **No `box-shadow` except dropdowns.** No gradients. No new colors outside the token set.

When in doubt, **open `/admin/dashboard` and look at the equivalent component there**. If the dashboard doesn't have it yet, propose a pattern that fits the rules above.

---

## 7. References

- Source bundle: `resources/views/alg-dashboard/index.blade.php` (root layout with token block)
- Variants: `resources/views/alg-dashboard/variant-a.blade.php` (classic) + `variant-b.blade.php` (editorial)
- Sidebars: `sidebar-a.blade.php` (224px white) + `sidebar-b.blade.php` (56px dark expandable)
- Topbar: `topbar.blade.php`
- Charts: `app/Support/DashboardCharts.php`
- Mock data shape: `app/Support/DashboardMockData.php`
- Real data feeder: `app/Support/DashboardData.php`
- CSS tokens & overrides: `public/css/alg.css`
