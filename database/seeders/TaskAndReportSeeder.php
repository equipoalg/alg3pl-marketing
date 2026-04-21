<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryReport;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskAndReportSeeder extends Seeder
{
    public function run(): void
    {
        $countryMap = Country::pluck('id', 'code')->toArray();

        // ── TASKS (91 from 6 countries) ──
        $tasks = [
            // ── EL SALVADOR (17) ──
            ['sv','P0','seo','Fijar 96 páginas no indexadas (49 redirects + 22 thin + 7 canonical)','3w','+++','Hemorragia de tráfico'],
            ['sv','P0','seo','Investigar caída 79% /sv/servicios/ en GSC','1w','+++','Posible penalización algoritmo'],
            ['sv','P0','analytics','Configurar conversiones nativas en GA4 (no solo Ads)','3d','+++','Ceguera de conversión'],
            ['sv','P0','technical','Corregir 49 redirects rotos','1w','++','Audit con GSC'],
            ['sv','P0','technical','Reparar 2 errores 404','1d','+','Links rotos'],
            ['sv','P1','content','Crear landing pages para keywords comerciales (transporte, aduanas)','4w','+++','90% tráfico es marca'],
            ['sv','P1','seo','Optimizar páginas de servicios existentes para SEO','3w','++','Bajo engagement en servicios'],
            ['sv','P1','ux','Agregar CTAs claros en todas las páginas','2w','++','Mejorar conversión'],
            ['sv','P1','content','Revivir blog con contenido de logística/aduanas','3w','++','9 vistas en 28 días'],
            ['sv','P1','seo','Fijar 7 páginas sin canonical tags','3d','+','Duplicados confunden Google'],
            ['sv','P2','technical','Implementar Schema markup (LocalBusiness, Service, FAQPage)','2w','++','Mejora CTR en GSC'],
            ['sv','P2','content','Optimizar para AI search (ChatGPT, Perplexity, etc)','3w','++','4% tráfico nuevo emergente'],
            ['sv','P2','content','Crear contenido geográfico específico (San Salvador focus)','2w','+','42% tráfico es San Salvador'],
            ['sv','P2','ux','Mejorar retención de usuarios (rediseño UX)','6w','+++','Engagement bajó 31%'],
            ['sv','P3','technical','Optimizar imágenes (compresión, WebP, lazy loading)','1w','+','Velocidad de página'],
            ['sv','P3','marketing','Crear presencia en redes sociales (LinkedIn, Facebook)','2w','+','0% tráfico social'],
            ['sv','P3','seo','Construir backlinks desde directorios y partners','4w','+','Authority building'],

            // ── GUATEMALA (16) ──
            ['gt','P0','seo','Reescribir 10 meta titles y descriptions',null,'+','Titles listos en P0-4'],
            ['gt','P0','analytics','Configurar tracking conversiones GA4',null,'+','Formularios, tel, WhatsApp'],
            ['gt','P0','seo','Auditar y corregir meta descriptions',null,'+','1 sin description, 1 truncada'],
            ['gt','P1','seo','Agregar ALT tags a imágenes (77% sin ALT)',null,'++','Accesibilidad + SEO'],
            ['gt','P1','content','Crear landing page empresas logística guatemala',null,'++',''],
            ['gt','P1','seo','Optimizar página paquetería con keywords',null,'+',''],
            ['gt','P1','seo','Optimizar carga marítima con keywords',null,'+',''],
            ['gt','P1','ux','Agregar CTAs en páginas de servicio',null,'++',''],
            ['gt','P1','technical','Implementar schema markup',null,'+',''],
            ['gt','P2','content','Crear 2 artículos blog keywords comerciales',null,'+',''],
            ['gt','P2','content','Crear landing agencia aduanal guatemala',null,'++',''],
            ['gt','P2','content','Crear landing transporte terrestre guatemala',null,'++',''],
            ['gt','P2','technical','Verificar sitemap y robots.txt',null,'+',''],
            ['gt','P3','content','Crear 4 artículos blog adicionales',null,'+',''],
            ['gt','P3','seo','Optimizar páginas basado en datos 3 meses',null,'+',''],
            ['gt','P3','analytics','Reporte resultados y recomendaciones',null,'+',''],

            // ── COSTA RICA (11) ──
            ['cr','P0','analytics','Implementar GA4 event tracking para formularios, calls, downloads',null,'+++','Setup en GSC también'],
            ['cr','P0','seo','Optimizar meta titles/descriptions (aduana costa rica: 478 imp, 2 clics)',null,'+++','Quick wins'],
            ['cr','P0','technical','Investigar caída engagement 44s → 34s. Revisar bounce rate y velocidad',null,'++','UX mobile'],
            ['cr','P1','ux','Nueva página optimizada para búsquedas comerciales en CR con CTAs',null,'++',''],
            ['cr','P1','content','Crear guía de procesos aduanales CR, tramitología, documentos',null,'++',''],
            ['cr','P1','content','Página/blog para traders chinos/Singapore (bilingüe)',null,'+',''],
            ['cr','P2','content','Posts sobre importación/exportación rutas principales (1 post/semana)',null,'+',''],
            ['cr','P2','technical','Agregar LocalBusiness + Service schema',null,'+',''],
            ['cr','P2','seo','Partner con importadores/exportadores para guest posts y backlinks',null,'+',''],
            ['cr','P3','technical','Comprimir y optimizar imágenes. Mejorar Core Web Vitals',null,'+',''],
            ['cr','P3','seo','Identificar directorios logísticos y blogs de negocios para links',null,'+',''],

            // ── HONDURAS (17) ──
            ['hn','P0','analytics','Configurar tracking de conversiones en GA4 (formulario contacto)','1d','++',''],
            ['hn','P0','seo','Optimizar meta titles para keywords aduanales','3d','++','servicios aduaneros, transporte terrestre'],
            ['hn','P0','seo','Optimizar meta descriptions para mejorar CTR','3d','++','Todas las páginas'],
            ['hn','P0','ux','Investigar causa decline de engagement (38s vs 53s en 2025)','3d','++',''],
            ['hn','P1','content','Crear página Servicios Aduaneros Honduras (keyword: 210 impresiones)',null,'+++',''],
            ['hn','P1','content','Crear landing page Logística Honduras (keyword: 131 impresiones)',null,'++',''],
            ['hn','P1','content','Crear página Transporte Terrestre Honduras (keyword: 114 impresiones)',null,'++',''],
            ['hn','P1','ux','Optimizar página de trámites aduanales HN + agregar CTAs',null,'++',''],
            ['hn','P1','ux','Agregar CTA (botón contacto) a todas las páginas principales','1d','+',''],
            ['hn','P1','seo','Crear guía SEO en Honduras (para rankear keywords comerciales)',null,'++',''],
            ['hn','P2','content','Blog strategy: posts mensuales sobre logística Honduras',null,'+',''],
            ['hn','P2','technical','Agregar schema markup (FAQPage, LocalBusiness) a páginas HN','3d','+',''],
            ['hn','P2','content','Crear contenido geográfico: ALG en Tegucigalpa, San Pedro Sula','3d','+',''],
            ['hn','P2','seo','Mejorar internal linking entre páginas de servicios HN','3d','+',''],
            ['hn','P3','seo','Optimizar imágenes de páginas HN (compresión, alt text)','3d','+',''],
            ['hn','P3','seo','Link building: conseguir 5-10 links desde directorios logísticos','1w','+',''],
            ['hn','P3','technical','Mejorar velocidad de carga de páginas HN','3d','+',''],

            // ── NICARAGUA (17) ──
            ['ni','P0','analytics','Configurar tracking de conversiones en GA4','1d','++',''],
            ['ni','P0','seo','Optimizar meta titles para keywords aduanales (aduana nicaragua)','3d','++',''],
            ['ni','P0','seo','Optimizar meta descriptions para mejorar CTR en aduanas','3d','++',''],
            ['ni','P0','ux','Investigar y resolver decline de engagement (37s es muy bajo)','3d','++',''],
            ['ni','P1','seo','Expandir página Aduanas de Nicaragua (1,113+602+285 impresiones)',null,'+++',''],
            ['ni','P1','content','Crear landing page Trámites Aduanales Nicaragua (CTAs fuertes)',null,'++',''],
            ['ni','P1','ux','Agregar CTA (botón contacto) a TODAS las páginas de aduanas','1d','+',''],
            ['ni','P1','content','Crear guía completa Cómo importar/exportar en Nicaragua',null,'++',''],
            ['ni','P1','seo','Optimizar página de transporte terrestre NI','3d','+',''],
            ['ni','P1','ux','Mejorar UX de páginas aduanales (reducir bounce, aumentar engagement)',null,'++',''],
            ['ni','P2','content','Blog strategy: posts sobre procesos aduanales Nicaragua',null,'+',''],
            ['ni','P2','technical','Agregar schema markup (FAQPage, LocalBusiness) a páginas NI','3d','+',''],
            ['ni','P2','content','Crear contenido geográfico: ALG en Managua, ALG en León','3d','+',''],
            ['ni','P2','seo','Mejorar internal linking entre páginas de aduanas','3d','+',''],
            ['ni','P3','seo','Optimizar imágenes de páginas NI (compresión, alt text)','3d','+',''],
            ['ni','P3','seo','Link building: conseguir 5 links desde directorios aduanales','1w','+',''],
            ['ni','P3','technical','Mejorar velocidad de carga (Test de Core Web Vitals)','3d','+',''],

            // ── PANAMA (13) ──
            ['pa','P0','analytics','Implementar GA4 event tracking para formularios y cotizaciones',null,'+++','Setup en GSC también'],
            ['pa','P0','ux','Investigar caída engagement 42s → 25s (-39% en 3 meses)',null,'+++','Revisar bounce rate, velocidad, UX'],
            ['pa','P0','seo','Mejorar meta titles/descriptions (237 imp, 7 clics). Quick win: +30 clics/mes',null,'++',''],
            ['pa','P1','content','Nueva página optimizada para keyword 3pl panama (164 imp)',null,'++',''],
            ['pa','P1','ux','Optimizar empresas logisticas panama (237 imp, 7 clics) + CTAs cotizar',null,'++',''],
            ['pa','P1','content','Página/blog en inglés para traders asiáticos (rutas PA-Asia)',null,'+',''],
            ['pa','P1','seo','Crear FAQ sobre qué es logística (87 impresiones)',null,'+',''],
            ['pa','P2','content','Posts importación/exportación y procesos aduanales PA (2 posts/mes)',null,'+',''],
            ['pa','P2','technical','Agregar schema proveedor 3PL en Panamá. Visibilidad local',null,'+',''],
            ['pa','P2','seo','Contactar navieras/importadores para guest posts y backlinks (Focus Asia)',null,'+',''],
            ['pa','P2','seo','Expandir a keywords de Colombia, Ecuador, Perú. PA como hub Sudamérica',null,'++',''],
            ['pa','P3','technical','Comprimir y optimizar imágenes. Mejorar Core Web Vitals',null,'+',''],
            ['pa','P3','seo','Identificar directorios logísticos y portales B2B para links',null,'+',''],
        ];

        foreach ($tasks as $t) {
            [$code, $priority, $category, $title, $effort, $impact, $notes] = $t;
            $countryId = $countryMap[$code] ?? null;
            if (!$countryId) continue;

            Task::create([
                'country_id' => $countryId,
                'title' => $title,
                'category' => $category,
                'priority' => $priority,
                'effort' => $effort,
                'impact' => $impact,
                'status' => 'pending',
                'notes' => $notes ?: null,
                'source_file' => "TAREAS_" . strtoupper($code) . ".xlsx",
            ]);
        }

        $this->command->info("Imported " . count($tasks) . " tasks across 6 countries.");

        // ── COUNTRY REPORTS ──
        $reports = [
            ['sv', [
                'kpis' => ['Usuarios totales (27m)' => '4,375', 'Sesiones orgánicas' => '2,770', 'Conversiones' => '31', 'Enganche promedio' => '51s', 'Páginas indexadas' => '59/155 (38%)', 'Clics GSC (16m)' => '1,390', 'Posición media' => '14.9', 'Tráfico marca' => '~90%'],
                'findings' => [
                    ['title' => 'SV es el LÍDER DIGITAL de toda ALG', 'detail' => '4,375 usuarios, 2,770 sesiones orgánicas y 31 conversiones en 27 meses. Todos los demás países juntos no tienen ni la mitad.'],
                    ['title' => 'Dependencia peligrosa de MARCA (90%)', 'detail' => '90% del tráfico viene de búsquedas de marca. Sin presencia en keywords comerciales.'],
                    ['title' => 'Engagement en CAÍDA LIBRE (-31% en 2 años)', 'detail' => 'Tiempo bajó de 1m14s (2024) a 51s (2026). La gente entra pero se va inmediatamente.'],
                    ['title' => '96 páginas DESAPARECIDAS de Google', 'detail' => '62% de páginas no indexadas: 49 redirects, 22 thin content, 7 sin canonical.'],
                    ['title' => '/sv/servicios/ COLAPSÓ 79% en impresiones', 'detail' => 'Probable actualización de algoritmo o contenido duplicado.'],
                    ['title' => 'ChatGPT envía 4% del tráfico (emergente)', 'detail' => '13 visitas desde ChatGPT. Oportunidad sin competencia.'],
                    ['title' => 'Blog MUERTO (9 vistas en 28 días)', 'detail' => 'Contenido no atrae. Revitalizar con keywords comerciales o eliminar.'],
                    ['title' => '93% nuevos usuarios = CERO RETENCIÓN', 'detail' => 'Nadie vuelve. La experiencia no genera cliente recurrente.'],
                ],
                'opportunities' => [
                    ['title' => 'Indexar las 96 páginas perdidas', 'detail' => 'Reparar redirects, crear contenido thin, fijar canonicals. Podría duplicar tráfico.', 'impact' => '+++'],
                    ['title' => 'Crear contenido comercial', 'detail' => 'Landing pages para keywords no-marca: transportista SV, empresa logística, trámites aduanales.', 'impact' => '+++'],
                    ['title' => 'Rediseñar experiencia de usuario', 'detail' => 'Mejorar navegación, CTAs, recuperar 30-40s de engagement.', 'impact' => '++'],
                    ['title' => 'Optimizar para AI search', 'detail' => 'Contenido para preguntas: ¿Cuánto cuesta enviar un container a USA?', 'impact' => '++'],
                    ['title' => 'Expandir geográficamente', 'detail' => 'Landing pages para GT, HN, NI con keywords locales. Podría triplicar tráfico.', 'impact' => '+++'],
                ],
                'summary' => 'SV es el líder digital de ALG con 4,375 usuarios y 31 conversiones en 27 meses, pero enfrenta dependencia de marca (90%), caída de engagement (-31%) y 96 páginas perdidas de Google.',
            ]],
            ['gt', [
                'kpis' => ['Usuarios 2025' => '1,788', 'Sesiones orgánicas 2025' => '1,100', 'Conversiones' => '0', 'Enganche promedio' => '45s', 'Clics GSC' => '280', 'Posición media' => '18.2'],
                'findings' => [
                    ['title' => 'GT es el 2do país en tráfico total', 'detail' => '1,788 usuarios en 2025. Crecimiento sostenido pero sin conversiones configuradas.'],
                    ['title' => '77% de imágenes sin ALT tags', 'detail' => 'Problema de accesibilidad y SEO que afecta ranking.'],
                    ['title' => 'CERO conversiones configuradas en GA4', 'detail' => 'No hay tracking de formularios ni llamadas. Ceguera total.'],
                ],
                'opportunities' => [
                    ['title' => 'Crear contenido para keywords comerciales', 'detail' => 'Paquetería Guatemala, agencia aduanal Guatemala, carga marítima GT.', 'impact' => '+++'],
                    ['title' => 'Mejorar CTR con mejor presentación en Google', 'detail' => 'Meta titles y descriptions optimizados. Quick win.', 'impact' => '++'],
                ],
                'summary' => 'Guatemala es el 2do mercado digital de ALG. Sin conversiones configuradas, 77% de imágenes sin ALT tags, pero con potencial comercial significativo.',
            ]],
            ['cr', [
                'kpis' => ['Usuarios 2025' => '1,200', 'Sesiones orgánicas' => '800', 'Conversiones' => '0', 'Enganche promedio' => '34s', 'Clics GSC' => '156', 'Keyword líder' => 'aduana costa rica (478 imp)'],
                'findings' => [
                    ['title' => 'Engagement cayó de 44s a 34s', 'detail' => 'Tendencia negativa que requiere investigación de UX y velocidad.'],
                    ['title' => 'Keyword aduana costa rica: 478 impresiones, solo 2 clics', 'detail' => 'Oportunidad enorme desperdiciada por meta descriptions pobres.'],
                ],
                'opportunities' => [
                    ['title' => 'Optimizar keyword empresas transporte carga costa rica', 'detail' => '126 impresiones pero SOLO 1 clic. Demanda existe, falta optimización.', 'impact' => '+++'],
                    ['title' => 'Contenido para traders asiáticos', 'detail' => 'China/Singapore representan tráfico emergente. Contenido bilingüe.', 'impact' => '++'],
                ],
                'summary' => 'Costa Rica muestra crecimiento (+23% proyectado) pero engagement preocupante (34s). Gran oportunidad en keyword aduana costa rica (478 impresiones).',
            ]],
            ['hn', [
                'kpis' => ['Usuarios 2025' => '900', 'Sesiones orgánicas' => '600', 'Conversiones' => '0', 'Enganche promedio' => '38s', 'Keyword líder' => 'servicios aduaneros HN (210 imp)'],
                'findings' => [
                    ['title' => 'Engagement decline: 53s → 38s en 2025', 'detail' => 'Caída significativa que necesita investigación urgente.'],
                    ['title' => 'Keywords aduanales con demanda no capturada', 'detail' => 'Servicios aduaneros (210 imp), logística Honduras (131 imp), transporte terrestre (114 imp).'],
                ],
                'opportunities' => [
                    ['title' => 'Crear 3 landing pages de servicios específicos', 'detail' => 'Aduaneros, logística, transporte terrestre. 455 impresiones combinadas.', 'impact' => '+++'],
                    ['title' => 'Contenido geográfico Tegucigalpa y San Pedro Sula', 'detail' => 'Localizar servicios para las dos ciudades principales.', 'impact' => '++'],
                ],
                'summary' => 'Honduras tiene keywords aduanales con demanda real (455+ impresiones) pero sin landing pages dedicadas. Engagement en declive.',
            ]],
            ['ni', [
                'kpis' => ['Usuarios 2025' => '1,100', 'Sesiones orgánicas' => '800', 'Conversiones' => '0', 'Enganche promedio' => '37s', 'Keyword líder' => 'aduana nicaragua (1,113 imp)'],
                'findings' => [
                    ['title' => 'NI tiene el keyword más fuerte de toda ALG', 'detail' => 'aduana nicaragua: 1,113 impresiones. Es el keyword con más volumen de todos los países.'],
                    ['title' => 'Engagement muy bajo: 37s', 'detail' => 'El más bajo de todos los países. UX necesita mejora urgente.'],
                ],
                'opportunities' => [
                    ['title' => 'Dominar keywords de aduanas Nicaragua', 'detail' => 'aduana nicaragua (1,113), aduana de nicaragua (602), aduanas nic (285). Total: 2,000+ impresiones.', 'impact' => '+++'],
                    ['title' => 'Guía de importación/exportación Nicaragua', 'detail' => 'Contenido evergreen de alto valor. Captura búsquedas comerciales.', 'impact' => '++'],
                ],
                'summary' => 'Nicaragua tiene el keyword más fuerte de ALG (aduana nicaragua: 1,113 impresiones) pero engagement crítico (37s). Enorme potencial aduanal.',
            ]],
            ['pa', [
                'kpis' => ['Usuarios 2025' => '950', 'Sesiones orgánicas' => '600', 'Conversiones' => '0', 'Enganche promedio' => '25s', 'Keyword líder' => 'empresas logisticas panama (237 imp)', 'Clics GSC' => '445'],
                'findings' => [
                    ['title' => 'Engagement COLAPSÓ: 42s → 25s (-39%) en 3 meses', 'detail' => 'La caída más agresiva de todos los países. Investigación urgente.'],
                    ['title' => 'Potencial 3PL internacional', 'detail' => 'Keywords como 3pl panama (164 imp) indican demanda de traders internacionales.'],
                ],
                'opportunities' => [
                    ['title' => 'Posicionar ALG como proveedor 3PL en Panamá', 'detail' => '3pl panama (164 imp), empresas logisticas panama (237 imp). Hub para Sudamérica.', 'impact' => '+++'],
                    ['title' => 'Contenido en inglés para traders asiáticos', 'detail' => 'Rutas PA-Asia, tiempos de envío, procesos. Mercado sin explotar.', 'impact' => '++'],
                    ['title' => 'Expandir a Colombia, Ecuador, Perú', 'detail' => 'PA como hub logístico para Sudamérica. Keywords con poca competencia.', 'impact' => '++'],
                ],
                'summary' => 'Panamá tiene engagement colapsado (-39%) pero potencial internacional: keywords 3PL, traders asiáticos, hub para Sudamérica.',
            ]],
        ];

        foreach ($reports as [$code, $data]) {
            $countryId = $countryMap[$code] ?? null;
            if (!$countryId) continue;

            CountryReport::updateOrCreate(
                ['country_id' => $countryId, 'period' => '2024-2026 YTD', 'type' => 'seo'],
                [
                    'kpis' => $data['kpis'],
                    'findings' => $data['findings'],
                    'opportunities' => $data['opportunities'],
                    'summary' => $data['summary'],
                    'source_file' => "INFORME_" . strtoupper($code) . ".docx",
                ]
            );
        }

        $this->command->info("Imported " . count($reports) . " country reports.");
    }
}
