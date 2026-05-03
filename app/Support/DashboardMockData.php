<?php

namespace App\Support;

/**
 * Mock data for the ALG dashboard — 1:1 port of data.jsx from the Claude
 * Design bundle (Dashboard ALG / dC0PA3ngVAG7QbibJNxkJQ).
 *
 * This intentionally mirrors the JSX shape exactly so the blade views can be
 * a 1:1 translation of dashboard-a.jsx and dashboard-b.jsx.
 */
class DashboardMockData
{
    public static function navSections(): array
    {
        return [
            ['label' => null, 'items' => [
                ['id' => 'dashboard', 'icon' => 'grid', 'label' => 'Dashboard', 'href' => '/admin/dashboard', 'active' => true],
            ]],
            ['label' => 'Marketing search', 'items' => [
                ['id' => 'kanban', 'icon' => 'columns', 'label' => 'Kanban Board', 'href' => '/admin/kanban'],
            ]],
            ['label' => 'CRM', 'items' => [
                ['id' => 'cuentas',     'icon' => 'building',  'label' => 'Cuentas',             'href' => '/admin/clients',  'count' => 142],
                ['id' => 'leads',       'icon' => 'inbox',     'label' => 'Bandeja de entrada',  'href' => '/admin/leads',    'count' => 483],
                ['id' => 'tags',        'icon' => 'tag',       'label' => 'Tags',                'href' => '/admin/tags'],
                ['id' => 'seguimiento', 'icon' => 'activity',  'label' => 'Seguimiento',         'href' => '/admin/tasks'],
            ]],
            ['label' => 'Marketing', 'items' => [
                ['id' => 'campanas',        'icon' => 'megaphone', 'label' => 'Campañas',        'href' => '/admin/campaigns', 'count' => 8],
                ['id' => 'funnels',         'icon' => 'filter',    'label' => 'Funnels',         'href' => '/admin/funnels'],
                ['id' => 'email-templates', 'icon' => 'mail',      'label' => 'Email Templates', 'href' => '/admin/email-templates'],
                ['id' => 'envios',          'icon' => 'send',      'label' => 'Envíos',          'href' => '/admin/email-campaigns'],
            ]],
            ['label' => 'Analytics', 'items' => [
                ['id' => 'trafico',  'icon' => 'trending-up', 'label' => 'Tráfico',         'href' => '/admin/analytics'],
                ['id' => 'search',   'icon' => 'search',      'label' => 'Search Console',  'href' => '/admin/search-console'],
                ['id' => 'reportes', 'icon' => 'file-text',   'label' => 'Reportes',        'href' => '/admin/country-reports'],
            ]],
        ];
    }

    public static function kpis(): array
    {
        return [
            ['id' => 'leads',    'label' => 'Leads totales',     'value' => 2847,   'delta' => 12.4, 'sparkColor' => 'accent', 'sub' => 'vs 2,533 mes anterior',     'series' => [11,13,12,14,16,15,18,17,19,21,20,22,24]],
            ['id' => 'cuentas',  'label' => 'Cuentas activas',   'value' => 142,    'delta' => 3.6,  'sparkColor' => 'ink',    'sub' => '5 nuevas esta semana',      'series' => [120,122,124,125,128,130,132,134,136,138,140,141,142]],
            ['id' => 'campanas', 'label' => 'Campañas activas',  'value' => 8,      'delta' => 0,    'sparkColor' => 'ink',    'sub' => '3 programadas',             'series' => [6,6,7,7,7,8,8,8,8,8,8,8,8]],
            ['id' => 'tasa',     'label' => 'Tasa de conversión','value' => '4.8%', 'delta' => 0.6,  'sparkColor' => 'accent', 'sub' => 'vs 4.2% anterior',          'series' => [3.8,3.9,4.0,4.1,4.0,4.2,4.3,4.4,4.5,4.6,4.7,4.7,4.8]],
        ];
    }

    /** 90-day organic/directo/referido traffic — deterministic shape matching JSX visual feel. */
    public static function trafficSeries(): array
    {
        $days = 90;
        $organic = []; $directo = []; $referido = [];
        $oV = 180; $dV = 90; $rV = 60;
        for ($i = 0; $i < $days; $i++) {
            // Deterministic pseudo-random: use sin/cos with index as seed
            $oV += (sin($i / 6) * 14) + (sin($i * 1.7) - 0.5) * 18 + ($i / $days) * 1.6;
            $dV += (sin($i / 9 + 1) * 8) + (sin($i * 2.3) - 0.5) * 10 + ($i / $days) * 0.6;
            $rV += (sin($i / 11 + 2) * 5) + (sin($i * 3.1) - 0.5) * 7 + ($i / $days) * 0.3;
            $organic[]  = max(40, (int) round($oV));
            $directo[]  = max(20, (int) round($dV));
            $referido[] = max(10, (int) round($rV));
        }
        return ['organic' => $organic, 'directo' => $directo, 'referido' => $referido];
    }

    /** 90-day labels (e.g., "26 abr") — for chart x-axis. */
    public static function trafficLabels(): array
    {
        $months = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $today = mktime(0, 0, 0, 4, 26, 2026);
        $out = [];
        for ($i = 89; $i >= 0; $i--) {
            $t = strtotime("-{$i} days", $today);
            $out[] = (int) date('j', $t) . ' ' . $months[(int) date('n', $t) - 1];
        }
        return $out;
    }

    public static function fuentes(): array
    {
        return [
            ['label' => 'Orgánico', 'value' => 8420, 'share' => 0.52, 'trend' => 14],
            ['label' => 'Directo',  'value' => 3960, 'share' => 0.24, 'trend' => 6],
            ['label' => 'Referido', 'value' => 2180, 'share' => 0.13, 'trend' => -2],
            ['label' => 'Social',   'value' => 980,  'share' => 0.06, 'trend' => 22],
            ['label' => 'Email',    'value' => 540,  'share' => 0.03, 'trend' => 8],
            ['label' => 'Pagado',   'value' => 220,  'share' => 0.02, 'trend' => 3],
        ];
    }

    public static function keywords(): array
    {
        return [
            ['kw' => 'alg el salvador',         'clicks' => 47, 'impr' => 286,  'pos' => 2.1,  'delta' => 0.4],
            ['kw' => 'alg logística',           'clicks' => 38, 'impr' => 412,  'pos' => 3.4,  'delta' => 0.2],
            ['kw' => 'logística centroamérica', 'clicks' => 29, 'impr' => 1840, 'pos' => 8.2,  'delta' => -0.3],
            ['kw' => 'alg guatemala',           'clicks' => 24, 'impr' => 178,  'pos' => 2.7,  'delta' => 0.1],
            ['kw' => '3pl honduras',            'clicks' => 21, 'impr' => 942,  'pos' => 6.4,  'delta' => 0.8],
            ['kw' => 'transporte costa rica',   'clicks' => 18, 'impr' => 1240, 'pos' => 9.1,  'delta' => -0.2],
            ['kw' => 'aduana el salvador',      'clicks' => 16, 'impr' => 720,  'pos' => 7.2,  'delta' => 0.3],
            ['kw' => 'alg transportes',         'clicks' => 14, 'impr' => 168,  'pos' => 3.1,  'delta' => 0.0],
            ['kw' => 'grupo alg',               'clicks' => 12, 'impr' => 224,  'pos' => 4.3,  'delta' => -0.1],
            ['kw' => 'logística méxico miami',  'clicks' => 11, 'impr' => 1480, 'pos' => 11.4, 'delta' => 1.2],
        ];
    }

    public static function pipelineStages(): array
    {
        return [
            ['id' => 'new',      'label' => 'Nuevo',      'count' => 184, 'color' => 'ink-5'],
            ['id' => 'contact',  'label' => 'Contactado', 'count' => 96,  'color' => 'ink-4'],
            ['id' => 'qual',     'label' => 'Calificado', 'count' => 54,  'color' => 'accent'],
            ['id' => 'proposal', 'label' => 'Propuesta',  'count' => 28,  'color' => 'accent'],
            ['id' => 'won',      'label' => 'Ganado',     'count' => 14,  'color' => 'pos'],
            ['id' => 'lost',     'label' => 'Perdido',    'count' => 22,  'color' => 'neg'],
        ];
    }

    public static function recentLeads(): array
    {
        return [
            ['name' => 'María Villalobos', 'company' => 'Café del Volcán S.A.', 'country' => 'GT', 'value' => '$24,800', 'stage' => 'Calificado', 'time' => 'hace 12 min', 'initials' => 'MV'],
            ['name' => 'Carlos Mendoza',   'company' => 'Industrias Mendoza',   'country' => 'SV', 'value' => '$8,400',  'stage' => 'Contactado', 'time' => 'hace 38 min', 'initials' => 'CM'],
            ['name' => 'Ana Recinos',      'company' => 'TextileX Honduras',    'country' => 'HN', 'value' => '$52,000', 'stage' => 'Propuesta',  'time' => 'hace 1 h',    'initials' => 'AR'],
            ['name' => 'Diego Fernández',  'company' => 'Pacífico Trading',     'country' => 'CR', 'value' => '$16,200', 'stage' => 'Calificado', 'time' => 'hace 2 h',    'initials' => 'DF'],
            ['name' => 'Lucía Pineda',     'company' => 'Cosmética Maya',       'country' => 'MX', 'value' => '$31,500', 'stage' => 'Nuevo',      'time' => 'hace 3 h',    'initials' => 'LP'],
            ['name' => 'Roberto Salazar',  'company' => 'Logitec PTY',          'country' => 'PA', 'value' => '$11,800', 'stage' => 'Nuevo',      'time' => 'hace 4 h',    'initials' => 'RS'],
        ];
    }

    public static function campaigns(): array
    {
        return [
            ['name' => 'Q2 — Centroamérica B2B',   'status' => 'Activa',     'sent' => 12480, 'open' => 0.42, 'click' => 0.061, 'spend' => '$3,200'],
            ['name' => 'Aduanas El Salvador',      'status' => 'Activa',     'sent' => 4820,  'open' => 0.38, 'click' => 0.048, 'spend' => '$1,100'],
            ['name' => 'Transporte multimodal MX', 'status' => 'Programada', 'sent' => 0,     'open' => null, 'click' => null,  'spend' => '$2,400'],
            ['name' => 'Reactivación leads 2025',  'status' => 'Activa',     'sent' => 8940,  'open' => 0.51, 'click' => 0.072, 'spend' => '$890'],
            ['name' => 'Caso de éxito — TextileX', 'status' => 'Pausada',    'sent' => 2110,  'open' => 0.34, 'click' => 0.052, 'spend' => '$420'],
        ];
    }

    public static function activity(): array
    {
        return [
            ['actor' => 'Sistema',       'action' => "sincronizó 18 nuevos leads desde HubSpot", 'time' => '10:42'],
            ['actor' => 'María García',  'action' => "movió 'Café del Volcán' a Propuesta",      'time' => '10:18'],
            ['actor' => 'Sistema',       'action' => "envió campaña 'Q2 — Centroamérica B2B'",   'time' => '09:55'],
            ['actor' => 'Andrés Rivera', 'action' => "agregó nota a 'TextileX Honduras'",        'time' => '09:31'],
            ['actor' => 'Sistema',       'action' => "actualizó posiciones Search Console",      'time' => '08:00'],
            ['actor' => 'María García',  'action' => "creó cuenta 'Pacífico Trading'",           'time' => 'ayer'],
        ];
    }

    public static function tasks(): array
    {
        return [
            ['title' => 'Llamar a Ana Recinos — TextileX',    'due' => 'Hoy 14:00',   'priority' => 'alta'],
            ['title' => 'Enviar propuesta — Café del Volcán', 'due' => 'Hoy 16:30',   'priority' => 'alta'],
            ['title' => 'Revisar campaña aduanas SV',         'due' => 'Mañana',      'priority' => 'media'],
            ['title' => 'Cierre Q2 — reporte ejecutivo',      'due' => 'Vie 30 abr',  'priority' => 'media'],
            ['title' => 'Sincronizar tags HubSpot',           'due' => 'Próx. semana','priority' => 'baja'],
        ];
    }

    public static function byCountry(): array
    {
        return [
            ['label' => 'SV', 'value' => 4820],
            ['label' => 'GT', 'value' => 3640],
            ['label' => 'HN', 'value' => 2180],
            ['label' => 'CR', 'value' => 1840],
            ['label' => 'MX', 'value' => 1620],
            ['label' => 'PA', 'value' => 980],
        ];
    }

    public static function all(): array
    {
        return [
            'navSections'    => self::navSections(),
            'kpis'           => self::kpis(),
            'trafficSeries' => self::trafficSeries(),
            'trafficLabels' => self::trafficLabels(),
            'fuentes'        => self::fuentes(),
            'keywords'       => self::keywords(),
            'pipelineStages' => self::pipelineStages(),
            'recentLeads'    => self::recentLeads(),
            'campaigns'      => self::campaigns(),
            'activity'       => self::activity(),
            'tasks'          => self::tasks(),
            'byCountry'      => self::byCountry(),
        ];
    }
}
